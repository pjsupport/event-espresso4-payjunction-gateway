<?php
/*
  Plugin Name: Event Espresso - PayJunction
  Plugin URI: http://www.payjunction.com
  Description: PayJunction Trinity payment gateway for Event Espresso 4
  Version: 1.0.3.dev.000
  Author: PayJunction
  Author URI: http://www.payjunction.com
  
  This program is free software; you can redistribute it and/or modify
  it under the terms of the GNU General Public License, version 2, as
  published by the Free Software Foundation.

  This program is distributed in the hope that it will be useful,
  but WITHOUT ANY WARRANTY; without even the implied warranty of
  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.See the
  GNU General Public License for more details.

  You should have received a copy of the GNU General Public License
  along with this program; if not, write to the Free Software
  Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA02110-1301USA
 *
 * ------------------------------------------------------------------------
 *
 * Event Espresso
 *
 * Event Registration and Management Plugin for WordPress
 *
 * @ package		Event Espresso
 * @ author			Event Espresso
 * @ copyright	(c) 2008-2014 Event Espresso  All Rights Reserved.
 * @ license		http://eventespresso.com/support/terms-conditions/   * see Plugin Licensing *
 * @ link				http://www.eventespresso.com
 * @ version	 	EE4
 *
 * ------------------------------------------------------------------------
 */
define( 'EE_PAYJUNCTION_VERSION', '0.0.1.dev.002' );
define( 'EE_PAYJUNCTION_PLUGIN_FILE',  __FILE__ );
function load_espresso_payjunction() {
if ( class_exists( 'EE_Addon' )) {
	// payjunction version
	require_once ( plugin_dir_path( __FILE__ ) . 'EE_Payjunction.class.php' );
	EE_Payjunction::register_addon();
}
}
add_action( 'AHEE__EE_System__load_espresso_addons', 'load_espresso_payjunction' );

// End of file espresso_payjunction.php
// Location: wp-content/plugins/espresso-payjunction/espresso_payjunction.php