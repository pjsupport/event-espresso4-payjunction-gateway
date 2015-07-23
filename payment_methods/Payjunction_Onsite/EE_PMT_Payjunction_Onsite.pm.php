<?php

if (!defined('EVENT_ESPRESSO_VERSION')) {
	exit('No direct script access allowed');
}

/**
 *
 * EE_PMT_Onsite
 *
 *
 * @package			Event Espresso
 * @subpackage
 * @author				Mike Nelson
 *
 */
class EE_PMT_Payjunction_Onsite extends EE_PMT_Base{
	public function __construct($pm_instance = NULL) {
        $gateway = plugin_dir_path( __FILE__) . '/EEG_Payjunction_Onsite.gateway.php';
        require_once $gateway;
        $this->_gateway = new EEG_PayJunction_Onsite();
        $this->_pretty_name = __("PayJunction Trinity", "event_espresso");
        $this->_default_description = __("Please fill out all required fields.", "event_espresso");
        $this->_requires_https = true;
        parent::__construct($pm_instance);
    }
    
    public function generate_new_billing_form(EE_Transaction $transaction = NULL) {
        $billing_form = new EE_Billing_Attendee_Info_Form($this->_pm_instance, array(
            'name' => 'PJ_Form',
            'subsections' => array(
                'cc_num' => new EE_Credit_Card_Input(array('required' => true, 'html_label_text' => __('Card Number', 'event_espresso'))),
                'cc_month' => new EE_Credit_Card_Month_Input(false, array('required' => true, 'html_label_text' => __('Expiration Month', 'event_espresso'))),
                'cc_year' => new EE_Credit_Card_Year_Input(array('required' => true, 'html_label_text' => __('Expiration Year', 'event_espresso'))),
                'cvv' => new EE_CVV_Input(array('html_label_text' => __('Card Security Code (CVV/CVC2)', 'event_espresso')))
            )
        ));
        
        return $billing_form;
    }
    
    public function generate_new_settings_form() {
        return new EE_Payment_Method_Form(array(
            'extra_meta_inputs' => array(
                'api_login' => new EE_Text_Input(array(
                    'html_label_text' => sprintf(__('QuickLink API Login %s', 'event_espresso'), $this->get_help_tab_link()),
                    'required' => true)
                ),
                'api_pass' => new EE_Text_Input(array(
                    'html_label_text' => sprintf(__('QuickLink API Password %s', 'event_espresso'), $this->get_help_tab_link()),
                    'required' => true)
                ),
                'local_avs' => new EE_Yes_No_Input(array(
                    'html_label_text' => sprintf(__('Use Local AVS Settings %s', 'event_espresso'), $this->get_help_tab_link()),
                    'default' => true)
                ),
                'avs_mode' => new EE_Select_Input(array(
                    'ADDRESS_OR_ZIP' => __('Address OR Zip', 'event_espresso'),
                    'ADDRESS_AND_ZIP' => __('Address AND Zip', 'event_espresso'),
                    'BYPASS' => __('Bypass AVS', 'event_espresso'),
                    'ADDRESS' => __('Street Address Only', 'event_espresso'),
                    'ZIP' => __('Zip/Postal Code Only', 'event_espresso'),
                    'OFF' => __('Turn off AVS', 'event_espresso')
                ), array(
                    'default' => 'ADDRESS_OR_ZIP',
                    'html_label_text' => sprintf(__('Address Verification Security Settings %s', 'event_espresso'), $this->get_help_tab_link())
                )),
                'cvv_mode' => new EE_Yes_No_Input(array(
                    'html_label_text' => sprintf(__('Disable CVV Security %s', 'event_espresso'), $this->get_help_tab_link()),
                    'default' => false)
                ),
                'auth_only' => new EE_Yes_No_Input(array(
                    'html_label_text' => sprintf(__('Authorize Only %s', 'event_espresso'), $this->get_help_tab_link()),
                    'html_help_text' => __('Transactions ran in this mode must be manually captured in your PayJunction account in order to be funded.', 'event_espresso'),
                    'default' => false)
                ),
                'request_signature' => new EE_Yes_No_Input(array(
                    'html_label_text' => sprintf(__('Send Signature Request %s', 'event_espresso'), $this->get_help_tab_link()),
                    'html_help_text' => __('Sends a copy of the receipt from PayJunction requesting the customer digitally sign for the transaction.', 'event_espresso'),
                    'default' => false)
                )
            )
        ));
    }
    
    public function help_tabs_config() {
        return array($this->get_help_tab_name() => array(
                'title' => __("PayJunction Trinity Settings", "event_espresso"),
                'filename'=> 'payment_methods_overview_payjunction'
            )
        );
    }
}

// End of file EE_PMT_Onsite.php