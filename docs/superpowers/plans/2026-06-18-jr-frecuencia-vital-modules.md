# JR FRECUENCIA VITAL — Nuevos Módulos: Plan de Implementación

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Agregar 6 módulos nuevos (Programación, Programas, Suscripción, Contacto backend, Páginas Legales, Menú actualizado) al sistema NEWS sin tocar funcionalidad existente.

**Architecture:** PHP puro + PDO. Tablas nuevas via `db_repair.php` (migración automática). Plantillas públicas en `template/Artemis/`. Admin en `admin/{módulo}/`. El router `index.php` ya maneja rutas de 1 parte automáticamente — solo necesita una ruta explícita nueva para `/programas/{slug}/`.

**Tech Stack:** PHP 8+, PDO/MySQL, Bootstrap 5 (admin), CSS variables Artemis (frontend), jQuery + SweetAlert2 (admin), Fetch API (formularios públicos).

---

## Mapa de archivos

### Crear
| Archivo | Responsabilidad |
|---------|----------------|
| `admin/radio/index.php` | Listado de programas de radio |
| `admin/radio/create.php` | Formulario crear programa |
| `admin/radio/edit.php` | Formulario editar programa |
| `admin/radio/delete.php` | Endpoint AJAX eliminar programa |
| `admin/radio/schedules.php` | CRUD parrilla semanal |
| `admin/radio/schedule_delete.php` | Endpoint AJAX eliminar slot de parrilla |
| `admin/subscribers/index.php` | Listado + acciones de suscriptores |
| `admin/contact/index.php` | Listado mensajes de contacto |
| `admin/legal/index.php` | Editor páginas legales (2 tabs) |
| `actions/contact.php` | POST handler mensajes de contacto |
| `actions/subscribe.php` | POST handler suscripción newsletter |
| `template/Artemis/contacto.php` | Página pública de contacto |
| `template/Artemis/suscripcion.php` | Página pública de suscripción |
| `template/Artemis/aviso-legal.php` | Página pública aviso legal |
| `template/Artemis/politica-privacidad.php` | Página pública política de privacidad |
| `template/Artemis/programas.php` | Listado público de programas |
| `template/Artemis/programa-single.php` | Detalle público de un programa |
| `template/Artemis/programacion.php` | Parrilla semanal pública |

### Modificar
| Archivo | Cambio |
|---------|--------|
| `admin/inc/db_repair.php` | +5 tablas, +4 permisos |
| `index.php` | +1 ruta explícita `/programas/{slug}/` |
| `admin/inc/menu.php` | +4 entradas nuevas |
| `template/Artemis/inc/menu-header.php` | +nuevos ítems condicionalmente |

---

## Task 1: Tablas y permisos en db_repair.php

**Files:**
- Modify: `admin/inc/db_repair.php`

- [ ] **Step 1: Agregar 4 nuevos permisos al array `$newPermissions`**

Localiza la línea `[25, 'Gestionar Comentarios', 'Contenido'],` en `db_repair.php` y agrega justo después:

```php
            [26, 'Gestionar Radio',           'Contenido'],
            [27, 'Gestionar Suscriptores',    'Contenido'],
            [28, 'Gestionar Mensajes',        'Contenido'],
            [29, 'Gestionar Páginas Legales', 'Configuración'],
```

- [ ] **Step 2: Agregar creación de 5 tablas nuevas**

Localiza el comentario `// SECCIÓN 3: COLUMNAS` en `db_repair.php` e inserta este bloque ANTES de él (después del foreach de permisos, dentro del try principal):

```php
        // =====================================================================
        // SECCIÓN 2: TABLAS NUEVAS
        // =====================================================================
        $newTables = [
            'programs' => "CREATE TABLE IF NOT EXISTS `programs` (
                `id`            INT AUTO_INCREMENT PRIMARY KEY,
                `title`         VARCHAR(255) NOT NULL,
                `slug`          VARCHAR(255) NOT NULL UNIQUE,
                `description`   TEXT,
                `image`         VARCHAR(500),
                `category`      VARCHAR(100),
                `hosts`         VARCHAR(255),
                `schedule_info` VARCHAR(255),
                `status`        ENUM('active','inactive') DEFAULT 'active',
                `created_at`    DATETIME DEFAULT CURRENT_TIMESTAMP
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",

            'schedules' => "CREATE TABLE IF NOT EXISTS `schedules` (
                `id`          INT AUTO_INCREMENT PRIMARY KEY,
                `program_id`  INT NOT NULL,
                `day_of_week` ENUM('lunes','martes','miercoles','jueves','viernes','sabado','domingo') NOT NULL,
                `start_time`  TIME NOT NULL,
                `end_time`    TIME NOT NULL,
                `host`        VARCHAR(255),
                `status`      ENUM('active','inactive') DEFAULT 'active',
                `created_at`  DATETIME DEFAULT CURRENT_TIMESTAMP,
                FOREIGN KEY (`program_id`) REFERENCES `programs`(`id`) ON DELETE CASCADE
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",

            'subscribers' => "CREATE TABLE IF NOT EXISTS `subscribers` (
                `id`               INT AUTO_INCREMENT PRIMARY KEY,
                `name`             VARCHAR(255) NOT NULL,
                `email`            VARCHAR(255) NOT NULL UNIQUE,
                `privacy_accepted` TINYINT(1) DEFAULT 0,
                `status`           ENUM('active','inactive') DEFAULT 'active',
                `created_at`       DATETIME DEFAULT CURRENT_TIMESTAMP
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",

            'contact_messages' => "CREATE TABLE IF NOT EXISTS `contact_messages` (
                `id`         INT AUTO_INCREMENT PRIMARY KEY,
                `name`       VARCHAR(255) NOT NULL,
                `email`      VARCHAR(255) NOT NULL,
                `phone`      VARCHAR(50),
                `message`    TEXT NOT NULL,
                `status`     ENUM('unread','read') DEFAULT 'unread',
                `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",

            'legal_pages' => "CREATE TABLE IF NOT EXISTS `legal_pages` (
                `id`         INT AUTO_INCREMENT PRIMARY KEY,
                `slug`       VARCHAR(100) NOT NULL UNIQUE,
                `title`      VARCHAR(255) NOT NULL,
                `content`    LONGTEXT,
                `updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",
        ];

        foreach ($newTables as $tableName => $createSQL) {
            try {
                $check = db()->prepare("
                    SELECT COUNT(*) FROM information_schema.TABLES
                    WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = ?
                ");
                $check->execute([$tableName]);
                if ($check->fetchColumn() == 0) {
                    db()->exec($createSQL);
                    $results['tables'][] = "Creada tabla: $tableName";
                } else {
                    $results['tables'][] = "Ya existe tabla: $tableName";
                }
            } catch (Exception $e) {
                $results['errors'][] = "Error tabla $tableName: " . $e->getMessage();
            }
        }

        // Seed filas fijas en legal_pages
        try {
            db()->exec("INSERT IGNORE INTO `legal_pages` (`slug`, `title`, `content`) VALUES
                ('aviso-legal', 'Aviso Legal', ''),
                ('politica-privacidad', 'Política de Privacidad', '')
            ");
        } catch (Exception $e) {
            $results['errors'][] = "Error seed legal_pages: " . $e->getMessage();
        }
```

- [ ] **Step 3: Verificar sintaxis**

```bash
php -l admin/inc/db_repair.php
```
Expected: `No syntax errors detected in admin/inc/db_repair.php`

- [ ] **Step 4: Ejecutar la migración**

Abre en el navegador: `http://tu-sitio/admin/config/repair_db.php`

Verifica que en los resultados aparezca:
- "Creada tabla: programs"
- "Creada tabla: schedules"
- "Creada tabla: subscribers"
- "Creada tabla: contact_messages"
- "Creada tabla: legal_pages"
- Permisos 26–29 creados

- [ ] **Step 5: Commit**

```bash
git add admin/inc/db_repair.php
git commit -m "feat: add DB tables and permissions for radio/subscribers/contact/legal modules"
```

---

## Task 2: Ruta explícita para /programas/{slug}/

**Files:**
- Modify: `index.php`

- [ ] **Step 1: Agregar ruta antes del catch-all de 2 partes**

Localiza esta línea en `index.php`:

```php
} elseif (count($parts) === 2 && !empty($parts[0]) && !empty($parts[1])) {
```

Inserta el siguiente bloque **justo antes** de ella:

```php
// ===============================
// Programa individual: /programas/slug/
// ===============================
} elseif ($parts[0] === 'programas' && isset($parts[1]) && !empty($parts[1])) {
    $_GET['page']         = 'programas';
    $_GET['program_slug'] = $parts[1];
    $templateFile         = __DIR__ . "/template/" . THEME . "/programa-single.php";

```

- [ ] **Step 2: Verificar sintaxis**

```bash
php -l index.php
```
Expected: `No syntax errors detected in index.php`

- [ ] **Step 3: Commit**

```bash
git add index.php
git commit -m "feat: add explicit route for /programas/{slug}/"
```

---

## Task 3: Action — Guardar mensajes de contacto

**Files:**
- Create: `actions/contact.php`

- [ ] **Step 1: Crear el archivo**

```php
<?php
require_once __DIR__ . '/../inc/config.php';
header('Content-Type: application/json; charset=utf-8');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['ok' => false, 'msg' => 'Método no permitido']);
    exit;
}

// Honeypot
if (!empty($_POST['website'])) {
    echo json_encode(['ok' => true, 'msg' => 'Mensaje enviado']);
    exit;
}

$name    = trim($_POST['name']    ?? '');
$email   = trim($_POST['email']   ?? '');
$phone   = trim($_POST['phone']   ?? '');
$message = trim($_POST['message'] ?? '');

$errors = [];
if (empty($name))                          $errors[] = 'El nombre es obligatorio';
if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = 'Correo inválido';
if (empty($message))                       $errors[] = 'El mensaje es obligatorio';
if (mb_strlen($message) > 2000)            $errors[] = 'El mensaje es demasiado largo';

if ($errors) {
    echo json_encode(['ok' => false, 'msg' => implode('. ', $errors)]);
    exit;
}

try {
    $stmt = db()->prepare("
        INSERT INTO contact_messages (name, email, phone, message)
        VALUES (?, ?, ?, ?)
    ");
    $stmt->execute([
        mb_substr($name, 0, 255),
        mb_substr($email, 0, 255),
        mb_substr($phone, 0, 50),
        mb_substr($message, 0, 2000),
    ]);
    echo json_encode(['ok' => true, 'msg' => '¡Mensaje enviado! Te responderemos pronto.']);
} catch (Throwable $e) {
    http_response_code(500);
    echo json_encode(['ok' => false, 'msg' => 'Error al enviar el mensaje. Inténtalo de nuevo.']);
}
```

- [ ] **Step 2: Verificar sintaxis**

```bash
php -l actions/contact.php
```
Expected: `No syntax errors detected in actions/contact.php`

- [ ] **Step 3: Test rápido con curl**

```bash
curl -s -X POST http://tu-sitio/actions/contact.php \
  -d "name=Test&email=test@test.com&message=Hola"
```
Expected: `{"ok":true,"msg":"¡Mensaje enviado! Te responderemos pronto."}`

- [ ] **Step 4: Commit**

```bash
git add actions/contact.php
git commit -m "feat: add contact form action handler"
```

---

## Task 4: Action — Guardar suscriptores

**Files:**
- Create: `actions/subscribe.php`

- [ ] **Step 1: Crear el archivo**

