<?php
/**
 * This file implements class customizer helper, a helper to  setup WordPress customizer
 *
 * @package WOOEI
 */

namespace WOOEI;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

use WP_Customize_Manager;

/**
 * WordPress Customizer helper
 */
abstract class Customizer_Helper {



	/**
	 * Defaults
	 *
	 * @var        array
	 */
	protected $defaults = array();
	/**
	 * Instances
	 *
	 * @var        array
	 */
	private static $instances = array();
	/**
	 * Settings option
	 *
	 * @var        string
	 */
	protected static $settings_option = 'customizer';

	/**
	 * Main panel name
	 *
	 * @var        string|bool
	 */
	protected $main_panel = 'Main Panel';

	/**
	 * Min capability
	 *
	 * @var        string
	 */
	protected $min_capability = 'manage_options';

	/**
	 * If we are in custimization mode
	 *
	 * @var        bool
	 */
	protected $is_customizing = false;

	/**
	 * If we are in preview mode
	 *
	 * @var        bool
	 */
	protected $is_previewing = false;


	/**
	 * Constructs a new instance.
	 */
	protected function __construct() {
		/**
		 * Setup customizer, settings need to be declared, so ajax saving (publish) would work
		 * we need to remove all customizations, so we set you customizer as first
		 * We use priority one so our customizer will kick right after widget customizer
		 */
		add_action( 'customize_register', array( $this, 'setup_customizer' ), 0 );
		if ( $this->is_preview_or_customization() ) {
			// Ensure we can selectively refresh widgets.
			add_action( 'customize_preview_init', array( $this, 'customize_preview_init' ), 0 );
		}
	}

	/**
	 * Hides the site editor notice.
	 */
	private function hide_site_editor_notice() {
		add_action(
			'customize_controls_print_styles',
			function() {
				echo '<style>#customize-notifications-area:has([data-code=site_editor_block_theme_notice]){display:none !important;}</style>';
			}
		);
	}

	/**
	 * Gets the instance.
	 *
	 * @return     Customizer_Helper  The instance.
	 */
	public static function get_instance() {
		$class = get_called_class();
		if ( ! isset( self::$instances[ $class ] ) ) {
			self::$instances[ $class ] = new $class();
		}

		return self::$instances[ $class ];
	}

	/**
	 * Gets all the default configuration values, or a specific value if a key is requested
	 *
	 * @param      string $key    The key.
	 *
	 * @return     mixed  The defaults.
	 */
	public function get_defaults( ?string $key = null ) {
		return isset( $key ) ? $this->defaults[ $key ] : $this->defaults;
	}

	/**
	 * Gets all the settings, or a specific value if a key is requested
	 * Defaults to default values
	 *
	 * @param      string $key    The key.
	 *
	 * @return     mixed  The settings.
	 */
	public function get_settings( ?string $key = null ) {
		static $settings;
		if ( null === $settings ) {
			$saved_settings   = get_option( static::$settings_option, array() );
			$default_settings = $this->get_defaults();

			// Deep merge arrays to handle nested settings like 'order_detail'.
			$settings = array_replace_recursive( $default_settings, $saved_settings );
		}
		if ( isset( $key ) ) {
			return isset( $settings[ $key ] ) ? $settings[ $key ] : null;
		}

		return $settings;
	}



	/**
	 * Gets the control class.
	 *
	 * @param      ?string $control  The control.
	 *
	 * @return     bool The control class name.
	 */
	public function get_control_class( ?string $control = null ) {
		if ( $control && class_exists( $control ) ) {
			return $control;
		}
		return '\WP_Customize_' . ( $control ? ucfirst( $control ) . '_' : '' ) . 'Control';
	}

	/**
	 * Prepares for the customize preview
	 *
	 * @param      WP_Customize_Manager $wp_customize  The wp customize.
	 */
	public function customize_preview_init( WP_Customize_Manager $wp_customize ) {
		// For the customizer preview, we won't inject wp_head and wp_footer as it will load more scripts and styles than we can handle.
		// So we need to "make" our own customizer_head and customizer_footer to ensure necessary customizer scripts are loaded and styles.
		add_action(
			static::$settings_option . '_customizer_header',
			function () use ( $wp_customize ) {
				wp_print_styles( array( 'customize-preview', 'wp-block-library' ) );
				$wp_customize->customize_preview_loading_style();
				$wp_customize->remove_frameless_preview_messenger_channel();
			}
		);

		add_action(
			static::$settings_option . '_customizer_footer',
			function () use ( $wp_customize ) {
				wp_print_scripts(
					array(
						'customize-base',
						'customize-preview',
						'customize-selective-refresh',
					)
				);

				$wp_customize->customize_preview_settings();
				$wp_customize->selective_refresh->export_preview_data();
			}
		);

		self::remove_hooks_except(
			'customize_preview_init',
			array(
				array( 'WP_Customize_Selective_Refresh', 'init_preview' ),
			)
		);
	}



