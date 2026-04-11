# QWEN.md - News CMS Project Context

## Project Overview

A PHP-based news/content management system with custom routing, MySQL database, and a multi-theme template system. This is a legacy PHP project without frameworks, designed for Colombian media outlets (Intermedia Colombia). It supports news articles, institutional pages, columnist profiles, e-commerce functionality, and a full admin panel for content management.

## Tech Stack

- **PHP 8.0+** (native, procedural code, no framework)
- **MySQL 8.0+** - PDO connection via `inc/config.php`
- **PHPMailer ^6.11** - via Composer for email
- **Apache** - requires `.htaccess` for mod_rewrite routing
- **Composer** - dependency management
- **Git** - version control with auto-update capability

## Architecture

### Entry Point & Routing
- `index.php` - Single entry point with custom URL router
- `.htaccess` - Mod_rewrite rules redirecting all requests to `index.php`
- Routes support Spanish URLs: `/buscar/`, `/institucional/`, `/columnista/`, `/noticias/`, `/categoria/post/`

### Key Directories
- `inc/` - Core includes (config, translations, cart functions)
- `inc/config.php` - DB connection singleton, constants, helper functions
- `admin/` - Full admin panel with session-based auth, permissions, and auto-update
- `admin/inc/` - Shared admin utilities (db_repair, auto-update, logging)
- `actions/` - API endpoints and webhooks
- `template/{THEME}/` - Multi-theme system (themes: Artemis, news, NewsEdge, newsers)
- `install/` - Database installation/migration scripts
- `mailer/` - Email sending utilities
- `public/` - Publicly accessible uploaded files

### Database
- Schema defined in `news.sql` and `intermed_news.sql`
- Key tables: `usuarios`, `roles`, `system_settings`, `posts`, `blog_posts`, `categorias`, `multimedia`, `popups`, `ads`, `products`, `coupons`, `system_logs`
- Auto-repair system: `admin/inc/db_repair.php` runs after git pulls to apply schema changes
- DB credentials stored in `inc/url_bd.php` (git-ignored)

## Setup & Running

### Prerequisites
1. PHP 8.0+ with PDO MySQL extension
2. MySQL 8.0+ database
3. Apache with mod_rewrite enabled

### Initial Setup
1. Create `inc/url_bd.php` with database credentials:
   ```php
   <?php
   $host = 'localhost';
   $dbname = 'your_db';
   $dbuser = 'your_user';
   $dbpass = 'your_pass';
   $url_site = 'https://yoursite.com';
   ```
2. Import database schema from `install/db.sql`
3. Run `composer install` to install PHPMailer
4. Configure Apache with mod_rewrite enabled
5. Access `/install/` for guided setup if needed

### Commands
- Check PHP syntax: `php -l <filename>`
- PHP built-in server (limited routing): `php -S localhost:8000`
- Install dependencies: `composer install`
- For full routing functionality, use Apache with `.htaccess`

## Code Conventions

### General
- Use `<?php` opening tags (no short tags)
- Procedural programming style (no classes required)
- Files outputting content may use closing `?>`

### Naming Conventions
- **Constants**: `UPPER_CASE` (e.g., `URLBASE`, `THEME`, `NOMBRE_SITIO`)
- **Functions**: `snake_case` (e.g., `db()`, `setFlash()`, `renderAdsenseBlock()`)
- **Variables**: `$snake_case` (e.g., `$templateFile`, `$pdo`, `$sys`)
- **Template files**: `kebab-case.php` (e.g., `institucional-single.php`)

### Database Access
- Always use the `db()` singleton function from `inc/config.php`
- Use prepared statements to prevent SQL injection:
  ```php
  $stmt = db()->prepare("SELECT * FROM table WHERE id = ?");
  $stmt->execute([$id]);
  $result = $stmt->fetch(PDO::FETCH_ASSOC);
  ```
- PDO configured with `EMULATE_PREPARES => false`

### Security
- Escape output with `htmlspecialchars($var, ENT_QUOTES, 'UTF-8')`
- Validate and sanitize all `$_GET`, `$_POST` input
- Session management with `session_start()` checks
- Soft-delete patterns (`deleted` flag, `borrado` flag)

### Error Handling
- Use try/catch for DB operations and external APIs
- Catch `PDOException` for database errors
- Catch `Throwable` for general errors
- Auto-redirect to `/install/` if DB connection fails

## Admin Panel Features

- **Authentication**: Session-based with role/permission system
- **Auto-update**: Git pull detection and automatic execution via `admin/inc/auto-update.php`
- **DB Repair**: Schema migrations run automatically after updates
- **Logging**: Action logging via `log_system_action()` in `inc/config.php`
- **Content Management**: Blog posts, categories, institutional pages, multimedia
- **System Settings**: Configurable via admin UI, stored in `system_settings` table
- **SMTP/Email**: Configurable mail settings, test endpoint at `actions/test_smtp.php`

## Template System

- Active theme determined by `system_settings.site_theme` constant (`THEME`)
- Template path: `template/{THEME}/`
- Templates include: `index.php`, `single.php`, `noticias.php`, `search.php`, `404.php`, etc.
- Shared header/footer: `template/{THEME}/inc/header.php` and `inc/footer.php`
- Content buffering: `ob_start()` / `ob_get_clean()` for template rendering

## Helper Functions (inc/config.php)

- `db()` - PDO singleton database connection
- `setFlash($type, $message)` - Session flash messages
- `renderAdsenseBlock($position)` - Google AdSense ad blocks
- `render_post_content($html)` - Convert Quill editor classes to inline styles
- `renderPopup()` - Render active popup modal
- `get_image_alt($imagePath, $fallback)` - Image alt text from multimedia table
- `log_system_action(...)` - System action logging

## E-commerce Features

- Shopping cart functionality in `inc/cart_functions.php`
- Product management with stock tracking
- Coupon/discount system
- Mercado Pago integration
- Order notifications via email and WhatsApp API

## Important Quirks

- Theme is database-driven, not config-file based
- DB connection auto-redirects to `/install/` if `url_bd.php` is missing
- Route order matters in `index.php` - specific routes must precede generic ones
- Single post route (`/categoria/post/`) is intentionally placed after special routes
- Auto-update system uses `git fetch + git reset --hard` (not `git pull`)
- Migration system in `db_repair.php` ensures schema compatibility across installations
- Content editor uses Quill, requiring `render_post_content()` for theme compatibility
- Each installation may have thousands of sites using this system; schema changes must be additive (never destructive)

## Key Files

| File | Purpose |
|------|---------|
| `index.php` | Main router and entry point |
| `inc/config.php` | DB, constants, helpers, settings loader |
| `.htaccess` | Apache mod_rewrite configuration |
| `admin/inc/db_repair.php` | Automatic database migrations |
| `admin/inc/auto-update.php` | Git-based auto-update system |
| `news.sql` / `intermed_news.sql` | Database schema dumps |
| `template/Artemis/` | Primary active theme |
| `inc/url_bd.php` | Database credentials (git-ignored) |
| `NOTAS_INTERNAS.txt` | Internal developer notes (Spanish) |
