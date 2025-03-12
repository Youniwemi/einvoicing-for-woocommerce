<?php
/**
 * This file implements invoice customizer, it also take care of related stuff such as :
 * - Adding submenu links
 * - Ajax endpoint to load template
 * - The invoice Preview
 *
 * @package WOOEI
 */

namespace WOOEI;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

require __DIR__ . '/class-customizer-helper.php';

define( 'WOOEI_PERMISSION_MANAGER', 'manage_woocommerce' );

/**
 * Invoice Customizer, handles the customization via the WordPress native customizer
 */
class Invoice_Customizer extends Customizer_Helper {




	/**
	 * Settings option
	 *
	 * @var string
	 */
	protected static $settings_option = 'wooei_customizations';

	/**
	 * Customizer defaults
	 *
	 * @var array
	 */
	protected $defaults = array(
		'logo'       => null,
		'logo_width' => 200,
	);

	/**
	 * Main panel name
	 *
	 * @var string|bool
	 */
	protected $main_panel = false;

	/**
	 * Preview Mode
	 *
	 * @var bool
	 */
	protected $is_previewing = false;


	/**
	 * Customization Mode
	 *
	 * @var bool
	 */
	protected $is_customizing = false;


	/**
	 * Constructs a new instance.
	 */
	protected function __construct() {

		$this->defaults['footer'] = __( 'We truly appreciate your business and look forward to helping you again soon.', 'einvoicing-for-woocommerce' );

		// If there is already a logo for this blog, we use it as a default.
		if ( function_exists( 'has_custom_logo' ) && has_custom_logo() ) {
			$custom_logo_id     = get_theme_mod( 'custom_logo' );
			list($logo, $width) = wp_get_attachment_image_src( $custom_logo_id, 'full' );

			$this->defaults['logo'] = $logo;
			// Avoid ugly default.
			$this->defaults['logo_width'] = max( $width, 300 );
		}

		$this->defaults = array_merge( $this->defaults, static::get_template_defaults( 'black' ) );

		$this->min_capability = WOOEI_PERMISSION_MANAGER;

		$this->prepare_customizer();
		parent::__construct();

		// Loads template action.
		add_action( 'wp_ajax_wooei_load_template', array( $this, 'load_template' ) );

		// 51, Just after the Settings.
		add_action( 'admin_menu', array( $this, 'add_customizer_submenu' ), 51 );

		if ( $this->is_previewing ) {
			add_action( 'template_redirect', array( $this, 'preview_invoice' ), 1 );
		}
	}

	/**
	 * Loads a template.
	 */
	public function load_template() {

		check_ajax_referer( 'wooei_load_template_nonce', 'wooei_load_template_nonce' );
		if ( empty( $_POST['template'] ) ) {
			die( 1 );
		}
		$template = sanitize_text_field( wp_unslash( $_POST['template'] ) );

		$settings = static::get_template_defaults( $template );

		if ( $settings ) {
			$saved             = get_option( static::$settings_option );
			$saved['template'] = $template;
			foreach ( $settings as $key => $value ) {
				$saved[ $key ] = $settings[ $key ];
			}

			update_option( static::$settings_option, $saved, false );
			exit;
		} else {
			wp_send_json( array( 'error' => 'Not existing Template' ) );
		}
	}

	/**
	 * Prepares for the customizer initialization
	 */
	protected function prepare_customizer() {
		if ( isset( $_REQUEST['wooei_customizer_nonce'] ) && wp_verify_nonce( sanitize_text_field( wp_unslash( $_REQUEST['wooei_customizer_nonce'] ) ), 'wooei_customizer_nonce' ) ) {
			$this->is_customizing = true;
		}
		if ( isset( $_REQUEST['wooei_preview_nonce'] ) && wp_verify_nonce( sanitize_text_field( wp_unslash( $_REQUEST['wooei_preview_nonce'] ) ), 'wooei_preview_nonce' ) ) {
			$this->is_previewing = true;
		}

		if ( $this->is_customizing || $this->is_previewing ) {
			add_filter(
				'customize_loaded_components',
				'__return_empty_array',
				1,
				2
			);
			include WOOEI_INCLUDES . 'compatibility.php';
		}
	}


