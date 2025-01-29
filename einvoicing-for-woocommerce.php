<?php
/**
 * Plugin Name: E-Invoicing For WooCommerce
 * Plugin URI: https://www.woo-einvoicing.com
 * Version: 0.3.0
 * Author: Instareza
 * Author URI: https://www.instareza.com
 * Description: Setup your WooCommerce PDF invoices effortlessly and ensure compliance with the latest electronic invoicing regulations! Enable Factur-X, UBL, ZUGFeRD and Xrechnung standards while customizing your invoices to reflect your brand.
 * Text Domain: einvoicing-for-woocommerce
 * Domain Path: /languages
 * Requires PHP: 8.1
 * Requires Plugins: woocommerce
 * Stable tag: 0.3.0
 *
 * WC requires at least: 7.0
 * WC tested up to: 9.5.1
 *
 * License: GNU General Public License v3.0
 * License URI: http://www.gnu.org/licenses/gpl-3.0.html
 *
  * @package         WOOEI
 */

namespace WOOEI;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

define( 'WOOEI_VERSION', '0.3.0' );
define( 'WOOEI_PLUGIN_DIR', __DIR__ );
define( 'WOOEI_PLUGIN_FILE', __FILE__ );
define( 'WOOEI_VENDOR', WOOEI_PLUGIN_DIR . '/vendor/' );
define( 'WOOEI_INCLUDES', WOOEI_PLUGIN_DIR . '/includes/' );
define( 'WOOEI_ASSETS', WOOEI_PLUGIN_DIR . '/assets/' );
define( 'WOOEI_MIN_PHP_VER', '8.1' );
define( 'WOOEI_MIN_WC_VER', '7.0' );
define(
	'WOOEI_REQUIRED_EXTENSIONS',
	array(
		'gd',
		'mbstring',
		'iconv',
		'dom',
	)
);


require WOOEI_PLUGIN_DIR . '/init-freemius.php';

/**
 * Gets the environment warning.
 *
 * @return bool|string  The environment warning or false if all good.
 */
function get_environment_warning() {
	if ( version_compare( phpversion(), WOOEI_MIN_PHP_VER, '<' ) ) {
		/* translators: 1: minimun php version number. 2: current php version number */
		$message = __( 'E-Invoicing For Woocommerce - The minimum PHP version required for this plugin is %1$s. You are running %2$s.', 'einvoicing-for-woocommerce' );
		return sprintf( $message, WOOEI_MIN_PHP_VER, phpversion() );
	}

	foreach ( WOOEI_REQUIRED_EXTENSIONS as $name ) {
		if ( ! extension_loaded( $name ) ) {
			/* translators: 1: the required extension name */
			$message = __( 'E-Invoicing For Woocommerce requires the php extension "%s" to be loaded to work.', 'einvoicing-for-woocommerce' );
			return sprintf( $message, strtoupper( $name ) );
		}
	}

	if ( ! defined( 'WC_VERSION' ) ) {
		return __( 'E-Invoicing For Woocommerce requires WooCommerce to be activated to work.', 'einvoicing-for-woocommerce' );
	}

	if ( version_compare( WC_VERSION, WOOEI_MIN_WC_VER, '<' ) ) {
		/* translators: 1: minimun WooCommerce version number. 2: current WooCommerce version number */
		$message = __( 'E-Invoicing For Woocommerce - The minimum WooCommerce version required for this plugin is %1$s. You are running %2$s.', 'einvoicing-for-woocommerce' );
		return sprintf( $message, WOOEI_MIN_WC_VER, WC_VERSION );
	}

	return false;
}


/**
 * Loads the plugins files if no environment warning
 */
