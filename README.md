# Logkit (WordPress Plugin)

Hide and protect the default WordPress login and admin URLs by defining a secret login path. Block direct access to `/wp-login.php` and `/wp-admin` while still allowing a safe recovery mode.

## Features

- Custom secret login URL (e.g. `https://example.com/your-secret/`).
- Blocks direct access to `wp-login.php` (GET) and `/wp-admin` for non‑logged‑in users.
- Allows legitimate flows: login POST, lost password, reset password, register, logout, email verifications.
- Recovery mode: `https://example.com/wp-login.php?logkit-recover=1` to avoid lockout.
- Automatic rewrite flush upon changing the secret slug.
- Settings page with secure validation and convenient Copy buttons.
- Noindex headers on login pages.
- I18n ready (en_US, es_ES included).
- Uninstall removes plugin options (single & multisite aware).

## Requirements

- WordPress 5.6+
- PHP 7.4+

## Installation

1. Upload the `logkit` folder to `/wp-content/plugins/`.
2. Activate the plugin from the WordPress Plugins screen.
3. Go to Settings → Logkit.
4. Choose a secret slug (e.g., `my-super-secret-login`). Save changes.
5. Visit the new login URL shown on the settings page.

## Build ZIP

From the plugin folder, you can generate a distributable ZIP (without hidden files) using:

```bash
chmod +x build-wporg-zip.sh
./build-wporg-zip.sh
```

By default, the ZIP is created at:

`./logkit-<version>.zip`

## How it works

- A rewrite rule maps your secret path to `index.php?logkit_login=1` and the plugin includes `wp-login.php` internally.
- Direct `wp-login.php` access is blocked (except POST and allowed actions like lostpassword/reset).
- `/wp-admin` is blocked for non‑logged‑in users; users must log in via the secret URL first.
- Recovery mode (`?logkit-recover=1`) allows using the default login in emergencies.

## Usage

- Login: Use the secret URL shown in Settings → Logkit.
- Admin: After logging in via the secret URL, browse to `/wp-admin` normally.
- Recovery: If the secret is forgotten, use `https://example.com/wp-login.php?logkit-recover=1`.

## Settings & Behavior

- The slug is validated against forbidden words and a minimum length.
- Changing the slug triggers an automatic flush of rewrite rules.
- Copy buttons provide quick copy of the secret login and recovery URLs.
- Block behavior is configurable: redirect to home (302), 404 Not Found, or 403 Forbidden.

## Filters

- `logkit_forbidden_login_slugs` (array): extend or override forbidden slugs.
- `logkit_min_login_slug_length` (int): set the minimum slug length.
- `logkit_allowed_admin_scripts` (array): allow specific admin scripts for non‑logged‑in requests (default: `admin-ajax.php`, `admin-post.php`).
- `logkit_allowed_login_actions` (array): extend allowed `wp-login.php` actions when the default login URL is blocked (default includes: `lostpassword`, `rp`, `resetpass`, `register`, `logout`, `postpass`, `verifyemail`, `confirm_admin_email`, `reauth`). Receives current `$action` as second argument.

## Recovery Mode

- Append `?logkit-recover=1` to `wp-login.php` to temporarily bypass the block and access the default login.
- Intended for emergencies only.

## Troubleshooting

- If the secret URL 404s, go to Settings → Permalinks and click Save to flush rewrite rules.
- Check for caching/CDN rules that might interfere with `wp-login.php` or the secret path.
- Ensure no other security/login plugins conflict with rewrite or `wp-login.php` behavior.

## Internationalization

- Text domain: `logkit`.
- `.po/.mo` files included in `languages/`.

## Uninstall

- Deleting the plugin removes its stored options (multisite aware).

## Security Notes

- Choose a non‑obvious, sufficiently long slug.
- Share the secret URL only with trusted administrators.
- Consider pairing with rate limiting / WAF solutions for further protection.

## License

GPL-2.0-or-later
