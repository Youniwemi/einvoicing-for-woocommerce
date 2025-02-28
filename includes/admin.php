<?php
/**
 * This file implements admin stuff, but mainly the onboarding process.
 *
 * @package WOOEI
 */

namespace WOOEI;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

define( 'WOOEI_ZIP_EXPORT_PATH', 'tmp_invoice-zip-' );

use \Automattic\WooCommerce\Utilities\FeaturesUtil;
use WC_Abstract_Order;
use ZipArchive;

/**
 * Starts a branded notice.
 */
function start_branded_notice() {   ?>
<div class="notice notice-info is-dismissible" style="display:flex; align-items: center;column-gap:2em">
	<div>
		<img width="120" src="<?php echo esc_url( WOOEI_PLUGIN_ASSETS . '/images/logo-dark.png' ); ?>" />
	</div>
	<div>
	<?php
}

/**
 * Ends a branded notice.
 */
function end_branded_notice() {
	?>
	</div>
	<button type="button" class="notice-dismiss" onclick="this.parentNode.remove();" ><span class="screen-reader-text"><?php esc_html_e( 'Dismiss this notice.', 'einvoicing-for-woocommerce' ); ?></span></button>
</div>
	<?php
}

/**
 * Get the plugin setting url.
 *
 * @return     string  The setting url.
 */
function settings_url() {
	return admin_url( 'admin.php?page=wc-settings&tab=wooei' );
}


/**
 * Onboarding notice, presents the use with the next steps to properly setup the plugin
 */
function onboarding_notice() {
	$pagenow         = $GLOBALS['pagenow'];
	$is_plugins_page = 'plugins.php' === $pagenow;

	/**
	 * Allow showing onboarding
	 *
	 * @since 0.0.4
	 */
	if ( apply_filters( 'wooei_show_onboarding', $is_plugins_page ) ) {
		// Show only for admin or shop manager.
		if ( ! current_user_can( 'shop_manager' ) && ! current_user_can( 'manage_options' && ! current_user_can( 'manage_woocommerce' ) ) ) {
			return;
		}

		$customized = get_option( 'wooei_customizations', false );
		$configured = get_option( 'wooei_invoice_type', false );
		// Is it a fresh install?
		$just_installed = get_transient( 'wooei_just_activated' ) === 'yes';
		// we are done, as it seems like it is properly setup.
		if ( $customized && $configured && $is_plugins_page ) {
			$setting_title   = __( 'Check E-Invoicing settings', 'einvoicing-for-woocommerce' );
			$customize_title = __( 'Verify your invoice customization', 'einvoicing-for-woocommerce' );
			if ( false === $just_installed ) {
				// Already configured, no need.
				return;
			}
		} elseif ( $customized && $configured ) {
			// Not a plugin page, not a reinstallation, we are done here.
			return;
		} else {
			$setting_title   = __( 'Setup E-Invoicing settings', 'einvoicing-for-woocommerce' );
			$customize_title = __( 'Visually customize your invoice', 'einvoicing-for-woocommerce' );
		}

		start_branded_notice();
		if ( $customized && $configured && $is_plugins_page ) {
			?>
			<h3><?php echo esc_html__( 'Welcome Back! E-Invoicing For WooCommerce reinstallation Is complete and almost ready to go', 'einvoicing-for-woocommerce' ); ?></h3>
			<p><?php echo esc_html__( 'Please ensure that your settings and customization are still accurate and enjoy e-invoicing regulations compliance.', 'einvoicing-for-woocommerce' ); ?></p>
		<?php } else { ?>
			<h3><?php echo esc_html__( 'E-Invoicing For WooCommerce is installed, almost ready to go', 'einvoicing-for-woocommerce' ); ?></h3>
			<p><?php echo esc_html__( 'In just a few steps, you\'ll meet all e-invoicing regulations effortlessly. All you need to do is set up your company details and visually customize your invoice.', 'einvoicing-for-woocommerce' ); ?></p>            
		<?php } ?> 
			<p>
		<?php if ( $just_installed || false === $configured ) : ?>
				<a class="button-primary button" href="<?php echo esc_url( admin_url( 'admin.php?page=wc-settings&tab=wooei' ) ); ?>"  ><?php echo esc_html( $setting_title ); ?></a>
		<?php endif; ?>
		<?php if ( $just_installed || false === $customized ) : ?>
				<a class="button <?php echo ( ! $just_installed && $configured ? 'button-primary' : '' ); ?>" href="<?php echo esc_url( Invoice_Customizer::customizer_link() ); ?>"  ><?php echo esc_html( $customize_title ); ?></a>
		<?php endif; ?>
			</p>
		<?php
		end_branded_notice();
		if ( $just_installed ) {
			delete_transient( 'wooei_just_activated' );
		}
	}
};

