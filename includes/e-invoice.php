<?php
/**
 * This file implements the e-invoice XML generation
 *
 * @package WOOEI
 */

namespace WOOEI;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

use DigitalInvoice\Invoice;
use WC_Abstract_Order;

/**
 * Creates the E-Invoice from WC_Abstract_Order
 *
 * @param      WC_Abstract_Order $order    The order.
 * @param      string            $profile  The E-Invoicing profile.
 *
 * @return     Invoice   The e-invoice.
 */
function get_invoice( WC_Abstract_Order $order, $profile = Invoice::FACTURX_BASIC ) {
	$issue_date = $order->get_date_completed();
	if ( null === $issue_date ) {
		// not paid.
		$issue_date = $order->get_date_created();
	}
	$invoice = new Invoice( $order->get_order_number(), $issue_date, $order->get_date_paid(), get_woocommerce_currency(), $profile );

	// Setup Seller.
	$invoice->setSeller(
		get_option( 'wooei_id_company' ),
		(string) get_option( 'wooei_id_type' ),
		get_option( 'wooei_company_name', get_bloginfo( 'name' ) )
	);

	$invoice->setSellerContact(
		null,
		null,
		get_option( 'wooei_shop_phone' ),
		get_option( 'wooei_shop_email' )
	);

	$seller_country_code = WC()->countries->get_base_country();

	$invoice->setSellerAddress(
		WC()->countries->get_base_address(),
		WC()->countries->get_base_postcode(),
		WC()->countries->get_base_city(),
		$seller_country_code
	);

	if ( get_option( 'wooei_id_vat' ) ) {
		$invoice->setSellerTaxRegistration( get_option( 'wooei_id_vat' ), 'VAT' );
	}

	// Setup Buyer.
	$buyer_country_code = $order->get_billing_country();

	$invoice->setBuyer( '', '', $order->get_billing_first_name() . ' ' . $order->get_billing_last_name() );

	$invoice->setBuyerAddress(
		$order->get_billing_address_1(),
		$order->get_billing_postcode(),
		$order->get_billing_city(),
		$buyer_country_code ? $buyer_country_code : $seller_country_code
	);

	foreach ( $order->get_items() as $key => $item ) {
		$total_line               = $item['line_total'];
		$tax                      = $item['line_tax'];
		$quantity                 = $item->get_quantity();
		$line_price_without_tax   = $total_line - $tax;
		$single_price_without_tax = $line_price_without_tax / max( 1, $quantity );
		$tva_rate                 = $tax / max( 1, $line_price_without_tax );

		$invoice->addItem( $item['name'], $single_price_without_tax, $quantity, $tva_rate, 'H87', $item['product_id'] );

	}

	return $invoice;
}

/**
 * Determines whether the specified profile is ubl.
 *
 * @param      string $profile  The profile.
 *
 * @return     bool    True if the specified profile is ubl, False otherwise.
 */
function is_ubl( string $profile ) {

	return in_array(
		$profile,
		array(
			WOOEI_TYPES_UBL_PEPPOL,
			WOOEI_TYPES_UBL_CIUS_AT,
			WOOEI_TYPES_UBL_CIUS_IT,
			WOOEI_TYPES_UBL_CIUS_NL,
			WOOEI_TYPES_UBL_CIUS_ES,
			WOOEI_TYPES_UBL_CIUS_RO,
		),
		true
	);
}

/**
 * Gets the profile.
 *
 * @return     string The Invoice profile.
 */
function get_invoice_profile() {
	return get_option( 'wooei_invoice_type', WOOEI_TYPES_PDF );
}


/**
 * Determines whether the specified profile is xml.
 *
 * @param      string $profile  The profile.
 *
 * @return     bool    True if the specified profile is xml, False otherwise.
 */
function is_xml( string $profile = null ) {
	if ( null === $profile ) {
		$profile = get_invoice_profile();
	}
	return WOOEI_TYPES_XRECHNUNG === $profile || is_ubl( $profile );
}

/**
 * Gets the final e-invoice file pdf or xml depending the the profile.
 *
 * @param      string            $pdf    The pdf content.
 * @param      WC_Abstract_Order $order  The order.
 * @param      string            $type   The type/profile.
 *
 * @return     string    The e invoice content.
 */
function get_e_invoice( string $pdf, WC_Abstract_Order $order, string $type = null ) {
	switch ( $type ) {
		case WOOEI_TYPES_FACTURX:
			$profile = Invoice::FACTURX_BASIC;
			break;
		case WOOEI_TYPES_ZUGFERD:
			$profile = Invoice::ZUGFERD_CONFORT;
			break;
		case WOOEI_TYPES_XRECHNUNG:
			$profile = Invoice::FACTURX_XRECHNUNG;
			break;
		case WOOEI_TYPES_UBL_PEPPOL:
			$profile = Invoice::UBL_PEPPOL;
			break;
		case WOOEI_TYPES_UBL_CIUS_AT:
			$profile = Invoice::UBL_CIUS_AT_NAT;
			break;
		case WOOEI_TYPES_UBL_CIUS_IT:
			$profile = Invoice::UBL_CIUS_IT;
			break;
		case WOOEI_TYPES_UBL_CIUS_NL:
			$profile = Invoice::UBL_NLCIUS;
			break;
		case WOOEI_TYPES_UBL_CIUS_ES:
			$profile = Invoice::UBL_CIUS_ES_FACE;
			break;
		case WOOEI_TYPES_UBL_CIUS_RO:
			$profile = Invoice::UBL_CIUS_RO;
			break;
		default:
			$profile = Invoice::FACTURX_BASIC;
	}
	$invoice = get_invoice( $order, $profile );
	if ( WOOEI_TYPES_XRECHNUNG === $type ) {
		return $invoice->getXml();
	}

	if ( is_ubl( $type ) ) {
		$name = 'invoice-' . $order->get_id() . '.pdf';
		$invoice->addEmbeddedAttachment( $order->get_id(), null, $name, $pdf, 'application/pdf', 'The pdf invoice' );
		return $invoice->getXml();
	}
	$pdf_content = $invoice->getPdf( $pdf );

	return $pdf_content;
}

/**
 * Allowed html tags styles to be included in invoice
 *
 * @return     <type>  ( description_of_the_return_value )
 */
function allowed_html_tags() {
	global $allowedposttags;
	return array_merge(
		$allowedposttags,
		array(
			'style' => array(
				'type'  => true,
				'media' => true,
			),
		)
	);
}



/**
 * Shortcut to render an EInvoice from a WC_Abstract_Order
 *
 * @param      WC_Abstract_Order $order   The order.
 * @param      bool              $is_html     Return the html format.
 *
 * @return     string   The invoice content
 */
function render_invoice( WC_Abstract_Order $order, $is_html = true ) {
	$invoice = new PdfInvoice( $order );
	return $invoice->render( $is_html );
}

/**
 * Get invoice filemae
 *
 * @param      WC_Abstract_Order $order  The order.
 *
 * @return     string             the filename.
 */
function invoice_filename( WC_Abstract_Order $order ) {
	$ext = is_xml() ? 'xml' : 'pdf';
	return 'Invoice-' . $order->ID . '.' . $ext;
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
		$invoice_content = render_invoice( $maybe_order, false );
		$attachments[]   = save_invoice_temp( $invoice_content, $maybe_order );
	}

	return $attachments;
}

add_filter( 'woocommerce_email_attachments', __NAMESPACE__ . '\attach_invoice_to_email', 999, 3 );
