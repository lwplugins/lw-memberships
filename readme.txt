=== LW Memberships ===
Contributors: lwplugins
Tags: membership, woocommerce, subscriptions, content restriction, members
Requires at least: 6.0
Tested up to: 6.7
Requires PHP: 8.2
Stable tag: 1.0.0
License: GPL-2.0-or-later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Lightweight membership system with WooCommerce integration. No bloat, no upsells.

== Description ==

LW Memberships is a lightweight membership plugin for WordPress that integrates seamlessly with WooCommerce. Create membership levels, link them to products, and restrict content to members only.

= Features =

* **Membership Levels** - Create unlimited membership levels with different durations
* **WooCommerce Integration** - Link products to membership levels for automatic access
* **WooCommerce Subscriptions** - Full support for recurring memberships
* **Content Restriction** - Restrict posts, pages, and custom post types
* **Shortcodes** - Display restricted content and member dashboards
* **Lightweight** - Minimal footprint, no bloat

= Requirements =

* WordPress 6.0+
* PHP 8.2+
* WooCommerce (optional, for product integration)
* WooCommerce Subscriptions (optional, for recurring memberships)

= Part of LW Plugins Family =

LW Memberships is part of the LW Plugins family - lightweight WordPress plugins with minimal footprint and maximum impact.

* [LW SEO](https://github.com/lwplugins/lw-seo) - Essential SEO features
* [LW Disable](https://github.com/lwplugins/lw-disable) - Disable WordPress features
* [LW Site Manager](https://github.com/lwplugins/lw-site-manager) - Site maintenance via AI/REST

== Installation ==

1. Upload the plugin files to `/wp-content/plugins/lw-memberships`
2. Activate the plugin through the 'Plugins' screen in WordPress
3. Go to LW Plugins > Memberships to configure

Or install via Composer:

`composer require lwplugins/lw-memberships`

== Frequently Asked Questions ==

= Does this require WooCommerce? =

No, WooCommerce is optional. You can manually assign memberships to users without WooCommerce. However, WooCommerce integration enables automatic membership granting on purchase.

= Does it work with WooCommerce Subscriptions? =

Yes! LW Memberships fully supports WooCommerce Subscriptions for recurring memberships. Membership status is automatically synced with subscription status.

= Can I restrict any content type? =

Yes, you can restrict posts, pages, and any custom post type.

== Screenshots ==

1. Membership Levels management
2. Content restriction meta box
3. Settings page

== Changelog ==

= 1.0.0 =
* Initial release
* Membership levels management
* WooCommerce integration
* WooCommerce Subscriptions support
* Content restriction (posts, pages, CPT)
* Shortcodes for restricted content

== Upgrade Notice ==

= 1.0.0 =
Initial release.