/**
 * Show a nice onboarding notice.
 */
add_action( 'admin_notices', __NAMESPACE__ . '\onboarding_notice' );



/**
 * Adds invoice download links.
 *
 * @param      string $column     The column.
 * @param      mixed  $the_order  The order.
 */
function add_invoice_download_links( string $column, $the_order ) {

	if ( 'invoice' === $column ) {
		if ( is_numeric( $the_order ) ) {
			$the_order = wc_get_order( $the_order );
		}
		if ( $the_order instanceof WC_Abstract_Order ) {
			$ajax     = admin_url( 'post.php' );
			$download = add_query_arg(
				array(
					'nonce_invoice' => wp_create_nonce( 'download_e_invoice' ),
					'action'        => 'download_e_invoice',
					'post'          => $the_order->get_id(),
				),
				$ajax
			);
			$preview  = add_query_arg(
				array(
					'html'      => '1',
					'TB_iframe' => true,
				),
				$download
			);
			$title    = $the_order->get_order_number();
			echo '<a class="thickbox" href="' . esc_url( $preview ) . '" title="' . esc_attr__( 'Preview the invoice', 'einvoicing-for-woocommerce' ) . ' ' . esc_attr( $title ) . '"><span class="dashicons dashicons-visibility icon-preview-file fx-icon "></span></a>';
			echo ' <a href="' . esc_url( $download ) . '" title="' . esc_attr__( 'Download the invoice', 'einvoicing-for-woocommerce' ) . '"><span class="dashicons dashicons-download"></span></a>';
		}
	}
}

/**
 * Adds invoice number.
 *
 * @param      string $column     The column.
 * @param      mixed  $the_order  The order.
 */
function add_invoice_number( string $column, $the_order ) {

	if ( 'invoice_number' === $column ) {
		if ( is_numeric( $the_order ) ) {
			$the_order = wc_get_order( $the_order );
		}
		if ( $the_order instanceof WC_Abstract_Order ) {
			$number = get_invoice_number( $the_order );
			echo $number ? esc_html( $number ) : esc_html__( 'Not invoiced yet', 'einvoicing-for-woocommerce' );
		}
	}
}


/**
 * Adds invoice columns.
 *
 * @param array $columns The columns.
 *
 * @return array  The columns with added column.
 */
function add_invoice_columns( array $columns ) {
	if ( has_invoice_numbering() ) {
		$columns['invoice_number'] = __( 'Invoice Number', 'einvoicing-for-woocommerce' );
	}
	$columns['invoice'] = __( 'Invoice', 'einvoicing-for-woocommerce' );
	add_thickbox();
	return $columns;
}


add_filter( 'manage_edit-shop_order_columns', __NAMESPACE__ . '\add_invoice_columns', 999 );
add_filter( 'manage_woocommerce_page_wc-orders_columns', __NAMESPACE__ . '\add_invoice_columns', 999 );

add_action( 'manage_shop_order_posts_custom_column', __NAMESPACE__ . '\add_invoice_download_links', 10, 2 );
add_action( 'manage_woocommerce_page_wc-orders_custom_column', __NAMESPACE__ . '\add_invoice_download_links', 10, 2 );

add_action( 'manage_shop_order_posts_custom_column', __NAMESPACE__ . '\add_invoice_number', 10, 2 );
add_action( 'manage_woocommerce_page_wc-orders_custom_column', __NAMESPACE__ . '\add_invoice_number', 10, 2 );


/**
 * Download E-Invoice action
 */
