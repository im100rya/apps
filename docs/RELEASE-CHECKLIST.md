# Release Checklist

## Versioning
- [ ] Update `panasys-popups.php` plugin `Version` and `PANASYS_POPUPS_VERSION`.
- [ ] Update `readme.txt` `Stable tag` and changelog.

## QA
- [ ] Run PHP linting.
- [ ] Run PHPCS.
- [ ] Run PHPUnit (when WP test suite is available).
- [ ] Manually verify popup flows and cooldown behavior.

## WordPress.org readiness
- [ ] Confirm WordPress.org username `im100rya`, author `Shaurya Srivastava`, company `Panasys Technologies`, and URL `https://panasys.in` are correct.
- [ ] Confirm donation link is valid.
- [ ] Confirm license headers, privacy/localStorage disclosure, and `LICENSE` file are present.
- [ ] Prepare assets (icons/banners/screenshots).

## Distribution
- [ ] Build release zip excluding development files (`.distignore`) with top-level folder name `panasys popups`.
- [ ] Publish/update tag.
- [ ] Deploy to WordPress.org SVN trunk/tag.
