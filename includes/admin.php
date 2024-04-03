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

use \Automattic\WooCommerce\Utilities\FeaturesUtil;
use WC_Abstract_Order;

/**
 * Get the plugin setting url.
 *
 * @return     string  The setting url.
 */
function settings_url() {
	return admin_url( 'admin.php?page=wc-settings&tab=einvoicing' );
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
		?>
	<div class="notice notice-info is-dismissible" style="display:flex; align-items: center;column-gap:2em">
		<div>
			<img width="120" src="<?php echo esc_url( WOOEI_PLUGIN_ASSETS . '/images/logo-dark.png' ); ?>" />
		</div>
		<div>
		<?php if ( $customized && $configured && $is_plugins_page ) { ?>
			<h3><?php echo esc_html__( 'Welcome Back! Woo E-Invoicing reinstallation Is complete and almost ready to go', 'einvoicing-for-woocommerce' ); ?></h3>
			<p><?php echo esc_html__( 'Please ensure that your settings and customization are still accurate and enjoy e-invoicing regulations compliance', 'einvoicing-for-woocommerce' ); ?></p>
		<?php } else { ?>
			<h3><?php echo esc_html__( 'Woo E-Invoicing For WooCommerce is installed, almost ready to go', 'einvoicing-for-woocommerce' ); ?></h3>
			<p><?php echo esc_html__( 'In just a few steps, you\'ll meet all e-invoicing regulations effortlessly. All you need to do is set up your company details and visually customize your invoice.', 'einvoicing-for-woocommerce' ); ?></p>            
		<?php } ?> 
			<p>
		<?php if ( $just_installed || false === $configured ) : ?>
				<a class="button-primary button" href="<?php echo esc_url( admin_url( 'admin.php?page=wc-settings&tab=einvoicing' ) ); ?>"  ><?php echo esc_html( $setting_title ); ?></a>
		<?php endif; ?>
		<?php if ( $just_installed || false === $customized ) : ?>
				<a class="button <?php echo ( ! $just_installed && $configured ? 'button-primary' : '' ); ?>" href="<?php echo esc_url( Invoice_Customizer::customizer_link() ); ?>"  ><?php echo esc_html( $customize_title ); ?></a>
		<?php endif; ?>
			</p>
		</div>
		<button type="button" class="notice-dismiss" onclick="this.parentNode.remove();" ><span class="screen-reader-text"><?php esc_html_e( 'Dismiss this notice.', 'einvoicing-for-woocommerce' ); ?></span></button>
	</div>
		<?php
	}
};

/**
 * Show a nice onboarding notice
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
 * Adds invoice columns.
 *
 * @param array $columns The columns.
 *
 * @return array  The columns with added column.
 */
function add_invoice_columns( array $columns ) {
	$columns['invoice'] = __( 'Invoice', 'einvoicing-for-woocommerce' );
	add_thickbox();
	return $columns;
}


add_filter( 'manage_edit-shop_order_columns', __NAMESPACE__ . '\add_invoice_columns', 999 );
add_filter( 'manage_woocommerce_page_wc-orders_columns', __NAMESPACE__ . '\add_invoice_columns', 999 );

add_action( 'manage_shop_order_posts_custom_column', __NAMESPACE__ . '\add_invoice_download_links', 10, 2 );
add_action( 'manage_woocommerce_page_wc-orders_custom_column', __NAMESPACE__ . '\add_invoice_download_links', 10, 2 );


/**
 * Download E-Invoice action
 */
add_action(
	'post_action_download_e_invoice',
	function ( $id ) {
		check_ajax_referer( 'download_e_invoice', 'nonce_invoice' );
		$order = wc_get_order( $id );
		if ( isset( $_GET['html'] ) ) {
			include WOOEI_TEMPLATE . 'invoice-preview.php';
		} else {
			$profile    = get_option( 'wooei_invoice_type', WOOEI_TYPES_PDF );
			$xml_or_pdf = is_xml( $profile ) ? 'xml' : 'pdf';
			PdfInvoice::send_headers( $order->ID . '.' . $xml_or_pdf, $xml_or_pdf );

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
