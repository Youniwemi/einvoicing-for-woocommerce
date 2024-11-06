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
	 * Gets the settings.
	 *
	 * @return     array  The settings.
	 */
	public function get_settings() {

		$settings = array(
			'section_title'          => array(
				'name' => __( 'Official Business Identification', 'einvoicing-for-woocommerce' ),
				'type' => 'title',
				'desc' => __( 'Provide your legally recognized business details for identification and verification.', 'einvoicing-for-woocommerce' ),
				'id'   => $this->id . '_settings_company',
			),
			'company_name'           => array(
				'name' => __( 'Company Name', 'einvoicing-for-woocommerce' ),
				'type' => 'text',
				'desc' => __( 'Enter the company name to show in your invoices.', 'einvoicing-for-woocommerce' ),
				'id'   => $this->id . '_company_name',
			),
			'id_company'             => array(
				'name' => __( 'Company Identification Number', 'einvoicing-for-woocommerce' ),
				'type' => 'text',
				'desc' => __( 'Enter the registration number issued by your Chamber of Commerce.', 'einvoicing-for-woocommerce' ),
				'id'   => $this->id . '_id_company',
			),
			'id_company_type'        => array(
				'name'    => __( 'Identification System', 'einvoicing-for-woocommerce' ),
				'type'    => 'select',
				'desc'    => __( 'Select the system associated with your company identification number.', 'einvoicing-for-woocommerce' ),
				'options' => Types::getInternationalCodes(),
				'id'      => $this->id . '_id_type',
			),
			'id_vat'                 => array(
				'name' => __( 'VAT Identification Number', 'einvoicing-for-woocommerce' ),
				'type' => 'text',
				'desc' => __( 'Provide the VAT number assigned to your business for tax purposes.', 'einvoicing-for-woocommerce' ),
				'id'   => $this->id . '_id_vat',
			),

			'email'                  => array(
				'name' => __( 'Business Email Address', 'einvoicing-for-woocommerce' ),
				'type' => 'email',
				'desc' => __( 'Enter the official email address for business correspondence and invoicing.', 'einvoicing-for-woocommerce' ),
				'id'   => $this->id . '_shop_email',
			),
			'phone'                  => array(
				'name' => __( 'Business Phone Number', 'einvoicing-for-woocommerce' ),
				'type' => 'text',
				'desc' => __( 'Provide the business phone number, including the country code.', 'einvoicing-for-woocommerce' ),
				'id'   => $this->id . '_shop_phone',
			),
			'section_end'            => array(
				'type' => 'sectionend',
				'id'   => $this->id . '_settings_company_end',
			),

			'section_type_invoicing' => array(
				'name' => __( 'E-Invoice Delivery Settings', 'einvoicing-for-woocommerce' ),
				'type' => 'title',
				'desc' => __( 'Choose the e-invoice format and the email attachement settings.', 'einvoicing-for-woocommerce' ),
				'id'   => $this->id . '_settings_invoicing',
			),

			'invoice_type'           => array(
				'name'    => __( 'Invoice Format', 'einvoicing-for-woocommerce' ),
				'type'    => 'select',
				'desc'    => __( 'Select the invoice format as per your country\'s regulations.', 'einvoicing-for-woocommerce' ),
				'options' => WOOEI_TYPES,
				'id'      => $this->id . '_invoice_type',
			),
		);
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
			'id'   => $this->id . '_settings_invoicing_end',

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

		return $settings;
	}
}

return new Settings();
