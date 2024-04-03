<?php
/**
 * The main template file for invoice customization.
 *
 * @package WOOEI
 */

namespace WOOEI;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
} // Exit if accessed directly;
?>
<!doctype html>
<html>
<head>
<?php
/**
 * Customizer header
 *
 * @since 0.0.4
 */
do_action( 'wooei_customizer_header' );
?>
</head>
<body>
<?php

echo wp_kses( render_invoice( $orders[0], true ), allowed_html_tags() );


/**
 * Customizer footer
 *
 * @since 0.0.4
 */
do_action( 'wooei_customizer_footer' );
?>
</body>
</html>
