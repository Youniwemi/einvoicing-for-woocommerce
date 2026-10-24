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
 * Determines whether the specified order should be invoiced.
 *
 * @param      WC_Abstract_Order $order  The order.
 *
 * @return     bool               True if the specified order should be invoiced, False otherwise.
 */
function should_be_invoiced( WC_Abstract_Order $order ) {
	return ! is_skipped_free_order( $order ) && in_array( $order->get_status(), array( 'completed', 'processing', 'refunded' ), true );
}

/**
 * Determines if the order is free and free orders are excluded from invoicing.
 *
 * @param      WC_Abstract_Order $order  The order.
 *
 * @return     bool  True if no invoice should be issued for this free order.
 */
function is_skipped_free_order( WC_Abstract_Order $order ) {
	return 'yes' === get_option( 'wooei_skip_free_orders', 'no' ) && 0.0 === (float) $order->get_total();
}

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
	$number = get_stored_invoice_number( $order );

	if ( empty( $number ) ) {
		// processing or completed or refunded, but no number, maybe just switched to invoice numbering.
		if ( should_be_invoiced( $order ) ) {
			$number = $order->get_id();
		} else {
			$number = null;
		}
	}
	return $number;
}

/**
 * Gets the invoice date, shared by the PDF and the XML.
 *
 * @param      WC_Abstract_Order $order  The order.
 *
 * @return     \WC_DateTime  The paid date, else completed date, else creation date.
 */
function get_invoice_date( WC_Abstract_Order $order ) {
	return $order->get_date_paid() ?? $order->get_date_completed() ?? $order->get_date_created();
}

/**
 * Gets the invoice number stored on the order (HPOS compatible).
 *
 * @param      WC_Abstract_Order $order  The order.
 *
 * @return     string  The stored invoice number, empty if none.
 */
function get_stored_invoice_number( WC_Abstract_Order $order ) {
	$number = $order->get_meta( '_invoice_number' );
	if ( '' === $number ) {
		// Numbers saved before 0.5.0 were stored as post meta.
		$number = get_post_meta( $order->get_id(), '_invoice_number', true );
	}
	return (string) $number;
}

/**
 * Stores the invoice number on the order (HPOS compatible).
 *
 * @param      WC_Abstract_Order $order   The order.
 * @param      string            $number  The invoice number.
 */
function set_invoice_number( WC_Abstract_Order $order, string $number ) {
	$order->update_meta_data( '_invoice_number', $number );
	$order->save_meta_data();
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
	// Default allowed statuses.
	$allowed_statuses = array( 'processing', 'completed', 'wc-processing', 'wc-completed' );

	// Add pending if checkbox is enabled.
	$generate_pending = get_option( 'wooei_generate_pending_invoices', 'no' );
	if ( 'yes' === $generate_pending ) {
		$allowed_statuses[] = 'pending';
		$allowed_statuses[] = 'wc-pending';
	}

	if ( ! in_array( $new_status, $allowed_statuses, true ) ) {
		return;
	}

	$order = wc_get_order( $order_id );
	// Skip if no order, excluded free order or already has invoice number.
	if ( ! $order || is_skipped_free_order( $order ) || get_stored_invoice_number( $order ) ) {
		return;
	}

	$reset_yearly = get_option( 'wooei_invoice_reset_number', 'no' ) === WOOEI_NUMBERING_RESET_YEAR;
	$padding      = get_option( 'wooei_invoice_number_padding', 4 );
	$format       = get_option( 'wooei_invoice_number_format', 'INV/{YEAR}/{NUMBER}' );

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

	set_invoice_number( $order, sanitize_text_field( $invoice_number ) );
	update_option( 'wooei_last_invoice_number', $next_number );
	update_option( 'wooei_last_invoice_date', time() );
}

// Generate invoice number on settings status to pending.
add_action(
	'woocommerce_order_status_pending',
	function ( $order_id ) {
		generate_invoice_number( $order_id, 'pending' );
	}
);

// Generate invoice number on settings status to processing.
add_action(
	'woocommerce_order_status_processing',
	function ( $order_id ) {
		generate_invoice_number( $order_id, 'processing' );
	}
);

// Generate invoice number on settings status to complete.
add_action(
	'woocommerce_order_status_completed',
	function ( $order_id ) {
		generate_invoice_number( $order_id, 'completed' );
	}
);

// Generate invoice number on order status change.
add_action(
	'woocommerce_order_status_changed',
	function ( $order_id, $old_status, $new_status ) {
		generate_invoice_number( $order_id, $new_status );
	},
	10,
	3
);