```php
<?php
require_once __DIR__ . '/../inc/config.php';
header('Content-Type: application/json; charset=utf-8');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['ok' => false, 'msg' => 'Método no permitido']);
    exit;
}

// Honeypot
if (!empty($_POST['website'])) {
    echo json_encode(['ok' => true, 'msg' => '¡Suscripción completada!']);
    exit;
}

$name             = trim($_POST['name']    ?? '');
$email            = trim($_POST['email']   ?? '');
$privacyAccepted  = !empty($_POST['privacy']) ? 1 : 0;

$errors = [];
if (empty($name))                                              $errors[] = 'El nombre es obligatorio';
if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = 'Correo inválido';
if (!$privacyAccepted)                                        $errors[] = 'Debes aceptar la política de privacidad';

if ($errors) {
    echo json_encode(['ok' => false, 'msg' => implode('. ', $errors)]);
    exit;
}

// Verificar duplicado
try {
    $check = db()->prepare("SELECT id FROM subscribers WHERE email = ?");
    $check->execute([mb_strtolower($email)]);
    if ($check->fetch()) {
        echo json_encode(['ok' => false, 'msg' => 'Este correo ya está suscrito.']);
        exit;
    }
} catch (Throwable $e) {
    http_response_code(500);
    echo json_encode(['ok' => false, 'msg' => 'Error interno. Inténtalo de nuevo.']);
    exit;
}

try {
    $stmt = db()->prepare("
        INSERT INTO subscribers (name, email, privacy_accepted)
        VALUES (?, ?, ?)
    ");
    $stmt->execute([
        mb_substr($name, 0, 255),
        mb_strtolower(mb_substr($email, 0, 255)),
        $privacyAccepted,
    ]);
    echo json_encode(['ok' => true, 'msg' => '¡Suscripción completada! Gracias por unirte.']);
} catch (Throwable $e) {
    http_response_code(500);
    echo json_encode(['ok' => false, 'msg' => 'Error al procesar la suscripción. Inténtalo de nuevo.']);
}
```

- [ ] **Step 2: Verificar sintaxis**

```bash
php -l actions/subscribe.php
```
Expected: `No syntax errors detected`

- [ ] **Step 3: Test con curl**

```bash
curl -s -X POST http://tu-sitio/actions/subscribe.php \
  -d "name=Test&email=test@test.com&privacy=1"
```
Expected: `{"ok":true,"msg":"¡Suscripción completada! Gracias por unirte."}`

Segunda llamada con el mismo email:
Expected: `{"ok":false,"msg":"Este correo ya está suscrito."}`

- [ ] **Step 4: Commit**

```bash
git add actions/subscribe.php
git commit -m "feat: add newsletter subscription action handler"
```

---

## Task 5: Template público — Contacto

**Files:**
- Create: `template/Artemis/contacto.php`

- [ ] **Step 1: Crear el template**

```php
<?php
$page_title       = 'Contacto | ' . NOMBRE_SITIO;
$page_description = 'Contáctanos para cualquier consulta, comentario o sugerencia.';
$currentPath      = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$page_canonical   = rtrim(URLBASE, '/') . '/' . ltrim($currentPath, '/');
?>

<section class="py-5" style="background: var(--dark); min-height: 60vh;">
    <div class="container">
        <div class="row mb-4 text-center">
            <div class="col-12">
                <h1 class="section-title" style="color: var(--text-color);">CONTACTO</h1>
                <p style="color: var(--text-muted);">¿Tienes alguna pregunta? Escríbenos</p>
            </div>
        </div>

        <div class="row">
            <div class="col-lg-8 mx-auto">
                <div style="background: var(--dark-secondary); border-radius: 20px; padding: 40px;">

                    <div id="contact-message" class="mb-3" style="display:none;"></div>

                    <form id="contactForm" novalidate>
                        <!-- Honeypot -->
                        <div style="display:none;"><input type="text" name="website" tabindex="-1" autocomplete="off"></div>

                        <div class="mb-4">
                            <label style="color: var(--text-color); margin-bottom: 8px; display: block;">Nombre *</label>
                            <input type="text" name="name" class="search-input" style="width:100%;" placeholder="Tu nombre completo" required>
                        </div>
                        <div class="mb-4">
                            <label style="color: var(--text-color); margin-bottom: 8px; display: block;">Correo electrónico *</label>
                            <input type="email" name="email" class="search-input" style="width:100%;" placeholder="tu@correo.com" required>
                        </div>
                        <div class="mb-4">
                            <label style="color: var(--text-color); margin-bottom: 8px; display: block;">Teléfono <span style="color:var(--text-muted);font-size:.85em;">(opcional)</span></label>
                            <input type="tel" name="phone" class="search-input" style="width:100%;" placeholder="+34 600 000 000">
                        </div>
                        <div class="mb-4">
                            <label style="color: var(--text-color); margin-bottom: 8px; display: block;">Mensaje *</label>
                            <textarea name="message" class="search-input" style="width:100%; min-height:150px; resize:vertical;" placeholder="Escribe tu mensaje..." required></textarea>
                        </div>
                        <button type="submit" id="contactBtn" class="btn-artemis w-100">
                            <i class="fas fa-paper-plane mr-2"></i> Enviar mensaje
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</section>

<script>
document.getElementById('contactForm').addEventListener('submit', function(e) {
    e.preventDefault();
    const btn = document.getElementById('contactBtn');
    const msg = document.getElementById('contact-message');
    btn.disabled = true;
    btn.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i> Enviando...';

    fetch('<?= URLBASE ?>/actions/contact.php', {
        method: 'POST',
        body: new FormData(this)
    })
    .then(r => r.json())
    .then(data => {
        msg.style.display = 'block';
        msg.style.padding = '12px 16px';
        msg.style.borderRadius = '8px';
        if (data.ok) {
            msg.style.background = '#d4edda';
            msg.style.color = '#155724';
            msg.innerHTML = '<i class="fas fa-check-circle mr-2"></i>' + data.msg;
            document.getElementById('contactForm').reset();
        } else {
            msg.style.background = '#f8d7da';
            msg.style.color = '#721c24';
            msg.innerHTML = '<i class="fas fa-exclamation-circle mr-2"></i>' + data.msg;
        }
    })
    .catch(() => {
        msg.style.display = 'block';
        msg.style.background = '#f8d7da';
        msg.style.color = '#721c24';
        msg.innerHTML = 'Error de conexión. Inténtalo de nuevo.';
    })
    .finally(() => {
        btn.disabled = false;
        btn.innerHTML = '<i class="fas fa-paper-plane mr-2"></i> Enviar mensaje';
    });
});
</script>
```

- [ ] **Step 2: Verificar sintaxis**

```bash
php -l template/Artemis/contacto.php
```

- [ ] **Step 3: Verificar en navegador**

Abre `http://tu-sitio/contacto/` — debe renderizar el formulario sin errores.
Envía el formulario con datos válidos — debe mostrar mensaje verde de éxito.
Verifica en BD: `SELECT * FROM contact_messages ORDER BY id DESC LIMIT 1;`

- [ ] **Step 4: Commit**

```bash
git add template/Artemis/contacto.php
git commit -m "feat: add contact page with functional form and AJAX submission"
```

---

## Task 6: Template público — Suscripción

**Files:**
- Create: `template/Artemis/suscripcion.php`

- [ ] **Step 1: Crear el template**

```php
<?php
$page_title       = 'Suscripción | ' . NOMBRE_SITIO;
$page_description = 'Suscríbete a nuestro boletín y recibe las últimas noticias de ' . NOMBRE_SITIO . '.';
$currentPath      = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$page_canonical   = rtrim(URLBASE, '/') . '/' . ltrim($currentPath, '/');
?>

<section class="py-5" style="background: var(--dark); min-height: 60vh;">
    <div class="container">
        <div class="row mb-4 text-center">
            <div class="col-12">
                <h1 class="section-title" style="color: var(--text-color);">SUSCRIPCIÓN</h1>
                <p style="color: var(--text-muted);">Únete a nuestra comunidad y mantente informado</p>
            </div>
        </div>

        <div class="row">
            <div class="col-lg-6 mx-auto">
                <div style="background: var(--dark-secondary); border-radius: 20px; padding: 40px; text-align:center;">

                    <i class="fas fa-envelope-open-text" style="font-size:3rem; color: var(--primary-color, #e21f0c); margin-bottom:20px;"></i>

                    <div id="sub-message" class="mb-3" style="display:none;"></div>

                    <form id="subscribeForm" novalidate>
                        <!-- Honeypot -->
                        <div style="display:none;"><input type="text" name="website" tabindex="-1" autocomplete="off"></div>

                        <div class="mb-4 text-left">
                            <label style="color: var(--text-color); margin-bottom: 8px; display: block;">Nombre *</label>
                            <input type="text" name="name" class="search-input" style="width:100%;" placeholder="Tu nombre" required>
                        </div>
                        <div class="mb-4 text-left">
                            <label style="color: var(--text-color); margin-bottom: 8px; display: block;">Correo electrónico *</label>
                            <input type="email" name="email" class="search-input" style="width:100%;" placeholder="tu@correo.com" required>
                        </div>
                        <div class="mb-4 text-left">
                            <label style="color: var(--text-color); cursor:pointer; display:flex; align-items:flex-start; gap:10px;">
                                <input type="checkbox" name="privacy" required style="margin-top:3px; flex-shrink:0;">
                                <span style="font-size:.9em;">
                                    He leído y acepto la
                                    <a href="<?= URLBASE ?>/politica-privacidad/" style="color: var(--primary-color, #e21f0c);" target="_blank">Política de Privacidad</a>
                                    y consiento el tratamiento de mis datos personales. *
                                </span>
                            </label>
                        </div>
                        <button type="submit" id="subBtn" class="btn-artemis w-100">
                            <i class="fas fa-bell mr-2"></i> Suscribirme
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</section>

<script>
document.getElementById('subscribeForm').addEventListener('submit', function(e) {
    e.preventDefault();
    const btn = document.getElementById('subBtn');
    const msg = document.getElementById('sub-message');
    btn.disabled = true;
    btn.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i> Procesando...';

    fetch('<?= URLBASE ?>/actions/subscribe.php', {
        method: 'POST',
        body: new FormData(this)
    })
    .then(r => r.json())
    .then(data => {
        msg.style.display = 'block';
        msg.style.padding = '12px 16px';
        msg.style.borderRadius = '8px';
        if (data.ok) {
            msg.style.background = '#d4edda';
            msg.style.color = '#155724';
            msg.innerHTML = '<i class="fas fa-check-circle mr-2"></i>' + data.msg;
            document.getElementById('subscribeForm').style.display = 'none';
        } else {
            msg.style.background = '#f8d7da';
            msg.style.color = '#721c24';
            msg.innerHTML = '<i class="fas fa-exclamation-circle mr-2"></i>' + data.msg;
            btn.disabled = false;
            btn.innerHTML = '<i class="fas fa-bell mr-2"></i> Suscribirme';
        }
    })
    .catch(() => {
        msg.style.display = 'block';
        msg.style.background = '#f8d7da';
        msg.style.color = '#721c24';
        msg.innerHTML = 'Error de conexión. Inténtalo de nuevo.';
        btn.disabled = false;
        btn.innerHTML = '<i class="fas fa-bell mr-2"></i> Suscribirme';
    });
});
</script>
```

- [ ] **Step 2: Verificar sintaxis**

```bash
php -l template/Artemis/suscripcion.php
```

- [ ] **Step 3: Verificar en navegador**

Abre `http://tu-sitio/suscripcion/` — debe renderizar el formulario.
Suscribe con datos válidos + checkbox marcado — debe mostrar mensaje de éxito y ocultar el formulario.
Verifica en BD: `SELECT * FROM subscribers ORDER BY id DESC LIMIT 1;`

