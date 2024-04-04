=== E-Invoicing For WooCommerce ===
Contributors: rahal.aboulfeth
Tags: e-invoicing, factur-x, UBL, invoices, WooCommerce
Requires at least: 5.0
Tested up to: 6.5
Requires PHP: 8.1
License: GPL
Stable tag: 0.1.7

Easily Customize WooCommerce PDF invoices and comply with Factur-X, UBL, and other e-invoicing standards.

== Description ==

=== WooCommerce E-Invoicing Customizer ===

Customize your WooCommerce PDF invoices and enable Factur-X, UBL, ZUGFeRD, and XRechnung formats to comply with electronic invoicing regulations while customizing your invoices to reflect your brand.

== Features ==

- **PDF Invoice Customization:** Elevate your PDF invoices to match your brand identity, using the official wordpress customizer.
- **Factur-X, UBL, ZUGFeRD and Xrechnung Formats:** Enable compliance with the latest e-invoicing regulations.
- **Automatically attach invoice** PDF or Factur-X to WooCommerce emails of your choice.
- **Effortless Integration:** Integrates with your existing WooCommerce setup.
- **Simple Invoicing:** Simplify invoicing processes for a more efficient store management.

== Requirements ==

- WordPress 5.0 or higher.
- PHP 8.1 or higher.
- PHP Extensions: GD, mbstring, dom, and iconv enabled.

== Installation ==
1. Upload the plugin to your plugins folder: 'wp-content/plugins/'
2. Activate the 'E-Invoicing For Woocommerce' plugin from the Plugins admin panel.
3. Customize your installation in the Woocommerce "Settings" Page in the  E-Invoicing Tab.
4. Enjoy.

== Usage ==

1. Go to **WooCommerce > Settings > E-Invoicing settings** to access the plugin settings.
2. Customize your PDF invoices according to your preferences.
3. Enable Factur-X, UBL, ZUGFeRD or Xrechnung formats for compliance with your e-invoicing regulations.

== Support and Compatibility ==

E-Invoicing for WooCommerce leverages the native WordPress Customizer, ensuring a user-friendly and familiar experience. We strive for extensive compatibility across installations, but understand that occasional issues may arise. Should you encounter any incompatibilities with your setup, please reach out without hesitation. Our team is committed to promptly addressing and resolving any concerns to enhance your overall experience.

== Credits ==
Big Thanks to : 
- [Youniwemi](https://packagist.org/packages/youniwemi/digital-invoice) for Digital Invoice - Easy wrapper around easybill/zugferd-php, atgp/factur-x and josemmo/einvoicing  that will allow you generate Factur-x and UBL in a very easy way.

== Changelog ==
= 0.1.7 : Fix arabic caracters in pdf invoice
= 0.1.6 : Fix adding invoice to emails as attachements
* Updated tested Wordpress up to version
* Fixed typos in readme
= 0.1.5 : Allow E-Invoices to be attached to more WooCommerce Emails
* Updated tested WooCommerce up to version
* Updated translations
= 0.1.4 : Applying Wordpress Plugin review team feedback
* Multiple sanitization and escaping fixes.
* Use WOOEI as unique prefix
= 0.1.3 : Minor style adjustments.
= 0.1.2 : Renamed slug to comply with the naming guidelines.
= 0.1.1 : Added PHP extensions (gd, mbstring, iconv and dom) verification before loading the plugin.
= 0.1.0 : Introduced support for the UBL format, expanding e-invoicing capabilities.
= 0.0.9 : Implemented role-based access control, allowing customization of minimum capabilities required for setting up the plugin.
= 0.0.8 : Resolved compatibility issues with the Astra WordPress theme.
= 0.0.7 : Minor fixes to improve the user experience.
= 0.0.6 : Confirmed compatibility with WooCommerce 8.4 =
* Declare compatibility with **High performance order tables**
= 0.0.5 : Improved the plugin onboarding experience =
* Nice instructive notices, better captions and labels.
= 0.0.1 : Initial release of E-Invoicing for WooCommerce =
* Initial release, PDF Invoice Customizer supporting Factur-X and ZUGFeRD formats, ensuring compliance with French and German e-invoicing standards.