	/**
	 * Simple Preview, last invoice
	 *
	 * @todo : Maybe replace with a fake Order
	 */
	public function preview_invoice() {
		$orders = wc_get_orders( array( 'numberposts' => 1 ) );
		if ( $orders ) {
			include WOOEI_TEMPLATE . 'invoice-customizer.php';
			exit();
		}
		wp_die( esc_html__( 'Please create an order to be able to customize the invoice', 'einvoicing-for-woocommerce' ) );
	}

	/**
	 * Customizer link
	 *
	 * @return string  The invoice customizer link
	 */
	public static function customizer_link() {
		$preview = add_query_arg(
			array(
				'wooei_preview_nonce' => wp_create_nonce( 'wooei_preview_nonce' ),
			),
			home_url( '/' )
		);

		return add_query_arg(
			array(
				'wooei_customizer_nonce' => wp_create_nonce( 'wooei_customizer_nonce' ),
				'url'                    => rawurlencode( $preview ),
			),
			admin_url( 'customize.php' )
		);
	}

	/**
	 * Adds a customizer submenu.
	 */
	public function add_customizer_submenu() {
		$configured = get_option( 'wooei_invoice_type', false );
		if ( $configured ) {
			$link = self::customizer_link();
		} else {
			$link = add_query_arg( 'configured', '0', settings_url() );
		}
		add_submenu_page(
			'woocommerce',
			__( 'Invoice Customizer', 'einvoicing-for-woocommerce' ),
			__( 'Invoice Customizer', 'einvoicing-for-woocommerce' ),
			$this->min_capability,
			$link
		);
	}


	/**
	 * Gets the prebuilt templates.
	 *
	 * @return array  The prebuilt templates.
	 */
	public static function get_prebuilt_templates() {
		$prebuilt_templates = array(
			'black' => 'Black',
		);
		/**
		 * Prebuilt templates
		 *
		 * @since 0.0.4
		 */
		return apply_filters( 'wooei_prebuilt_templates', $prebuilt_templates );
	}

	/**
	 * Gets the template defaults,
	 *
	 * @param string $template The template.
	 *
	 * @return ?array  The templates defaults.
	 */
	public static function get_template_defaults( string $template ) {
		$templates_defaults = array(
			'black' => array(
				'main_bg_color'         => '#ffffff',
				'head_bg_color'         => '#000000', // FX HEADER BACKGOUND COLOR.
				'head_txt_color'        => '#ffffff', // FX HEADER TEXT COLOR.
				'head_tb_padding'       => 15, // FX HEADER PADDING (TOP-BOTTOM).
				'fx_lr_padding'         => 15, // FX PADDING (LEFT & RIGHT).
				'main_font_family'      => 'arial',
				'main_font_color'       => '#000000',
				'main_font_size'        => 11,
				'title_color'           => '#000000',

				// CUSTOMER NOTES SECTION.
				'customer_note_title'   => '#000000',
				'customer_note_txt'     => '#000000',

				// INVOICE DETAILS SECTION.
				'details_invoice_bg'    => '#f9f9f9',
				'details_invoice_txt'   => '#777777',
				'details_invoice_title' => '#000000',
				'details_tb_padding'    => 15,

				'footer_txt_color'      => '#000000',
				'footer_txt_size'       => 12,
				'order_detail'          => array(
					'border_color'         => '#eaeaea',
					'border_size'          => 0.5,
					'cell_padding'         => 10,
					'th_padding'           => 10,
					'td_align'             => 'left',
					'th_align'             => 'left',
					'table_head_bg'        => '#000000', // TABLE HEAD BACKGOUND.
					'table_head_txt_color' => '#ffffff', // TABLE HEAD TEXT COLOR.
					'odd_row_bg_color'     => '#ffffff', // Background color for odd rows.
					'even_row_bg_color'    => '#f6f6f6', // Background color for even rows.
					'top_bottom_margin'    => 20,
				),
			),
		);
		/**
		 * Templates defaults
		 *
		 * @since 0.0.4
		 */
		$defaults = apply_filters( 'wooei_templates_defaults', $templates_defaults );

		return isset( $defaults[ $template ] ) ? $defaults[ $template ] : null;
	}





	/**
	 * Init controls
	 *
	 * @inheritdoc
	 */
	public function init_controls() {
		include_once WOOEI_INCLUDES . '/class-prebuilt-template-control.php';
	}