- [ ] **Step 4: Commit**

```bash
git add template/Artemis/suscripcion.php
git commit -m "feat: add subscription page with GDPR checkbox and AJAX submission"
```

---

## Task 7: Templates públicos — Páginas legales

**Files:**
- Create: `template/Artemis/aviso-legal.php`
- Create: `template/Artemis/politica-privacidad.php`

- [ ] **Step 1: Crear aviso-legal.php**

```php
<?php
$legalPage = null;
try {
    $stmt = db()->prepare("SELECT * FROM legal_pages WHERE slug = 'aviso-legal' LIMIT 1");
    $stmt->execute();
    $legalPage = $stmt->fetch();
} catch (Throwable $e) {}

$page_title       = (!empty($legalPage['title']) ? $legalPage['title'] : 'Aviso Legal') . ' | ' . NOMBRE_SITIO;
$page_description = 'Aviso legal de ' . NOMBRE_SITIO . '.';
$currentPath      = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$page_canonical   = rtrim(URLBASE, '/') . '/' . ltrim($currentPath, '/');
?>

<section class="py-5" style="background: var(--dark); min-height: 60vh;">
    <div class="container">
        <div class="row mb-4">
            <div class="col-12">
                <h1 class="section-title" style="color: var(--text-color);">
                    <?= htmlspecialchars($legalPage['title'] ?? 'Aviso Legal') ?>
                </h1>
                <?php if (!empty($legalPage['updated_at'])): ?>
                    <p style="color: var(--text-muted); font-size:.85em;">
                        Última actualización: <?= date('d/m/Y', strtotime($legalPage['updated_at'])) ?>
                    </p>
                <?php endif; ?>
            </div>
        </div>
        <div class="row">
            <div class="col-lg-10 mx-auto">
                <div style="background: var(--dark-secondary); border-radius: 16px; padding: 40px; color: var(--text-color); line-height: 1.8;">
                    <?php if (!empty($legalPage['content'])): ?>
                        <?= $legalPage['content'] ?>
                    <?php else: ?>
                        <p style="color: var(--text-muted); text-align:center;">Contenido en preparación.</p>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</section>
```

- [ ] **Step 2: Crear politica-privacidad.php**

```php
<?php
$legalPage = null;
try {
    $stmt = db()->prepare("SELECT * FROM legal_pages WHERE slug = 'politica-privacidad' LIMIT 1");
    $stmt->execute();
    $legalPage = $stmt->fetch();
} catch (Throwable $e) {}

$page_title       = (!empty($legalPage['title']) ? $legalPage['title'] : 'Política de Privacidad') . ' | ' . NOMBRE_SITIO;
$page_description = 'Política de privacidad y protección de datos de ' . NOMBRE_SITIO . '.';
$currentPath      = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$page_canonical   = rtrim(URLBASE, '/') . '/' . ltrim($currentPath, '/');
?>

<section class="py-5" style="background: var(--dark); min-height: 60vh;">
    <div class="container">
        <div class="row mb-4">
            <div class="col-12">
                <h1 class="section-title" style="color: var(--text-color);">
                    <?= htmlspecialchars($legalPage['title'] ?? 'Política de Privacidad') ?>
                </h1>
                <?php if (!empty($legalPage['updated_at'])): ?>
                    <p style="color: var(--text-muted); font-size:.85em;">
                        Última actualización: <?= date('d/m/Y', strtotime($legalPage['updated_at'])) ?>
                    </p>
                <?php endif; ?>
            </div>
        </div>
        <div class="row">
            <div class="col-lg-10 mx-auto">
                <div style="background: var(--dark-secondary); border-radius: 16px; padding: 40px; color: var(--text-color); line-height: 1.8;">
                    <?php if (!empty($legalPage['content'])): ?>
                        <?= $legalPage['content'] ?>
                    <?php else: ?>
                        <p style="color: var(--text-muted); text-align:center;">Contenido en preparación.</p>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</section>
```

- [ ] **Step 3: Verificar sintaxis**

```bash
php -l template/Artemis/aviso-legal.php && php -l template/Artemis/politica-privacidad.php
```

- [ ] **Step 4: Verificar en navegador**

- `http://tu-sitio/aviso-legal/` → debe mostrar "Contenido en preparación" (hasta que se edite desde admin)
- `http://tu-sitio/politica-privacidad/` → ídem

- [ ] **Step 5: Commit**

```bash
git add template/Artemis/aviso-legal.php template/Artemis/politica-privacidad.php
git commit -m "feat: add legal pages (aviso-legal, politica-privacidad)"
```

---

## Task 8: Templates públicos — Programas

**Files:**
- Create: `template/Artemis/programas.php`
- Create: `template/Artemis/programa-single.php`

- [ ] **Step 1: Crear programas.php**

```php
<?php
$page_title       = 'Programas | ' . NOMBRE_SITIO;
$page_description = 'Conoce todos los programas de ' . NOMBRE_SITIO . '.';
$currentPath      = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$page_canonical   = rtrim(URLBASE, '/') . '/' . ltrim($currentPath, '/');

$programs = [];
try {
    $programs = db()->query("
        SELECT * FROM programs WHERE status = 'active' ORDER BY title ASC
    ")->fetchAll();
} catch (Throwable $e) {}
?>

<section class="py-5" style="background: var(--dark); min-height: 60vh;">
    <div class="container">
        <div class="row mb-4 text-center">
            <div class="col-12">
                <h1 class="section-title" style="color: var(--text-color);">PROGRAMAS</h1>
                <p style="color: var(--text-muted);">Toda nuestra parrilla de contenido</p>
            </div>
        </div>

        <?php if (empty($programs)): ?>
            <div class="row"><div class="col-12 text-center py-5">
                <i class="fas fa-radio" style="font-size:3rem; color: var(--text-muted);"></i>
                <p style="color: var(--text-muted); margin-top:15px;">Próximamente nuestros programas.</p>
            </div></div>
        <?php else: ?>
        <div class="row">
            <?php foreach ($programs as $prog): ?>
            <div class="col-md-6 col-lg-4 mb-4">
                <a href="<?= URLBASE ?>/programas/<?= htmlspecialchars($prog['slug']) ?>/"
                   style="text-decoration:none;">
                    <div style="background: var(--dark-secondary); border-radius: 16px; overflow:hidden; height:100%; transition: transform .2s;"
                         onmouseover="this.style.transform='translateY(-4px)'"
                         onmouseout="this.style.transform='translateY(0)'">
                        <?php if (!empty($prog['image'])): ?>
                            <img src="<?= htmlspecialchars(rtrim(URLBASE,'/').'/'.$prog['image']) ?>"
                                 alt="<?= htmlspecialchars($prog['title']) ?>"
                                 style="width:100%; height:200px; object-fit:cover;">
                        <?php else: ?>
                            <div style="width:100%; height:200px; background: var(--dark); display:flex; align-items:center; justify-content:center;">
                                <i class="fas fa-microphone" style="font-size:3rem; color: var(--text-muted);"></i>
                            </div>
                        <?php endif; ?>
                        <div style="padding: 20px;">
                            <?php if (!empty($prog['category'])): ?>
                                <span style="background: var(--primary-color, #e21f0c); color:#fff; font-size:.75em; padding:3px 10px; border-radius:20px;">
                                    <?= htmlspecialchars($prog['category']) ?>
                                </span>
                            <?php endif; ?>
                            <h2 style="color: var(--text-color); margin: 10px 0 8px; font-size:1.1rem;">
                                <?= htmlspecialchars($prog['title']) ?>
                            </h2>
                            <?php if (!empty($prog['hosts'])): ?>
                                <p style="color: var(--text-muted); font-size:.85em; margin:0;">
                                    <i class="fas fa-user mr-1"></i><?= htmlspecialchars($prog['hosts']) ?>
                                </p>
                            <?php endif; ?>
                        </div>
                    </div>
                </a>
            </div>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>
    </div>
</section>
```

- [ ] **Step 2: Crear programa-single.php**

```php
<?php
$programSlug = $_GET['program_slug'] ?? '';
$program     = null;
$schedules   = [];

try {
    $stmt = db()->prepare("SELECT * FROM programs WHERE slug = ? AND status = 'active' LIMIT 1");
    $stmt->execute([$programSlug]);
    $program = $stmt->fetch();
} catch (Throwable $e) {}

if (!$program) {
    http_response_code(404);
    $templateFile = __DIR__ . '/404.php';
    if (file_exists($templateFile)) include $templateFile;
    exit;
}

try {
    $stmt = db()->prepare("
        SELECT * FROM schedules WHERE program_id = ? AND status = 'active'
        ORDER BY FIELD(day_of_week,'lunes','martes','miercoles','jueves','viernes','sabado','domingo'), start_time
    ");
    $stmt->execute([$program['id']]);
    $schedules = $stmt->fetchAll();
} catch (Throwable $e) {}

$page_title       = htmlspecialchars($program['title']) . ' | ' . NOMBRE_SITIO;
$page_description = !empty($program['description']) ? mb_substr(strip_tags($program['description']), 0, 160) : 'Programa de ' . NOMBRE_SITIO;
$currentPath      = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$page_canonical   = rtrim(URLBASE, '/') . '/' . ltrim($currentPath, '/');
?>

<section class="py-5" style="background: var(--dark);">
    <div class="container">
        <div class="row align-items-center mb-5">
            <div class="col-lg-5 mb-4 mb-lg-0">
                <?php if (!empty($program['image'])): ?>
                    <img src="<?= htmlspecialchars(rtrim(URLBASE,'/').'/'.$program['image']) ?>"
                         alt="<?= htmlspecialchars($program['title']) ?>"
                         style="width:100%; border-radius:16px; object-fit:cover; max-height:360px;">
                <?php else: ?>
                    <div style="width:100%; height:300px; background: var(--dark-secondary); border-radius:16px; display:flex; align-items:center; justify-content:center;">
                        <i class="fas fa-microphone" style="font-size:4rem; color: var(--text-muted);"></i>
                    </div>
                <?php endif; ?>
            </div>
            <div class="col-lg-7">
                <?php if (!empty($program['category'])): ?>
                    <span style="background: var(--primary-color, #e21f0c); color:#fff; font-size:.8em; padding:4px 14px; border-radius:20px;">
                        <?= htmlspecialchars($program['category']) ?>
                    </span>
                <?php endif; ?>
                <h1 style="color: var(--text-color); margin: 14px 0 10px; font-size:2rem;">
                    <?= htmlspecialchars($program['title']) ?>
                </h1>
                <?php if (!empty($program['hosts'])): ?>
                    <p style="color: var(--text-muted);">
                        <i class="fas fa-user mr-2"></i><?= htmlspecialchars($program['hosts']) ?>
                    </p>
                <?php endif; ?>
                <?php if (!empty($program['description'])): ?>
                    <div style="color: var(--text-color); line-height:1.8; margin-top:16px;">
                        <?= nl2br(htmlspecialchars($program['description'])) ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <?php if (!empty($schedules)): ?>
        <div class="row">
            <div class="col-12">
                <h2 style="color: var(--text-color); margin-bottom:20px; font-size:1.3rem;">
                    <i class="fas fa-clock mr-2" style="color: var(--primary-color, #e21f0c);"></i> Horarios
                </h2>
                <div style="background: var(--dark-secondary); border-radius:12px; overflow:hidden;">
                    <table class="table table-borderless mb-0">
                        <thead>
                            <tr style="background: var(--primary-color, #e21f0c); color:#fff;">
                                <th>Día</th>
                                <th>Inicio</th>
                                <th>Fin</th>
                                <?php if (!empty(array_filter(array_column($schedules, 'host')))): ?>
                                <th>Conductor</th>
                                <?php endif; ?>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($schedules as $slot): ?>
                            <tr style="color: var(--text-color); border-bottom: 1px solid rgba(255,255,255,.05);">
                                <td><?= ucfirst(htmlspecialchars($slot['day_of_week'])) ?></td>
                                <td><?= htmlspecialchars(substr($slot['start_time'], 0, 5)) ?></td>
                                <td><?= htmlspecialchars(substr($slot['end_time'], 0, 5)) ?></td>
                                <?php if (!empty(array_filter(array_column($schedules, 'host')))): ?>
                                <td><?= htmlspecialchars($slot['host'] ?? '') ?></td>
                                <?php endif; ?>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        <?php endif; ?>
    </div>
</section>
```

