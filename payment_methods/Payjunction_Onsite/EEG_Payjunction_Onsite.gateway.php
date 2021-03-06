<?php

if (!defined('EVENT_ESPRESSO_VERSION')) {
	exit('No direct script access allowed');
}

class EEG_Payjunction_Onsite extends EE_Onsite_Gateway{

	protected $_api_login;
    protected $_api_pass;
    public $_api_key_live = 'd96b5255-91f1-47f6-85d7-2d084dff34cd';
    public $_api_key_labs = 'd4aa13a8-a04e-46b0-b2fa-0fb2d463d754';
    
    protected $_currrencies_supported = array('USD');
    protected $_supports_sending_refunds = true;
    
    protected $_local_avs;
    protected $_avs_mode;
    protected $_cvv_mode;
    protected $_auth_only;
    protected $_request_signature;
    
    private $_post_fields = array();
    private $_fraud_alert = false;
    
    const PRODUCTION_URL = "https://api.payjunction.com/transactions";
    const LABS_URL = "https://api.payjunctionlabs.com/transactions";
    
    public function process_rest_request($type, $post=null, $txnid=null) {
        // Make sure cURL is installed before proceeding
        //if (function_exists('curl_version')) {
        if ($this->_debug_mode) {
            $gateway = self::LABS_URL;
            $appkey = $this->_api_key_labs;
            $login = 'pj-ql-01';
            $pass = 'pj-ql-01p';
        } else {
            $gateway =  self::PRODUCTION_URL;
            $appkey = $this->_api_key_live;
            $login = $this->_api_login;
            $pass = $this->_api_pass;
        }
		$url = !is_null($txnid) ? $gateway."/".$txnid : $gateway;
		$options = array(
		    'httpversion' => '1.1',
		    'headers' => array('X-PJ-Application-Key' => $appkey, 'Accept' => 'application/json', 'Authorization' =>  'Basic ' . base64_encode($login.':'.$pass)),
		    'sslverify' => true,
		    'method' => $type,
		    'body' => $post
	    );
	    $content = wp_remote_request($url, $options);
	    
	    if (is_wp_error($content)) {
	        return array('errors' => array('type' => 'wp_remote_request', 'message' => __($content->get_error_message(), 'event_espresso')));
	    } else {
		    return json_decode(wp_remote_retrieve_body($content), true);
	    }
	}
	
	protected function _filter_fraud_declines($code, $message) {
	    $fraud_codes = array('AA', 'AI', 'AN', 'AU', 'AW', 'AX', 'AY', 'AZ', 'CV', 'FB', 'FF', 'FG');
	    if (!in_array($code, $fraud_codes)) {
	        $this->_fraud_alert = false;
	        return sprintf("%s: %s", $code, $message);
	    } else {
	        $this->_fraud_alert = true;
	        return __('There was an issue with processing the transaction, please contact us directly before attempting to run the card again', 'event_espresso');
	    }
	}
    
    public function do_direct_payment($payment, $billing_info = null) {
    	$txn = $payment->transaction();
    	$pr = $txn->primary_registration();
        // Build our post array
        $this->_post_fields['amountBase'] = $this->format_currency($payment->amount());
        $this->_post_fields['cardNumber'] = $billing_info['cc_num'];
        $this->_post_fields['cardExpMonth'] = $billing_info['cc_month'];
        $this->_post_fields['cardExpYear'] = $billing_info['cc_year'];
        $this->_post_fields['billingFirstName'] = $billing_info['first_name'];
        $this->_post_fields['billingLastName'] = $billing_info['last_name'];
        $this->_post_fields['billingAddress'] = $billing_info['address'];
        $this->_post_fields['billingCity'] = $billing_info['city'];
        $this->_post_fields['billingState'] = $billing_info['state'];
        $this->_post_fields['billingZip'] = $billing_info['zip'];
        $this->_post_fields['billingPhone'] = $billing_info['phone'];
        $this->_post_fields['customerId'] = $pr->ID();
        // if $_cvv_mode is true, we DON'T run CVV check
        if ($this->_cvv_mode) {
            $this->_post_fields['cvv'] = 'OFF';
        } else {
            $this->_post_fields['cvv'] = 'ON';
            $this->_post_fields['cardCvv'] = $billing_info['cvv'];
        }
        if ($this->_local_avs) $this->_post_fields['avs'] = $this->_avs_mode;
        if ($this->_auth_only) $this->_post_fields['action'] = "HOLD";
        
        $response = $this->process_rest_request("POST", $this->_post_fields);
        
        if (!empty($response)) {
        	if (isset($response['transactionId'])) { // Valid response, this field would not be sent if there were errors
        	    //$payment->set_transaction_id($response['transactionId']);
	        	$status = (strcmp($response['response']['code'], '00') == 0 || strcmp($response['response']['code'], '85') == 0) ? $this->_pay_model->approved_status() : $this->_pay_model->declined_status();
	        	$payment->set_status($status);
	        	$payment->set_amount(floatval($response['amountTotal']));
	        	$payment->set_gateway_response($this->_filter_fraud_declines($response['response']['code'], $response['response']['message']));
	        	$this->_fraud_alert ? $payment->set_extra_accntng(sprintf("%s: %s", $response['response']['code'], $response['response']['message'])) : $payment->set_extra_accntng($pr->reg_code());
	        	$payment->set_txn_id_chq_nmbr($response['transactionId']);
	        	$payment->set_details(print_r($response, true));
        	} else { // If we don't have a transaction id, we must have hit at least one error
        		$payment->set_status($this->_pay_model->declined_status());
        		$errors = array();
                foreach ($response['errors'] as $err) {
                    if (isset($err['parameter'])) {
                        $errors[] = sprintf('%s: %s - %s; ',$err['type'], $err['parameter'], $err['message']);
                    } else {
                        $errors[] = sprintf('%s: %s; ', $err['type'], $err['message']);
                    }
                }
                $messages = sprintf(_n( 'There has been %s error: %s', 'There have been %s errors: %s', count($errors), 'event_espresso'), count($errors), implode('<br>', $errors));
    			$payment->set_gateway_response($messages);
    			$payment->set_details(print_r($response, true));
        	}
        } else { // No response from the Trinity servers
        	$payment->set_status($this->_pay_model->declined_status());
        	$payment->set_gateway_response(__("Reply from the Trinity gateway servers not received.", "event_espresso"));
        	$payment->set_details(print_r($response));
        }
        return $payment;
    }
    