add_action(
	'post_action_download_e_invoice',
	function ( $id ) {
		check_ajax_referer( 'download_e_invoice', 'nonce_invoice' );
		$order = wc_get_order( $id );
		if ( isset( $_GET['xml'] ) ) {
			$profile = get_invoice_profile();
			$invoice = get_invoice( $order, 'urn:cen.eu:en16931:2017#compliant#urn:factur-x.eu:1p0:basic' );
			// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- safe binary pdf content or xml.
			echo $invoice->getXml();
		} elseif ( isset( $_GET['html'] ) ) {
			include WOOEI_TEMPLATE . 'invoice-preview.php';
		} else {
			$profile    = get_invoice_profile();
			$xml_or_pdf = is_xml( $profile ) ? 'xml' : 'pdf';
			PdfInvoice::send_headers( $order->get_id() . '.' . $xml_or_pdf, $xml_or_pdf );

			$xml_or_pdf_content_safe = render_invoice( $order, false );
			// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- safe binary pdf content or xml.
			echo $xml_or_pdf_content_safe;
		}

		exit();
	}
);

/**
 * Adding link to documentation in the plugin row
 */
add_filter(
	'plugin_row_meta',
	function ( $links, $file ) {
		if ( WOOEI_PLUGIN_BASENAME !== $file ) {
			return $links;
		}

		$row_meta = array(
			'docs' => '<a href="https://www.woo-einvoicing.com/docs/" aria-label="' . esc_attr__( 'View E-Invoicing for WooCommerce documentation', 'einvoicing-for-woocommerce' ) . '">' . esc_html__( 'E-Invoicing Documentation', 'einvoicing-for-woocommerce' ) . '</a>',
		);
		return array_merge( $links, $row_meta );
	},
	10,
	2
);

/**
 * Show upgrade notice
 *
 * @param      array $data      The data.
 * @param      array $response  The response.
 */
function prefix_plugin_update_message( $data, $response ) {
	if ( isset( $response->upgrade_notice ) ) {
		printf(
			'<div class="update-message">%s</div>',
			wp_kses_post( wpautop( $response->upgrade_notice ) )
		);
	}
}
add_action( 'in_plugin_update_message-einvoicing-for-woocommerce/einvoicing-for-woocommerce.php', __NAMESPACE__ . '\prefix_plugin_update_message', 10, 2 );

/**
 * Declare compatibility with WooCommerce
 */
add_action(
	'before_woocommerce_init',
	function () {
		if ( class_exists( FeaturesUtil::class ) ) {
			FeaturesUtil::declare_compatibility( 'custom_order_tables', WOOEI_PLUGIN_FILE, true );
		}
	}
);

// Adding to order list bulk invoice download item.
add_filter( 'bulk_actions-edit-shop_order', __NAMESPACE__ . '\add_bulk_download_item', 20, 1 );
add_filter( 'bulk_actions-woocommerce_page_wc-orders', __NAMESPACE__ . '\add_bulk_download_item', 20, 1 );
/**
 * Add Bulk download Item
 *
 * @param      array $actions  The actions.
 *
 * @return     array The actions.
 */
function add_bulk_download_item( $actions ) {
	if ( class_exists( ZipArchive::class ) ) {
		$actions['download_invoices'] = __( 'Download invoices', 'einvoicing-for-woocommerce' );
	}
	return $actions;
}



// Make the action from selected orders.
add_filter( 'handle_bulk_actions-edit-shop_order', __NAMESPACE__ . '\downloads_handle_bulk', 10, 3 );
add_filter( 'handle_bulk_actions-woocommerce_page_wc-orders', __NAMESPACE__ . '\downloads_handle_bulk', 10, 3 );

/**
 * Create a zip for selected post_ids
 *
 * @param      string $redirect_to  The redirect to.
 * @param      string $action       The action.
 * @param      string $post_ids     The post identifiers.
 *
 * @return     string  The filtred redirect url.
 */
