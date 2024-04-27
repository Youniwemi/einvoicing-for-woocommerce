<?php
/**
 * The main template file for invoice rendering.
 *
 * @package WOOEI
 */

namespace WOOEI;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
} // Exit if accessed directly;
?>
<style type="text/css" media="screen">
	body,
	p,
	h1,
	h2,
	h3,
	h4,
	h5,
	h6 {
		margin: 0;
		padding: 0;
	}

	body,
	p,
	td, th {
		font-family: <?php echo ( $main_font_family ? Customizer_Helper::get_font_family( $main_font_family ) : 'inherit' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped, WordPressDotOrg.sniffs.OutputEscaping.UnescapedOutputParameter -- get_font_family returns valid font-family css strings ?>;
		font-size: <?php echo (float) $main_font_size; ?>px;
		line-height: 1.2em;
		color: <?php echo esc_attr( $main_font_color ); ?>;
	}
	h1 {
		font-size: <?php echo esc_attr( round( $main_font_size * 1.3, 1 ) ); ?>px;
		font-weight: normal;
		line-height: 1.5em;
	}
	h2 {
		font-size: <?php echo esc_attr( round( $main_font_size * 1.2, 1 ) ); ?>px;
		line-height: 1.4em;
	}
	h3 {
		font-size: <?php echo esc_attr( round( $main_font_size * 1.1, 1 ) ); ?>px;
		line-height: 1.3em;
	}

	body,
	p {
		margin-bottom: 0;
		-webkit-text-size-adjust: none;
		-ms-text-size-adjust: none;
	}



/******* INVOICE PREVIEW ***********/
	@page {
		margin: 0cm;
	}
	body{
		margin: 0px;
		background-color: <?php echo esc_attr( $main_bg_color ); ?>;
	}
	#TB_ajaxContent {
		padding: 0;
		margin: 0 auto;
		width: 100% !important;
	}
/****************************** FX INVOICE STYLE ******************************/
	.customize-partial-edit-shortcuts-shown .container{
		width: 80% !important;
		display: flex;
		flex-direction: column;
		justify-content: space-between;
	}

	.fx-float-r{
		float: right;
	}
	.fx-float-l{
		float: left;
	}
	.invoice-section{
		background-image: <?php echo esc_attr( $head_bg_color ); ?>;
	}
	.container {
		margin: 0 auto;
		position: relative;
	}

	.fx-container{
		display: flow-root;
	}

	/******** FX HEAD *******/
	.fx-invoice-head{
		background-color: <?php echo esc_attr( $head_bg_color ); ?>;
		color: <?php echo esc_attr( $head_txt_color ); ?>;
	}
	.fx-invoice-logo{
		float: left;
	}

	.fx-invoice-logo img{
		width: <?php echo isset( $logo_width ) ? (int) $logo_width : '200'; ?>px;
	}
	.fx-invoice-shop-info {
		float: right;
		min-width:200px;
	}

	.fx-invoice-head .fx-container{
		display: flow-root;
		padding: <?php echo esc_attr( $head_tb_padding ); ?>px <?php echo esc_attr( $fx_lr_padding ); ?>px;
	}
	/* Clear the float using display: flow-root */
