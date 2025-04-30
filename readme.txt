=== E-Invoicing For WooCommerce ===
Contributors: rahal.aboulfeth
Tags: e-invoicing, factur-x, UBL, invoice, WooCommerce, pdf
Requires at least: 5.0
Tested up to: 6.8
Requires PHP: 8.1
License: GPL
Stable tag: 0.3.9

Easily Customize WooCommerce PDF invoices and comply with Factur-X, UBL, and other e-invoicing standards.

== Description ==

=== WooCommerce E-Invoicing Customizer ===

Customize your WooCommerce PDF invoices and enable FacturX, UBL, ZUGFeRD, and XRechnung formats to comply with electronic invoicing regulations while customizing your invoices to reflect your brand.

https://www.youtube.com/watch?v=0SWRxHRXnEM

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


== Frequently Asked Questions ==


= How do I customize my PDF invoice template? =

Unlike other plugins that require code editing or premium add-ons, we use the native WordPress Customizer for complete visual editing. You'll be able to customize to design your invoice with real-time preview. 

= Can I use my own company logo and details on invoices? =

You can easily add your logo, company details, VAT number, registration information, phone, email, and customize the entire header and footer sections.

= Can I view changes to my invoice design before saving? =

Yes, our integration with the WordPress Customizer allows you to see live preview changes before saving them. This makes designing your perfect invoice fast and stress-free.

= What is Factur-X and why do I need it? =

Factur-X is a hybrid e-invoicing format that combines a PDF invoice with embedded XML data. It's required for B2B transactions in France and increasingly across Europe. Our plugin fully supports this format, making your store compliant with French e-invoicing regulations.

= What is UBL format and why is it important? =

Universal Business Language (UBL) is an open international standard for e-invoicing. UBL support makes your invoices compatible with public sector requirements in many European countries and growing adoption worldwide. It future-proofs your business against evolving e-invoicing mandates.

= What is ZUGFeRD/XRechnung and do I need it for my business? =

ZUGFeRD is the German standard for electronic invoices, while XRechnung is required for invoicing to German public authorities. If you do business in Germany or with German companies, these formats are essential. Our plugin supports both, ensuring full compliance with German e-invoicing regulations.


= How does sequential invoice numbering work? =

Our sequential numbering system automatically generates unique, sequential invoice numbers when orders change to "processing" or "completed" status. You can customize the format, prefix, suffix, and starting number. The system maintains consistent numbering and can reset every year.

= Can I download multiple invoices at once? =

Yes! We have a bulk invoice download functionality. From the WooCommerce orders list, select multiple orders and download all invoices in a single ZIP package - saving you significant time compared to downloading individually.

= How do I attach invoices to WooCommerce emails? =

Our plugin lets you choose exactly which WooCommerce emails should include invoice attachments (order confirmation, processing, completed, etc.). Configure this in WooCommerce > Settings > E-Invoicing > Email Settings.

= What happens if invoice generation fails? =

If an invoice fails to generate for any reason, an admin email notification is sent automatically. The plugin also logs detailed information using WooCommerce's logging system, making troubleshooting straightforward.

This is not supposed to happen, but should you encounter any invoice generation issue, please reach out without hesitation.

= Does this plugin support multilingual stores? =

Yes! The plugin includes translations for multiple languages including French, German and Spanish.


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

2025-04-30 - version 0.3.9
* Improvement - Added the possibility to show Invoice Date instead of Order Date

2025-04-24 - version 0.3.8
* Fixed - Removed some warning and notices that appear in the customizer
* Improvement - Better Identification System labels
* Submission to WooCommerce Marketplace : implementing UX feedback 

2025-03-12 - version 0.3.7
* Fixed - Bug while generating e-invoice if the order contains a free product.

2025-03-05 - version 0.3.6
* Submission to WooCommerce Marketplace : Passing Qit Tests

2025-03-01 - version 0.3.5
* Use WooCommerce Logger to log invoice generation errors
* Invoice number should be generated when order status is processing or completed

2025-02-28 - version 0.3.4
* Bug fix when changing invoice number

2025-02-12 - version 0.3.3
* Bug fix when no taxation is setup
* Avoid deprecated error caused by translation loaded too early
* If invoice fails to generate for any reason, notify the admin by email

2025-02-11 - version 0.3.2
* Updated digital-invoice dependency