    public function do_direct_refund($payment, $refund_info = null) {
    	// In order to do a direct refund, we MUST have the transactionId token
    	$transactionId = $payment->get_txn_id_chq_nmbr();
    	if (empty($transactionId)) {
    		$payment->set_status($this->_pay_model->declined_status());
    		$payment->set_details(__('Refund cannot be processed because there was no transactionId found', 'event_espresso'));
    		$payment->set_gateway_response('Request not sent');
    	} else {
    		// We seem to have a transactionId, let's try and run the refund
    		$post = array(
    			'transactionId' => $transactionId,
    			'amountBase' => $this->format_currency($payment->amount()),
    			'action' => 'REFUND',
    			'billingFirstName' => $refund_info['first_name'],
    			'billingLastName' => $refund_info['last_name'],
    			'billingAddress' => $refund_info['address'],
    			'billingCity' => $refund_info['city'],
    			'billingState' => $refund_info['state'],
    			'billingZip' => $refund_info['zip'],
    			'billingPhone' => $refund_info['phone']
			);
			$response = $this->process_rest_request('POST', $post, $transactionId);
			if (!empty($response)) {
				if (isset($response['transactionId'])) {
					if (in_array($response['response']['code'], array('00', '85'))) {
						//Success
						$payment->set_status($this->_pay_model->approved_status());
						$payment->set_amount(floatval($response['amountTotal']));
						$payment->set_gateway_response(sprintf('%s: %s', $response['response']['code'], $response['response']['message']));
						$payment->set_details(print_r($response));
						$payment->set_extra_accntng($payment->primary_registration()->reg_code());
					} else {
						//Declined
						$payment->set_status($this->_pay_model->declined_status());
						$payment->set_details(print_r($response));
						$payment->set_gateway_response(sprintf('%s: %s', $response['response']['code'], $response['response']['message']));
					}
				} else { // If we don't have a transaction id, we must have hit at least one error
	        		$payment->set_status($this->_pay_model->declined_status());
	        		$errors = array();
                    foreach ($response['errors'] as $err) {
                        if (isset($err['parameter'])) {
                            $errors[] = sprintf('%s: %s - %s; ',$err['type'], $err['parameter'], $err['message']);
                        } else {
                            $errors[] = sprintf('%s: %s; ', $err['type'], $err['message']);
                        }
                    }
                    $messages = sprintf(_n( 'There has been %s error: %s', 'There have been %s errors: %s', count($errors), 'event_espresso'), count($errors), implode('<br>', $errors));
	    			$payment->set_gateway_response($messages);
	    			$payment->set_details(print_r($response, true));
	        	}
	        } else { // No response from the Trinity servers
	        	$payment->set_status($this->_pay_model->declined_status());
	        	$payment->set_gateway_response(__("Reply from the Trinity gateway servers not received.", "event_espresso"));
	        	$payment->set_details(print_r($response));
	        }
    	}
        return $payment;
    }
}

// End of file EEG_Payjunction_Onsite.php