function load() {
	define( 'WOOEI_PLUGIN_BASENAME', plugin_basename( __FILE__ ) );
	load_plugin_textdomain( 'einvoicing-for-woocommerce', false, dirname( WOOEI_PLUGIN_BASENAME ) . '/languages' );
	$warning = get_environment_warning();
	if ( $warning ) {
		if ( is_admin() ) {
			add_action(
				'admin_notices',
				function () use ( $warning ) {
					printf( '<div class="notice notice-error"><p>%s</p></div>', esc_html( $warning ) );
				}
			);
		}
		return;
	}
	define( 'WOOEI_PLUGIN_ASSETS', untrailingslashit( plugins_url( '/', __FILE__ ) ) . '/assets/' );
	define( 'WOOEI_TEMPLATE', WOOEI_PLUGIN_DIR . '/templates/' );
	define(
		'WOOEI_EMAIL_TYPES',
		array(
			'new_order'                 => __( 'New order (Admin email)', 'einvoicing-for-woocommerce' ),
			'customer_completed_order'  => __( 'Completed order', 'einvoicing-for-woocommerce' ),
			'customer_processing_order' => __( 'Processing order', 'einvoicing-for-woocommerce' ),
			'customer_invoice'          => __( 'Customer invoice / Order details', 'einvoicing-for-woocommerce' ),
		)
	);

	define( 'WOOEI_TYPES_PDF', 'pdf' );
	define( 'WOOEI_TYPES_FACTURX', 'factur-x' );
	define( 'WOOEI_TYPES_ZUGFERD', 'zugferd' );
	define( 'WOOEI_TYPES_XRECHNUNG', 'xrechnung' );
	define( 'WOOEI_TYPES_UBL_PEPPOL', 'ubl_peppol' );
	define( 'WOOEI_TYPES_UBL_CIUS_AT', 'ubl_cius_at' );
	define( 'WOOEI_TYPES_UBL_CIUS_IT', 'ubl_cius_it' );
	define( 'WOOEI_TYPES_UBL_CIUS_NL', 'ubl_cius_nl' );
	define( 'WOOEI_TYPES_UBL_CIUS_ES', 'ubl_cius_es' );
	define( 'WOOEI_TYPES_UBL_CIUS_RO', 'ubl_cius_ro' );
	define(
		'WOOEI_TYPES',
		array(
			WOOEI_TYPES_PDF         => 'Pdf',
			WOOEI_TYPES_FACTURX     => 'Factur-x',
			WOOEI_TYPES_ZUGFERD     => 'ZUGFeRD',
			WOOEI_TYPES_XRECHNUNG   => 'XRechnung',
			WOOEI_TYPES_UBL_PEPPOL  => 'UBL (Peppol EU)',
			WOOEI_TYPES_UBL_CIUS_AT => 'UBL (Austrian)',
			WOOEI_TYPES_UBL_CIUS_IT => 'UBL (Italia)',
			WOOEI_TYPES_UBL_CIUS_NL => 'UBL (Netherlands)',
			WOOEI_TYPES_UBL_CIUS_ES => 'UBL (Spanish)',
			WOOEI_TYPES_UBL_CIUS_RO => 'UBL (Roumania)',
		)
	);

	define( 'WOOEI_NUMBERING_ORDER', 'order_id' );
	define( 'WOOEI_NUMBERING_INVOICE', 'invoice_number' );

	define(
		'WOOEI_NUMBERING_STRATEGY',
		array(
			WOOEI_NUMBERING_ORDER   => __( 'Use Order Number', 'einvoicing-for-woocommerce' ),
			WOOEI_NUMBERING_INVOICE => __( 'Use sequential invoice number', 'einvoicing-for-woocommerce' ),
		)
	);

	define( 'WOOEI_NUMBERING_NO_RESET', 'no' );
	define( 'WOOEI_NUMBERING_RESET_YEAR', 'yearly' );

	define(
		'WOOEI_INVOICE_NUMBER_RESET',
		array(
			WOOEI_NUMBERING_NO_RESET   => __( 'Never', 'einvoicing-for-woocommerce' ),
			WOOEI_NUMBERING_RESET_YEAR => __( 'Yearly', 'einvoicing-for-woocommerce' ),
		)
	);

	include WOOEI_VENDOR . 'autoload.php';

	// E-Invoicing functions.
	include WOOEI_INCLUDES . '/e-invoice.php';

	// Attachment Hooks.
	include WOOEI_INCLUDES . '/attachments.php';

	// Numbering functions and Hooks.
	include WOOEI_INCLUDES . '/numbering.php';

	// E-Invoice Pdf/Xml Generation.
	include WOOEI_INCLUDES . '/class-pdfinvoice.php';

	// Invoice Customizer.
	include WOOEI_INCLUDES . '/class-invoice-customizer.php';

	if ( is_admin() ) {
		include WOOEI_INCLUDES . '/changesets.php';
		// Onboarding stuff.
		include WOOEI_INCLUDES . '/admin.php';

		// woocommerce settings.
		add_filter(
			'woocommerce_get_settings_pages',
			function ( $settings ) {
				$settings[] = include WOOEI_INCLUDES . '/class-settings.php';
				return $settings;
			}
		);
	}

	// Common includes done.

	/**
	 * Our plugin is ready, and we are in dashboard let the word know it
	 *
	 * @since 0.0.4
	 */
	do_action( 'wooei_ready' );
}

// Activation hook to enable onboarding notice.
register_activation_hook(
	__FILE__,
	function () {
		update_option( 'wooei_version', WOOEI_VERSION );
		set_transient( 'wooei_just_activated', 'yes' );
	}
);

// Load when all plugins are loaded so we can check for WooCommerce version.
// We load at 9, class-invoice-customizer.php need to setup correctly the customizer by hooking into the customize_loaded_components filter before the customizer inits (customizer inits at 10 : _wp_customize_publish_changeset and _wp_customize_include in wp-includes/themes.php).
add_action( 'plugins_loaded', __NAMESPACE__ . '\load', 9 );
