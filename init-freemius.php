<?php

/**
 * Freemius initialisation
 *
 * @package WOOEI
 */
namespace WOOEI;

if ( !defined( 'ABSPATH' ) ) {
    exit;
    // Exit if accessed directly.
}
if ( !function_exists( 'wooei_fs' ) ) {
    /**
     * Create a helper function for easy SDK access.
     */
    function wooei_fs() {
        global $wooei_fs;
        if ( !isset( $wooei_fs ) ) {
            // Include Freemius SDK.
            include_once WOOEI_VENDOR . 'freemius/wordpress-sdk/start.php';
            $wooei_fs = fs_dynamic_init( array(
                'id'             => '14509',
                'slug'           => 'einvoicing-for-woocommerce',
                'premium_slug'   => 'einvoicing-pro-for-woocommerce',
                'type'           => 'plugin',
                'public_key'     => 'pk_15e83a068f50da8d0e052078acd4a',
                'is_premium'     => false,
                'premium_suffix' => 'Pro',
                'has_addons'     => false,
                'has_paid_plans' => true,
                'trial'          => array(
                    'days'               => 14,
                    'is_require_payment' => true,
                ),
                'menu'           => array(
                    'slug'    => 'einvoicing-for-woocommerce',
                    'support' => false,
                ),
                'is_live'        => true,
            ) );
        }
        return $wooei_fs;
    }

    // Init Freemius.
    wooei_fs();
    /**
     * Signal that SDK was initiated.
     *
     * @since 0.0.1
     */
    do_action( 'wooei_fs_loaded' );
}
// Onboarding is presented in the dashboard as well.
add_filter( 'wooei_show_onboarding', function ( $show ) {
    $pagenow = $GLOBALS['pagenow'];
    if ( 'index.php' === $pagenow ) {
        $show = true;
    }
    return $show;
} );
/**
 * Adds the Woo E-Invoicing admin menu.
 */
function admin_menu() {
    // phpcs:ignore WordPress.PHP.DiscouragedPHPFunctions.obfuscation_base64_encode, WordPress.WP.AlternativeFunctions.file_get_contents_file_get_contents, Generic.PHP.ForbiddenFunctions.Found -- Passing a base64-encoded SVG using a data URI.
    $svg_icon = base64_encode( file_get_contents( WOOEI_ASSETS . 'images/icon.svg' ) );
    add_menu_page(
        'E-Invoicing For WooCommerce',
        'E-Invoicing For WooCommerce',
        'manage_options',
        'einvoicing-for-woocommerce',
        __NAMESPACE__ . '\\plugin_page',
        'data:image/svg+xml;base64,' . $svg_icon
    );
}

/**
 * Woo E-Invoicing admin page, help with the onboarding.
 */
function plugin_page() {
    // go the our tab.
    wp_safe_redirect( settings_url() );
    exit;
}

/**
 * WooCommerce notice settings, open to receive feedback.
 */
function notice_settings() {
    ?>
	<div class="notice notice-info is-dismissible" ><p>
	<?php 
    echo wp_kses_post( sprintf( 
        /* translators: 1: Url to the contact form. */
        __( 'Welcome to E-Invoicing For WooCommerce, feel to <a href="%s" >contact us</a> if you have any question or suggestion.', 'einvoicing-for-woocommerce' ),
        esc_url( wooei_fs()->contact_url() )
     ) );
    ?>
		</p><button type="button" class="notice-dismiss" onclick="this.parentNode.remove();" ></button>
	</div>
	<?php 
}

add_action( 'wooei_ready', function () {
    if ( is_admin() ) {
        add_action( 'admin_menu', __NAMESPACE__ . '\\admin_menu' );
        add_action( 'woocommerce_before_settings_einvoicing', __NAMESPACE__ . '\\notice_settings' );
    }
} );
add_action( 'wooei_before_templates', function () {
    ?>
		<p>
			<?php 
    esc_html_e( 'More prebuilt templates are available in premium version', 'einvoicing-for-woocommerce' );
    ?>
		</p>
			<?php 
} );