- [ ] **Step 3: Verificar sintaxis**

```bash
php -l template/Artemis/programas.php && php -l template/Artemis/programa-single.php
```

- [ ] **Step 4: Verificar en navegador**

`http://tu-sitio/programas/` — debe mostrar "Próximamente nuestros programas." (BD vacía).

- [ ] **Step 5: Commit**

```bash
git add template/Artemis/programas.php template/Artemis/programa-single.php
git commit -m "feat: add public programs list and single program templates"
```

---

## Task 9: Template público — Programación (parrilla semanal)

**Files:**
- Create: `template/Artemis/programacion.php`

- [ ] **Step 1: Crear programacion.php**

```php
<?php
$page_title       = 'Programación | ' . NOMBRE_SITIO;
$page_description = 'Parrilla de programación de ' . NOMBRE_SITIO . '. Consulta los horarios de todos nuestros programas.';
$currentPath      = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$page_canonical   = rtrim(URLBASE, '/') . '/' . ltrim($currentPath, '/');

$days = ['lunes','martes','miercoles','jueves','viernes','sabado','domingo'];
$dayLabels = ['lunes'=>'Lunes','martes'=>'Martes','miercoles'=>'Miércoles','jueves'=>'Jueves','viernes'=>'Viernes','sabado'=>'Sábado','domingo'=>'Domingo'];

// Día activo: día actual en español
$todayMap = [1=>'lunes',2=>'martes',3=>'miercoles',4=>'jueves',5=>'viernes',6=>'sabado',7=>'domingo'];
$activeDay = $todayMap[(int)date('N')] ?? 'lunes';

// Hora actual para calcular "en vivo"
$nowTime = date('H:i:s');

// Cargar todos los slots activos con nombre del programa
$allSlots = [];
try {
    $rows = db()->query("
        SELECT s.*, p.title AS program_title, p.slug AS program_slug, p.image AS program_image
        FROM schedules s
        INNER JOIN programs p ON p.id = s.program_id
        WHERE s.status = 'active' AND p.status = 'active'
        ORDER BY FIELD(s.day_of_week,'lunes','martes','miercoles','jueves','viernes','sabado','domingo'), s.start_time
    ")->fetchAll();
    foreach ($rows as $row) {
        $allSlots[$row['day_of_week']][] = $row;
    }
} catch (Throwable $e) {}
?>

<section class="py-5" style="background: var(--dark); min-height: 60vh;">
    <div class="container">
        <div class="row mb-4 text-center">
            <div class="col-12">
                <h1 class="section-title" style="color: var(--text-color);">PROGRAMACIÓN</h1>
                <p style="color: var(--text-muted);">Parrilla semanal — en vivo todos los días</p>
            </div>
        </div>

        <!-- Tabs días -->
        <div class="row mb-3">
            <div class="col-12">
                <div style="display:flex; flex-wrap:wrap; gap:8px; justify-content:center;">
                    <?php foreach ($days as $day): ?>
                    <button onclick="showDay('<?= $day ?>')"
                            id="tab-<?= $day ?>"
                            style="padding:8px 18px; border-radius:30px; border:2px solid var(--primary-color, #e21f0c);
                                   background:<?= $day === $activeDay ? 'var(--primary-color,#e21f0c)' : 'transparent' ?>;
                                   color:<?= $day === $activeDay ? '#fff' : 'var(--text-color)' ?>;
                                   cursor:pointer; font-weight:600; transition:.2s; font-size:.9rem;">
                        <?= $dayLabels[$day] ?>
                    </button>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>

        <!-- Contenido por día -->
        <?php foreach ($days as $day): ?>
        <div id="day-<?= $day ?>" style="display:<?= $day === $activeDay ? 'block' : 'none' ?>;">
            <?php if (empty($allSlots[$day])): ?>
                <div class="text-center py-5">
                    <p style="color: var(--text-muted);">Sin programación registrada para este día.</p>
                </div>
            <?php else: ?>
                <div class="row">
                    <?php foreach ($allSlots[$day] as $slot):
                        $isLive = ($nowTime >= $slot['start_time'] && $nowTime < $slot['end_time'] && $day === $activeDay);
                    ?>
                    <div class="col-md-6 col-lg-4 mb-3">
                        <a href="<?= URLBASE ?>/programas/<?= htmlspecialchars($slot['program_slug']) ?>/"
                           style="text-decoration:none;">
                            <div style="background: var(--dark-secondary); border-radius:12px; padding:20px; height:100%;
                                        <?= $isLive ? 'border: 2px solid var(--primary-color,#e21f0c);' : '' ?>
                                        transition: transform .2s;"
                                 onmouseover="this.style.transform='translateY(-3px)'"
                                 onmouseout="this.style.transform='translateY(0)'">
                                <div style="display:flex; justify-content:space-between; align-items:flex-start; margin-bottom:10px;">
                                    <span style="color: var(--primary-color, #e21f0c); font-weight:700; font-size:1.1rem;">
                                        <?= htmlspecialchars(substr($slot['start_time'],0,5)) ?> — <?= htmlspecialchars(substr($slot['end_time'],0,5)) ?>
                                    </span>
                                    <?php if ($isLive): ?>
                                        <span style="background:#e21f0c; color:#fff; font-size:.7rem; font-weight:700;
                                                     padding:3px 10px; border-radius:20px; animation: pulse 1.5s infinite;">
                                            ● EN VIVO
                                        </span>
                                    <?php endif; ?>
                                </div>
                                <h3 style="color: var(--text-color); margin:0 0 6px; font-size:1rem;">
                                    <?= htmlspecialchars($slot['program_title']) ?>
                                </h3>
                                <?php if (!empty($slot['host'])): ?>
                                    <p style="color: var(--text-muted); font-size:.85em; margin:0;">
                                        <i class="fas fa-user mr-1"></i><?= htmlspecialchars($slot['host']) ?>
                                    </p>
                                <?php endif; ?>
                            </div>
                        </a>
                    </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
        <?php endforeach; ?>
    </div>
</section>

<style>
@keyframes pulse {
    0%, 100% { opacity: 1; }
    50%       { opacity: 0.6; }
}
</style>

<script>
function showDay(day) {
    ['lunes','martes','miercoles','jueves','viernes','sabado','domingo'].forEach(function(d) {
        document.getElementById('day-' + d).style.display = 'none';
        var tab = document.getElementById('tab-' + d);
        tab.style.background = 'transparent';
        tab.style.color = 'var(--text-color)';
    });
    document.getElementById('day-' + day).style.display = 'block';
    var activeTab = document.getElementById('tab-' + day);
    activeTab.style.background = 'var(--primary-color, #e21f0c)';
    activeTab.style.color = '#fff';
}
</script>
```

- [ ] **Step 2: Verificar sintaxis**

```bash
php -l template/Artemis/programacion.php
```

- [ ] **Step 3: Verificar en navegador**

`http://tu-sitio/programacion/` — debe mostrar tabs de días, tab del día actual activo, mensaje "Sin programación registrada" (BD vacía).

- [ ] **Step 4: Commit**

```bash
git add template/Artemis/programacion.php
git commit -m "feat: add weekly schedule page with live highlight"
```

---

## Task 10: Admin — CRUD de Programas de Radio

**Files:**
- Create: `admin/radio/index.php`
- Create: `admin/radio/create.php`
- Create: `admin/radio/edit.php`
- Create: `admin/radio/delete.php`

- [ ] **Step 1: Crear admin/radio/index.php**

```php
<?php
require_once __DIR__ . '/../../inc/config.php';
require_once __DIR__ . '/../login/session.php';
$permisopage = 'Gestionar Radio';
include('../login/restriction.php');
require_once __DIR__ . '/../inc/flash_helpers.php';

$programs = db()->query("SELECT * FROM programs ORDER BY title ASC")->fetchAll();
?>
<!doctype html>
<html lang="es">
<head>
<meta charset="utf-8">
<title>Programas de Radio</title>
<?php include('../inc/header.php'); ?>
</head>
<body>
<?php include('../inc/menu.php'); ?>
<div class="main-content">
    <div class="container-fluid">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h1 class="h3">Programas de Radio</h1>
            <a href="<?= URLBASE ?>/admin/radio/create.php" class="btn btn-primary">
                <i class="fas fa-plus mr-1"></i> Nuevo Programa
            </a>
        </div>
        <?php renderFlashMessages(); ?>
        <div class="card shadow-sm">
            <div class="card-body p-0">
                <table class="table table-hover mb-0">
                    <thead class="thead-light">
                        <tr>
                            <th>Título</th>
                            <th>Categoría</th>
                            <th>Conductores</th>
                            <th>Estado</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php if (empty($programs)): ?>
                        <tr><td colspan="5" class="text-center text-muted py-4">Sin programas. <a href="<?= URLBASE ?>/admin/radio/create.php">Crear primero</a>.</td></tr>
                    <?php else: ?>
                        <?php foreach ($programs as $p): ?>
                        <tr>
                            <td><strong><?= htmlspecialchars($p['title']) ?></strong></td>
                            <td><?= htmlspecialchars($p['category'] ?? '') ?></td>
                            <td><?= htmlspecialchars($p['hosts'] ?? '') ?></td>
                            <td>
                                <span class="badge badge-<?= $p['status'] === 'active' ? 'success' : 'secondary' ?>">
                                    <?= $p['status'] === 'active' ? 'Activo' : 'Inactivo' ?>
                                </span>
                            </td>
                            <td>
                                <a href="<?= URLBASE ?>/admin/radio/edit.php?id=<?= $p['id'] ?>" class="btn btn-sm btn-outline-primary mr-1">
                                    <i class="fas fa-edit"></i>
                                </a>
                                <button class="btn btn-sm btn-outline-danger"
                                        onclick="confirmDelete(<?= $p['id'] ?>, '<?= htmlspecialchars($p['title'], ENT_QUOTES) ?>')">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
<script>
function confirmDelete(id, title) {
    Swal.fire({
        title: '¿Eliminar "' + title + '"?',
        text: 'Se eliminará también su programación asociada.',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#d33',
        cancelButtonText: 'Cancelar',
        confirmButtonText: 'Eliminar'
    }).then(function(result) {
        if (result.isConfirmed) {
            fetch('<?= URLBASE ?>/admin/radio/delete.php', {
                method: 'POST',
                headers: {'Content-Type': 'application/x-www-form-urlencoded'},
                body: 'id=' + id
            })
            .then(r => r.json())
            .then(data => {
                if (data.ok) location.reload();
                else Swal.fire('Error', data.msg, 'error');
            });
        }
    });
}
</script>
<?php include('../inc/menu-footer.php'); ?>
<?php include('../inc/flash_simple.php'); ?>
</body>
</html>
```

