<?php
/**
 * This file implements upgrade notification, it also allows us to translate easily the changesets
 *
 * @package WOOEI
 */

namespace WOOEI;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Shows the changesets since.
 *
 * @param      string $current  The current version.
 */
function show_changesets_since( string $current ) {
	// Let's define all the changesets, this way, we can easily translate them, and most importantly show what changed since the previous version.
	$all = array(
		__( '0.3.9 : Improvement - Added the possibility to show Invoice Date instead of Order Date, head to the customizer in the "Setup fields visibility" section to activate it', 'einvoicing-for-woocommerce' ) => null,
		__( '0.3.8 : Removed some warning and updated digital-invoice dependency', 'einvoicing-for-woocommerce' ) => array(
			__( 'Fixed - Removed some warning and notices that appear in the customizer.', 'einvoicing-for-woocommerce' ),
			__( 'Improvement - Better Identification System labels.', 'einvoicing-for-woocommerce' ),
		),
		__( '0.3.7 : Fixed - Bug while generating e-invoice if the order contains a free product.', 'einvoicing-for-woocommerce' ) => null,
		__( '0.3.6 : Submission to WooCommerce Marketplace : Passing Qit Tests', 'einvoicing-for-woocommerce' ) => null,
		__( '0.3.5 : Invoice Sequential Numbering', 'einvoicing-for-woocommerce' ) => array(
			__( 'Use WooCommerce Logger to log invoice generation errors', 'einvoicing-for-woocommerce' ),
			__( 'Invoice number should be generated when order status is processing or completed', 'einvoicing-for-woocommerce' ),
		),
		__( '0.3.4 : Bug fix when changing invoice number', 'einvoicing-for-woocommerce' ) => null,
		__( '0.3.3 : Bug fix when no taxation is setup', 'einvoicing-for-woocommerce' ) => array(
			__( 'Avoid deprecated error caused by translation loaded too early', 'einvoicing-for-woocommerce' ),
			__( 'If invoice fails to generate for any reason, notify the admin by email', 'einvoicing-for-woocommerce' ),
		),
		__( '0.3.2 : Updated digital-invoice dependency', 'einvoicing-for-woocommerce' ) => null,
		__( '0.3.1 : Updated digital-invoice dependency', 'einvoicing-for-woocommerce' ) => array(
			__( 'Better german translation', 'einvoicing-for-woocommerce' ),
		),
		__( '0.3.0 : Added sequential invoice numbering', 'einvoicing-for-woocommerce' ) => array(
			__( 'Added settings section to configure numbering strategy, format, override last number.', 'einvoicing-for-woocommerce' ),
			__( 'On change status change to processing or completed, the invoice number is applied.', 'einvoicing-for-woocommerce' ),
		),
		__( '0.2.9 : Better compatibility with different themes and plugins' ) => array(
			__( 'Fixed a fatal error that occurred in some cases if the theme or installed plugins were interacting with the native customizer.', 'einvoicing-for-woocommerce' ),
			__( 'Fixed the settings link to redirect to E-Invoice tab instead of WooCommerce.', 'einvoicing-for-woocommerce' ),
		),
		__( '0.2.8 : Fix Fatal error when order has no modified date, shows today\'s date.', 'einvoicing-for-woocommerce' ) => null,
		__( '0.2.7 : Minor Improvements', 'einvoicing-for-woocommerce' ) => array(
			__( 'Updated dependency digital-invoice dependency', 'einvoicing-for-woocommerce' ),
			__( 'Pdf Invoice : Show date_modified when order is not paid', 'einvoicing-for-woocommerce' ),
		),
		__( '0.2.6 : Fix bad escaping in delivery address.', 'einvoicing-for-woocommerce' ) => null,
		__( '0.2.5 : PDF Invoice Enhancements and Compatibility Fixes', 'einvoicing-for-woocommerce' ) => array(
			__( 'PDF Invoice now displays extensive billing and shipping information (company name, address line 1, address line 2, city, postal code, state, country)', 'einvoicing-for-woocommerce' ),
			__( 'Fixed compatibility with OceanWP theme', 'einvoicing-for-woocommerce' ),
			__( 'Updated WordPress and WooCommerce compatibility to latest versions', 'einvoicing-for-woocommerce' ),
		),
		__( '0.2.4 : Welcome to the spanish translation', 'einvoicing-for-woocommerce' ) => array(
			__( 'Added the possiblity to set a company name different than the shop name.', 'einvoicing-for-woocommerce' ),
			__( 'Added spanish translation', 'einvoicing-for-woocommerce' ),

		),
		__( '0.2.3 : Fix fatal error after the plugin upgrade.', 'einvoicing-for-woocommerce' ) => null,
		__( '0.2.2 : Minor Improvements', 'einvoicing-for-woocommerce' ) => array(
			__( 'Updated tested Wordpress up to version.', 'einvoicing-for-woocommerce' ),
			__( 'Added option to support adding phone number and email to the invoice', 'einvoicing-for-woocommerce' ),

		),
		__( '0.2.1 : Important critical fixes', 'einvoicing-for-woocommerce' ) => array(
			__( 'This version adds the possibility to view the project changes.', 'einvoicing-for-woocommerce' ),
			__( 'Fixed refresh preview.', 'einvoicing-for-woocommerce' ),
			__( 'Fixed error while attaching invoice to email.', 'einvoicing-for-woocommerce' ),
		),
		__( '0.2.0 : Bulk invoices download', 'einvoicing-for-woocommerce' ) => array(
			__( 'This version adds the possibility to download multiple e-invoices in a Zip package from the WooCommerce orders list table.', 'einvoicing-for-woocommerce' ),
			__( 'After each upgrade, an admin notice will display the changes since the previously installed version.', 'einvoicing-for-woocommerce' ),
		),
		__( '0.1.9 : Minor Improvements', 'einvoicing-for-woocommerce' ) => array(
			__( 'Prices in the invoice table now utilize WooCommerce formatting for consistency.', 'einvoicing-for-woocommerce' ),
			__( 'Corrected an issue where the footer was not properly aligned in the Preview mode.', 'einvoicing-for-woocommerce' ),
		),
		__( '0.1.8 : Fix translation loading and added WooCommerce as a plugin dependency', 'einvoicing-for-woocommerce' ) => null,
		__( '0.1.7 : Fix arabic caracters in pdf invoice', 'einvoicing-for-woocommerce' ) => null,
		__( '0.1.6 : Fix adding invoice to emails as attachements', 'einvoicing-for-woocommerce' ) => array(
			__( 'Updated tested Wordpress up to version', 'einvoicing-for-woocommerce' ),
			__( 'Fixed typos in readme', 'einvoicing-for-woocommerce' ),
		),
		__( '0.1.5 : Allow E-Invoices to be attached to more WooCommerce Emails', 'einvoicing-for-woocommerce' ) => array(
			__( 'Updated tested WooCommerce up to version', 'einvoicing-for-woocommerce' ),
			__( 'Updated translations', 'einvoicing-for-woocommerce' ),
		),
		__( '0.1.4 : Applying Wordpress Plugin review team feedback', 'einvoicing-for-woocommerce' ) => array(
			__( 'Multiple sanitization and escaping fixes.', 'einvoicing-for-woocommerce' ),
			__( 'Use WOOEI as unique prefix.', 'einvoicing-for-woocommerce' ),
		),
		__( '0.1.3 : Minor style adjustments.', 'einvoicing-for-woocommerce' ) => null,
		__( '0.1.2 : Renamed slug to comply with the naming guidelines.', 'einvoicing-for-woocommerce' ) => null,
		__( '0.1.1 : Added PHP extensions (gd, mbstring, iconv and dom) verification before loading the plugin.', 'einvoicing-for-woocommerce' ) => null,
		__( '0.1.0 : Introduced support for the UBL format, expanding e-invoicing capabilities.', 'einvoicing-for-woocommerce' ) => null,
		__( '0.0.9 : Implemented role-based access control, allowing customization of minimum capabilities required for setting up the plugin.', 'einvoicing-for-woocommerce' ) => null,
		__( '0.0.8 : Resolved compatibility issues with the Astra WordPress theme.', 'einvoicing-for-woocommerce' ) => null,
		__( '0.0.7 : Minor fixes to improve the user experience.', 'einvoicing-for-woocommerce' ) => null,
		__( '0.0.6 : Confirmed compatibility with WooCommerce 8.4', 'einvoicing-for-woocommerce' ) => array(
			__( 'Declare compatibility with **High performance order tables**', 'einvoicing-for-woocommerce' ),
		),
		__( '0.0.5 : Improved the plugin onboarding experience', 'einvoicing-for-woocommerce' ) => array( __( 'Nice instructive notices, better captions and labels.', 'einvoicing-for-woocommerce' ) ),
		__( '0.0.1 : Initial release of E-Invoicing for WooCommerce', 'einvoicing-for-woocommerce' ) => array(
			__( 'Initial release, PDF Invoice Customizer supporting Factur-X and ZUGFeRD formats, ensuring compliance with French and German e-invoicing standards.', 'einvoicing-for-woocommerce' ),
		),
	);

	$changes = array_filter(
		$all,
		function ( $title ) use ( $current ) {
			list($version, $_title) = explode( ' : ', $title );
			return version_compare( trim( $version ), $current ) > 0;
		},
		ARRAY_FILTER_USE_KEY
	);
	if ( '0' !== $current ) {
		start_branded_notice();
	}
	?>
	<h3><?php echo esc_html__( 'Thank you for upgrading E-Invoicing For WooCommerce', 'einvoicing-for-woocommerce' ); ?></h3>
	<p><?php echo esc_html__( 'Please take notice of what changed :', 'einvoicing-for-woocommerce' ); ?></p> 
	<?php

	foreach ( $changes as $title => $detail ) {
		?>
		<h4><?php echo esc_html( $title ); ?></h4>
		<?php
		if ( $detail ) {
			foreach ( $detail as $change ) {
				echo '<p> - ' . esc_html( $change ) . '</p>';
			}
		}
	}
	if ( '0' !== $current ) {
		end_branded_notice();
	}
}

