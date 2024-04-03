<?php
/**
 * This file implements some compatibility changes to ensure customizer is working correctly.
 * No namespace is declared here
 *
 * @package WOOEI
 */

/**
 * Theme Astra Disable. Astra theme customizer loads a lot of js and css that causes incorrect rendering, the aim of this override is to cancel its behavior.
 * To do this we can't use our namespace in this file, and this file must be included only when we are customizing the invoice.
 * Thank god Astra tests if the class exist before instanciating Astra_Customizer.
 */
class Astra_Customizer {
	/**
	 * Initiator
	 * Do nothing please.
	 */
	public static function get_instance() {}
}