- [ ] **Step 2: Crear admin/radio/create.php**

```php
<?php
require_once __DIR__ . '/../../inc/config.php';
require_once __DIR__ . '/../login/session.php';
$permisopage = 'Gestionar Radio';
include('../login/restriction.php');
require_once __DIR__ . '/../inc/flash_helpers.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title  = trim($_POST['title']  ?? '');
    $slug   = trim($_POST['slug']   ?? '');
    $errors = [];

    if (empty($title)) $errors[] = 'El título es obligatorio';

    if (empty($slug)) {
        $slug = strtolower(preg_replace('/[^a-z0-9]+/i', '-', $title));
        $slug = trim($slug, '-');
    }
    $slug = mb_substr(strtolower(preg_replace('/[^a-z0-9-]/i', '', $slug)), 0, 200);

    // Verificar slug único
    if (!$errors) {
        $check = db()->prepare("SELECT COUNT(*) FROM programs WHERE slug = ?");
        $check->execute([$slug]);
        if ($check->fetchColumn() > 0) $errors[] = 'El slug ya existe, elige otro título o modifica el slug';
    }

    // Imagen
    $imagePath = null;
    if (!empty($_FILES['image']['name'])) {
        $ext = strtolower(pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION));
        if (!in_array($ext, ['jpg','jpeg','png','webp'])) {
            $errors[] = 'Imagen: solo JPG, PNG, WebP';
        } elseif ($_FILES['image']['size'] > 5 * 1024 * 1024) {
            $errors[] = 'Imagen: máximo 5MB';
        } else {
            $dir = __DIR__ . '/../../public/images/programs/';
            if (!is_dir($dir)) mkdir($dir, 0755, true);
            $filename = time() . '_' . preg_replace('/[^a-z0-9\._-]/i','_',$_FILES['image']['name']);
            if (move_uploaded_file($_FILES['image']['tmp_name'], $dir . $filename)) {
                $imagePath = 'public/images/programs/' . $filename;
            }
        }
    }

    if (empty($errors)) {
        $stmt = db()->prepare("INSERT INTO programs (title, slug, description, image, category, hosts, schedule_info, status) VALUES (?,?,?,?,?,?,?,?)");
        $stmt->execute([
            mb_substr($title, 0, 255),
            $slug,
            mb_substr(trim($_POST['description'] ?? ''), 0, 5000),
            $imagePath,
            mb_substr(trim($_POST['category'] ?? ''), 0, 100),
            mb_substr(trim($_POST['hosts'] ?? ''), 0, 255),
            mb_substr(trim($_POST['schedule_info'] ?? ''), 0, 255),
            in_array($_POST['status'] ?? '', ['active','inactive']) ? $_POST['status'] : 'active',
        ]);
        log_system_action('Crear Programa', $title, 'programs', db()->lastInsertId());
        setFlash('success', 'Programa creado correctamente');
        header('Location: ' . URLBASE . '/admin/radio/');
        exit;
    }
    setFlash('error', implode('<br>', $errors));
}
?>
<!doctype html>
<html lang="es">
<head>
<meta charset="utf-8">
<title>Nuevo Programa</title>
<?php include('../inc/header.php'); ?>
</head>
<body>
<?php include('../inc/menu.php'); ?>
<div class="main-content">
    <div class="container-fluid">
        <div class="d-flex align-items-center mb-4">
            <a href="<?= URLBASE ?>/admin/radio/" class="btn btn-sm btn-outline-secondary mr-3">
                <i class="fas fa-arrow-left"></i>
            </a>
            <h1 class="h3 mb-0">Nuevo Programa</h1>
        </div>
        <?php renderFlashMessages(); ?>
        <div class="card shadow-sm">
            <div class="card-body">
                <form method="POST" enctype="multipart/form-data">
                    <div class="form-group">
                        <label>Título *</label>
                        <input type="text" name="title" class="form-control" value="<?= htmlspecialchars($_POST['title'] ?? '') ?>" required>
                    </div>
                    <div class="form-group">
                        <label>Slug</label>
                        <input type="text" name="slug" class="form-control" value="<?= htmlspecialchars($_POST['slug'] ?? '') ?>" placeholder="Se genera automáticamente">
                    </div>
                    <div class="form-group">
                        <label>Descripción</label>
                        <textarea name="description" class="form-control" rows="4"><?= htmlspecialchars($_POST['description'] ?? '') ?></textarea>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Categoría</label>
                                <input type="text" name="category" class="form-control" value="<?= htmlspecialchars($_POST['category'] ?? '') ?>">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Conductores</label>
                                <input type="text" name="hosts" class="form-control" value="<?= htmlspecialchars($_POST['hosts'] ?? '') ?>">
                            </div>
                        </div>
                    </div>
                    <div class="form-group">
                        <label>Horario general <small class="text-muted">(ej: Lunes a Viernes 8:00–10:00)</small></label>
                        <input type="text" name="schedule_info" class="form-control" value="<?= htmlspecialchars($_POST['schedule_info'] ?? '') ?>">
                    </div>
                    <div class="form-group">
                        <label>Imagen</label>
                        <input type="file" name="image" class="form-control-file" accept=".jpg,.jpeg,.png,.webp">
                    </div>
                    <div class="form-group">
                        <label>Estado</label>
                        <select name="status" class="form-control">
                            <option value="active" <?= ($_POST['status'] ?? 'active') === 'active' ? 'selected' : '' ?>>Activo</option>
                            <option value="inactive" <?= ($_POST['status'] ?? '') === 'inactive' ? 'selected' : '' ?>>Inactivo</option>
                        </select>
                    </div>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save mr-1"></i> Guardar Programa
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>
<?php include('../inc/menu-footer.php'); ?>
<?php include('../inc/flash_simple.php'); ?>
</body>
</html>
```

- [ ] **Step 3: Crear admin/radio/edit.php**

```php
<?php
require_once __DIR__ . '/../../inc/config.php';
require_once __DIR__ . '/../login/session.php';
$permisopage = 'Gestionar Radio';
include('../login/restriction.php');
require_once __DIR__ . '/../inc/flash_helpers.php';

$id = (int)($_GET['id'] ?? 0);
$program = null;
try {
    $stmt = db()->prepare("SELECT * FROM programs WHERE id = ?");
    $stmt->execute([$id]);
    $program = $stmt->fetch();
} catch (Throwable $e) {}

if (!$program) {
    setFlash('error', 'Programa no encontrado');
    header('Location: ' . URLBASE . '/admin/radio/');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title  = trim($_POST['title'] ?? '');
    $slug   = trim($_POST['slug']  ?? $program['slug']);
    $errors = [];

    if (empty($title)) $errors[] = 'El título es obligatorio';
    $slug = mb_substr(strtolower(preg_replace('/[^a-z0-9-]/i', '', $slug)), 0, 200);

    // Verificar slug único excluyendo este ID
    if (!$errors) {
        $check = db()->prepare("SELECT COUNT(*) FROM programs WHERE slug = ? AND id != ?");
        $check->execute([$slug, $id]);
        if ($check->fetchColumn() > 0) $errors[] = 'El slug ya existe';
    }

    // Imagen
    $imagePath = $program['image'];
    if (!empty($_FILES['image']['name'])) {
        $ext = strtolower(pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION));
        if (!in_array($ext, ['jpg','jpeg','png','webp'])) {
            $errors[] = 'Imagen: solo JPG, PNG, WebP';
        } elseif ($_FILES['image']['size'] > 5 * 1024 * 1024) {
            $errors[] = 'Imagen: máximo 5MB';
        } else {
            $dir = __DIR__ . '/../../public/images/programs/';
            if (!is_dir($dir)) mkdir($dir, 0755, true);
            $filename = time() . '_' . preg_replace('/[^a-z0-9\._-]/i','_',$_FILES['image']['name']);
            if (move_uploaded_file($_FILES['image']['tmp_name'], $dir . $filename)) {
                // Eliminar imagen anterior
                if ($program['image'] && file_exists(__DIR__ . '/../../' . $program['image'])) {
                    @unlink(__DIR__ . '/../../' . $program['image']);
                }
                $imagePath = 'public/images/programs/' . $filename;
            }
        }
    }

    if (empty($errors)) {
        $stmt = db()->prepare("UPDATE programs SET title=?, slug=?, description=?, image=?, category=?, hosts=?, schedule_info=?, status=? WHERE id=?");
        $stmt->execute([
            mb_substr($title, 0, 255),
            $slug,
            mb_substr(trim($_POST['description'] ?? ''), 0, 5000),
            $imagePath,
            mb_substr(trim($_POST['category'] ?? ''), 0, 100),
            mb_substr(trim($_POST['hosts'] ?? ''), 0, 255),
            mb_substr(trim($_POST['schedule_info'] ?? ''), 0, 255),
            in_array($_POST['status'] ?? '', ['active','inactive']) ? $_POST['status'] : 'active',
            $id,
        ]);
        log_system_action('Editar Programa', $title, 'programs', $id);
        setFlash('success', 'Programa actualizado');
        header('Location: ' . URLBASE . '/admin/radio/');
        exit;
    }
    setFlash('error', implode('<br>', $errors));
    // Merge POST con program para re-render
    $program = array_merge($program, $_POST);
}
?>
<!doctype html>
<html lang="es">
<head>
<meta charset="utf-8">
<title>Editar Programa</title>
<?php include('../inc/header.php'); ?>
</head>
<body>
<?php include('../inc/menu.php'); ?>
<div class="main-content">
    <div class="container-fluid">
        <div class="d-flex align-items-center mb-4">
            <a href="<?= URLBASE ?>/admin/radio/" class="btn btn-sm btn-outline-secondary mr-3">
                <i class="fas fa-arrow-left"></i>
            </a>
            <h1 class="h3 mb-0">Editar: <?= htmlspecialchars($program['title']) ?></h1>
        </div>
        <?php renderFlashMessages(); ?>
        <div class="card shadow-sm">
            <div class="card-body">
                <form method="POST" enctype="multipart/form-data">
                    <div class="form-group">
                        <label>Título *</label>
                        <input type="text" name="title" class="form-control" value="<?= htmlspecialchars($program['title']) ?>" required>
                    </div>
                    <div class="form-group">
                        <label>Slug</label>
                        <input type="text" name="slug" class="form-control" value="<?= htmlspecialchars($program['slug']) ?>">
                    </div>
                    <div class="form-group">
                        <label>Descripción</label>
                        <textarea name="description" class="form-control" rows="4"><?= htmlspecialchars($program['description'] ?? '') ?></textarea>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Categoría</label>
                                <input type="text" name="category" class="form-control" value="<?= htmlspecialchars($program['category'] ?? '') ?>">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Conductores</label>
                                <input type="text" name="hosts" class="form-control" value="<?= htmlspecialchars($program['hosts'] ?? '') ?>">
                            </div>
                        </div>
                    </div>
                    <div class="form-group">
                        <label>Horario general</label>
                        <input type="text" name="schedule_info" class="form-control" value="<?= htmlspecialchars($program['schedule_info'] ?? '') ?>">
                    </div>
                    <div class="form-group">
                        <label>Imagen</label>
                        <?php if (!empty($program['image'])): ?>
                            <div class="mb-2">
                                <img src="<?= URLBASE . '/' . htmlspecialchars($program['image']) ?>" style="max-height:120px; border-radius:8px;">
                            </div>
                        <?php endif; ?>
                        <input type="file" name="image" class="form-control-file" accept=".jpg,.jpeg,.png,.webp">
                    </div>
                    <div class="form-group">
                        <label>Estado</label>
                        <select name="status" class="form-control">
                            <option value="active"   <?= $program['status'] === 'active'   ? 'selected' : '' ?>>Activo</option>
                            <option value="inactive" <?= $program['status'] === 'inactive' ? 'selected' : '' ?>>Inactivo</option>
                        </select>
                    </div>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save mr-1"></i> Guardar cambios
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>
<?php include('../inc/menu-footer.php'); ?>
<?php include('../inc/flash_simple.php'); ?>
</body>
</html>
```

