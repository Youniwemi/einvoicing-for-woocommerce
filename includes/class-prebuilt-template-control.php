<?php
/**
 * This file implements prebuilt template control.
 *
 * @package WOOEI
 */

namespace WOOEI;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

if ( class_exists( '\WP_Customize_Control' ) ) {
	/**
	 * Prebuilt template control, with action to load the template
	 */
	class Prebuilt_Template_Control extends \WP_Customize_Control {


		/**
		 * Control Type
		 *
		 * @var string
		 */
		public $type = 'prebuilt-template';

		/**
		 * Renders the content of the control
		 */
		public function render_content() {
			$nonce = wp_create_nonce( 'wooei_load_template_nonce' );
			?>

			<span class="customize-control-title">
			<?php esc_html_e( 'Load Template', 'einvoicing-for-woocommerce' ); ?>
			</span>

			<?php
			/**
			 * Before template selector
			 *
			 * @since 0.0.9
			 */
			do_action( 'wooei_before_templates' );
			?>
			<select id="prebuilt-template" name="prebuilt-template" >

			<?php foreach ( $this->choices as $value => $label ) : ?>
				<option value="<?php echo esc_attr( $value ); ?>"  
				<?php
				if ( $this->value() === $value ) {
					echo 'selected';
				}
				?>
				>
				<?php echo esc_html( $label ); ?>
				</option>
			<?php endforeach; ?>
			</select>
			<?php wp_nonce_field( 'wooei_template', 'wooei_template' ); ?>
			<input type="button" class="button button-primary" id="wooei-load-template" style="margin-top: 1rem;" value="<?php esc_attr_e( 'Load Template', 'einvoicing-for-woocommerce' ); ?>" />

			<script>
				jQuery(document).ready(function($) {
					const templateSelector = $('#prebuilt-template');
					const currentTemplate = templateSelector.val();
					$('#wooei-load-template').click(function(){
						if (confirm(<?php echo wp_json_encode( __( 'Are you sure you want to load this template, you will lose all your settings', 'einvoicing-for-woocommerce' ) ); ?> ) == false) {
							templateSelector.val(currentTemplate);
							return;
						}
						const me = $(this);
						me.prop('disabled', true);
						const template = $('#prebuilt-template').val();

						const data = {
							action: 'wooei_load_template',
							template: template,
							wooei_load_template_nonce : <?php echo wp_json_encode( $nonce ); ?>
						};

						// Send request to server.
						$.post( ajaxurl, data, function( result ) {
							location.reload();
						}).fail(function(xhr) {
							alert(xhr.responseText);
						}).always(function() {
							me.prop('disabled', false);
						});

						return false;
					});

				});                
			</script>
			<?php
		}
	}
}
