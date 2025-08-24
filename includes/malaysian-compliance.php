<?php
/**
 * Malaysian compliance functionality including MSIC codes
 *
 * @package WOOEI
 */

namespace WOOEI;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}
use WC_Order;
use WP_REST_Request;
use DigitalInvoice\Types;
use DigitalInvoice\EnumToArray;
use DigitalInvoice\Presets\Malaysia;

/**
 * Check if Malaysian UBL is currently selected
 */
function is_malaysian_ubl_active() {
	$invoice_type = get_option( 'wooei_invoice_type', WOOEI_TYPES_PDF );
	return WOOEI_TYPES_UBL_CIUS_MY === $invoice_type;
}

/**
 * Register TIN field using WooCommerce Additional Checkout Fields API
 */
function register_malaysian_tin_checkout_field() {
	if ( ! function_exists( 'woocommerce_register_additional_checkout_field' ) ) {
		return; // Additional fields API not available.
	}

	woocommerce_register_additional_checkout_field(
		array(
			'id'       => 'wooei/billing_tin',
			'label'    => __( 'Tax Identification Number (TIN)', 'einvoicing-for-woocommerce' ),
			'location' => 'contact',
			'type'     => 'text',
			'required' => false,
		)
	);
}

/**
 * Validate TIN field during checkout using Store API hook
 *
 * @param WC_Order        $order   WooCommerce order object.
 * @param WP_REST_Request $request API request object.
 * @throws \Exception When TIN format is invalid.
 */
function validate_malaysian_tin_field( $order, $request ) {
	$additional_fields = $request->get_param( 'additional_fields' ) ?? array();
	$tin               = $additional_fields['wooei/billing_tin'] ?? '';

	// TIN validation for Malaysia - basic format check.
	if ( ! empty( $tin ) && ! preg_match( '/^[A-Za-z0-9]{10,20}$/', $tin ) ) {
		throw new \Exception( __( 'Please enter a valid TIN format (10-20 alphanumeric characters).', 'einvoicing-for-woocommerce' ) );
	}
}

/**
 * Save TIN field to order meta using Store API hook
 *
 * @param WC_Order        $order WooCommerce order object.
 * @param WP_REST_Request $request API request object.
 */
function save_malaysian_tin_to_order( WC_Order $order, $request ) {
	$additional_fields = $request->get_param( 'additional_fields' ) ?? array();
	$tin               = $additional_fields['wooei/billing_tin'] ?? '';

	if ( ! empty( $tin ) ) {
		$order->update_meta_data( '_billing_tin', sanitize_text_field( $tin ) );
	}
}




/**
 * Apply Malaysian e-invoicing compliance to UBL invoice
 *
 * @param object $invoice The invoice object.
 * @param object $order   The WooCommerce order.
 * @param string $profile The UBL profile type.
 * @return object Modified invoice object.
 */
function ensure_malaysian_einvoice_compliance( $invoice, $order, $profile ) {
	// Only process for Malaysian UBL.
	if ( ! is_malaysian_ubl_active() ) {
		return $invoice;
	}
	$invoice->addSellerIdentifier( get_option( 'wooei_id_company' ), get_option( 'wooei_id_type' ) );

	// Add buyer TIN if available.
	$tin = $order->get_meta( '_billing_tin' );
	if ( ! empty( $tin ) ) {
		$invoice->setBuyerIdentifier( $tin, 'TIN', 'Other' );
	}

	// Add MSIC code from seller settings.
	$msic_code = get_option( 'wooei_msic_code' );
	$msic_name = get_option( 'wooei_msic_name' );
	if ( ! empty( $msic_code ) && ! empty( $msic_name ) ) {
		$invoice->setSellerIndustryClassification( $msic_code, $msic_name );
	}

	return $invoice;
}

/**
 * Add commodity classification to invoice items
 *
 * @param object $invoice_item The created invoice item.
 * @param object $invoice      The invoice object.
 * @param object $item         The WooCommerce order item.
 * @param string $profile      The invoice profile/type.
 */