/**
 * Upgrade completed hook
 *
 * @param      WP_Upgrader $upgrader_object  The upgrader object.
 * @param      array       $options          The options.
 */
function upgrade_completed( $upgrader_object, $options ) {
	if ( 'update' === $options['action'] && 'plugin' === $options['type'] && isset( $options['plugins'] ) ) {
		// Iterate through the plugins being updated and check if ours is there.
		foreach ( $options['plugins'] as $plugin ) {
			if ( WOOEI_PLUGIN_BASENAME === $plugin ) {
				// Set a transient to record that our plugin has just been updated.
				set_transient( 'wooei_just_upgraded', 'yes' );
			}
		}
	}
}
add_action( 'upgrader_process_complete', __NAMESPACE__ . '\upgrade_completed', 10, 2 );


/**
 * Check version change upon plugin upgrade
 */
function db_check() {
	$just_upgraded = get_transient( 'wooei_just_upgraded' ) === 'yes';
	if ( $just_upgraded ) {
		$current_version = get_option( 'wooei_version', '0.1.7' );
		if ( WOOEI_VERSION !== $current_version ) {
			show_changesets_since( $current_version );
			update_option( 'wooei_version', WOOEI_VERSION );
		}
		delete_transient( 'wooei_just_upgraded' );
	}
}

add_action( 'admin_notices', __NAMESPACE__ . '\db_check' );


add_action(
	'admin_menu',
	function () {
		add_submenu_page(
			null,
			'E-Invoicing For Woo changesets',
			'E-Invoicing For Woo changesets',
			'manage_options',
			'einvoicing-changes',
			__NAMESPACE__ . '\all_changesets'
		);
	}
);

/**
 * Show all changesets page
 */
function all_changesets() {
	show_changesets_since( '0' );
}
