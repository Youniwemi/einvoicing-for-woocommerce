<?php
/**
 * This file implements some compatibility changes to ensure customizer is working correctly with Blocksy theme.
 *
 * @package WOOEI
 */

namespace WOOEI;

// Only run when Blocksy theme is active.
add_action(
	'after_setup_theme',
	function() {
		// Check if Blocksy theme is active.
		if ( ! class_exists( 'Blocksy_Manager', false ) ) {
			return;
		}

		// Prevent conflicts with Blocksy's customizer scripts.
		remove_action( 'customize_controls_print_footer_scripts', array( '_WP_Editors', 'force_uncompressed_tinymce' ), 1 );
		remove_action( 'customize_controls_print_footer_scripts', array( '_WP_Editors', 'print_default_editor_scripts' ), 45 );
	},
	1
);

