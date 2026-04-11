=== Panasys Popups ===
Contributors: 100rya
Donate link: https://www.paypal.me/100rya
Tags: popup, modal, shortcode, forms, images
Requires at least: 6.0
Tested up to: 6.7
Requires PHP: 7.4
Stable tag: 1.1.0
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Create accessible popups for shortcodes, forms, images, and rich content using reusable popup entries and trigger shortcodes.

== Description ==

Panasys Popups is a lightweight popup plugin that lets site owners create popup entries in wp-admin and display them anywhere through shortcodes.

= Key Features =

* Popup custom post type for reusable popup content.
* Works with shortcode-based forms and dynamic content.
* Frontend popup trigger shortcode with custom label and classes.
* Popup list view shows shortcode snippets right next to popup names.
* Admin editor also shows ready-to-copy shortcode snippets near popup title.
* 1-day suppression after closing a popup (prevents frequent repeats).
* Optional popup auto-open behavior.
* Accessible modal dialog semantics and ESC-to-close support.
* Responsive modal dimensions designed to avoid full-screen takeover.

= Perfect For =

* Contact forms and lead generation forms.
* Image promos, banners, and newsletter signup prompts.
* Any editor or block content you want inside a modal dialog.

= Important Notes =

* Popups can contain shortcodes, forms, and images.
* Closing a popup stores a browser-side cooldown for 1 day.
* The plugin includes a small admin support/donation notice for administrators.

== Installation ==

1. Upload the plugin folder to `/wp-content/plugins/panasys-popups`, or install via **Plugins > Add New**.
2. Activate **Panasys Popups** from the Plugins page.
3. Go to **Panasys Popups** in the dashboard and create your popup content.
4. Insert `[panasys_popup id="123"]` on pages/posts/templates where popup markup should load.
5. Insert `[panasys_popup_trigger id="123" label="Open Popup"]` where the trigger button should appear.

== Frequently Asked Questions ==

= Can I show forms (Contact Form 7, WPForms, Gravity Forms) in a popup? =

Yes. Add the form shortcode to popup content and it will render inside the modal.

= Can I show images and galleries in popups? =

Yes. Use the editor to insert images/media blocks directly.

= How does popup cooldown work? =

When a visitor closes a popup, the plugin saves a browser-side cooldown value and prevents it from reopening for 1 day.

= Is this plugin Gutenberg compatible? =

Yes. Popup content supports the block editor and REST API.

== Shortcode Reference ==

`[panasys_popup id="123"]`

* `id` (required): Popup post ID.

`[panasys_popup_trigger id="123" label="Open Popup" class="my-button"]`

* `id` (required): Popup post ID.
* `label` (optional): Trigger text.
* `class` (optional): Additional CSS classes (space-separated).

== Screenshots ==

1. Popup editor screen with popup settings.
2. Popup rendered on frontend with trigger button.
3. Example popup containing form shortcode and image content.

== Changelog ==

= 1.1.0 =
* Added 1-day popup suppression after user closes a popup.
* Added admin support/donation notice with dismiss option.
* Improved publication docs and release guidance.

= 1.0.0 =
* Initial release.

== Upgrade Notice ==

= 1.1.0 =
Adds popup cooldown behavior and admin support notice.
