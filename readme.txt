=== LW Memberships ===
Contributors: lwplugins
Tags: membership, woocommerce, subscriptions, content restriction, members
Requires at least: 6.0
Tested up to: 6.7
Requires PHP: 8.2
Stable tag: 1.1.3
License: GPL-2.0-or-later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Lightweight membership system with WooCommerce integration. No bloat, no upsells.

== Description ==

LW Memberships is a lightweight membership plugin for WordPress that integrates seamlessly with WooCommerce. Create membership plans, link them to products, and restrict content to members only.

= Features =

* **Membership Plans** - Create unlimited membership plans with different durations
* **Tabbed Plan Editor** - Manage plans with General, Content, WooCommerce, and Members tabs
* **WooCommerce Integration** - Link products to membership plans for automatic access
* **WooCommerce Subscriptions** - Full support for recurring memberships
* **Content Restriction** - Restrict posts, pages, and custom post types
* **Content Hiding** - Restricted content is hidden from archives and search results
* **Manual Member Management** - Add and remove members directly from the plan editor
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
3. Go to LW Plugins > Plans to create membership plans

Or install via Composer:

`composer require lwplugins/lw-memberships`

== Frequently Asked Questions ==

= Does this require WooCommerce? =

No, WooCommerce is optional. You can manually assign memberships to users without WooCommerce. However, WooCommerce integration enables automatic membership granting on purchase.

= Does it work with WooCommerce Subscriptions? =

Yes! LW Memberships fully supports WooCommerce Subscriptions for recurring memberships. Membership status is automatically synced with subscription status.

= Can I restrict any content type? =

Yes, you can restrict posts, pages, and any custom post type.

= Is restricted content hidden from archives? =

Yes, restricted content is automatically hidden from archives, search results, and feeds for users who do not have access.

== Screenshots ==

1. Membership Plans management
2. Plan editor with tabbed interface
3. Content restriction meta box
4. Settings page

== Changelog ==

= 1.1.3 =
* Isolate third-party admin notices on LW plugin pages

= 1.1.2 =
* Add fresh POT file and Hungarian (hu_HU) translation

= 1.1.1 =
* New: Central plugin registry from GitHub JSON

= 1.1.0 =
* Renamed "Levels" to "Plans" throughout the plugin
* Added tabbed plan editor (General, Content, WooCommerce, Members)
* Added Content tab for assigning posts/pages to plans
* Added Members tab for manual member management
* Added WP_Query-level content hiding for archives and search
* Added AJAX-powered search for content and members
* Improved content restriction to hide posts from archives and feeds

= 1.0.0 =
* Initial release
* Membership levels management
* WooCommerce integration
* WooCommerce Subscriptions support
* Content restriction (posts, pages, CPT)
* Shortcodes for restricted content

== Upgrade Notice ==

= 1.1.0 =
Major update: Levels renamed to Plans, tabbed editor, content hiding in archives.

= 1.0.0 =
Initial release.
