# WordPress.org Submission Documentation

This document lists the required and recommended steps to publish **Panasys Popups** on the WordPress.org Plugin Directory. Publisher details: WordPress.org username `im100rya`, author `Shaurya Srivastava`, company `Panasys Technologies`, website `https://panasys.in`.

## 1) Required package files

Include these files in your release zip. The requested top-level release folder name is `panasys popups`:

- `panasys-popups.php` (plugin header metadata)
- `readme.txt` (WordPress.org format)
- `LICENSE` (GPL-compatible license text)
- `readme.txt` privacy/compliance disclosure for localStorage cooldown behavior
- `uninstall.php` (cleanup logic)
- `includes/`, `assets/` runtime files

## 2) Plugin metadata checklist

Verify before release:

- Plugin Name, WordPress.org contributor username, Author, Author URI, company, and Version are correct.
- `Requires at least`, `Requires PHP`, and tested version are up to date.
- Text domain and domain path are set.
- License is GPL-compatible and declared in both plugin header and readme.

## 3) WordPress.org assets checklist

Add the following in your plugin SVN `/assets` directory during submission:

- `icon-128x128.png`
- `icon-256x256.png`
- `banner-772x250.png` (optional)
- `banner-1544x500.png` (optional retina)
- `screenshot-1.png`, `screenshot-2.png`, etc.

## 4) Pre-release QA checklist

Run these checks before packaging:

1. `php -l` on plugin PHP files.
2. `composer run phpcs`.
3. `composer test` (WordPress tests, if environment is available).
4. Manual functional checks:
   - Popup open/close behavior.
   - 1-day suppression after close.
   - Form/image/shortcode rendering inside popup.
   - Accessibility keyboard close via ESC.
   - Responsive display on mobile.

## 5) Security & compliance checklist

- Escape output and sanitize user input.
- Verify nonces for settings/notice actions.
- Avoid tracking/telemetry unless explicitly disclosed.
- Disclose browser localStorage usage for the 1-day popup cooldown in the readme and privacy policy guidance.
- Ensure no bundled premium code restrictions in the directory version.
- Ensure donation links are informational and non-blocking.

## 6) Packaging checklist

- Exclude development-only files from zip (see `.distignore`).
- Build the release archive with the top-level plugin folder named `panasys popups`.
- Ensure no secrets or local environment files are included.
- Tag release and keep readme changelog aligned with plugin version.

## 7) Post-publish maintenance checklist

- Track support threads and bug reports.
- Update tested version after each new WordPress major release validation.
- Keep changelog, stable tag, release notes, and compliance documentation in sync.