	/**
	 * Sets up the customizer
	 *
	 * @param      WP_Customize_Manager $wp_customize  The wp customize.
	 */
	public function setup_customizer( WP_Customize_Manager $wp_customize ) {
		list( $settings, $sections ) = $this->get_customizer_settings();
		// Add settings first.
		$option = static::$settings_option;
		foreach ( $settings as $config ) {
			// phpcs:ignore Squiz.PHP.DisallowMultipleAssignments.Found -- Intentional multiple assignment for variable initialization.
			$id = $default = $sanitize_callback = $control = $description = $transport = $input_attrs = $components = null;
			// phpcs:ignore WordPress.PHP.DontExtract.extract_extract --  Using extract() as $config is controlled and its structure is known and safe.
			extract( $config );
			if ( ! $components ) {
				$setting_id = $option . "[$id]";
				$wp_customize->add_setting(
					$setting_id,
					array(
						'type'              => 'option',
						'transport'         => isset( $transport ) ? $transport : 'refresh',
						'capability'        => $this->min_capability,
						'default'           => isset( $default ) ? $default : '',
						'sanitize_callback' => isset( $sanitize_callback ) ?
						array( $this->get_control_class( $control ), $sanitize_callback ) :
						'',
					)
				);
			} else {
				foreach ( $components as $sub => $sub_config ) {
					$sub_id = $option . "[$id][$sub]";
					// phpcs:ignore Squiz.PHP.DisallowMultipleAssignments.Found -- Intentional multiple assignment for variable initialization.
					$sanitize_callback = $default = $transport = null;
					// phpcs:ignore WordPress.PHP.DontExtract.extract_extract --  Using extract() as $sub_config is controlled and its structure is known and safe.
					extract( $sub_config );
					$wp_customize->add_setting(
						$sub_id,
						array(
							'type'              => 'option',
							'transport'         => isset( $transport ) ? $transport : 'refresh',
							'capability'        => $this->min_capability,
							'default'           => isset( $default ) ? $default : '',
							'sanitize_callback' => isset( $sanitize_callback ) ?
							array( $this->get_control_class( $control ), $sanitize_callback ) :
							'',
						)
					);
				}
			}
		}

		// only in preview and email customization.
		if ( ! $this->is_preview_or_customization() ) {
			return;
		}

		$this->init_controls();

		// We remove all the registered hooks.
		self::remove_hooks_except(
			'customize_register',
			array(
				array( 'WP_Customize_Manager', 'register_dynamic_settings' ),
			)
		);

		if ( $this->main_panel ) {
			$panel_id = $this->settings_option . '-panel';
			$wp_customize->add_panel(
				'invoice_customizer-panel',
				array(
					'title'      => $this->main_panel,
					'capability' => $this->min_capability,
				)
			);
		} else {
			$panel_id = null;
		}

		foreach ( $sections as $id => $section ) {
			$section_id = $option . '-' . $id;
			$wp_customize->add_section(
				$section_id,
				array(
					'title'      => $section,
					'capability' => $this->min_capability,
					'panel'      => $panel_id,
				)
			);
		}

		foreach ( $settings as $config ) {
			// phpcs:ignore Squiz.PHP.DisallowMultipleAssignments.Found -- Intentional multiple assignment for variable initialization.
			$id = $label = $section = $control = $type = $description = $choices = $input_attrs = $transport = $selectors = $render_callback = $components = null;
			// phpcs:ignore WordPress.PHP.DontExtract.extract_extract --  Using extract() as $config is controlled and its structure is known and safe.
			extract( $config );
			$control    = $this->get_control_class( $control );
			$setting_id = $option . "[$id]";
			$section_id = $option . '-' . $section;

			if ( ! $components ) {
				$wp_customize->add_control(
					new $control(
						$wp_customize,
						$setting_id,
						array(
							'settings'        => $setting_id,
							'label'           => $label,
							'type'            => $type,
							'active_callback' => '__return_true',
							'description'     => $description,
							'choices'         => $choices,
							'section'         => $section_id,
							'input_attrs'     => $input_attrs,
						)
					)
				);

				if ( $wp_customize->selective_refresh && $selectors ) {
					$wp_customize->selective_refresh->add_partial(
						$option . "[$id]",
						array(
							'selector'        => $selectors,
							'render_callback' => $render_callback,
						)
					);
				}
			} else {
				foreach ( $components as $sub => $sub_config ) {
					$sub_id = $option . "[$id][$sub]";
					// phpcs:ignore Squiz.PHP.DisallowMultipleAssignments.Found -- Intentional multiple assignment for variable initialization.
					$label = $type = $description = $sanitize_callback = $choices = $default = $transport = $control = $selectors = null;
					// phpcs:ignore WordPress.PHP.DontExtract.extract_extract --  Using extract() as $sub_config is controlled and its structure is known and safe.
					extract( $sub_config );
					$control = $this->get_control_class( $control );
					$wp_customize->add_control(
						new $control(
							$wp_customize,
							$sub_id,
							array(
								'settings'        => $sub_id,
								'label'           => $label,
								'type'            => $type,
								'active_callback' => '__return_true',
								'description'     => $description,
								'choices'         => $choices,
								'section'         => $section_id,
								'input_attrs'     => $input_attrs,
							)
						)
					);
					if ( $wp_customize->selective_refresh && $selectors ) {
						$wp_customize->selective_refresh->add_partial(
							$sub_id,
							array(
								'selector'        => $selectors,
								'render_callback' => $render_callback,
							)
						);
					}
				}
			}
		}

		// We remove all the registered hooks (except default WP mandatory ones).
		self::remove_hooks_except(
			'customize_controls_print_styles',
			array( 'wp_resource_hints' )
		);

		self::remove_hooks_except(
			'customize_controls_print_scripts',
			array()
		);

		self::remove_hooks_except(
			'customize_controls_enqueue_scripts',
			array(
				'wp_plupload_default_settings',
				// all hooks added by WP_Customize_Manager.
				'WP_Customize_Manager',
			)
		);

		if ( $this->is_customizing ) {
			$this->hide_site_editor_notice();
		}
	}


