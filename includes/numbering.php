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

/**
 * Determines if invoices should be numbered.
 *
 * @return     bool  True if invoice numbering, False otherwise.
 */
function has_invoice_numbering() {
	return get_option( 'wooei_numbering_strategy', WOOEI_NUMBERING_ORDER ) === WOOEI_NUMBERING_INVOICE;
}

/**
 * Gets the invoice number.
 *
 * @param      WC_Abstract_Order $order  The order.
 *
 * @return     int  The invoice number.
 */
function get_invoice_number( WC_Abstract_Order $order ) {
	$number = get_post_meta( $order->get_id(), '_invoice_number', true );

	if ( empty( $number ) ) {
		// processing or completed or refunded, but no number, maybe just switched to invoice numbering.
		if ( in_array( $order->get_status(), array( 'completed', 'processing', 'refunded' ), true ) ) {
			$number = $order->get_id();
		} else {
			$number = null;
		}
	}
	return $number;
}



/**
 * Generates the invoice number
 *
 * @param      int    $order_id    The order identifier.
 * @param      string $new_status  The new status.
 */
function generate_invoice_number( $order_id, $new_status ) {

	if ( ! has_invoice_numbering() ) {
		return;
	}
	// Only proceed for processing/completed status.
	if ( ! in_array( $new_status, array( 'processing', 'completed' ), true ) ) {
		return;
	}

	// Skip if already has invoice number.
	if ( get_post_meta( $order_id, '_invoice_number', true ) ) {
		return;
	}

	$reset_yearly = get_option( 'wooei_invoice_reset_number', 'no' ) === WOOEI_NUMBERING_RESET_YEAR;
	$padding      = get_option( 'wooei_invoice_number_padding' ) ?? 4;
	$format       = get_option( 'wooei_invoice_number_format' ) ?? 'INV/{YEAR}/{NUMBER}';

	$last_number = (int) get_option( 'wooei_last_invoice_number', 0 );
	$next_number = $last_number + 1;

	$last_date = get_option( 'wooei_last_invoice_date' );
	$this_year = gmdate( 'Y' );

	if ( $reset_yearly && $last_date ) {
		$last_year = gmdate( 'Y', $last_date );
		if ( $last_year !== $this_year ) {
			$next_number = 1;
		}
	}

	$padded_number = str_pad( $next_number, $padding, '0', STR_PAD_LEFT );

	$invoice_number = str_replace(
		array( '{YEAR}', '{NUMBER}' ),
		array( $this_year, $padded_number ),
		$format
	);

	update_post_meta( $order_id, '_invoice_number', sanitize_text_field( $invoice_number ) );
	update_option( 'wooei_last_invoice_number', $next_number );
	update_option( 'wooei_last_invoice_date', time() );
}

// Generate invoice number on settings status to processing.
add_action(
	'woocommerce_order_status_processing',
	function( $order_id, WC_Abstract_Order $order, $status_transition ) {
		generate_invoice_number( $order_id, 'processing' );
	},
	10,
	3
);

// Generate invoice number on settings status to complete.
add_action(
	'woocommerce_order_status_completed',
	function( $order_id, WC_Abstract_Order $order, $status_transition ) {
		generate_invoice_number( $order_id, 'completed' );
	},
	10,
	3
);

// Generate invoice number on order status change.
add_action(
	'woocommerce_order_status_changed',
	function( $order_id, $old_status, $new_status, WC_Abstract_Order $order ) {
		generate_invoice_number( $order_id, $new_status );
	},
	10,
	4
);





