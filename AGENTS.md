# AGENTS.md

## Project Overview
PHP news website with custom routing, MySQL database, and template system.

## Tech Stack
- **PHP** (no framework) - custom router in `index.php`
- **MySQL** - PDO connection via `inc/config.php`
- **PHPMailer** - via Composer for email
- **Apache** - requires `.htaccess` (mod_rewrite)

## Key Directories
- `index.php` - Entry point, handles all routing
- `inc/config.php` - DB connection, constants, helpers
- `admin/` - Admin panel
- `template/*/` - Themes (theme name from `system_settings` table)

## Setup Requirements
1. Create `inc/url_bd.php` with DB credentials: `$host`, `$dbname`, `$dbuser`, `$dbpass`, `$url_site`
2. Database must have `system_settings` table with site configuration
3. Run via Apache (required for `.htaccess` routing)

## Routing (index.php)
- `/buscar/termino/` - Search
- `/noticias/` - News listing
- `/noticias/post/slug/` - Single post
- `/institucional/` or `/institucional/slug/` - Institutional pages
- `/columnista/` - Columnists
- Other routes: category/post, static pages, home

## Important Quirks
- Theme is read from `system_settings.site_theme` (not a config file)
- DB connection auto-redirects to `/install/` if `url_bd.php` missing
- Uses PHP sessions - `setFlash()` helper in `inc/config.php`
- AdSense renders via `renderAdsenseBlock(position)` function
- Mercado Pago webhooks: `/actions/mp_webhook.php`
- Route order matters in index.php - single post must be after special routes

## No Formal Testing
This is a simple PHP project - no lint/typecheck/test commands exist.
