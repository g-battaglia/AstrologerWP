# WP.org release readiness checklist (F10.6)

Use this checklist before submitting the plugin to the WP.org directory. Every item must pass for a clean review.

## Version alignment
- [ ] `astrologer-api.php` `Version:` header matches `readme.txt` `Stable tag:` and `package.json` `version`.
- [ ] `CHANGELOG.md` has an entry for the new version.

## Readme compliance
- [ ] `readme.txt` has all required headers: Contributors, Donate link, Tags, Requires at least, Tested up to, Stable tag, Requires PHP, License, License URI.
- [ ] `Tested up to` matches the latest stable WordPress release.
- [ ] `Tags` are relevant and fewer than 10.
- [ ] Description is under 150 words for the short description line.
- [ ] All sections properly formatted (Description, Installation, FAQ, Screenshots, Changelog, Upgrade Notice).

## Code quality
- [ ] `vendor/bin/phpcs` reports zero errors.
- [ ] `vendor/bin/phpstan analyse --memory-limit=1G` reports zero errors at level 8.
- [ ] `npx eslint admin-src/ blocks/ interactivity-src/` reports zero errors.
- [ ] `npx stylelint "blocks/**/*.css" "admin-src/**/*.scss"` reports zero errors.
- [ ] `wp plugin check astrologer-api` passes (run on a wp-env with plugin-check installed).

## Security audit
- [ ] All REST mutation routes have nonce + capability checks.
- [ ] All direct file access has `defined( 'ABSPATH' ) || exit;`.
- [ ] Stored secrets (API key, Geonames username) encrypted via Sodium.
- [ ] SVG sanitization via `SvgSanitizer` on all chart outputs.
- [ ] No `eval()`, `create_function()`, `base64_decode()` of user input.
- [ ] No external HTTP requests without nonce/capability guard from the plugin surface.

## Functionality smoke test (fresh wp-env)
- [ ] `make down && rm -rf $WP_ENV_HOME/<hash> && make up` — fresh install.
- [ ] Plugin activates without errors (`wp plugin activate astrologer-api`).
- [ ] Setup wizard redirects on first visit.
- [ ] Settings save works (test via UI and `wp astrologer settings set`).
- [ ] Test-connection button returns success with a valid RapidAPI key.
- [ ] Insert `birth-form` block in a page, submit with Einstein birth data, chart renders.
- [ ] `wp astrologer health` returns OK.
- [ ] `wp astrologer doctor` all green.
- [ ] Cron events scheduled: `wp cron event list`.
- [ ] Uninstall via `wp plugin deactivate --uninstall` cleans up options/CPT/caps.

## Assets
- [ ] `assets/wporg/screenshot-1.png` through `screenshot-N.png` (real PNGs, not placeholder text).
- [ ] `assets/wporg/banner-1544x500.png` and `banner-772x250.png`.
- [ ] `assets/wporg/icon-256x256.png` and `icon-128x128.png`.

## i18n
- [ ] `languages/astrologer-api.pot` regenerated with `npm run make-pot`.
- [ ] At least one translation (e.g. `astrologer-api-it_IT.po`) demonstrates the workflow.

## Distribution ZIP
- [ ] `make zip` produces `dist/astrologer-api-<version>.zip`.
- [ ] ZIP opens cleanly: no `_legacy/`, `tests/`, `node_modules/`, `.git/`, `*.md` except `readme.txt`.
- [ ] ZIP install works on a fresh WP site: upload + activate + basic flow.
- [ ] Production `vendor/` composer deps included (no dev deps).

## Git / GitHub
- [ ] Tag the release: `git tag v1.0.0 && git push origin v1.0.0`.
- [ ] Create a GitHub release with the tag; paste the `CHANGELOG.md` section.
- [ ] Attach the ZIP from `dist/` to the GitHub release.

## WP.org submission (human approval required)
- [ ] Submit ZIP via https://wordpress.org/plugins/developers/add/ (F10.7 — `[?]`).
- [ ] Wait for review approval (typical: 1–6 weeks).
- [ ] On approval: SVN push `trunk/` + tag `tags/<version>/` (F10.8 — `[?]`).

## Post-release
- [ ] Pin release announcement in GitHub Discussions.
- [ ] Update plugin page description with new features if applicable.
- [ ] Monitor the WP.org support forum for the plugin.
