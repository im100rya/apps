=== Panasys Popups ===
Contributors: im100rya
Donate link: https://www.paypal.me/100rya
Tags: popup, modal, shortcode, forms, images
Requires at least: 6.0
Tested up to: 6.7
Requires PHP: 7.4
Stable tag: 1.4.0
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Create accessible popups for shortcodes, forms, images, and rich content using reusable popup entries and trigger shortcodes.

== Description ==

Panasys Popups is a lightweight popup plugin from Panasys Technologies that lets site owners create popup entries in wp-admin and display them anywhere through shortcodes.

= Key Features =

* Popup custom post type for reusable popup content.
* Works with shortcode-based forms and dynamic content.
* Frontend popup trigger shortcode with custom label and classes.
* Popup list view shows shortcode snippets right next to popup names.
* Admin editor also shows ready-to-copy shortcode snippets near popup title.
* Adjustable modal width, background color, and text color for each popup.
* Sidebar Settings page for default popup styling, popup session frequency, and donation information.
* Popup content supports text, images, YouTube videos, and supported social media embeds from links.
* Popup session frequency options: every page load, one day, one week, or never again in the same browser.
* Optional popup auto-open behavior.
* Accessible modal dialog semantics and ESC-to-close support.
* Responsive modal dimensions designed to avoid full-screen takeover.

= Author and Publisher =

* WordPress.org username: im100rya
* Author: Shaurya Srivastava
* Company: Panasys Technologies
* Website: https://panasys.in

= Perfect For =

* Contact forms and lead generation forms.
* Image promos, banners, and newsletter signup prompts.
* Any editor or block content you want inside a modal dialog.

= Important Notes =

* Popups can contain shortcodes, forms, and images.
* Closing a popup stores a browser-side cooldown for 1 day.
* The plugin includes a small admin support/donation notice for administrators.
* This plugin does not transmit popup visitor data to an external service.

== Installation ==

1. Upload the plugin folder to `/wp-content/plugins/panasys-popups`, or install via **Plugins > Add New**.
2. Activate **Panasys Popups** from the Plugins page.
3. Go to **Panasys Popups** in the dashboard and create your popup content.
4. Configure default modal width, background color, text color, popup session frequency, and donation details from **Panasys Popups > Settings** in the WordPress admin sidebar.
5. Override modal width, background color, text color, and auto-open behavior in each popup **Popup Settings** panel.
6. Insert `[panasys_popup id="123"]` on pages/posts/templates where popup markup should load.
7. Insert `[panasys_popup_trigger id="123" label="Open Popup"]` where the trigger button should appear.

== Frequently Asked Questions ==

= Can I show forms (Contact Form 7, WPForms, Gravity Forms) in a popup? =

Yes. Add the form shortcode to popup content and it will render inside the modal.

= Can I show images and galleries in popups? =

Yes. Use the editor to insert images/media blocks directly.

= Can I adjust popup colors and width? =

Yes. Use **Panasys Popups > Settings** for defaults and each popup's **Popup Settings** panel for overrides.

= How does popup cooldown work? =

From **Panasys Popups > Settings**, choose whether popups can appear every page load, after one day, after one week, or never again in the same browser after closing.

= Is this plugin Gutenberg compatible? =

Yes. Popup content supports the block editor and REST API.

= Can I embed YouTube videos and social media posts? =

Yes. Add supported media/social URLs on their own line in popup content or use embed blocks. WordPress oEmbed handles major providers such as YouTube and supported social platforms.

= Where are the donation details? =

The plugin settings page includes PayPal `paypal.me/100rya` and UPI `im100rya@upi` donation options for supporting the free plugin.

= Does this plugin collect personal data? =

No personal data is sent to the plugin author. A browser-side localStorage value is used only to remember that a visitor closed a popup for the 1-day cooldown.

== Privacy and Compliance ==

Panasys Popups stores popup cooldown information in the visitor browser using localStorage. The stored value is limited to a popup-specific hidden-until timestamp and is not transmitted to Panasys Technologies by this plugin.

If you place third-party forms, analytics scripts, tracking pixels, or embedded media inside popup content, review those providers' privacy terms and update your site's privacy policy accordingly.

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

= 1.4.0 =
* Added popup session frequency setting: every page load, one day, one week, or never again in the same browser.
* Added PayPal and UPI donation details to the plugin settings page.
* Made the popup close button more explicit and accessible.

= 1.3.0 =
* Added sidebar Settings page for default modal width, background color, and text color.
* Improved popup rendering for text, images, YouTube, and supported social media oEmbed links.
* Documented release packaging folder name as `panasys popups`.

= 1.2.0 =
* Added per-popup settings for modal width, background color, and text color.
* Updated author, publisher, privacy, and WordPress.org publication metadata.

= 1.1.0 =
* Added 1-day popup suppression after user closes a popup.
* Added admin support/donation notice with dismiss option.
* Improved publication docs and release guidance.

= 1.0.0 =
* Initial release.

== Upgrade Notice ==

= 1.4.0 =
Adds configurable popup session frequency and donation details in plugin settings.

= 1.3.0 =
Adds sidebar styling defaults and improved embed support for rich popup content.

= 1.2.0 =
Adds per-popup visual settings and updated WordPress.org publisher documentation.
