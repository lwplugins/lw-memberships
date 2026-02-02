# LW Memberships

> **Warning**
> This plugin is currently in **alpha stage** and under active development. It is **not recommended for production use**. APIs and database schemas may change without notice. Use at your own risk.

Lightweight membership system for WordPress with WooCommerce integration. Part of the [LW Plugins](https://github.com/lwplugins) family.

## Features

- **Membership Levels** - Create unlimited membership levels with customizable durations (lifetime, days, months, years)
- **WooCommerce Integration** - Link products to membership levels for automatic access on purchase
- **WooCommerce Subscriptions** - Full support for recurring memberships with status synchronization
- **Content Restriction** - Restrict posts, pages, and custom post types to specific membership levels
- **Shortcodes** - Display restricted content and member dashboards
- **Template Overrides** - Customize restriction messages via theme templates
- **Lightweight** - Minimal footprint, no bloat, no upsells

## Requirements

- WordPress 6.0+
- PHP 8.2+
- WooCommerce (optional, for product integration)
- WooCommerce Subscriptions (optional, for recurring memberships)

## Installation

### Via Composer

```bash
composer require lwplugins/lw-memberships
```

### Manual Installation

1. Download or clone this repository
2. Run `composer install` in the plugin directory
3. Upload to `/wp-content/plugins/lw-memberships`
4. Activate the plugin through the WordPress admin

## Usage

### Creating Membership Levels

1. Go to **LW Plugins > Levels**
2. Click **Add New**
3. Configure level name, duration, and linked WooCommerce products
4. Save the level

### Restricting Content

1. Edit any post, page, or custom post type
2. Find the **Membership Restriction** meta box in the sidebar
3. Select which membership levels can access this content
4. Update/publish the post

### Shortcodes

**Restrict inline content:**
```
[lw_mship_restricted level="1,2" message="Members only content"]
  Your protected content here
[/lw_mship_restricted]
```

**Display user's memberships:**
```
[lw_mship_memberships show_expired="no"]
```

### Public API Functions

```php
// Check if user has specific level
lw_mship_user_has_level( int $level_id, ?int $user_id = null ): bool

// Check if user can access content
lw_mship_user_can_access( int $post_id, ?int $user_id = null ): bool

// Get user's active memberships
lw_mship_get_user_memberships( ?int $user_id = null ): array

// Grant membership to user
lw_mship_grant_membership( int $user_id, int $level_id, string $source = 'manual', ?int $order_id = null )

// Revoke membership from user
lw_mship_revoke_membership( int $user_id, int $level_id ): bool

// Get all membership levels
lw_mship_get_levels( bool $active_only = true ): array
```

## Template Overrides

Copy templates from `templates/` to your theme's `lw-memberships/` directory to customize:

- `restriction-not-logged-in.php` - Message for logged out users
- `restriction-no-access.php` - Message for users without required membership
- `restriction-expired.php` - Message for expired memberships
- `restriction-paused.php` - Message for paused memberships
- `my-memberships.php` - User's membership list

## Hooks

### Actions

```php
// Fired when membership is granted
do_action( 'lw_mship_membership_granted', $membership_id, $user_id, $level_id );

// Fired when membership is revoked
do_action( 'lw_mship_membership_revoked', $membership_id, $user_id, $level_id );

// Fired when membership expires
do_action( 'lw_mship_membership_expired', $membership_id, $user_id, $level_id );
```

### Filters

```php
// Customize supported post types for restriction
apply_filters( 'lw_mship_supported_post_types', array $types );
```

## Development

```bash
# Install dependencies
composer install

# Run code standards check
composer phpcs

# Auto-fix code standards
composer phpcbf
```

## Database Tables

The plugin creates 4 custom tables:

| Table | Description |
|-------|-------------|
| `{prefix}_lw_mship_levels` | Membership levels |
| `{prefix}_lw_mship_level_products` | Level-Product associations |
| `{prefix}_lw_mship_user_memberships` | User memberships |
| `{prefix}_lw_mship_content_rules` | Content restriction rules |

## Related Plugins

- [LW SEO](https://github.com/lwplugins/lw-seo) - Lightweight SEO plugin
- [LW Disable](https://github.com/lwplugins/lw-disable) - Disable WordPress features
- [LW Site Manager](https://github.com/lwplugins/lw-site-manager) - Site maintenance via AI/REST

## License

GPL-2.0-or-later