- [ ] **Step 4: Crear admin/radio/delete.php**

```php
<?php
require_once __DIR__ . '/../../inc/config.php';
require_once __DIR__ . '/../login/session.php';
header('Content-Type: application/json');

if (!isset($_SESSION['user_permissions']) || !in_array('Gestionar Radio', $_SESSION['user_permissions'])) {
    echo json_encode(['ok' => false, 'msg' => 'Sin permisos']);
    exit;
}

$id = (int)($_POST['id'] ?? 0);
if (!$id) { echo json_encode(['ok' => false, 'msg' => 'ID inválido']); exit; }

try {
    $stmt = db()->prepare("SELECT image FROM programs WHERE id = ?");
    $stmt->execute([$id]);
    $p = $stmt->fetch();
    if ($p && $p['image'] && file_exists(__DIR__ . '/../../' . $p['image'])) {
        @unlink(__DIR__ . '/../../' . $p['image']);
    }
    db()->prepare("DELETE FROM programs WHERE id = ?")->execute([$id]);
    log_system_action('Eliminar Programa', 'ID: ' . $id, 'programs', $id);
    echo json_encode(['ok' => true]);
} catch (Throwable $e) {
    echo json_encode(['ok' => false, 'msg' => 'Error al eliminar']);
}
```

- [ ] **Step 5: Verificar sintaxis de todos los archivos**

```bash
php -l admin/radio/index.php && php -l admin/radio/create.php && php -l admin/radio/edit.php && php -l admin/radio/delete.php
```

- [ ] **Step 6: Verificar en navegador**

Abre `http://tu-sitio/admin/radio/` — debe mostrar tabla vacía con botón "Nuevo Programa".
Crea un programa desde `http://tu-sitio/admin/radio/create.php` — debe guardarse y redirigir al listado.

- [ ] **Step 7: Commit**

```bash
git add admin/radio/
git commit -m "feat: add radio programs CRUD admin"
```

---

## Task 11: Admin — Parrilla semanal (schedules)

**Files:**
- Create: `admin/radio/schedules.php`
- Create: `admin/radio/schedule_delete.php`

- [ ] **Step 1: Crear admin/radio/schedules.php**

```php
<?php
require_once __DIR__ . '/../../inc/config.php';
require_once __DIR__ . '/../login/session.php';
$permisopage = 'Gestionar Radio';
include('../login/restriction.php');
require_once __DIR__ . '/../inc/flash_helpers.php';

$programs = db()->query("SELECT id, title FROM programs WHERE status='active' ORDER BY title ASC")->fetchAll();
$days = ['lunes','martes','miercoles','jueves','viernes','sabado','domingo'];
$dayLabels = ['lunes'=>'Lunes','martes'=>'Martes','miercoles'=>'Miércoles','jueves'=>'Jueves','viernes'=>'Viernes','sabado'=>'Sábado','domingo'=>'Domingo'];

// Guardar nuevo slot
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['program_id'])) {
    $programId = (int)$_POST['program_id'];
    $day       = in_array($_POST['day_of_week'] ?? '', $days) ? $_POST['day_of_week'] : null;
    $start     = $_POST['start_time'] ?? '';
    $end       = $_POST['end_time']   ?? '';
    $host      = trim($_POST['host']  ?? '');
    $errors    = [];

    if (!$programId) $errors[] = 'Selecciona un programa';
    if (!$day)       $errors[] = 'Selecciona un día';
    if (!$start)     $errors[] = 'Hora de inicio requerida';
    if (!$end)       $errors[] = 'Hora de fin requerida';
    if ($start >= $end) $errors[] = 'La hora de fin debe ser mayor a la de inicio';

    if (empty($errors)) {
        $stmt = db()->prepare("INSERT INTO schedules (program_id, day_of_week, start_time, end_time, host, status) VALUES (?,?,?,?,?,?)");
        $stmt->execute([$programId, $day, $start, $end, mb_substr($host,0,255), 'active']);
        setFlash('success', 'Slot agregado');
    } else {
        setFlash('error', implode('<br>', $errors));
    }
    header('Location: ' . URLBASE . '/admin/radio/schedules.php');
    exit;
}

// Cargar parrilla
$allSlots = [];
try {
    $rows = db()->query("
        SELECT s.*, p.title AS program_title
        FROM schedules s
        INNER JOIN programs p ON p.id = s.program_id
        ORDER BY FIELD(s.day_of_week,'lunes','martes','miercoles','jueves','viernes','sabado','domingo'), s.start_time
    ")->fetchAll();
    foreach ($rows as $row) {
        $allSlots[$row['day_of_week']][] = $row;
    }
} catch (Throwable $e) {}
?>
<!doctype html>
<html lang="es">
<head>
<meta charset="utf-8">
<title>Programación</title>
<?php include('../inc/header.php'); ?>
</head>
<body>
<?php include('../inc/menu.php'); ?>
<div class="main-content">
    <div class="container-fluid">
        <h1 class="h3 mb-4">Parrilla de Programación</h1>
        <?php renderFlashMessages(); ?>

        <!-- Formulario agregar slot -->
        <div class="card shadow-sm mb-4">
            <div class="card-header"><strong>Agregar slot</strong></div>
            <div class="card-body">
                <form method="POST">
                    <div class="row">
                        <div class="col-md-3">
                            <div class="form-group">
                                <label>Programa *</label>
                                <select name="program_id" class="form-control" required>
                                    <option value="">— Seleccionar —</option>
                                    <?php foreach ($programs as $p): ?>
                                    <option value="<?= $p['id'] ?>"><?= htmlspecialchars($p['title']) ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-2">
                            <div class="form-group">
                                <label>Día *</label>
                                <select name="day_of_week" class="form-control" required>
                                    <option value="">— Día —</option>
                                    <?php foreach ($days as $d): ?>
                                    <option value="<?= $d ?>"><?= $dayLabels[$d] ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-2">
                            <div class="form-group">
                                <label>Inicio *</label>
                                <input type="time" name="start_time" class="form-control" required>
                            </div>
                        </div>
                        <div class="col-md-2">
                            <div class="form-group">
                                <label>Fin *</label>
                                <input type="time" name="end_time" class="form-control" required>
                            </div>
                        </div>
                        <div class="col-md-2">
                            <div class="form-group">
                                <label>Conductor</label>
                                <input type="text" name="host" class="form-control" placeholder="Opcional">
                            </div>
                        </div>
                        <div class="col-md-1 d-flex align-items-end">
                            <div class="form-group">
                                <button type="submit" class="btn btn-primary"><i class="fas fa-plus"></i></button>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <!-- Parrilla por día -->
        <?php foreach ($days as $day): ?>
        <div class="card shadow-sm mb-3">
            <div class="card-header d-flex justify-content-between">
                <strong><?= $dayLabels[$day] ?></strong>
                <span class="badge badge-secondary"><?= count($allSlots[$day] ?? []) ?> slots</span>
            </div>
            <?php if (!empty($allSlots[$day])): ?>
            <div class="card-body p-0">
                <table class="table table-sm table-hover mb-0">
                    <thead class="thead-light">
                        <tr><th>Inicio</th><th>Fin</th><th>Programa</th><th>Conductor</th><th>Estado</th><th></th></tr>
                    </thead>
                    <tbody>
                    <?php foreach ($allSlots[$day] as $slot): ?>
                    <tr>
                        <td><?= substr($slot['start_time'],0,5) ?></td>
                        <td><?= substr($slot['end_time'],0,5) ?></td>
                        <td><?= htmlspecialchars($slot['program_title']) ?></td>
                        <td><?= htmlspecialchars($slot['host'] ?? '') ?></td>
                        <td><span class="badge badge-<?= $slot['status']==='active'?'success':'secondary' ?>"><?= $slot['status'] === 'active' ? 'Activo' : 'Inactivo' ?></span></td>
                        <td>
                            <button class="btn btn-sm btn-outline-danger"
                                    onclick="delSlot(<?= $slot['id'] ?>)">
                                <i class="fas fa-trash"></i>
                            </button>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            <?php else: ?>
            <div class="card-body text-muted text-center py-3">Sin slots este día</div>
            <?php endif; ?>
        </div>
        <?php endforeach; ?>
    </div>
</div>
<script>
function delSlot(id) {
    Swal.fire({
        title: '¿Eliminar slot?',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#d33',
        cancelButtonText: 'Cancelar',
        confirmButtonText: 'Eliminar'
    }).then(function(r) {
        if (r.isConfirmed) {
            fetch('<?= URLBASE ?>/admin/radio/schedule_delete.php', {
                method: 'POST',
                headers: {'Content-Type': 'application/x-www-form-urlencoded'},
                body: 'id=' + id
            }).then(r => r.json()).then(data => {
                if (data.ok) location.reload();
                else Swal.fire('Error', data.msg, 'error');
            });
        }
    });
}
</script>
<?php include('../inc/menu-footer.php'); ?>
<?php include('../inc/flash_simple.php'); ?>
</body>
</html>
```

- [ ] **Step 2: Crear admin/radio/schedule_delete.php**

```php
<?php
require_once __DIR__ . '/../../inc/config.php';
require_once __DIR__ . '/../login/session.php';
header('Content-Type: application/json');

if (!isset($_SESSION['user_permissions']) || !in_array('Gestionar Radio', $_SESSION['user_permissions'])) {
    echo json_encode(['ok' => false, 'msg' => 'Sin permisos']);
    exit;
}

$id = (int)($_POST['id'] ?? 0);
if (!$id) { echo json_encode(['ok' => false, 'msg' => 'ID inválido']); exit; }

try {
    db()->prepare("DELETE FROM schedules WHERE id = ?")->execute([$id]);
    echo json_encode(['ok' => true]);
} catch (Throwable $e) {
    echo json_encode(['ok' => false, 'msg' => 'Error al eliminar']);
}
```

- [ ] **Step 3: Verificar sintaxis**

```bash
php -l admin/radio/schedules.php && php -l admin/radio/schedule_delete.php
```

- [ ] **Step 4: Verificar en navegador**

`http://tu-sitio/admin/radio/schedules.php` — debe mostrar 7 secciones de días vacías + formulario.
Agrega un slot con programa activo + día + horario — debe aparecer en la sección correspondiente.
Verifica que en `http://tu-sitio/programacion/` el slot aparece en el día correcto.

- [ ] **Step 5: Commit**

```bash
git add admin/radio/schedules.php admin/radio/schedule_delete.php
git commit -m "feat: add weekly schedule admin (add/delete slots)"
```

---

## Task 12: Admin — Suscriptores

**Files:**
- Create: `admin/subscribers/index.php`

- [ ] **Step 1: Crear admin/subscribers/index.php**