	/**
	 * Removes all hooks from action except...
	 *
	 * @param      string $action  The action.
	 * @param      array  $except  The except.
	 */
	protected function remove_hooks_except( string $action, array $except ) {
		global $wp_filter;

		foreach ( $wp_filter[ $action ]->callbacks as $priority => $callbacks ) {
			foreach ( $callbacks as $order => $callback ) {
				if ( is_array( $callback['function'] ) && is_object( $callback['function'][0] ) ) {
					$object = get_class( $callback['function'][0] );
					$method = $callback['function'][1];
					// check first for the method.
					if ( in_array( array( $object, $method ), $except, true ) ) {
						continue;
					}
					// check for the class alone.
					if ( in_array( $object, $except, true ) ) {
						continue;
					}
				} elseif ( is_callable( $callback['function'] ) ) {
					if ( in_array( $callback['function'], $except, true ) ) {
						continue;
					}
				}
				unset( $wp_filter[ $action ]->callbacks[ $priority ][ $order ] );
			}
		}
	}

	/**
	 * Gets the font family.
	 *
	 * @param      string $key    The key.
	 *
	 * @return     string|array  The font family.
	 */
	public static function get_font_family( ?string $key = null ) {
		static $font_families;
		if ( null === $font_families ) {
			/**
			 * Override Customizer font families.
			 *
			 * @since        0.0.4
			 */
			$font_families = apply_filters(
				'customizer_font_families',
				array(
					'helvetica'   => '"Helvetica Neue", Helvetica, Roboto, Arial, sans-serif',
					'arial'       => 'Arial, Helvetica, sans-serif',
					'arial_black' => '"Arial Black", Gadget, sans-serif',
					'courier'     => '"Courier New", Courier, monospace',
					'impact'      => 'Impact, Charcoal, sans-serif',
					'lucida'      => '"Lucida Sans Unicode", "Lucida Grande", sans-serif',
					'palatino'    => '"Palatino Linotype", "Book Antiqua", Palatino, serif',
					'georgia'     => 'Georgia, serif',
				)
			);
		}
		if ( $key ) {
			return $font_families[ $key ];
		}
		return $font_families;
	}


	/**
	 * Gets the font transforms.
	 *
	 * @return     array   The font transforms.
	 */
	protected static function get_font_transforms() {
		static $font_transform;
		if ( null === $font_transform ) {
			$font_transform = array(
				'uppercase'  => __( 'Uppercase' ),
				'capitalize' => __( 'Capitalize' ),
				'lowercase'  => __( 'Lowercase' ),
				'none'       => __( 'None' ),
			);
		}

		return $font_transform;
	}

	/**
	 * Gets the text align.
	 *
	 * @return     array   The text align.
	 */
	protected static function get_text_align() {
		static $text_align;
		if ( null === $text_align ) {
			$text_align = array(
				'left'    => __( 'Left' ),
				'center'  => __( 'Center' ),
				'right'   => __( 'Right' ),
				'justify' => __( 'Justify' ),
			);
		}
		return $text_align;
	}


	/**
	 * Determines if preview or customization.
	 */
	public function is_preview_or_customization() {
		return $this->is_previewing || $this->is_customizing;
	}

	/**
	 * Gets the customizer settings.
	 */
	abstract public function get_customizer_settings();

	/**
	 * Initializes the controls.
	 */
	abstract public function init_controls();
}