2025-02-08 - version 0.3.1
* Updated digital-invoice dependency
* Better german translation

2025-01-29 - version 0.3.0
* Added sequential invoice numbering
* Added settings section to configure numbering strategy, format, override last number.
* On change status change to processing or completed, the invoice number is applied.

2025-01-08 - version 0.2.9
* Better compatibility with different themes and plugins
* Fixed a fatal error that occurred in some cases if the theme or installed plugins were interacting with the native customizer.
* Fixed the settings link to redirect to E-Invoice tab instead of WooCommerce.

2025-01-06 - version 0.2.8
Fix Fatal error when order has no modified date, shows today's date.

2024-12-31 - version 0.2.7
* Happy new year and Thank you all for your support. 
* Updated dependency digital-invoice dependency
* Pdf Invoice : Show date_modified when order is not paid

2024-12-25 - version 0.2.6
* Fix bad escaping in delivery address. 

2024-12-22 - version 0.2.5
* PDF Invoice now displays extensive billing and shipping information (company name, address line 1, address line 2, city, postal code, state, country)
* Fixed compatibility with OceanWP theme
* Updated WordPress and WooCommerce compatibility to latest versions

2024-11-06 - version 0.2.4
* Added the possiblity to set a company name different than the shop name.
* Added spanish translation

2024-10-15 - version 0.2.3
* Fix fatal error after the plugin upgrade.

2024-10-14 - version 0.2.2
* Updated tested Wordpress up to version.
* Added option to support adding phone number and email to the invoice.


2024-05-26 - version 0.2.1
* Fixed Fatal error while attaching invoice to email.
* Fixed refresh preview.
* This version adds the possibility to view the project changes.

2024-05-17 - version 0.2.0
* This version adds the possibility to download multiple e-invoices in a Zip package from the WooCommerce orders list table.
* After each upgrade, an admin notice will display the changes since the previously installed version.

2024-04-27 - version 0.1.9
* Prices in the invoice table now utilize WooCommerce formatting for consistency.
* Corrected an issue where the footer was not properly aligned in the Preview mode.

2024-04-24 - version 0.1.8
* Fix translation loading and added WooCommerce as a plugin dependency

2024-04-04 - version 0.1.7
Fix arabic caracters in pdf invoice

2024-04-03 - version 0.1.6
* Fix adding invoice to emails as attachements
* Updated tested Wordpress up to version
* Fixed typos in readme

2024-04-03 - version 0.1.5
* Published to WordPress Plugins repository
* Allow E-Invoices to be attached to more WooCommerce Emails
* Updated tested WooCommerce up to version
* Updated translations

2024-04-02 - version 0.1.4
* Applying Wordpress Plugin review team feedback
* Multiple sanitization and escaping fixes.
* Use WOOEI as unique prefix

2024-04-01 - version 0.1.3
* Minor style adjustments.

2024-04-01 - version 0.1.2
* Renamed slug to comply with the naming guidelines.
2024-03-15 - version 0.1.1
* Added PHP extensions (gd, mbstring, iconv and dom) verification before loading the plugin.

2024-03-14 - version 0.1.0
* Introduced support for the UBL format, expanding e-invoicing capabilities.

2024-03-14 - version 0.0.9
* Implemented role-based access control, allowing customization of minimum capabilities required for setting up the plugin.


2024-03-13 - version 0.0.8
* Resolved compatibility issues with the Astra WordPress theme.

2024-03-12 - version 0.0.7
* Minor fixes to improve the user experience.

2024-03-11 - version 0.0.6
* Confirmed compatibility with WooCommerce 8.4 =
* Declare compatibility with **High performance order tables**

2024-03-11 - version 0.0.5
* Improved the plugin onboarding experience =
* Nice instructive notices, better captions and labels.

2024-03-11 - version 0.0.1
* Initial release, PDF Invoice Customizer supporting Factur-X and ZUGFeRD formats, ensuring compliance with French and German e-invoicing standards.


== Upgrade Notice ==
= 0.3.5 =
This version fixes a sequential invoice number generation and ensures invoice generation errors are properly logged, should be upgraded as soon as possible.
= 0.2.1 =
This version fixes a Fatal Error, should be upgraded as soon as possible.
= 0.2 =
This version adds the possibility to download multiple e-invoices in a Zip package from the WooCommerce orders list table.