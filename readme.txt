=== Login Cloak ===
Contributors: axielroque
Tags: security, login, admin, hardening
Requires at least: 6.0
Requires PHP: 7.4
Tested up to: 6.9
Stable tag: 1.1.1
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Hide and protect your WordPress login URL without touching core files.
Includes recovery mode and a random path generator.

== Description ==

Login Cloak lets you hide the default WordPress login and admin URLs by using a custom slug, e.g. `https://example.com/your-custom-path/`.
It blocks direct access to `wp-login.php` and to `wp-admin/` for non-logged users, while keeping essential endpoints working (AJAX and admin-post).

== Features ==

- Custom login slug shown in Settings → Login Cloak
- One-click random path generator
- Blocks direct access to `wp-login.php`
- Blocks `wp-admin/` for visitors (while allowing AJAX and admin-post)
- Recovery mode to prevent lockout

== Installation ==

1. Upload the plugin to `/wp-content/plugins/` and activate it.
2. Go to Settings → Login Cloak.
3. Set your custom login path or click “Generate Random Path”.
4. Use the displayed Login URL to access your site.

== Usage ==

- Login URL is displayed in the settings page as `https://example.com/<your-slug>/`.
- You can update the slug anytime; rewrite rules are flushed automatically.
- The plugin rejects short or obvious slugs for better security.

== Recovery mode ==

If you get locked out or need to bypass the protection temporarily, append the recovery parameter to the default login URL:

`https://example.com/wp-login.php?lcloak-recover=1`

Note: For legacy compatibility, the `slp-recover=1` parameter is also supported.

This disables the blocking logic only for that request so you can log in.

== Compatibility ==

- AJAX requests via `admin-ajax.php` continue to work for both visitors and logged-in users.
- Front-end forms using `admin-post.php` (including `nopriv_*` actions) remain functional.
- Cron runs (`wp_doing_cron()`) are not blocked.

If you have a custom endpoint inside `wp-admin/` that must remain accessible to visitors, you can allowlist its script via the `lcloak_allowed_admin_scripts` filter. Note that the legacy filter name `slp_allowed_admin_scripts` is also supported for backwards compatibility.

== Uninstall ==

Deactivating the plugin restores normal rewrite rules. If you also remove the plugin, the option storing the slug may remain unless you delete it manually or provide an uninstall routine.

== Frequently Asked Questions ==

Q: I forgot my custom login path. How can I log in?

A: Use the recovery URL: `https://example.com/wp-login.php?lcloak-recover=1`.

Q: My front-end form stopped working.

A: Ensure it posts to `admin-post.php` and that your `nopriv_*` action is registered. Those routes are allowlisted by default.

== Changelog ==

= 1.1.1 =
- Rename plugin branding to Login Cloak and update text domain.
- Update prefixes to avoid collisions and improve compatibility.

= 1.1.0 =
- Initial public version with recovery mode, random path generator, and admin/AJAX compatibility.