	/**
	 * Gets the customizer settings.
	 *
	 * @inheritdoc
	 */
	public function get_customizer_settings() {
		static $settings, $sections;
		if ( null === $settings ) {
			$sections = array(
				// Prebuit templates.
				'template'               => __( 'Ready-to-Use designs', 'einvoicing-for-woocommerce' ),

				// GENERAL STYLE (BACKGROUND MAIL COLOR,FONT FAMILY...).
				'fields'                 => __( 'Fields visibility', 'einvoicing-for-woocommerce' ),

				// GENERAL STYLE (BACKGROUND MAIL COLOR,FONT FAMILY...).
				'general'                => __( 'Global style options', 'einvoicing-for-woocommerce' ),

				// INVOICE HEADER (LOGO & INVOICE INFORMATION).
				'header_invoice'         => __( 'Invoice header design', 'einvoicing-for-woocommerce' ),

				// INVOICE DETAILS SECTION (BILLING ADDRESS & INVOICE details).
				'details_invoice'        => __( 'Invoice details design', 'einvoicing-for-woocommerce' ),

				// INVOICE ORDER DETAIL (LOGO & INVOICE INFORMATION).
				'order_detail'           => __( 'Invoice table design', 'einvoicing-for-woocommerce' ),

				// CUSTOMER NOTES SECTION.
				'customer_notes_section' => __( 'Customer notes section', 'einvoicing-for-woocommerce' ),

				// FOOTER.
				'footer_section'         => __( 'Invoice footer design', 'einvoicing-for-woocommerce' ),
			);

			$order_detail = $this->get_defaults( 'order_detail' );
			$settings     = array(

				// Templates.
				// Email template.
				array(
					'id'          => 'template',
					'label'       => __( 'Choose a prebuilt template', 'einvoicing-for-woocommerce' ),
					'description' => __( 'You will loose your customizations', 'einvoicing-for-woocommerce' ),
					'section'     => 'template',
					'control'     => Prebuilt_Template_Control::class,
					'choices'     => self::get_prebuilt_templates(),
					'default'     => 'black',
					'transport'   => 'refresh',
				),

				array(
					'id'         => 'fields',
					'label'      => __( 'Setup fields visibility', 'einvoicing-for-woocommerce' ),
					'section'    => 'fields',
					'default'    => array(),
					'components' => array(
						'display_phone' => array(

							'label'       => __( 'Display client phone number', 'einvoicing-for-woocommerce' ),
							'description' => __( 'Hide or displays the customer phone number', 'einvoicing-for-woocommerce' ),
							'section'     => 'fields',
							'type'        => 'checkbox',
							'default'     => false,
						),

						'display_email' => array(

							'label'       => __( 'Display client email number', 'einvoicing-for-woocommerce' ),
							'description' => __( 'Hide or displays the customer email number', 'einvoicing-for-woocommerce' ),
							'section'     => 'fields',
							'type'        => 'checkbox',
							'default'     => false,
						),
					),
				),

				// GENERAL STYLE (BACKGROUND MAIL COLOR,FONT FAMILY...).
				array(
					'id'          => 'main_bg_color',
					'label'       => __( 'Select main background color', 'einvoicing-for-woocommerce' ),
					'description' => __( 'Choose a color that forms the backdrop of your invoice for a cohesive brand look.', 'einvoicing-for-woocommerce' ),
					'section'     => 'general',
					'control'     => 'color',
					'selectors'   => '.container',
					'default'     => $this->get_defaults( 'main_bg_color' ),
				),
				array(
					'id'          => 'main_font_family',
					'label'       => __( 'Select the main font family', 'einvoicing-for-woocommerce' ),
					'description' => __( 'Pick a font that reflects your brand\'s style and ensures legibility for all readers.', 'einvoicing-for-woocommerce' ),
					'section'     => 'general',
					'type'        => 'select',
					'selectors'   => 'body',
					'choices'     => self::get_font_family(),
					'default'     => $this->get_defaults( 'main_font_family' ),
				),
				array(
					'id'          => 'main_font_size',
					'label'       => __( 'Adjust main font size', 'einvoicing-for-woocommerce' ),
					'description' => __( 'Set the size of your text to balance readability with the efficient use of space.', 'einvoicing-for-woocommerce' ),
					'section'     => 'general',
					'type'        => 'range',
					'input_attrs' => array(
						'step' => 1,
						'min'  => 8,
						'max'  => 70,
					),
					'default'     => $this->get_defaults( 'main_font_size' ),
				),

				array(
					'id'          => 'main_font_color',
					'label'       => __( 'Select main text color', 'einvoicing-for-woocommerce' ),
					'description' => __( 'Choose the primary color for your invoice text to ensure good visibility and a professional look.', 'einvoicing-for-woocommerce' ),
					'section'     => 'general',
					'type'        => 'color',
					'default'     => $this->get_defaults( 'main_font_color' ),
				),

				array(
					'id'          => 'title_color',
					'label'       => __( 'Select title color', 'einvoicing-for-woocommerce' ),
					'description' => __( 'Set a distinct color for titles to draw attention to section headings and organize content.', 'einvoicing-for-woocommerce' ),
					'section'     => 'general',
					'control'     => 'color',
					'selectors'   => '.fx-invoice-title',
					'default'     => $this->get_defaults( 'title_color' ),
				),

				array(
					'id'          => 'fx_lr_padding',
					'label'       => __( 'Adjust left & right padding', 'einvoicing-for-woocommerce' ),
					'description' => __( 'Modify the left and right padding to control the whitespace around your invoice content, creating a more readable and aesthetically pleasing layout.', 'einvoicing-for-woocommerce' ),
					'section'     => 'general',
					'type'        => 'range',
					'default'     => $this->get_defaults( 'fx_lr_padding' ),
				),
				// INVOICE HEADER SECTION ( LOGO ).
				array(
					'id'          => 'logo',
					'label'       => __( 'Choose your logo', 'einvoicing-for-woocommerce' ),
					'description' => __( 'Upload your company logo for brand recognition.', 'einvoicing-for-woocommerce' ),
					'section'     => 'header_invoice',
					'control'     => 'image',
					'selectors'   => '#invoice_logo',
					'default'     => $this->get_defaults( 'logo' ),
				),
				array(
					'id'          => 'logo_width',
					'label'       => __( 'Set logo width (px)', 'einvoicing-for-woocommerce' ),
					'description' => __( 'Adjust the width of your logo to ensure it\'s clearly visible without overpowering the header content.', 'einvoicing-for-woocommerce' ),
					'section'     => 'header_invoice',
					'type'        => 'range',
					'default'     => $this->get_defaults( 'logo_width' ),
					'input_attrs' => array(
						'min' => 90,
						'max' => 300,
					),
				),

				array(
					'id'          => 'head_bg_color',
					'label'       => __( 'Select header background color', 'einvoicing-for-woocommerce' ),
					'description' => __( 'Select a color for the header that complements your logo and enhances the header text.', 'einvoicing-for-woocommerce' ),
					'section'     => 'header_invoice',
					'control'     => 'color',
					'selectors'   => '.fx-invoice-head',
					'default'     => $this->get_defaults( 'head_bg_color' ),
				),
				array(
					'id'          => 'head_txt_color',
					'label'       => __( 'Select header text color', 'einvoicing-for-woocommerce' ),
					'description' => __( 'Choose a text color that provides sufficient contrast against the header background for easy reading.', 'einvoicing-for-woocommerce' ),
					'section'     => 'header_invoice',
					'control'     => 'color',

					'selectors'   => '.fx-invoice-shop-info',
					'default'     => $this->get_defaults( 'head_txt_color' ),
				),
				array(
					'id'          => 'head_tb_padding',
					'label'       => __( 'Adjust top & bottom padding', 'einvoicing-for-woocommerce' ),
					'description' => __( 'Modify the vertical spacing within the header to fit your design and content needs.', 'einvoicing-for-woocommerce' ),
					'section'     => 'header_invoice',
					'type'        => 'range',
					'default'     => $this->get_defaults( 'head_tb_padding' ),
					'selectors'   => '.fx-invoice-head .fx-container',
				),

				array(
					'id'         => 'order_detail',
					'label'      => __( 'Setup order detail', 'einvoicing-for-woocommerce' ),
					'section'    => 'order_detail',
					'default'    => $order_detail,
					'selectors'  => 'table.order-details',
					'components' => array(
						'border_color'         => array(
							'label'       => __( 'Select border color', 'einvoicing-for-woocommerce' ),
							'description' => __( 'Choose a border color that complements your invoice\'s color scheme for a cohesive look.', 'einvoicing-for-woocommerce' ),
							'control'     => 'color',
							'default'     => $order_detail['border_color'],

						),
						'border_size'          => array(
							'label'       => __( 'Adjust border size', 'einvoicing-for-woocommerce' ),
							'description' => __( 'Control the thickness of the table\'s borders to enhance or soften the grid lines.', 'einvoicing-for-woocommerce' ),
							'type'        => 'range',
							'input_attrs' => array(
								'step' => 0.5,
								'min'  => 0,
								'max'  => 10,
							),
							'default'     => $order_detail['border_size'],
						),

						'top_bottom_margin'    => array(
							'label'       => __( 'Adjust table top & bottom margins', 'einvoicing-for-woocommerce' ),
							'description' => __( 'Increase or decrease the space above and below your table to fit the content into your page layout optimally.', 'einvoicing-for-woocommerce' ),
							'type'        => 'range',
							'input_attrs' => array(
								'step' => 0.5,
								'min'  => 0,
								'max'  => 60,
							),
							'default'     => $order_detail['top_bottom_margin'],
						),

						'table_head_bg'        => array(
							'label'       => __( 'Select table header background', 'einvoicing-for-woocommerce' ),
							'description' => __( 'Select a background color for the table headers to distinguish them from the data rows.', 'einvoicing-for-woocommerce' ),
							'control'     => 'color',
							'selectors'   => 'table.order-details th',
							'default'     => $order_detail['table_head_bg'],
						),

						'table_head_txt_color' => array(
							'label'       => __( 'Select table header text color', 'einvoicing-for-woocommerce' ),
							'description' => __( 'Pick a text color for the header that ensures clear readability and stands out against the background.', 'einvoicing-for-woocommerce' ),
							'control'     => 'color',
							'default'     => $order_detail['table_head_txt_color'],
						),
						'th_padding'           => array(
							'label'       => __( 'Adjust header padding', 'einvoicing-for-woocommerce' ),
							'description' => __( 'Modify the space within the header cells to make your titles more prominent or to fit more text.', 'einvoicing-for-woocommerce' ),
							'type'        => 'range',
							'input_attrs' => array(
								'step' => 1,
								'min'  => 4,
								'max'  => 30,
							),
							'default'     => $order_detail['th_padding'],
						),

						'th_align'             => array(
							'label'       => __( 'Set header alignment', 'einvoicing-for-woocommerce' ),
							'description' => __( 'Align your header text to the left, right, or center to match your table\'s formatting style.', 'einvoicing-for-woocommerce' ),
							'type'        => 'select',
							'choices'     => self::get_text_align(),
							'default'     => $order_detail['th_align'],
						),

						'cell_padding'         => array(
							'label'       => __( 'Adjust cell padding', 'einvoicing-for-woocommerce' ),
							'description' => __( 'Fine-tune the space within each cell to improve content readability and focus.', 'einvoicing-for-woocommerce' ),
							'type'        => 'range',
							'input_attrs' => array(
								'step' => 1,
								'min'  => 4,
								'max'  => 30,
							),
							'default'     => $order_detail['cell_padding'],
						),

						'td_align'             => array(
							'label'       => __( 'Set cell alignment', 'einvoicing-for-woocommerce' ),
							'description' => __( 'Align the content within your cells to the left, right, center, or justify to ensure a neat and organized appearance.', 'einvoicing-for-woocommerce' ),
							'type'        => 'select',
							'choices'     => self::get_text_align(),
							'default'     => $order_detail['td_align'],
						),

						'odd_row_bg_color'     => array(
							'label'       => __( 'Select Zebra-striped row (Odd)', 'einvoicing-for-woocommerce' ),
							'description' => __( 'Choose color for odd rows to increase readability.', 'einvoicing-for-woocommerce' ),
							'control'     => 'color',
							'default'     => $order_detail['odd_row_bg_color'],
						),

						'even_row_bg_color'    => array(
							'label'       => __( 'Select Zebra-striped row (Even)', 'einvoicing-for-woocommerce' ),
							'description' => __( 'Choose color for even rows to increase readability.', 'einvoicing-for-woocommerce' ),
							'control'     => 'color',
							'default'     => $order_detail['even_row_bg_color'],
						),
					),
				),

				// INVOICE DETAILS SECTION.

				array(
					'id'          => 'details_invoice_bg',
					'label'       => __( 'Select background color', 'einvoicing-for-woocommerce' ),
					'description' => __( 'Choose a background color for the invoice details area to create visual distinction from other sections.', 'einvoicing-for-woocommerce' ),
					'section'     => 'details_invoice',
					'control'     => 'color',
					'selectors'   => '.fx-invoice-shipping',
					'default'     => $this->get_defaults( 'details_invoice_bg' ),
				),
				array(
					'id'          => 'details_invoice_txt',
					'label'       => __( 'Select text color', 'einvoicing-for-woocommerce' ),
					'description' => __( 'Pick a color for the main body of text that ensures easy reading against the background.', 'einvoicing-for-woocommerce' ),
					'section'     => 'details_invoice',
					'control'     => 'color',

					'selectors'   => '.fx-invoice-shipping',
					'default'     => $this->get_defaults( 'details_invoice_txt' ),
				),
				array(
					'id'          => 'details_invoice_title',
					'label'       => __( 'Select title color', 'einvoicing-for-woocommerce' ),
					'description' => __( 'Set a distinct color for the detail section titles to separate them from other text and enhance hierarchy.', 'einvoicing-for-woocommerce' ),
					'section'     => 'details_invoice',
					'control'     => 'color',

					'selectors'   => '.fx-invoice-shipping',
					'default'     => $this->get_defaults( 'details_invoice_title' ),
				),
				array(
					'id'          => 'details_tb_padding',
					'label'       => __( 'Adjust top & bottom padding', 'einvoicing-for-woocommerce' ),
					'description' => __( 'Tweak the vertical space above and below your invoice details to improve layout and readability.', 'einvoicing-for-woocommerce' ),
					'section'     => 'details_invoice',
					'type'        => 'range',
					'default'     => $this->get_defaults( 'details_tb_padding' ),
				),

				// CUSTOMER NOTES SECTION.
				array(
					'id'          => 'customer_note_title',
					'label'       => __( 'Select title color', 'einvoicing-for-woocommerce' ),
					'description' => __( 'Pick a color for the note section\'s title to highlight it or to match your brand\'s theme.', 'einvoicing-for-woocommerce' ),
					'section'     => 'customer_notes_section',
					'control'     => 'color',
					'default'     => $this->get_defaults( 'customer_note_title' ),
				),
				array(
					'id'          => 'customer_note_txt',
					'label'       => __( 'Select text color', 'einvoicing-for-woocommerce' ),
					'description' => __( 'Choose a color for the note\'s body text that is readable on the chosen background for customer clarity.', 'einvoicing-for-woocommerce' ),
					'section'     => 'customer_notes_section',
					'control'     => 'color',
					'default'     => $this->get_defaults( 'customer_note_txt' ),
				),

				// FOOTER.
				array(
					'id'          => 'footer',
					'selectors'   => '#footer_text',
					'label'       => __( 'Type your footer message', 'einvoicing-for-woocommerce' ),
					'description' => __( 'Enter any legal information, notes of thanks, or company details here.', 'einvoicing-for-woocommerce' ),
					'section'     => 'footer_section',
					'type'        => 'textarea',
					'default'     => $this->get_defaults( 'footer' ),
				),
				array(
					'id'          => 'footer_bg_color',
					'label'       => __( 'Select footer background color', 'einvoicing-for-woocommerce' ),
					'description' => __( 'Choose a background color that complements the header and provides a visual anchor for your invoice.', 'einvoicing-for-woocommerce' ),
					'section'     => 'general',
					'control'     => 'color',
					'section'     => 'footer_section',
					'selectors'   => '.fx-footer .fx-container',
					'default'     => $this->get_defaults( 'main_bg_color' ),
				),
				array(
					'id'          => 'footer_txt_color',
					'label'       => __( 'Select footer text color', 'einvoicing-for-woocommerce' ),
					'description' => __( 'Select a text color that ensures readability against the footer background.', 'einvoicing-for-woocommerce' ),
					'section'     => 'footer_section',
					'selectors'   => '.fx-footer',
					'control'     => 'color',
					'default'     => $this->get_defaults( 'footer_txt_color' ),
				),
				array(
					'id'          => 'footer_txt_size',
					'label'       => __( 'Select font size', 'einvoicing-for-woocommerce' ),
					'description' => __( 'Set the size of your footer text to balance readability. (Default size is 12px)', 'einvoicing-for-woocommerce' ),
					'section'     => 'footer_section',
					'selectors'   => '.fx-footer',
					'type'        => 'range',
					'default'     => $this->get_defaults( 'footer_txt_size' ),
				),
			);
		}
		return array( $settings, $sections );
	}
}

/**
 * Inits the customizer when woo_einvoicing is ready
 */
add_action(
	'wooei_ready',
	function () {
		Invoice_Customizer::get_instance();
	}
);
