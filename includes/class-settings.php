<?php
/**
 * This file implements the settings tab
 *
 * @package WOOEI
 */

namespace WOOEI;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

use DigitalInvoice\Types;
use DigitalInvoice\InternationalCodeDesignator;
use WC_Settings_Page;
use WC_Admin_Settings;

/**
 * Plugin main settings tab page
 */
class Settings extends WC_Settings_Page {



	/**
	 * Tab Id
	 *
	 * @inheritdoc
	 * @var string
	 */
	protected $id = 'wooei';

	/**
	 * Constructor.
	 */
	public function __construct() {
		$this->label = __( 'E-Invoicing', 'einvoicing-for-woocommerce' );
		parent::__construct();

		add_action( 'woocommerce_settings_saved', array( $this, 'after_save' ) );
		add_action( 'woocommerce_update_options_wooei', array( $this, 'override_last_invoice_number' ) );
		// phpcs:ignore WordPress.Security.NonceVerification.Recommended, WordPress.Security.NonceVerification.Missing
		if ( empty( $_POST ) && isset( $_GET['configured'] ) && '0' === $_GET['configured'] ) {
			add_action(
				'admin_notices',
				function () {
					$message = __( 'Before customizing the visual aspect of your invoice, first make sure your settings are properly configured.', 'einvoicing-for-woocommerce' );
					printf( '<div class="notice notice-error"><p>%s</p></div>', esc_html( $message ) );
				}
			);
		}
	}

	/**
	 * Adds a message after settings are saved.
	 */
	public function after_save() {
		global $current_tab;
		if ( $this->id === $current_tab ) {
			$customized = get_option( $this->id . '_customizations', false );
			if ( false === $customized ) {
				$message = __( 'Consider checking the invoice customization to complete your setting.', 'einvoicing-for-woocommerce' );
				WC_Admin_Settings::add_message( $message );
			}
		}
	}

	/**
	 * Override the last_invoice_number
	 */
	public function override_last_invoice_number() {
		$override = get_option( $this->id . '_override_last_invoice_number', 0 );

		if ( $override > 0 ) {
			update_option( $this->id . '_last_invoice_number', $override );
			delete_option( $this->id . '_override_last_invoice_number' );
		}
	}