```php
<?php
require_once __DIR__ . '/../../inc/config.php';
require_once __DIR__ . '/../login/session.php';
$permisopage = 'Gestionar Suscriptores';
include('../login/restriction.php');
require_once __DIR__ . '/../inc/flash_helpers.php';

// Acciones POST (toggle status / delete)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id     = (int)($_POST['id'] ?? 0);
    $action = $_POST['action'] ?? '';
    if ($id && $action === 'toggle') {
        $current = db()->prepare("SELECT status FROM subscribers WHERE id = ?");
        $current->execute([$id]);
        $row = $current->fetch();
        if ($row) {
            $newStatus = $row['status'] === 'active' ? 'inactive' : 'active';
            db()->prepare("UPDATE subscribers SET status = ? WHERE id = ?")->execute([$newStatus, $id]);
        }
    } elseif ($id && $action === 'delete') {
        db()->prepare("DELETE FROM subscribers WHERE id = ?")->execute([$id]);
        setFlash('success', 'Suscriptor eliminado');
    }
    header('Location: ' . URLBASE . '/admin/subscribers/');
    exit;
}

$total  = db()->query("SELECT COUNT(*) FROM subscribers")->fetchColumn();
$active = db()->query("SELECT COUNT(*) FROM subscribers WHERE status='active'")->fetchColumn();
$subs   = db()->query("SELECT * FROM subscribers ORDER BY created_at DESC")->fetchAll();
?>
<!doctype html>
<html lang="es">
<head>
<meta charset="utf-8">
<title>Suscriptores</title>
<?php include('../inc/header.php'); ?>
</head>
<body>
<?php include('../inc/menu.php'); ?>
<div class="main-content">
    <div class="container-fluid">
        <h1 class="h3 mb-4">Suscriptores</h1>
        <?php renderFlashMessages(); ?>

        <div class="row mb-4">
            <div class="col-md-3">
                <div class="card text-center shadow-sm">
                    <div class="card-body">
                        <div class="h4"><?= $total ?></div>
                        <div class="text-muted">Total</div>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card text-center shadow-sm">
                    <div class="card-body">
                        <div class="h4 text-success"><?= $active ?></div>
                        <div class="text-muted">Activos</div>
                    </div>
                </div>
            </div>
        </div>

        <div class="card shadow-sm">
            <div class="card-body p-0">
                <table class="table table-hover mb-0">
                    <thead class="thead-light">
                        <tr><th>Nombre</th><th>Email</th><th>Privacidad</th><th>Estado</th><th>Fecha</th><th>Acciones</th></tr>
                    </thead>
                    <tbody>
                    <?php if (empty($subs)): ?>
                        <tr><td colspan="6" class="text-center text-muted py-4">Sin suscriptores aún</td></tr>
                    <?php else: ?>
                        <?php foreach ($subs as $s): ?>
                        <tr>
                            <td><?= htmlspecialchars($s['name']) ?></td>
                            <td><?= htmlspecialchars($s['email']) ?></td>
                            <td><?= $s['privacy_accepted'] ? '<span class="badge badge-success">Sí</span>' : '<span class="badge badge-warning">No</span>' ?></td>
                            <td>
                                <form method="POST" style="display:inline;">
                                    <input type="hidden" name="id" value="<?= $s['id'] ?>">
                                    <input type="hidden" name="action" value="toggle">
                                    <button type="submit" class="badge badge-<?= $s['status']==='active'?'success':'secondary' ?> border-0 cursor-pointer">
                                        <?= $s['status'] === 'active' ? 'Activo' : 'Inactivo' ?>
                                    </button>
                                </form>
                            </td>
                            <td><?= date('d/m/Y', strtotime($s['created_at'])) ?></td>
                            <td>
                                <form method="POST" style="display:inline;"
                                      onsubmit="return confirm('¿Eliminar este suscriptor?')">
                                    <input type="hidden" name="id" value="<?= $s['id'] ?>">
                                    <input type="hidden" name="action" value="delete">
                                    <button type="submit" class="btn btn-sm btn-outline-danger">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </form>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
<?php include('../inc/menu-footer.php'); ?>
<?php include('../inc/flash_simple.php'); ?>
</body>
</html>
```

- [ ] **Step 2: Verificar sintaxis y navegador**

```bash
php -l admin/subscribers/index.php
```

`http://tu-sitio/admin/subscribers/` — debe mostrar tabla vacía con contadores en 0.
Suscribe desde el formulario público, recarga admin — debe aparecer el registro.
Prueba el toggle de estado y la eliminación.

- [ ] **Step 3: Commit**

```bash
git add admin/subscribers/
git commit -m "feat: add subscribers admin panel"
```

---

## Task 13: Admin — Mensajes de contacto

**Files:**
- Create: `admin/contact/index.php`

- [ ] **Step 1: Crear admin/contact/index.php**

```php
<?php
require_once __DIR__ . '/../../inc/config.php';
require_once __DIR__ . '/../login/session.php';
$permisopage = 'Gestionar Mensajes';
include('../login/restriction.php');
require_once __DIR__ . '/../inc/flash_helpers.php';

// Marcar leído
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id     = (int)($_POST['id'] ?? 0);
    $action = $_POST['action'] ?? '';
    if ($id && $action === 'read') {
        db()->prepare("UPDATE contact_messages SET status='read' WHERE id=?")->execute([$id]);
    } elseif ($id && $action === 'unread') {
        db()->prepare("UPDATE contact_messages SET status='unread' WHERE id=?")->execute([$id]);
    } elseif ($id && $action === 'delete') {
        db()->prepare("DELETE FROM contact_messages WHERE id=?")->execute([$id]);
        setFlash('success', 'Mensaje eliminado');
    }
    header('Location: ' . URLBASE . '/admin/contact/');
    exit;
}

$messages = db()->query("SELECT * FROM contact_messages ORDER BY created_at DESC")->fetchAll();
$unread   = db()->query("SELECT COUNT(*) FROM contact_messages WHERE status='unread'")->fetchColumn();
?>
<!doctype html>
<html lang="es">
<head>
<meta charset="utf-8">
<title>Mensajes de Contacto</title>
<?php include('../inc/header.php'); ?>
</head>
<body>
<?php include('../inc/menu.php'); ?>
<div class="main-content">
    <div class="container-fluid">
        <h1 class="h3 mb-4">
            Mensajes de Contacto
            <?php if ($unread > 0): ?>
                <span class="badge badge-danger"><?= $unread ?> sin leer</span>
            <?php endif; ?>
        </h1>
        <?php renderFlashMessages(); ?>

        <div class="card shadow-sm">
            <div class="card-body p-0">
                <table class="table table-hover mb-0">
                    <thead class="thead-light">
                        <tr><th>Nombre</th><th>Email</th><th>Teléfono</th><th>Mensaje</th><th>Estado</th><th>Fecha</th><th>Acciones</th></tr>
                    </thead>
                    <tbody>
                    <?php if (empty($messages)): ?>
                        <tr><td colspan="7" class="text-center text-muted py-4">Sin mensajes</td></tr>
                    <?php else: ?>
                        <?php foreach ($messages as $m): ?>
                        <tr style="<?= $m['status']==='unread' ? 'font-weight:600;' : '' ?>">
                            <td><?= htmlspecialchars($m['name']) ?></td>
                            <td><a href="mailto:<?= htmlspecialchars($m['email']) ?>"><?= htmlspecialchars($m['email']) ?></a></td>
                            <td><?= htmlspecialchars($m['phone'] ?? '') ?></td>
                            <td style="max-width:250px; overflow:hidden; text-overflow:ellipsis; white-space:nowrap;"
                                title="<?= htmlspecialchars($m['message']) ?>">
                                <?= htmlspecialchars(mb_substr($m['message'], 0, 80)) ?><?= mb_strlen($m['message']) > 80 ? '...' : '' ?>
                            </td>
                            <td>
                                <?php if ($m['status'] === 'unread'): ?>
                                    <span class="badge badge-warning">Sin leer</span>
                                <?php else: ?>
                                    <span class="badge badge-secondary">Leído</span>
                                <?php endif; ?>
                            </td>
                            <td><?= date('d/m/Y H:i', strtotime($m['created_at'])) ?></td>
                            <td style="white-space:nowrap;">
                                <?php if ($m['status'] === 'unread'): ?>
                                <form method="POST" style="display:inline;">
                                    <input type="hidden" name="id" value="<?= $m['id'] ?>">
                                    <input type="hidden" name="action" value="read">
                                    <button type="submit" class="btn btn-sm btn-outline-success mr-1" title="Marcar leído">
                                        <i class="fas fa-check"></i>
                                    </button>
                                </form>
                                <?php else: ?>
                                <form method="POST" style="display:inline;">
                                    <input type="hidden" name="id" value="<?= $m['id'] ?>">
                                    <input type="hidden" name="action" value="unread">
                                    <button type="submit" class="btn btn-sm btn-outline-secondary mr-1" title="Marcar no leído">
                                        <i class="fas fa-envelope"></i>
                                    </button>
                                </form>
                                <?php endif; ?>
                                <form method="POST" style="display:inline;"
                                      onsubmit="return confirm('¿Eliminar este mensaje?')">
                                    <input type="hidden" name="id" value="<?= $m['id'] ?>">
                                    <input type="hidden" name="action" value="delete">
                                    <button type="submit" class="btn btn-sm btn-outline-danger">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </form>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
<?php include('../inc/menu-footer.php'); ?>
<?php include('../inc/flash_simple.php'); ?>
</body>
</html>
```

- [ ] **Step 2: Verificar sintaxis y navegador**

```bash
php -l admin/contact/index.php
```

Envía un mensaje desde `http://tu-sitio/contacto/`.
Abre `http://tu-sitio/admin/contact/` — debe aparecer con badge "sin leer".
Marca como leído — badge desaparece.

- [ ] **Step 3: Commit**

```bash
git add admin/contact/
git commit -m "feat: add contact messages admin panel with read/unread status"
```

---

## Task 14: Admin — Páginas legales

**Files:**
- Create: `admin/legal/index.php`

- [ ] **Step 1: Crear admin/legal/index.php**

```php
<?php
require_once __DIR__ . '/../../inc/config.php';
require_once __DIR__ . '/../login/session.php';
$permisopage = 'Gestionar Páginas Legales';
include('../login/restriction.php');
require_once __DIR__ . '/../inc/flash_helpers.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $slug    = in_array($_POST['slug'] ?? '', ['aviso-legal','politica-privacidad']) ? $_POST['slug'] : null;
    $title   = trim($_POST['title']   ?? '');
    $content = $_POST['content'] ?? '';

    if ($slug && !empty($title)) {
        $stmt = db()->prepare("UPDATE legal_pages SET title=?, content=? WHERE slug=?");
        $stmt->execute([mb_substr($title,0,255), $content, $slug]);
        log_system_action('Editar Página Legal', $slug, 'legal_pages', $slug);
        setFlash('success', 'Página guardada correctamente');
    } else {
        setFlash('error', 'Datos inválidos');
    }
    header('Location: ' . URLBASE . '/admin/legal/?tab=' . urlencode($slug ?? 'aviso-legal'));
    exit;
}

$pages = [];
try {
    $rows = db()->query("SELECT * FROM legal_pages ORDER BY id ASC")->fetchAll();
    foreach ($rows as $row) $pages[$row['slug']] = $row;
} catch (Throwable $e) {}

$activeTab = in_array($_GET['tab'] ?? '', ['aviso-legal','politica-privacidad']) ? $_GET['tab'] : 'aviso-legal';
?>
<!doctype html>
<html lang="es">
<head>
<meta charset="utf-8">
<title>Páginas Legales</title>
<?php include('../inc/header.php'); ?>
<?php include('../inc/summernote.php'); ?>
</head>
<body>
<?php include('../inc/menu.php'); ?>
<div class="main-content">
    <div class="container-fluid">
        <h1 class="h3 mb-4">Páginas Legales</h1>
        <?php renderFlashMessages(); ?>

        <!-- Tabs -->
        <ul class="nav nav-tabs mb-3">
            <li class="nav-item">
                <a class="nav-link <?= $activeTab === 'aviso-legal' ? 'active' : '' ?>"
                   href="<?= URLBASE ?>/admin/legal/?tab=aviso-legal">Aviso Legal</a>
            </li>
            <li class="nav-item">
                <a class="nav-link <?= $activeTab === 'politica-privacidad' ? 'active' : '' ?>"
                   href="<?= URLBASE ?>/admin/legal/?tab=politica-privacidad">Política de Privacidad</a>
            </li>
        </ul>

        <?php
        $page = $pages[$activeTab] ?? ['slug' => $activeTab, 'title' => '', 'content' => ''];
        ?>
        <div class="card shadow-sm">
            <div class="card-body">
                <form method="POST">
                    <input type="hidden" name="slug" value="<?= htmlspecialchars($page['slug']) ?>">
                    <div class="form-group">
                        <label>Título *</label>
                        <input type="text" name="title" class="form-control"
                               value="<?= htmlspecialchars($page['title']) ?>" required>
                    </div>
                    <div class="form-group">
                        <label>Contenido</label>
                        <textarea id="summernote" name="content" class="form-control"><?= $page['content'] ?></textarea>
                    </div>
                    <div class="d-flex justify-content-between align-items-center">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save mr-1"></i> Guardar
                        </button>
                        <a href="<?= URLBASE ?>/<?= htmlspecialchars($page['slug']) ?>/" target="_blank"
                           class="btn btn-outline-secondary btn-sm">
                            <i class="fas fa-external-link-alt mr-1"></i> Ver página
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
<script>
$('#summernote').summernote({
    height: 400,
    lang: 'es-ES',
    toolbar: [
        ['style',['bold','italic','underline','clear']],
        ['font',['strikethrough']],
        ['para',['ul','ol','paragraph']],
        ['insert',['link']],
        ['view',['codeview']]
    ]
});
</script>
<?php include('../inc/menu-footer.php'); ?>
<?php include('../inc/flash_simple.php'); ?>
</body>
</html>
```