.fx-clear:after {
	content: "";
	display: table;
	clear: both;
}
	/***** FX CONTENT *******/
	.fx-invoice-shipping .fx-container,
	.fx-footer .fx-container,
	.fx-invoice-order .fx-container{
		padding: 20px <?php echo esc_attr( $fx_lr_padding ); ?>px;
	}


	.fx-invoice-title{
		color: <?php echo esc_attr( $title_color ); ?>;
	}

	/***************** INVOICE DETAILS SECTION *****************/
	.fx-invoice-right{
		text-align: right;
	}
	.fx-invoice-item{
		width: 33%;
	}
	.fx-invoice-shipping {
		background-color: <?php echo esc_attr( $details_invoice_bg ); ?>;
		padding: <?php echo esc_attr( $details_tb_padding ); ?>px <?php echo esc_attr( $fx_lr_padding ); ?>px;
	}
	.fx-invoice-shipping .fx-container {
		color: <?php echo esc_attr( $details_invoice_txt ); ?>;
	}
	.fx-invoice-title{
		color: <?php echo esc_attr( $details_invoice_title ); ?>;
	}
	/****** TBALE INVOICE ORDERS SECTION **********/
	.fx-invoice-order {
		min-height: calc(70vh);
	}

	table.order-details tr:nth-child(even) {
		background-color:<?php echo esc_attr( $order_detail['even_row_bg_color'] ); ?>;
	}

	table.order-details tr:nth-child(odd) {
	background-color:<?php echo esc_attr( $order_detail['odd_row_bg_color'] ); ?>;
	}

	table , td, th{
		border-collapse: collapse;
	}

	.order-details .quantity,
	.order-details .price,
	.order-details .total {
		width: 15%;
	}

	table.order-details{ 
		width:100%;
		margin-top: <?php echo (float) $order_detail['top_bottom_margin']; ?>px;
		margin-bottom: <?php echo (float) $order_detail['top_bottom_margin']; ?>px;
		page-break-before: avoid;
	}

	table.order-details tr { 
		page-break-inside: always;
		page-break-after: auto;    
	}

	table.order-details td,  table.order-details th{
		border: <?php echo (float) $order_detail['border_size']; ?>px solid <?php echo esc_attr( $order_detail['border_color'] ); ?> ;
	}

	table.order-details th{
		background-color:<?php echo esc_attr( $order_detail['table_head_bg'] ); ?>;
		color:<?php echo esc_attr( $order_detail['table_head_txt_color'] ); ?>;
		text-align: <?php echo esc_attr( $order_detail['th_align'] ); ?>;
		padding: <?php echo (float) $order_detail['th_padding']; ?>px;
	}
	table.order-details td{
		padding: <?php echo (float) $order_detail['cell_padding']; ?>px;
		text-align: <?php echo esc_attr( $order_detail['td_align'] ); ?>;
	}

	table.order-details tr.no-borders,
	table.order-details td.no-borders {
		border: 0 !important;
		border-top: 0 !important;
		border-bottom: 0 !important;
		padding: 0 !important;
		width: auto;
		background-color:<?php echo esc_attr( $main_bg_color ); ?>;
	}

	table.totals {
		width: 100%;
		margin-top: 5mm;
	}

	table.totals .price {
		width: 50%;
	}
	/***************** CUSTOMER NOTE SECTION ******************/
	.customer-notes {
		padding: 0 15px 0px 0;
	}
	.customer-notes h3{
		color: <?php echo esc_attr( $customer_note_title ); ?>;
	}
	.customer-notes p{
		color: <?php echo esc_attr( $customer_note_txt ); ?>;
	}
	/***************** INVOICE FOOTER SECTION *****************/

	.fx-footer {
		background-color: <?php echo esc_attr( $footer_bg_color ); ?>;
		height: auto;
		width: 100%;
		margin-bottom: 0;
		position: absolute;
		bottom: 0;
	}
	.fx-footer #footer_text {
		color: <?php echo esc_attr( $footer_txt_color ); ?>;
		font-size: <?php echo floatval( $footer_txt_size ); ?>px;
	}
	.pagenum:before {
		content: counter(page);
	}
	.pagenum,.pagecount {
		font-family: sans-serif;
	}
</style>
<?php
/**
 * Before Document
 *
 * @since 0.0.4
 */
