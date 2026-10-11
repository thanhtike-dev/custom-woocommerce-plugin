<?php
/**
 * Plugin Name: Custom Inventory Manager
 * Description: A private frontend inventory management dashboard powered by WooCommerce.
 * Version: 1.0.0
 * Author: Than Htike
 * Requires Plugins: woocommerce
 * Text Domain: custom-inventory-manager
 *
 * @package TH THEME
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'CIM_VERSION', '1.0.0' );
define( 'CIM_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
define( 'CIM_PLUGIN_URL', plugin_dir_url( __FILE__ ) );

/**
 * Initialize the plugin.
 */
function cim_init() {
	if ( ! class_exists( 'WooCommerce' ) ) {
		add_action( 'admin_notices', 'cim_woocommerce_notice' );
		return;
	}

	require_once CIM_PLUGIN_DIR . 'includes/class-cim-inventory-dashboard.php';

	CIM_Inventory_Dashboard::init();
}
add_action( 'plugins_loaded', 'cim_init' );

/**
 * Show a warning if WooCommerce is inactive.
 */
function cim_woocommerce_notice() {
	if ( ! current_user_can( 'activate_plugins' ) ) {
		return;
	}

	echo '<div class="notice notice-error"><p>';
	echo esc_html__(
		'Custom Inventory Manager requires WooCommerce to be installed and activated.',
		'custom-inventory-manager'
	);
	echo '</p></div>';
}

function cim_enqueue_dashboard_assets() {
	if ( ! is_page( 'inventory' ) ) {
		return;
	}

	wp_enqueue_style(
		'cim-inventory',
		CIM_PLUGIN_URL . 'assets/css/inventory.css',
		array(),
		CIM_VERSION
	);
}
add_action( 'wp_enqueue_scripts', 'cim_enqueue_dashboard_assets' );
