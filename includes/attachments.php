<?php
/**
 * This file implements attachments feature
 *
 * @package WOOEI
 */

namespace WOOEI;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

use WC_Abstract_Order;
use WP_Filesystem;
use Throwable;

/**
 * Get invoice filemae
 *
 * @param      WC_Abstract_Order $order  The order.
 *
 * @return     string             the filename.
 */
function invoice_filename( WC_Abstract_Order $order ) {
	$ext = is_xml() ? 'xml' : 'pdf';
	return 'Invoice-' . $order->get_id() . '.' . $ext;
}



/**
 * Ensures a directory exists
 *
 * @param      string $directory_path  The directory.
 */
function ensure_directory_exists( $directory_path ) {
	if ( WP_Filesystem() ) {
		global $wp_filesystem;

		if ( ! is_dir( $directory_path ) || ! wp_is_writable( $directory_path ) ) {
			$wp_filesystem->mkdir( $directory_path );
		}
	} else {
		if ( ! is_dir( $directory_path ) || ! wp_is_writable( $directory_path ) ) {
			mkdir( $directory_path );
		}
	}
}


/**
 * Saves a pdf temporary.
 *
 * @param      string            $content    The invoice content (pdf or xml).
 * @param      WC_Abstract_Order $order  The order.
 *
 * @return     string             the filepath of the saved pdf.
 */
function save_invoice_temp( string $content, WC_Abstract_Order $order ) {
	// Get the URL of the temporary file.
	$upload_dir         = wp_upload_dir();
	$date               = $order->get_date_created();
	$temp_directory_url = $upload_dir['basedir'] . '/tmp_invoice-' . $date->format( 'hidmY' ) . '/'; // Construct the URL.

	$filepath = $temp_directory_url . invoice_filename( $order );
	ensure_directory_exists( $temp_directory_url );
	if ( WP_Filesystem() ) {
		global $wp_filesystem;
		$wp_filesystem->put_contents( $filepath, $content );
	} else {
		file_put_contents( $filepath, $content ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_read_file_put_contents -- We use file_put_contents only if WP_Filesystem not available
	}

	return $filepath;
}

/**
 * Attaches the invoice to email.
 *
 * @param      array  $attachments  The attachments.
 * @param      string $email_id     The email identifier.
 * @param      mixed  $maybe_order       The object.
 *
 * @return     array   The email attachments.
 */
function attach_invoice_to_email( array $attachments, string $email_id, mixed $maybe_order ) {
	// no order, or not in our declared types.
	if ( ! ( $maybe_order instanceof WC_Abstract_Order ) || ! isset( WOOEI_EMAIL_TYPES[ $email_id ] ) ) {
		return $attachments;
	}
	$can_attach = get_option( 'wooei_invoice_attach_invoice', 'no' );

	if ( 'no' === $can_attach ) {
		return $attachments;
	}
	$send_for = get_option( 'wooei_invoice_attach', array() );

	if ( isset( $send_for[ $email_id ] ) && 'yes' === $send_for[ $email_id ] ) {
		try {
			$invoice_content = render_invoice( $maybe_order, false );
			$attachments[]   = save_invoice_temp( $invoice_content, $maybe_order );
		} catch ( Throwable $error ) {
			// Failed to generate or save invoice, try to notify the admin.
			/* translators: 1: Order Id. 2: Error */
			$message     = sprintf( __( 'Failed to generate invoice for order: %1$s - %2$s', 'einvoicing-for-woocommerce' ), $maybe_order->get_id(), $error->getMessage() );
			$admin_email = get_option( 'admin_email' );
			// Register shutdown function only when error occurs.
			add_action(
				'shutdown',
				function() use ( $admin_email, $message ) {
					wp_mail( $admin_email, __( 'Invoice Generation Failed', 'einvoicing-for-woocommerce' ), $message );
				}
			);

		}
	}

	return $attachments;
}

add_filter( 'woocommerce_email_attachments', __NAMESPACE__ . '\attach_invoice_to_email', 999, 3 );