do_action( 'wooei_before_document', $this->order );
?>
<div class="container">
	<div class="row fx-invoice-head fx-clear">
		<div class="fx-container">
			<div class="fx-invoice-logo">
				<?php if ( isset( $logo ) && $logo ) { ?> 
				<img src="<?php echo esc_url( $logo ); ?>" alt="logo" />
				<?php } ?> 
			</div>
			<div class="fx-invoice-shop-info">
					<h3 class="shop-name"><?php echo esc_html( $this->shop_name() ); ?></h3>
					<div class="shop-address"><?php echo wp_kses_post( $this->shop_address() ); ?></div>
			</div>
		</div>
	</div>
	<div class="row fx-invoice-shipping fx-clear">
		<div class="fx-container">
			<div class="fx-invoice-item fx-invoice-left fx-float-l">
				<h2 class="fx-invoice-title"><?php esc_html_e( 'Billing to:', 'einvoicing-for-woocommerce' ); ?></h2>
				<div class="address billing-address">
					<div class="billing-nane"><?php echo esc_html( $this->get_billing_to() ); ?></div>
					<?php echo esc_html( $this->get_billing_address_1() ); ?>
					<?php
					/**
					 * After billing address
					 *
					 * @since 0.0.4
					 */
					do_action( 'wooei_after_billing_address', $this->order );
					?>
					<?php if ( isset( $this->settings['display_email'] ) ) : ?>
						<div class="billing-email"><?php echo esc_html( $this->get_billing_email() ); ?></div>
					<?php endif; ?>
					<?php if ( isset( $this->settings['display_phone'] ) ) : ?>
						<div class="billing-phone"><?php echo esc_html( $this->get_billing_phone() ); ?></div>
					<?php endif; ?>
				</div>
			</div>
			<div class="fx-invoice-item fx-invoice-center fx-float-l">
				<?php if ( $this->get_shipping_address_1() ) : ?>
				<div class="address shipping-address">
						<h2 class="fx-invoice-title"><?php esc_html_e( 'Shipping address:', 'einvoicing-for-woocommerce' ); ?></h2>
					<?php
					/**
					 * Before Shipping Address
					 *
					 * @since 0.0.4
					 */
					do_action( 'wooei_before_shipping_address', $this->order );
					?>
					<?php echo esc_html( $this->get_shipping_address_1() ); ?>
					<?php
					/**
					 * After Shipping Address
					 *
					 * @since 0.0.4
					 */
					do_action( 'wooei_after_shipping_address', $this->order );
					?>
					<?php if ( isset( $this->settings['display_phone'] ) ) : ?>
						<div class="shipping-phone"><?php echo esc_html( $this->get_shipping_phone() ); ?></div>
					<?php endif; ?>
				</div>
				<?php endif; ?>    
			</div>
			<div class="fx-invoice-item fx-invoice-right fx-float-r">
				<h2 class="fx-invoice-title document-type-label">
					<?php echo esc_html( $this->title() ); ?>
				</h2>
				<div class="order-data-addresses">
					<div class="order-data">
						<?php
						/**
						 * Before order data
						 *
						 * @since 0.0.4
						 */
						do_action( 'wooei_before_order_data', $this->order );
						?>
						<?php if ( isset( $this->settings['display_number'] ) ) : ?>
							<div class="invoice-number">
							<?php echo esc_html( $this->get_number_title() ); ?>
							<?php echo esc_html( $this->get_invoice_number() ); ?>
							</div>
						<?php endif; ?>
						<?php if ( isset( $this->settings['display_date'] ) ) : ?>
							<div class="invoice-date">
							<?php echo esc_html( $this->get_date_title() ); ?>
							<?php echo esc_html( $this->get_date_paid() ? $this->get_date_paid()->format( 'd-m-Y' ) : 'Not paid' ); ?>
							</div>
						<?php endif; ?>
						<div class="order-number">
							<?php esc_html_e( 'Order Number:', 'einvoicing-for-woocommerce' ); ?>
							<?php echo esc_html( $this->get_order_number() ); ?>
						</div>
						<div class="order-date">
							<?php esc_html_e( 'Order Date:', 'einvoicing-for-woocommerce' ); ?>
							<?php echo esc_html( $this->get_date_paid() ? $this->get_date_paid()->format( 'd-m-Y' ) : 'Not paid' ); ?>
						</div>
						<?php
						/**
						 * After order data
						 *
						 * @since 0.0.4
						 */
						do_action( 'wooei_after_order_data', $this->order );
						?>
					</div>
				</div>
			</div>
		</div>
	</div>
	<div class="row fx-invoice-order">
		<div class="fx-container">
			<?php
			/**
			 * Before order details
			 *
			 * @since 0.0.4
			 */
			do_action( 'wooei_before_order_details', $this->order );
			?>
			<table class="order-details">
				<thead>
					<tr>
						<th class="product"><?php esc_html_e( 'Product', 'einvoicing-for-woocommerce' ); ?></th>
						<th class="quantity"><?php esc_html_e( 'Quantity', 'einvoicing-for-woocommerce' ); ?></th>
						<th class="price"><?php esc_html_e( 'Price', 'einvoicing-for-woocommerce' ); ?></th>
						<th class="total"><?php esc_html_e( 'Total', 'einvoicing-for-woocommerce' ); ?></th>
					</tr>
				</thead>
				<tbody>
					<?php
					foreach ( $this->order->get_items() as $item_id => $item ) :
						?>
						<tr class="item-order item-<?php echo esc_attr( $item_id ); ?>">
							<td class="product">
								<span class="item-name"><?php echo esc_html( $item['name'] ); ?></span>
								<span class="item-meta"><?php echo esc_html( $item['meta'] ); ?></span>
								<?php if ( isset( $item['sku'] ) && isset( $item['weight'] ) ) : ?>
									<dl class="meta">
								<?php endif; ?>
						<?php
						if ( ! empty( $item['sku'] ) ) :
							?>
							<dt class="sku"><?php esc_html_e( 'SKU:', 'einvoicing-for-woocommerce' ); ?></dt><dd class="sku"><?php echo esc_attr( $item['sku'] ); ?></dd>
							<?php
						endif;
						?>
						<?php if ( ! empty( $item['weight'] ) ) : ?>
										<dt class="weight"><?php esc_html_e( 'Weight:', 'einvoicing-for-woocommerce' ); ?></dt><dd class="weight"><?php echo esc_attr( $item['weight'] ); ?><?php echo esc_attr( get_option( 'woocommerce_weight_unit' ) ); ?></dd>
						<?php endif; ?>
								</dl>
							</td>
							<td class="quantity"><?php echo esc_html( $item['quantity'] ); ?></td>
							<td class="price"><?php echo esc_html( $this->format_money( $item['subtotal'] ) ); ?></td>
							<td class="total"><?php echo esc_html( $this->format_money( $item['total'] ) ); ?></td>
						</tr>
					<?php endforeach; ?>
				</tbody>
				<tfoot>
					<tr class="no-borders">
						<td class="no-borders"  colspan="2">
							<div class="customer-notes">
								<?php if ( $this->get_customer_note() ) : ?>
									<h3><?php esc_html_e( 'Customer Notes', 'einvoicing-for-woocommerce' ); ?></h3>
									<p><?php echo esc_html( $this->get_customer_note() ); ?></p>
								<?php endif; ?>
							</div>                
						</td>
						<td class="no-borders" colspan="2">
							<table class="totals">
								<tfoot>
									<?php foreach ( $this->order->get_order_item_totals() as $key => $total ) : ?>
										<tr class="<?php echo esc_attr( $key ); ?>">
											<th class="description"><?php echo esc_html( $total['label'] ); ?></th>
											<td class="price"><span class="totals-price"><?php echo wp_kses_post( $total['value'] ); ?></span></td>
										</tr>
									<?php endforeach; ?>
								</tfoot>
							</table>
						</td>
					</tr>
				</tfoot>
			</table>
			<?php
			/**
			 * After order details
			 *
			 * @since 0.0.4
			 */
			do_action( 'wooei_after_order_details', $this->order );
			?>
		</div>
	</div>
	<?php if ( isset( $footer ) && $footer ) { ?>
	<div class="row fx-footer fx-clear">
		<div class="fx-container">
			<p id="footer_text"><?php echo wp_kses_post( $footer ); ?></p>
		</div>
	</div>
	<?php } ?>
</div>
