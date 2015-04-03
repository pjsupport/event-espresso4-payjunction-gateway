<?php if ( ! defined( 'EVENT_ESPRESSO_VERSION' )) { exit(); }
/**
 * ------------------------------------------------------------------------
 *
 * Class  EE_Payjunction
 *
 * @package			Event Espresso
 * @subpackage		espresso-payjunction
 * @author			    Brent Christensen
 * @ version		 	$VID:$
 *
 * ------------------------------------------------------------------------
 */
// define the plugin directory path and URL
define( 'EE_PAYJUNCTION_BASENAME', plugin_basename( EE_PAYJUNCTION_PLUGIN_FILE ));
define( 'EE_PAYJUNCTION_PATH', plugin_dir_path( __FILE__ ));
define( 'EE_PAYJUNCTION_URL', plugin_dir_url( __FILE__ ));
Class  EE_Payjunction extends EE_Addon {

	/**
	 * class constructor
	 */
	public function __construct() {
	}

	public static function register_addon() {
		// register addon via Plugin API
		EE_Register_Addon::register(
			'Payjunction',
			array(
				'version' 					=> EE_PAYJUNCTION_VERSION,
				'min_core_version' => '4.6.0.dev.000',
				'main_file_path' 				=> EE_PAYJUNCTION_PLUGIN_FILE,
				// if plugin update engine is being used for auto-updates. not needed if PUE is not being used.
				'pue_options'			=> array(
					'pue_plugin_slug' => 'espresso_payjunction',
					'plugin_basename' => EE_PAYJUNCTION_BASENAME,
					'checkPeriod' => '24',
					'use_wp_update' => FALSE,
					),
				'payment_method_paths' => array(
					EE_PAYJUNCTION_PATH . 'payment_methods' . DS . 'Payjunction_Onsite',
					),
		));
	}



	/**
	 * 	additional_admin_hooks
	 *
	 *  @access 	public
	 *  @return 	void
	 */
	public function additional_admin_hooks() {
		// is admin and not in M-Mode ?
		if ( is_admin() && ! EE_Maintenance_Mode::instance()->level() ) {
			add_filter( 'plugin_action_links', array( $this, 'plugin_actions' ), 10, 2 );
		}
	}



	/**
	 * plugin_actions
	 *
	 * Add a settings link to the Plugins page, so people can go straight from the plugin page to the settings page.
	 * @param $links
	 * @param $file
	 * @return array
	 */
	public function plugin_actions( $links, $file ) {
		if ( $file == EE_PAYJUNCTION_BASENAME ) {
			// before other links
			array_unshift( $links, '<a href="admin.php?page=espresso_payments">' . __('Settings') . '</a>' );
		}
		return $links;
	}






}
// End of file EE_Payjunction.class.php
// Location: wp-content/plugins/espresso-payjunction/EE_Payjunction.class.php