	/**
	 * Gets the settings.
	 *
	 * @return     array  The settings.
	 */
	public function get_settings() {

		// We allow only pdf if Identification System is not correctly setup.
		$id_type = (string) get_option( 'wooei_id_type', null );
		if ( $id_type ) {
			$invoice_types = WOOEI_TYPES;
			$einvoice_help = __( 'Select the invoice format as per your country\'s regulations.', 'einvoicing-for-woocommerce' ) . '<br/>' . __( 'If your country has not implemented e-invoicing yet, you should choose PDF.', 'einvoicing-for-woocommerce' );
		} else {
			$invoice_types = array( 'pdf' => 'Pdf' );
			$einvoice_help = __( 'To be able to activate e-invoice formats, you should first setup the Official Business Identification.', 'einvoicing-for-woocommerce' );
		}

		$settings = array(
			'section_title'             => array(
				'name' => __( 'Official Business Identification', 'einvoicing-for-woocommerce' ),
				'type' => 'title',
				'desc' => __( 'Provide your legally recognized business details for identification and verification.', 'einvoicing-for-woocommerce' ),
				'id'   => $this->id . '_settings_company',
			),
			'company_name'              => array(
				'name' => __( 'Company Name', 'einvoicing-for-woocommerce' ),
				'type' => 'text',
				'desc' => __( 'Enter the company name to show in your invoices.', 'einvoicing-for-woocommerce' ),
				'id'   => $this->id . '_company_name',
			),
			'id_company'                => array(
				'name' => __( 'Company Identification Number', 'einvoicing-for-woocommerce' ),
				'type' => 'text',
				'desc' => __( 'Enter the registration number issued by your Chamber of Commerce.', 'einvoicing-for-woocommerce' ),
				'id'   => $this->id . '_id_company',
			),
			'id_company_type'           => array(
				'name'    => __( 'Identification System', 'einvoicing-for-woocommerce' ),
				'type'    => 'select',
				'desc'    => __( 'This setting is mandatory for e-invoices.', 'einvoicing-for-woocommerce' ) . '<br/>' . __( 'The type of business identification depends on your country. For example, France uses SIREN and SIRET.', 'einvoicing-for-woocommerce' ) . '<br/>' .
				__( 'If your country has implemented e-invoicing, don\'t hesitate to contact us if you don\'t find your identification system.', 'einvoicing-for-woocommerce' ),
				'options' => array_merge(
					array( '' => __( 'Choose identification system based on your country', 'my-textdomain' ) ),
					Types::getInternationalCodes()
				),
				'id'      => $this->id . '_id_type',
			),
			'id_vat'                    => array(
				'name' => __( 'VAT Identification Number', 'einvoicing-for-woocommerce' ),
				'type' => 'text',
				'desc' => __( 'Provide the VAT number assigned to your business for tax purposes.', 'einvoicing-for-woocommerce' ),
				'id'   => $this->id . '_id_vat',
			),

			'email'                     => array(
				'name' => __( 'Business Email Address', 'einvoicing-for-woocommerce' ),
				'type' => 'email',
				'desc' => __( 'Enter the official email address for business correspondence and invoicing.', 'einvoicing-for-woocommerce' ),
				'id'   => $this->id . '_shop_email',
			),
			'phone'                     => array(
				'name' => __( 'Business Phone Number', 'einvoicing-for-woocommerce' ),
				'type' => 'text',
				'desc' => __( 'Provide the business phone number, including the country code.', 'einvoicing-for-woocommerce' ),
				'id'   => $this->id . '_shop_phone',
			),
			'section_end'               => array(
				'type' => 'sectionend',
				'id'   => $this->id . '_settings_company_end',
			),
		);

		// Allow other modules to add company identification settings
		$settings = apply_filters( 'wooei_settings_company_identification', $settings );

		$settings = array_merge( $settings, array(

			'section_numbering_title'   => array(
				'name' => __( 'Invoice numbering settings', 'einvoicing-for-woocommerce' ),
				'type' => 'title',
				'desc' => __( 'Configure your invoice numbering system', 'einvoicing-for-woocommerce' ),
				'id'   => $this->id . '_settings_numbering',
			),

			'numbering_strategy'        => array(
				'name'    => __( 'Numbering System', 'einvoicing-for-woocommerce' ),
				'type'    => 'select',
				'desc'    => __( 'Select how invoice numbers are generated', 'einvoicing-for-woocommerce' ),
				'options' => WOOEI_NUMBERING_STRATEGY,
				'default' => WOOEI_NUMBERING_ORDER,
				'id'      => $this->id . '_numbering_strategy',
			),

			'generate_pending_invoices' => array(
				'name'    => __( 'Generate invoice numbers for pending orders', 'einvoicing-for-woocommerce' ),
				'type'    => 'checkbox',
				'desc'    => __( 'Check to generate invoice numbers when orders are created in pending status', 'einvoicing-for-woocommerce' ),
				'default' => 'no',
				'id'      => $this->id . '_generate_pending_invoices',
			),

			'invoice_reset_number'      => array(
				'name'    => __( 'Reset invoice number frequency', 'einvoicing-for-woocommerce' ),
				'desc'    => __( 'Choose whether to restart numbering or not', 'einvoicing-for-woocommerce' ),
				'type'    => 'select',
				'options' => WOOEI_INVOICE_NUMBER_RESET,
				'default' => WOOEI_NUMBERING_NO_RESET,
				'id'      => $this->id . '_invoice_reset_number',
			),

			'invoice_number_padding'    => array(
				'name'    => __( 'Invoice number minimum digits', 'einvoicing-for-woocommerce' ),
				'type'    => 'number',
				'desc'    => __( 'Set minimum digits in invoice numbers (e.g., 4 digits: 0001, 0045)', 'einvoicing-for-woocommerce' ),
				'default' => 4,
				'id'      => $this->id . '_invoice_number_padding',
			),

			'invoice_number_format'     => array(
				'name'        => __( 'Invoice numbers format', 'einvoicing-for-woocommerce' ),
				'type'        => 'text',
				'desc'        => __( 'Define the format using {YEAR} and {NUMBER} placeholders', 'einvoicing-for-woocommerce' ),
				'default'     => 'INV/{YEAR}/{NUMBER}',
				'placeholder' => 'Ex: INV/{YEAR}/{NUMBER}',
				'id'          => $this->id . '_invoice_number_format',
			),

			'last_invoice_number'       => array(
				'name'              => __( 'Last invoice number', 'einvoicing-for-woocommerce' ),
				'type'              => 'number',
				'desc'              => __( 'Last invoice number in case you need to override it', 'einvoicing-for-woocommerce' ),
				'default'           => '',
				'id'                => $this->id . '_override_last_invoice_number',
				'custom_attributes' => array(
					'min'  => 0,
					'step' => 1,
				),
			),

			'invoice_filename_format'   => array(
				'name'        => __( 'Invoice Filename Format', 'einvoicing-for-woocommerce' ),
				'type'        => 'text',
				'desc'        => __( 'Define the filename format using {ORDER_ID}, {INVOICE_NUMBER}, {DATE}, {CLIENT} placeholders', 'einvoicing-for-woocommerce' ),
				'default'     => 'Invoice-{ORDER_ID}',
				'placeholder' => 'Ex: Invoice-{ORDER_ID} or INV-{INVOICE_NUMBER}-{DATE}',
				'id'          => $this->id . '_invoice_filename_format',
			),

			'section_numbering_end'     => array(
				'type' => 'sectionend',
				'id'   => $this->id . '_settings_numbering_end',
			),

			'section_type_invoicing'    => array(
				'name' => __( 'E-Invoice Delivery Settings', 'einvoicing-for-woocommerce' ),
				'type' => 'title',
				'desc' => __( 'Choose the e-invoice format and the email attachement settings.', 'einvoicing-for-woocommerce' ),
				'id'   => $this->id . '_settings_invoicing',
			),

			'invoice_type'              => array(
				'name'    => __( 'Invoice Format', 'einvoicing-for-woocommerce' ),
				'type'    => 'select',
				'desc'    => $einvoice_help,
				'options' => $invoice_types,
				'id'      => $this->id . '_invoice_type'
			),
		));

		$settings['attach_invoice'] = array(
			'title'         => __( 'Include Invoice as Attachment', 'einvoicing-for-woocommerce' ),
			'id'            => $this->id . '_invoice_attach_invoice',
			'desc'          => __( 'Check to attach invoices to emails automatically', 'einvoicing-for-woocommerce' ),
			'default'       => 'no',
			'type'          => 'checkbox',
			'checkboxgroup' => 'start',

		);

		foreach ( WOOEI_EMAIL_TYPES as $key => $title ) {
			$settings[ "attach_invoice_$key" ] = array(
				'desc'          => $title,
				'type'          => 'checkbox',
				'checkboxgroup' => array_key_last( WOOEI_EMAIL_TYPES ) === $key ? 'end' : '',
				'id'            => $this->id . "_invoice_attach[$key]",
				'autoload'      => false,
			);
		}

		$settings['section_type_invoicing_end'] = array(
			'type' => 'sectionend',
			'id'   => $this->id . '_settings_invoicing_end'
		);

		$settings['section_invoicing_customizer'] = array(
			'name' => __( 'Customize your invoice design', 'einvoicing-for-woocommerce' ),
			'type' => 'title',
			/* translators: %s: URL to Invoice customizer. */
			'desc' => sprintf( __( 'Ready to give your invoices a personalized touch? Use our customizer to tailor the visual aspects of your invoice. <a href="%s">Click here to visually customize your invoice</a>', 'einvoicing-for-woocommerce' ), Invoice_Customizer::customizer_link() ),
			'id'   => $this->id . '_customizer',
		);

		$settings['section_type_customizer_end'] = array(
			'type' => 'sectionend',
			'id'   => $this->id . '_customizer_end',
		);

		$changeset = 'admin.php?page=einvoicing-changes';
		$changeset = admin_url( $changeset );

		$settings['section_invoicing_misc'] = array(
			'name' => __( 'Miscellaneous', 'einvoicing-for-woocommerce' ),
			'type' => 'title',
			/* translators: %s: URL to Invoice customizer. */
			'desc' => sprintf( __( 'Discover the project <a href="%s">changesets</a>.', 'einvoicing-for-woocommerce' ), esc_url( $changeset ) ),
			'id'   => $this->id . '_customizer_misc',
		);

		$settings['section_invoicing_misc_end'] = array(
			'type' => 'sectionend',
			'id'   => $this->id . '_customizer_misc_end',
		);

		if ( ! has_invoice_numbering() ) {
			unset( $settings['invoice_reset_number'] );
			unset( $settings['invoice_number_padding'] );
			unset( $settings['invoice_number_format'] );
			unset( $settings['last_invoice_number'] );
		}

		return $settings;
	}
}

return new Settings();
