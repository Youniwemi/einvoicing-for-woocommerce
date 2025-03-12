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
	$date_paid  = $order->get_date_paid();
	if ( null === $issue_date ) {
		// not paid.
		$issue_date = $order->get_date_created();
	}
	$invoice = new Invoice( $order->get_order_number(), $issue_date, $date_paid, get_woocommerce_currency(), $profile );

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

	if ( count( $order->get_tax_totals() ) === 0 ) {
		$invoice->setTaxExemption( Invoice::EXEMPT_FROM_TAX, 'Exempt From Tax' );
	}
	// only if tax.
	if ( get_option( 'wooei_id_vat' ) ) {
		$invoice->setSellerTaxRegistration( $seller_country_code . get_option( 'wooei_id_vat' ), 'VA' );
	}

	// Setup Buyer.
	$buyer_country_code = $order->get_billing_country();

	$company_name = $order->get_billing_company();
	$contact_name = $order->get_billing_first_name() . ' ' . $order->get_billing_last_name();
	$buyer_name   = $company_name ? $company_name : $contact_name;
	// we dont have at this point any identification, leave it empty.
	$invoice->setBuyer( '', $buyer_name, '' );

	$invoice->setBuyerAddress(
		$order->get_billing_address_1(),
		$order->get_billing_postcode(),
		$order->get_billing_city(),
		$buyer_country_code ? $buyer_country_code : $seller_country_code
	);

	if ( $date_paid ) {
		$invoice->setPaymentTerms( $date_paid );
	}

	foreach ( $order->get_items() as $key => $item ) {
		$total_line               = $item['line_total'];
		$tax                      = $item['line_tax'];
		$quantity                 = $item->get_quantity();
		$line_price_without_tax   = (float) $total_line;
		$single_price_without_tax = $line_price_without_tax / max( 1, $quantity );
		$tax_rate                 = ( 0.0 === $line_price_without_tax ) ? 0 : ( $tax / $line_price_without_tax ) * 100;
		$invoice->addItem( $item['name'], $single_price_without_tax, $tax_rate, $quantity, 'H87', $item['product_id'] );
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
function is_xml( ?string $profile = null ) {
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
 * @param      bool              $return_xml   Return the xml only.
 *
 * @return     string    The e invoice content.
 */
function get_e_invoice( string $pdf, WC_Abstract_Order $order, ?string $type = null, $return_xml = false ) {
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
			$profile = Invoice::UBL_PEPOOL;
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
	if ( $return_xml ) {
		return $invoice->getXml();
	}
	try {
		$pdf_content = $invoice->getPdf( $pdf );
	} catch ( \Exception $e ) {
		return $pdf;
	}

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