function downloads_handle_bulk( $redirect_to, $action, $post_ids ) {
	if ( 'download_invoices' !== $action ) {
		return $redirect_to;
	}
	if ( ! class_exists( ZipArchive::class ) ) {
		wp_die( esc_html__( 'ZipArchive not present, please check your configuration', 'einvoicing-for-woocommerce' ) );
	}
	$zip        = new ZipArchive();
	$upload_dir = wp_upload_dir();
	$date       = gmdate( 'hidmY' );
	$filepath   = $date . '/' . "export-$date.zip";
	$temp_zip   = $upload_dir['basedir'] . '/' . WOOEI_ZIP_EXPORT_PATH . $filepath;
	ensure_directory_exists( dirname( $temp_zip ) );
	if ( $zip->open( $temp_zip, ZipArchive::CREATE ) === true ) {
		foreach ( $post_ids as $post_id ) {
			$order           = wc_get_order( $post_id );
			$invoice_content = render_invoice( $order, false );
			$zip->addFromString( invoice_filename( $order ), $invoice_content );
		}
		$zip->close();
		$redirect_to = add_query_arg(
			array(
				'invoice_export'       => $filepath,
				'nonce_invoice_export' => wp_create_nonce( 'nonce_invoice_export' ),

			),
			$redirect_to
		);
	}

	return $redirect_to;
}


// The results notice from bulk action on orders.
add_action( 'admin_notices', __NAMESPACE__ . '\downloads_notice' );
/**
 * Shows download link
 */
function downloads_notice() {
	// check nonce.
	if ( ! isset( $_REQUEST['nonce_invoice_export'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_REQUEST['nonce_invoice_export'] ) ), 'nonce_invoice_export' ) ) {
		return;
	}
	if ( ! isset( $_REQUEST['invoice_export'] ) || empty( $_REQUEST['invoice_export'] ) ) {
		return; // Exit, not concerned.
	}
	// Avoid parent directory explore.
	$path       = str_replace( '..', '', sanitize_text_field( wp_unslash( $_REQUEST['invoice_export'] ) ) );
	$upload_dir = wp_upload_dir();
	if ( file_exists( $upload_dir['basedir'] . '/' . WOOEI_ZIP_EXPORT_PATH . $path ) ) {
		$export_url = $upload_dir ['baseurl'] . '/' . WOOEI_ZIP_EXPORT_PATH . $path;
		$ready      = __( 'Your export is ready.', 'einvoicing-for-woocommerce' );
		$click      = __( 'Click to download', 'einvoicing-for-woocommerce' );
		printf( '<div id="message" class="updated fade"><p>%s <a href="%s">%s</a></p></div>', esc_html( $ready ), esc_url( $export_url ), esc_html( $click ) );
	} else {
		printf( 'Exported file is not present' );
	}
}



/**
 * Adds an invoice number field.
 *
 * @param      WC_Abstract_Order $order  The order.
 */
function add_invoice_number_field( WC_Abstract_Order $order ) {
	woocommerce_wp_text_input(
		array(
			'id'            => '_invoice_number',
			'label'         => __( 'Invoice Number :', 'einvoicing-for-woocommerce' ),
			'value'         => get_post_meta( $order->get_id(), '_invoice_number', true ),
			'wrapper_class' => 'form-field-wide',
		)
	);
}

/**
 * Saves an invoice number field.
 *
 * @param      int $order_id  The order identifier.
 */
function save_invoice_number_field( int $order_id ) {
	// phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
	if ( empty( $_POST['woocommerce_meta_nonce'] ) || ! wp_verify_nonce( wp_unslash( $_POST['woocommerce_meta_nonce'] ), 'woocommerce_save_data' ) ) {
		return;
	}
	if ( isset( $_POST['_invoice_number'] ) ) {
		update_post_meta( $order_id, '_invoice_number', sanitize_text_field(wp_unslash( $_POST['_invoice_number'] )) );
	}
}

/**
 * Displays the invoice number
 *
 * @param      WC_Abstract_Order $order  The order.
 */
function display_invoice_number( WC_Abstract_Order $order ) {
	$invoice_number = get_invoice_number( $order );
	if ( $invoice_number ) {
		echo '<p class="woocommerce-order-data__meta order_number"><strong>' . esc_html__( 'Invoice #', 'einvoicing-for-woocommerce' ) . '</strong>' . esc_html( $invoice_number ) . '</p>';
	}
}

if ( has_invoice_numbering() ) {
	// Add invoice number field to order edit page.
	add_action( 'woocommerce_admin_order_data_after_order_details', __NAMESPACE__ . '\add_invoice_number_field' );
	// Save custom invoice number.
	add_action( 'woocommerce_process_shop_order_meta', __NAMESPACE__ . '\save_invoice_number_field' );

	// Display the invoice number in the admin order details.
	add_action( 'woocommerce_admin_order_data_after_payment_info', __NAMESPACE__ . '\display_invoice_number' );
}

