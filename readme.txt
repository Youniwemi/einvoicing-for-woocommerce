=== E-Invoicing For WooCommerce ===
Contributors: rahal.aboulfeth
Tags: e-invoicing, factur-x, UBL, invoice, WooCommerce
Requires at least: 5.0
Tested up to: 6.7.1
Requires PHP: 8.1
License: GPL
Stable tag: 0.3.4

Easily Customize WooCommerce PDF invoices and comply with Factur-X, UBL, and other e-invoicing standards.

== Description ==

=== WooCommerce E-Invoicing Customizer ===

Customize your WooCommerce PDF invoices and enable FacturX, UBL, ZUGFeRD, and XRechnung formats to comply with electronic invoicing regulations while customizing your invoices to reflect your brand.

== Features ==

- **PDF Invoice Customization:** Elevate your PDF invoices to match your brand identity with our native Invoice Designer, using the official wordpress customizer.
- **Factur-X, UBL, ZUGFeRD and Xrechnung Formats:** Enable compliance with the latest e-invoicing regulations.
- **Automatically attach invoice** PDF or Factur-X to WooCommerce emails of your choice.
- **Effortless Integration:** Integrates with your existing WooCommerce setup.
- **Simple Invoicing:** Simplify invoicing processes for a more efficient store management.

== Requirements ==

- WordPress 5.0 or higher.
- PHP 8.1 or higher.
- PHP Extensions: GD, mbstring, dom, and iconv enabled. ZipArchive to allow zip creation.

== Installation ==
1. Upload the plugin to your plugins folder: 'wp-content/plugins/'
2. Activate the 'E-Invoicing For Woocommerce' plugin from the Plugins admin panel.
3. Customize your installation in the Woocommerce "Settings" Page in the E-Invoicing Tab.
4. Enjoy.

== Usage ==

1. Go to **WooCommerce > Settings > E-Invoicing settings** to access the plugin settings.
2. Customize your PDF invoices according to your preferences.
3. Enable Factur-X, UBL, ZUGFeRD or Xrechnung einvoice formats for compliance with your e-invoicing regulations.

== Support and Compatibility ==

E-Invoicing for WooCommerce Invoice Designer leverages the native WordPress Customizer, ensuring a user-friendly and familiar experience. We strive for extensive compatibility across installations, but understand that occasional issues may arise. Should you encounter any incompatibilities with your setup, please reach out without hesitation. Our team is committed to promptly addressing and resolving any concerns to enhance your overall experience.


== Screenshots ==
1. Select E-invoice Format ( FacturX, UBL versions)
2. PDF Invoice Customizer - available sections
3. PDF Invoice Designer - Header
4. PDF Invoice Designer - Footer
5. Invoice numbering setup


== Credits ==
Big Thanks to : 
- [Youniwemi](https://packagist.org/packages/youniwemi/digital-invoice) for Digital Invoice - Easy wrapper around easybill/zugferd-php, atgp/factur-x and josemmo/einvoicing  that will allow you generate Factur-x and UBL in a very easy way.

== Changelog ==
= 0.3.4 : Bug fix when changing invoice number
= 0.3.3 : Bug fix when no taxation is setup
* Avoid deprecated error caused by translation loaded too early
* If invoice fails to generate for any reason, notify the admin by email
= 0.3.2 : Updated digital-invoice dependency
= 0.3.1 : Updated digital-invoice dependency
* Better german translation
= 0.3.0 : Added sequential invoice numbering
* Added settings section to configure numbering strategy, format, override last number.
* On change status change to processing or completed, the invoice number is applied.
= 0.2.9 : Better compatibility with different themes and plugins
* Fixed a fatal error that occurred in some cases if the theme or installed plugins were interacting with the native customizer.
* Fixed the settings link to redirect to E-Invoice tab instead of WooCommerce.
= 0.2.8 : Fix Fatal error when order has no modified date, shows today's date.
= 0.2.7 : Happy new year and Thank you all for your support. 
* Updated dependency digital-invoice dependency
* Pdf Invoice : Show date_modified when order is not paid
= 0.2.6 : Fix bad escaping in delivery address. 
= 0.2.5 : PDF Invoice Enhancements and Compatibility Fixes
* PDF Invoice now displays extensive billing and shipping information (company name, address line 1, address line 2, city, postal code, state, country)
* Fixed compatibility with OceanWP theme
* Updated WordPress and WooCommerce compatibility to latest versions
= 0.2.4 : Welcome to the spanish translation
* Added the possiblity to set a company name different than the shop name.
* Added spanish translation
= 0.2.3 : Fix fatal error after the plugin upgrade.
= 0.2.2 : Minor improvements
* Updated tested Wordpress up to version.
* Added option to support adding phone number and email to the invoice.
= 0.2.1 : Important critical fixes
* Fixed Fatal error while attaching invoice to email.
* Fixed refresh preview.
* This version adds the possibility to view the project changes.
= 0.2.0 : Bulk invoices download
* This version adds the possibility to download multiple e-invoices in a Zip package from the WooCommerce orders list table.
* After each upgrade, an admin notice will display the changes since the previously installed version.
= 0.1.9 : Minor Improvements
* Prices in the invoice table now utilize WooCommerce formatting for consistency.
* Corrected an issue where the footer was not properly aligned in the Preview mode.
= 0.1.8 : Fix translation loading and added WooCommerce as a plugin dependency
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


== Upgrade Notice ==
= 0.2.1 =
This version fixes a Fatal Error, should be upgraded as soon as possible.
= 0.2 =
This version adds the possibility to download multiple e-invoices in a Zip package from the WooCommerce orders list table.