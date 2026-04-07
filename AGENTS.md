# AGENTS.md

## Project Overview
PHP news website with custom routing, MySQL database, and template system. This is a legacy PHP project without frameworks.

## Tech Stack
- **PHP** (native, no framework) - custom router in `index.php`
- **MySQL** - PDO connection via `inc/config.php`
- **PHPMailer** - via Composer for email (`phpmailer/phpmailer:^6.11`)
- **Apache** - requires `.htaccess` for mod_rewrite routing

## Key Directories
- `index.php` - Entry point and router
- `inc/config.php` - DB connection, constants, helper functions
- `inc/url_bd.php` - Database credentials (create this file)
- `admin/` - Admin panel (separate authentication)
- `template/*/` - Themes (theme name from `system_settings.site_theme`)
- `actions/` - API endpoints and webhooks (e.g., `mp_webhook.php`)

## Setup Requirements
1. Create `inc/url_bd.php` with:
   ```php
   <?php
   $host = 'localhost';
   $dbname = 'your_db';
   $dbuser = 'your_user';
   $dbpass = 'your_pass';
   $url_site = 'https://yoursite.com';
   ```
2. Database must have `system_settings` table with site configuration
3. Run via Apache with mod_rewrite enabled

## Build/Lint/Test Commands
**No formal testing or linting exists for this project.**

- To check PHP syntax: `php -l <filename>`
- To run PHP built-in server: `php -S localhost:8000` (but routing requires Apache)
- Composer: `composer install` (installs PHPMailer)

## Code Style Guidelines

### General
- Use `<?php` opening tag (no short tags `<?`)
- All PHP files should end with closing `?>` only if they output content
- Use procedural code (no classes required; mimic existing patterns)

### Naming Conventions
- **Constants**: `UPPER_CASE` (e.g., `URLBASE`, `NOMBRE_SITIO`)
- **Functions**: `snake_case` or `camelCase` (e.g., `db()`, `setFlash()`, `renderAdsenseBlock()`)
- **Variables**: `$snake_case` (e.g., `$templateFile`, `$pdo`)
- **Template files**: `kebab-case.php` (e.g., `institucional-single.php`)

### Database
- Always use PDO with prepared statements to prevent SQL injection:
  ```php
  $stmt = db()->prepare("SELECT * FROM table WHERE id = ?");
  $stmt->execute([$id]);
  $result = $stmt->fetch(PDO::FETCH_ASSOC);
  ```
- Use `db()` singleton function (defined in `inc/config.php`)
- Set `PDO::ATTR_EMULATE_PREPARES => false` for security

### Security
- Always escape output with `htmlspecialchars($var, ENT_QUOTES, 'UTF-8')`
- Use prepared statements for all DB queries
- Validate and sanitize all `$_GET`, `$_POST` input
- Use `session_start()` before accessing `$_SESSION`

### Error Handling
- Use try/catch for DB operations and external APIs
- Catch `PDOException` for database errors
- Catch `Throwable` for general errors
- Redirect to `/install/` if DB connection fails

### Routing (index.php)
- Route order matters - specific routes must come before generic ones
- Single post route (`/categoria/post/`) must be after special routes
- Use `$_GET['page']`, `$_GET['slug']`, `$_GET['post']` for template variables
- Set `$templateFile` to load the appropriate template

### Templates
- Theme loaded from `system_settings.site_theme`
- Template path: `template/{THEME}/`
- Include `inc/header.php` and `inc/footer.php` for page wrapper
- Use `ob_start()`/`ob_get_clean()` for content buffering

### Helpers (in inc/config.php)
- `db()` - PDO singleton connection
- `setFlash(string $type, string $message)` - Set session flash message
- `renderAdsenseBlock(int $position)` - Render AdSense blocks
- `render_post_content(string $html)` - Convert Quill classes to inline styles
- `renderPopup()` - Render active popup

### Session Management
- Use `setFlash()` helper in `inc/config.php` for flash messages
- Always check `session_status() === PHP_SESSION_NONE` before `session_start()`

### Common Patterns
```php
// Include config first
require_once __DIR__ . '/inc/config.php';

// Check for DB connection
if (!db()) {
    header('Location: /install/');
    exit;
}

// Return early pattern for API endpoints
$json = json_encode(['success' => true, 'data' => $data]);
header('Content-Type: application/json');
echo $json;
exit;
```

## Important Quirks
- Theme is read from `system_settings.site_theme` (not a config file)
- DB connection auto-redirects to `/install/` if `url_bd.php` missing
- Uses PHP sessions - `setFlash()` helper in `inc/config.php`
- AdSense renders via `renderAdsenseBlock(position)` function
- Mercado Pago webhooks: `/actions/mp_webhook.php`
- Route order matters in index.php

## Database Tables (common)
- `usuarios` - User accounts (has `borrado` soft-delete flag)
- `roles` - User roles
- `system_settings` - Site configuration
- `posts` - News articles
- `categorias` - Categories
- `multimedia` - Media files
- `popups` - Popup configurations
- `ads` - Ad placements

## Adding New Features
1. Create new template in `template/{THEME}/`
2. Add route in `index.php` (place before generic single-post route)
3. Add helper functions in `inc/config.php` if needed
4. Create action endpoints in `actions/` directory
