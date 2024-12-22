<?php
/**
 * This file implements some compatibility changes to ensure customizer is working correctly with oceanwp.
 *
 * @package WOOEI
 */

namespace WOOEI;

// Just don't let oceanwp load it's classes , this file is loaded on our invoice customizer context, so no worry.
add_action(
	'after_setup_theme',
	function() {
		// this one will avoid initializing wpocean customizer.
		remove_action( 'after_setup_theme', array( 'OCEANWP_Theme_Class', 'classes' ), 4 );
		remove_action( 'after_setup_theme', array( 'OCEANWP_Theme_Class', 'theme_setup' ), 10 );
	},
	1
);

