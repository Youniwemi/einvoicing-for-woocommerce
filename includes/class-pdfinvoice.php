<?php
/**
 * This file implements the PdfInvoice.
 *
 * @package WOOEI
 */

namespace WOOEI;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

// Simple PDF stuff.
use Dompdf\Dompdf;
use Dompdf\Options;

// Woocommerce Stuff.
use WC_Abstract_Order;

// Wp classes.
use WP_Http;

/**
 * Pdf Invoice class, renders the pdf
 */
class PdfInvoice {



	/**
	 * Invoice html content
	 *
	 * @var string
	 */
	public $html;

	/**
	 * Dom Pdf settings
	 *
	 * @var array
	 */
	public $pdf_settings;

	/**
	 * WooCommerce Order
	 *
	 * @var WC_Abstract_Order
	 */
	public $order;

	/**
	 * Price decimals
	 *
	 * @var int
	 */
	protected $price_decimals;

	/**
	 * Price decimal separator.
	 *
	 * @var string
	 */
	protected $price_decimal_separator;

	/**
	 * Price Thousand Separator
	 *
	 * @var string
	 */
	protected $price_thousand_separator;


	/**
	 * Default settings
	 *
	 * @var        array
	 */
	public const DEFAULT_SETTINGS = array(
		'paper_size'        => 'A4',
		'paper_orientation' => 'portrait',
		'font_subsetting'   => false,
	);

	/**
	 * Constructs a new instance.
	 *
	 * @param WC_Abstract_Order $order        The order.
	 * @param array             $pdf_settings The pdf settings.
	 */
	public function __construct( WC_Abstract_Order $order, $pdf_settings = array() ) {
		$this->order                    = $order;
		$this->pdf_settings             = array_merge( self::DEFAULT_SETTINGS, $pdf_settings );
		$this->price_decimals           = wc_get_price_decimals();
		$this->price_decimal_separator  = wc_get_price_decimal_separator();
		$this->price_thousand_separator = wc_get_price_thousand_separator();
	}

	/**
	 * Outputs the pdf content
	 *
	 * @param string $html The html.
	 *
	 * @return string  pdf output.
	 */
	public function output( string $html ) {
		// set options.
		$options = new Options(
			/**
			 * Override Dompdf options
			 *
			 * @since 0.0.4
			 */
			apply_filters(
				'wooei_dompdf_options',
				array(
					'isRemoteEnabled' => true,
				)
			)
		);

		// instantiate and use the dompdf class.
		$dompdf = new Dompdf( $options, 'UTF-8' );
		$dompdf->loadHtml( $html );
		$dompdf->setPaper( $this->pdf_settings['paper_size'], $this->pdf_settings['paper_orientation'] );
		$dompdf->render();
		return $dompdf->output();
	}

	/**
	 * Magic function to retreieve data inside the template, will forward calls
	 * to WC_Order, or retrieve options
	 *
	 * @param string $method The method.
	 * @param <type> $args   The arguments.
	 *
	 * @return mixed  value
	 */
	public function __call( string $method, $args = null ) {
		switch ( $method ) {
			case 'get_order_number':
			case 'get_date_paid':
			case 'get_billing_address_1':
			case 'get_billing_email':
			case 'get_billing_phone':
			case 'get_shipping_address_1':
			case 'get_shipping_phone':
			case 'get_shipping_email':
			case 'get_payment_method':
			case 'get_customer_note':
			case 'get_billing_first_name':
			case 'get_billing_last_name':
				return $this->order->$method();

			case 'get_billing_address':
				return implode(
					'<br/>',
					array_filter(
						array(
							$this->order->get_billing_company(),
							$this->order->get_billing_address_1(),
							$this->order->get_billing_address_2(),
							$this->order->get_billing_city(),
							$this->order->get_billing_postcode(),
							$this->order->get_billing_state(),
							$this->order->get_billing_country(),
						)
					)
				);

			case 'get_shipping_address':
				return implode(
					'<br/>',
					array_filter(
						array(
							$this->order->get_shipping_company(),
							$this->order->get_shipping_address_1(),
							$this->order->get_shipping_address_2(),
							$this->order->get_shipping_city(),
							$this->order->get_shipping_postcode(),
							$this->order->get_shipping_state(),
							$this->order->get_shipping_country(),
						)
					)
				);

			case 'get_billing_to':
				return $this->order->get_billing_first_name() . ' ' . $this->order->get_billing_last_name();

			case 'title':
				/* translators: 1: Invoice Number */
				return sprintf( __( 'Invoice #%s', 'einvoicing-for-woocommerce' ), $this->order->get_id() );
			case 'shop_name':
				return get_option( 'wooei_company_name', get_bloginfo( 'name' ) );

			case 'shop_address':
				$countries = WC()->countries;
				return implode(
					'<br/>',
					array_filter(
						array(
							$countries->get_base_address(),
							$countries->get_base_address_2(),
							$countries->get_base_city(),
							$countries->get_base_postcode(),
							$countries->get_base_state(),
							$countries->get_base_country(),
						)
					)
				);

			default:
				return get_option( $method, $method );
		}
		return $method;
	}

	/**
	 * Renders or returns the E-Invoice
	 *
	 * @param bool $is_html    Return the html format ( for preview ).
	 *
	 * @return string|void  The E-Invoice content if returned.
	 */
	public function render( $is_html = false ) {
		$is_final = ! $is_html;
		$type     = get_invoice_profile();

		if ( $is_final && WOOEI_TYPES_XRECHNUNG === $type ) {
			return get_e_invoice( '', $this->order, $type );
		}

		$settings = Invoice_Customizer::get_instance()->get_settings();
		// phpcs:ignore WordPress.PHP.DontExtract.extract_extract -- extracting variables for easy acces in the template 
		extract( $settings );
		// Logo may not be an absolute URL, we have to fix it.
		if ( isset( $logo ) && $logo ) {
			$logo = WP_Http::make_absolute_url( $logo, get_home_url() );
		}
		ob_start();
		include WOOEI_TEMPLATE . 'invoice.php';
		$html_safe = ob_get_clean();

		if ( $is_final ) {
			$head     = "<!doctype html>\n<html><head><meta http-equiv='Content-Type' content='text/html; charset=utf-8'/><style>* { font-family: DejaVu Sans, sans-serif; }</style></head><body>";
			$pdf_safe = $this->output( $head . $html_safe . '</body></html>' );
			return get_e_invoice( $pdf_safe, $this->order, $type );
		}
		return $html_safe;
	}



	/**
	 * Sends file headers.
	 *
	 * @param string $filename The filename.
	 * @param string $type     The mime type.
	 */
	public static function send_headers( string $filename, string $type = 'pdf' ) {
		$mime = 'pdf' === $type ? 'application/pdf' : 'text/xml';

		header( 'Content-Description: File Transfer' );
		header( 'Content-Type: ' . $mime );
		header( 'Content-Disposition: attachment; filename="' . $filename . '"' );
		header( 'Content-Transfer-Encoding: binary' );
		header( 'Connection: Keep-Alive' );
		header( 'Expires: 0' );
		header( 'Cache-Control: must-revalidate, post-check=0, pre-check=0' );
		header( 'Pragma: public' );
	}

	/**
	 * Formats prices in defined WooCommerce options.
	 *
	 * @param      mixed $price  The price.
	 *
	 * @return     string  Formated price
	 */
	public function format_money( $price ) {
		$price = (float) $price;
		return number_format( $price, $this->price_decimals, $this->price_decimal_separator, $this->price_thousand_separator );
	}
}