- [ ] **Step 2: Verificar sintaxis**

```bash
php -l admin/legal/index.php
```

- [ ] **Step 3: Verificar en navegador**

`http://tu-sitio/admin/legal/` — debe mostrar dos tabs: Aviso Legal | Política de Privacidad.
Edita el Aviso Legal, guarda — verifica en `http://tu-sitio/aviso-legal/` que el contenido aparece.

- [ ] **Step 4: Commit**

```bash
git add admin/legal/
git commit -m "feat: add legal pages admin editor with tabs"
```

---

## Task 15: Menú del panel administrativo

**Files:**
- Modify: `admin/inc/menu.php`

- [ ] **Step 1: Agregar entradas de Radio, Suscriptores, Mensajes y Legales**

Localiza el bloque `<!-- INSTITUCIONAL -->` en `admin/inc/menu.php` e inserta el siguiente bloque **justo después** del cierre `<?php endif; ?>` de Institucional:

```php
    <!-- RADIO -->
    <?php if (isset($_SESSION["user_permissions"]) && in_array('Gestionar Radio', $_SESSION["user_permissions"])): ?>
        <a href="#" class="has-submenu" onclick="toggleSubmenu(event)">
            <i class="fas fa-broadcast-tower"></i> Radio <i class="fas fa-chevron-down"></i>
        </a>
        <div class="submenu">
            <a href="<?= URLBASE ?>/admin/radio/" onclick="closeSubmenus()">
                <i class="fas fa-list"></i> Programas
            </a>
            <a href="<?= URLBASE ?>/admin/radio/schedules.php" onclick="closeSubmenus()">
                <i class="fas fa-calendar-alt"></i> Programación
            </a>
        </div>
    <?php endif; ?>

    <!-- SUSCRIPTORES -->
    <?php if (isset($_SESSION["user_permissions"]) && in_array('Gestionar Suscriptores', $_SESSION["user_permissions"])): ?>
        <a href="<?= URLBASE ?>/admin/subscribers/" onclick="closeSubmenus()">
            <i class="fas fa-users"></i> Suscriptores
        </a>
    <?php endif; ?>

    <!-- MENSAJES DE CONTACTO -->
    <?php if (isset($_SESSION["user_permissions"]) && in_array('Gestionar Mensajes', $_SESSION["user_permissions"])): ?>
        <?php
        $unreadMsgs = 0;
        try {
            $unreadMsgs = (int) db()->query("SELECT COUNT(*) FROM contact_messages WHERE status='unread'")->fetchColumn();
        } catch (Throwable $e) {}
        ?>
        <a href="<?= URLBASE ?>/admin/contact/" onclick="closeSubmenus()">
            <i class="fas fa-envelope"></i> Mensajes
            <?php if ($unreadMsgs > 0): ?>
                <span class="badge"><?= $unreadMsgs ?></span>
            <?php endif; ?>
        </a>
    <?php endif; ?>

    <!-- PÁGINAS LEGALES -->
    <?php if (isset($_SESSION["user_permissions"]) && in_array('Gestionar Páginas Legales', $_SESSION["user_permissions"])): ?>
        <a href="<?= URLBASE ?>/admin/legal/" onclick="closeSubmenus()">
            <i class="fas fa-balance-scale"></i> Páginas Legales
        </a>
    <?php endif; ?>
```

- [ ] **Step 2: Verificar sintaxis**

```bash
php -l admin/inc/menu.php
```

- [ ] **Step 3: Verificar en navegador**

Abre el admin — deben aparecer los nuevos ítems de menú (si el usuario tiene los permisos asignados).
Si no aparecen, asigna los permisos 26–29 al rol Administrador desde la gestión de roles.

- [ ] **Step 4: Commit**

```bash
git add admin/inc/menu.php
git commit -m "feat: add radio/subscribers/messages/legal entries to admin menu"
```

---

## Task 16: Menú público — Artemis

**Files:**
- Modify: `template/Artemis/inc/menu-header.php`

- [ ] **Step 1: Agregar consultas condicionales al inicio del archivo**

Al inicio de `menu-header.php`, justo después de los `require_once`, agrega:

```php
<?php
// Contar slots activos para mostrar Programación condicionalmente
$hasSchedules = false;
$hasPrograms  = false;
try {
    $hasSchedules = (bool) db()->query("SELECT COUNT(*) FROM schedules WHERE status='active'")->fetchColumn();
    $hasPrograms  = (bool) db()->query("SELECT COUNT(*) FROM programs WHERE status='active'")->fetchColumn();
} catch (Throwable $e) {}

// Páginas legales con contenido
$legalLinks = [];
try {
    $legalRows = db()->query("SELECT slug, title FROM legal_pages WHERE content != '' AND content IS NOT NULL")->fetchAll();
    foreach ($legalRows as $lr) $legalLinks[$lr['slug']] = $lr['title'];
} catch (Throwable $e) {}
?>
```

- [ ] **Step 2: Agregar ítems al nav desktop**

Localiza `<li class="nav-item">` con el enlace a `/contact` en el nav desktop y **reemplaza** ese ítem por los siguientes (esto actualiza Contacto a `/contacto/` y agrega los nuevos):

```php
                    <?php if ($hasPrograms): ?>
                    <li class="nav-item">
                        <a class="nav-link" href="<?= URLBASE ?>/programas/">PROGRAMAS</a>
                    </li>
                    <?php endif; ?>

                    <?php if ($hasSchedules): ?>
                    <li class="nav-item">
                        <a class="nav-link" href="<?= URLBASE ?>/programacion/">PROGRAMACIÓN</a>
                    </li>
                    <?php endif; ?>

                    <li class="nav-item">
                        <a class="nav-link" href="<?= URLBASE ?>/suscripcion/">SUSCRIPCIÓN</a>
                    </li>

                    <li class="nav-item">
                        <a class="nav-link" href="<?= URLBASE ?>/contacto/"><?= strtoupper(t_theme('theme_contacto')) ?></a>
                    </li>
```

- [ ] **Step 3: Agregar ítems al nav móvil**

Localiza `<li>` con el enlace a `/contact` en el mobile nav y **reemplaza** ese ítem por:

```php
                    <?php if ($hasPrograms): ?>
                    <li>
                        <a href="<?= URLBASE ?>/programas/">
                            <i class="fas fa-microphone"></i>Programas
                        </a>
                    </li>
                    <?php endif; ?>

                    <?php if ($hasSchedules): ?>
                    <li>
                        <a href="<?= URLBASE ?>/programacion/">
                            <i class="fas fa-calendar-alt"></i>Programación
                        </a>
                    </li>
                    <?php endif; ?>

                    <li>
                        <a href="<?= URLBASE ?>/suscripcion/">
                            <i class="fas fa-bell"></i>Suscripción
                        </a>
                    </li>

                    <li>
                        <a href="<?= URLBASE ?>/contacto/">
                            <i class="fas fa-envelope"></i><?= t_theme('theme_contacto') ?>
                        </a>
                    </li>
```

- [ ] **Step 4: Agregar enlaces legales al footer**

Abre `template/Artemis/inc/footer.php` y agrega dentro del footer, junto a los enlaces existentes:

```php
<?php if (!empty($legalLinks)): ?>
    <div style="text-align:center; padding: 10px 0; font-size:.85em;">
        <?php foreach ($legalLinks as $slug => $title): ?>
            <a href="<?= URLBASE ?>/<?= htmlspecialchars($slug) ?>/"
               style="color: var(--text-muted); margin: 0 10px;">
                <?= htmlspecialchars($title) ?>
            </a>
        <?php endforeach; ?>
    </div>
<?php endif; ?>
```

**Nota:** Si `$legalLinks` no está definido en el footer (está definido en menu-header.php), decláralo en footer.php con la misma query. El footer se incluye después que menu-header.php, por lo que la variable estará disponible si el footer está en el mismo scope de inclusión. Si no, añade la query directamente en footer.php.

- [ ] **Step 5: Verificar sintaxis**

```bash
php -l template/Artemis/inc/menu-header.php
```

- [ ] **Step 6: Verificar en navegador**

- Sin programas/schedules en BD: Programas y Programación NO aparecen en el nav
- Crea un programa activo y un slot activo desde admin: ambos ítems aparecen en el nav
- Suscripción y Contacto siempre visibles
- Aviso Legal y Política de Privacidad en el footer solo si tienen contenido

- [ ] **Step 7: Commit final**

```bash
git add template/Artemis/inc/menu-header.php template/Artemis/inc/footer.php
git commit -m "feat: update public nav with conditional radio/schedule/subscription items and legal footer links"
```

---

## Self-Review

### Spec coverage

| Requisito | Task |
|-----------|------|
| `programs` table | Task 1 |
| `schedules` table | Task 1 |
| `subscribers` table | Task 1 |
| `contact_messages` table | Task 1 |
| `legal_pages` table + seed | Task 1 |
| Permisos 26-29 | Task 1 |
| Ruta `/programas/{slug}/` | Task 2 |
| Actions contact + subscribe | Tasks 3–4 |
| Honeypot en formularios | Tasks 3–4 |
| GDPR checkbox | Task 6 |
| Templates públicos (7) | Tasks 5–9 |
| Badge EN VIVO por hora | Task 9 |
| Admin Radio CRUD | Task 10 |
| Admin Parrilla | Task 11 |
| Admin Suscriptores | Task 12 |
| Admin Mensajes (leído/no leído) | Task 13 |
| Admin Páginas Legales (tabs) | Task 14 |
| Menú admin con badge mensajes | Task 15 |
| Menú público condicional | Task 16 |
| Legales en footer (condicional) | Task 16 |

Todos los requisitos cubiertos. ✓
