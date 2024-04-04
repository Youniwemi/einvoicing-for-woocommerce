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
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
</head>
<body>
<?php
echo wp_kses( render_invoice( $order, true ), allowed_html_tags() );
?>
</body>
</html>
