=== Panasys Popups ===
Contributors: 100rya
Tags: popup, modal, shortcode, forms, images
Requires at least: 6.0
Tested up to: 6.7
Requires PHP: 7.4
Stable tag: 1.0.0
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Build accessible popups for shortcodes, forms, images, and rich content with a flexible shortcode trigger system.

== Description ==

Panasys Popups helps you create reusable popups from the WordPress admin and place them anywhere using shortcodes.

Use cases include:

* Contact forms (e.g. Contact Form 7, WPForms, Gravity Forms shortcodes).
* Image lightboxes and promotional banners.
* Newsletter signup forms and lead generation blocks.
* Any content supported by the block editor/classic editor.

Plugin highlights:

* Custom post type for creating and managing popup content.
* Works with shortcodes and dynamic form plugins.
* Trigger shortcode for buttons/links with custom labels and classes.
* Optional auto-open per popup.
* Accessible dialog markup and keyboard close (ESC) support.

== Installation ==

1. Upload the plugin files to `/wp-content/plugins/panasys-popups`, or install through the WordPress plugins screen.
2. Activate the plugin through the 'Plugins' screen in WordPress.
3. Go to **Panasys Popups** in admin and create a popup.
4. Add `[panasys_popup id="123"]` where popup markup should be rendered.
5. Add `[panasys_popup_trigger id="123" label="Open popup"]` to place the trigger button.

== Frequently Asked Questions ==

= Can I show forms inside a popup? =

Yes. Insert your form shortcode (such as Contact Form 7) in popup content.

= Can I show images and galleries? =

Yes. Add images directly in the popup editor content.

= Is it Gutenberg compatible? =

Yes. The popup post type supports the block editor and REST API.

== Shortcode Reference ==

`[panasys_popup id="123"]`

* `id` (required): Popup post ID.

`[panasys_popup_trigger id="123" label="Open Popup" class="my-button"]`

* `id` (required): Popup post ID.
* `label` (optional): Trigger text.
* `class` (optional): Space-separated CSS classes.

== Testing and Quality Assurance ==

Before release, run the following:

1. **Lint:** `php -l` on plugin PHP files.
2. **Coding standards:** `composer run phpcs`.
3. **Unit tests:** `composer test` (WordPress test suite).
4. **Manual browser checks:**
   * Verify popup open/close behavior by trigger, overlay click, and ESC key.
   * Verify shortcodes render forms and images correctly.
   * Verify auto-open behavior from popup settings.
   * Verify responsive behavior on mobile widths.

== Screenshots ==

1. Popup post type editor with popup settings metabox.
2. Popup rendering on the frontend with trigger button.

== Changelog ==

= 1.0.0 =
* Initial release.