function add_commodity_classification_to_invoice_item( $invoice_item, $invoice, $item, $profile ) {
	// Only process for Malaysian UBL.
	if ( ! is_malaysian_ubl_active() ) {
		return;
	}

	$product = $item->get_product();
	if ( $product ) {
		$commodity_classification = $product->get_meta( 'wooei_commodity_classification' );

		// If product doesn't have classification, try to get from category.
		if ( empty( $commodity_classification ) ) {
			$product_categories = wp_get_post_terms( $product->get_id(), 'product_cat' );
			if ( ! empty( $product_categories ) ) {
				foreach ( $product_categories as $category ) {
					$category_classification = get_term_meta( $category->term_id, 'wooei_commodity_classification', true );
					if ( ! empty( $category_classification ) ) {
						$commodity_classification = $category_classification;
						break; // Use the first category with classification.
					}
				}
			}
		}

		if ( ! empty( $commodity_classification ) ) {
			$invoice->addItemClassification( $invoice_item, $commodity_classification, 'CLASS' );
		}
	}
}

/**
 * Add commodity classification field to product data
 */
function add_product_commodity_classification_field() {
	// Only add field if Malaysian UBL is active.
	if ( ! is_malaysian_ubl_active() ) {
		return;
	}

	$classification_codes = Malaysia::getItemClassificationCodes();
	// Prepend empty option.
	$classification_codes = array( '' => __( 'Select Classification Code', 'einvoicing-for-woocommerce' ) ) + $classification_codes;

	echo '<div class="options_group">';
	woocommerce_wp_select(
		array(
			'id'          => 'wooei_commodity_classification',
			'label'       => __( 'Commodity Classification Code', 'einvoicing-for-woocommerce' ),
			'desc_tip'    => true,
			'description' => __( 'Select the commodity classification code for this product (required for Malaysian e-invoicing)', 'einvoicing-for-woocommerce' ),
			'options'     => $classification_codes,
		)
	);
	echo '</div>';
}

/**
 * Save commodity classification field
 *
 * @param int $post_id Product ID.
 */
function save_product_commodity_classification_field( $post_id ) {
	// Only save if Malaysian UBL is active.
	if ( ! is_malaysian_ubl_active() ) {
		return;
	}

	$commodity_classification = isset( $_POST['wooei_commodity_classification'] ) ? sanitize_text_field( wp_unslash( $_POST['wooei_commodity_classification'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Missing
	update_post_meta( $post_id, 'wooei_commodity_classification', $commodity_classification );
}

/**
 * Add commodity classification field to product category
 *
 * @param object $term The category term object.
 */
function add_category_commodity_classification_field( $term ) {
	// Only add field if Malaysian UBL is active.
	if ( ! is_malaysian_ubl_active() ) {
		return;
	}

	$classification_codes = array( '' => __( 'Select Classification Code', 'einvoicing-for-woocommerce' ) );
	$classification_codes = array_merge( $classification_codes, Malaysia::getItemClassificationCodes() );

	$term_id              = $term->term_id;
	$saved_classification = get_term_meta( $term_id, 'wooei_commodity_classification', true );
	?>
	<tr class="form-field">
		<th scope="row" valign="top">
			<label for="wooei_commodity_classification"><?php esc_html_e( 'Commodity Classification Code', 'einvoicing-for-woocommerce' ); ?></label>
		</th>
		<td>
			<select name="wooei_commodity_classification" id="wooei_commodity_classification" style="width: 100%;">
				<?php foreach ( $classification_codes as $code => $description ) : ?>
					<option value="<?php echo esc_attr( $code ); ?>" <?php selected( $saved_classification, $code ); ?>>
						<?php echo esc_html( $code ? "$code - $description" : $description ); ?>
					</option>
				<?php endforeach; ?>
			</select>
			<p class="description"><?php esc_html_e( 'Default commodity classification for products in this category (Malaysian e-invoicing)', 'einvoicing-for-woocommerce' ); ?></p>
		</td>
	</tr>
	<?php
}

/**
 * Save commodity classification field for product category
 *
 * @param int $term_id Term ID.
 */
function save_category_commodity_classification_field( $term_id ) {
	// Only save if Malaysian UBL is active.
	if ( ! is_malaysian_ubl_active() ) {
		return;
	}

	if ( isset( $_POST['wooei_commodity_classification'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Missing
		$commodity_classification = sanitize_text_field( wp_unslash( $_POST['wooei_commodity_classification'] ) ); // phpcs:ignore WordPress.Security.NonceVerification.Missing
		update_term_meta( $term_id, 'wooei_commodity_classification', $commodity_classification );
	}
}



/**
 * Add MSIC setting to company identification settings
 *
 * @param array $settings Existing settings array.
 * @return array Modified settings array.
 */
function add_msic_setting_to_company_identification( $settings ) {
	// Only add MSIC setting if Malaysian UBL is active.
	if ( ! is_malaysian_ubl_active() ) {
		return $settings;
	}

	// Get MSIC codes from the digital invoice library.
	$msic_codes = Malaysia::getMsicCodes();
	// Prepend empty option.
	$msic_codes = array( '' => __( 'Select MSIC Code', 'einvoicing-for-woocommerce' ) ) + $msic_codes;

	// Add MSIC setting before the section end.
	$section_end = $settings['section_end'];
	unset( $settings['section_end'] );

	$settings['msic_code'] = array(
		'name'    => __( 'MSIC Code', 'einvoicing-for-woocommerce' ),
		'type'    => 'select',
		'desc'    => __( 'Select your Malaysian Standard Industrial Classification (MSIC) code. This is required for Malaysian e-invoicing compliance.', 'einvoicing-for-woocommerce' ),
		'options' => $msic_codes,
		'id'      => 'wooei_msic_code',
	);

	$settings['section_end'] = $section_end;

	return $settings;
}



/**
 * Save MSIC name when WooCommerce settings are saved
 */
function save_msic_name_on_settings_update() {
	// Check if we're saving the wooei tab settings.
	if ( isset( $_POST['wooei_msic_code'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Missing
		$msic_code = sanitize_text_field( wp_unslash( $_POST['wooei_msic_code'] ) ); // phpcs:ignore WordPress.Security.NonceVerification.Missing
		if ( ! empty( $msic_code ) ) {
			$msic_codes = Malaysia::getMsicCodes();
			$msic_name  = $msic_codes[ $msic_code ] ?? '';
			update_option( 'wooei_msic_name', $msic_name );
		}
	}
}

/**
 * Initialize Malaysian TIN functionality hooks
 */
function init_malaysian_compliance() {
	if ( is_malaysian_ubl_active() ) {
		// Register TIN field using Additional Checkout Fields API.
		register_malaysian_tin_checkout_field();

		// Validate field using Store API hook for block checkout.
		add_action( 'woocommerce_store_api_checkout_update_order_from_request', __NAMESPACE__ . '\validate_malaysian_tin_field', 10, 2 );

		// Save to order using Store API hook for block checkout.
		add_action( 'woocommerce_store_api_checkout_update_order_from_request', __NAMESPACE__ . '\save_malaysian_tin_to_order', 10, 2 );

		// Hook into invoice generation to add Malaysian compliance - this one already checks the profile parameter.
		add_filter( 'wooei_invoice_before_return', __NAMESPACE__ . '\ensure_malaysian_einvoice_compliance', 10, 3 );

		// Add MSIC setting to company identification section.
		add_filter( 'wooei_settings_company_identification', __NAMESPACE__ . '\add_msic_setting_to_company_identification' );

		// Save MSIC name when settings are updated.
		add_action( 'woocommerce_update_options_wooei', __NAMESPACE__ . '\save_msic_name_on_settings_update' );

		// Add commodity classification field to products.
		add_action( 'woocommerce_product_options_general_product_data', __NAMESPACE__ . '\add_product_commodity_classification_field' );
		add_action( 'woocommerce_process_product_meta', __NAMESPACE__ . '\save_product_commodity_classification_field' );

		// Add commodity classification field to product categories.
		add_action( 'product_cat_edit_form_fields', __NAMESPACE__ . '\add_category_commodity_classification_field' );
		add_action( 'edited_product_cat', __NAMESPACE__ . '\save_category_commodity_classification_field' );

		// Add commodity classification to invoice items.
		add_action( 'wooei_invoice_item_added', __NAMESPACE__ . '\add_commodity_classification_to_invoice_item', 10, 4 );
	}
}

// Initialize hooks after WooCommerce init - required for Additional Checkout Fields API.
add_action( 'woocommerce_init', __NAMESPACE__ . '\init_malaysian_compliance' );
