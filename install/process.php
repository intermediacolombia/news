<?php
if (file_exists(__DIR__ . '/../inc/url_bd.php')) {
    die('El sistema ya está instalado. Elimina /inc/url_bd.php para reinstalar.');
}

$db_host = trim($_POST['db_host'] ?? '');
$db_name = trim($_POST['db_name'] ?? '');
$db_user = trim($_POST['db_user'] ?? '');
$db_pass = $_POST['db_pass'] ?? '';
$site_url = rtrim($_POST['site_url'] ?? '', '/');

$admin_name     = trim($_POST['admin_name'] ?? '');
$admin_lastname = trim($_POST['admin_lastname'] ?? '');
$admin_email    = trim($_POST['admin_email'] ?? '');
$admin_username = trim($_POST['admin_username'] ?? '');
$admin_password = $_POST['admin_password'] ?? '';

if (!$db_host || !$db_name || !$db_user || !$site_url || !$admin_name || !$admin_email || !$admin_username || !$admin_password) {
    die('Error: Todos los campos son obligatorios.');
}

// Tablas cuyo INSERT se omite en instalación limpia
$skipInsertTables = [
    'usuarios',
    'blog_posts',
    'blog_post_category',
    'blog_post_views',
    'visit_stats',
    'user_tokens',
    'password_resets',
    'multimedia',
    'institutional_pages',
];

try {
    // ── 1. Conectar sin dbname ────────────────────────────────────────────────
    $pdo = new PDO(
        "mysql:host={$db_host};charset=utf8mb4",
        $db_user,
        $db_pass,
        [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_EMULATE_PREPARES   => false,
        ]
    );

    // ── 2. Crear BD y seleccionarla ───────────────────────────────────────────
    $pdo->exec("CREATE DATABASE IF NOT EXISTS `{$db_name}` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
    $pdo->exec("USE `{$db_name}`");

    // ── 3. Ejecutar news.sql de la raíz del proyecto ──────────────────────────
    $sqlFile = __DIR__ . '/../news.sql';
    if (!file_exists($sqlFile)) {
        throw new Exception("No se encontró news.sql en la raíz del proyecto.");
    }

    $handle = fopen($sqlFile, 'r');
    if (!$handle) {
        throw new Exception("No se pudo abrir news.sql (permisos).");
    }

    $statement    = '';
    $inSkipInsert = false;
    $errors       = [];

    while (($line = fgets($handle)) !== false) {
        $trim = trim($line);

        // Saltar vacías, comentarios -- y bloques /*!...*/
        if ($trim === '')                             continue;
        if (str_starts_with($trim, '--'))             continue;
        if (str_starts_with($trim, '/*!'))            continue;
        if (str_starts_with($trim, '/*'))             continue;

        // Detectar inicio de INSERT para tablas a omitir
        if (!$inSkipInsert) {
            foreach ($skipInsertTables as $tbl) {
                if (stripos($trim, "INSERT INTO `{$tbl}`") === 0) {
                    $inSkipInsert = true;
                    break;
                }
            }
        }

        if ($inSkipInsert) {
            // El INSERT puede ser multi-línea; esperar el ; final
            if (preg_match('/;\s*$/', $trim)) {
                $inSkipInsert = false;
            }
            continue;
        }

        $statement .= $line;

        if (preg_match('/;\s*$/', $trim)) {
            $sql = trim($statement);
            $statement = '';

            if ($sql === '') continue;

            try {
                $pdo->exec($sql);
            } catch (PDOException $e) {
                // Registrar pero continuar (p.ej. duplicados en reintalación parcial)
                $errors[] = $e->getMessage() . ' → ' . mb_substr($sql, 0, 120);
            }
        }
    }
    fclose($handle);

    // ── 4. Verificar tabla usuarios ───────────────────────────────────────────
    $exists = $pdo->query("SHOW TABLES LIKE 'usuarios'")->fetchColumn();
    if (!$exists) {
        $detail = $errors ? implode("\n", array_slice($errors, 0, 5)) : 'Sin detalles';
        throw new Exception("No se creó la tabla 'usuarios'.\n\nPrimeros errores:\n{$detail}");
    }

    // ── 5. Insertar usuario administrador ─────────────────────────────────────
    // El rol 'Administrador' (id=1) ya fue insertado por el SQL seed
    $passwordHash = password_hash($admin_password, PASSWORD_BCRYPT);

    $stmt = $pdo->prepare("
        INSERT INTO usuarios
            (nombre, apellido, correo, username, rol_id, password, rol, estado, borrado, intentos, es_columnista)
        VALUES
            (?, ?, ?, ?, 1, ?, 'admin', 0, 0, 0, 0)
    ");
    $stmt->execute([$admin_name, $admin_lastname, $admin_email, $admin_username, $passwordHash]);

    // ── 6. Escribir /inc/url_bd.php ───────────────────────────────────────────
    $configContent  = "<?php\n";
    $configContent .= "if (!isset(\$host))   \$host   = " . var_export($db_host, true) . ";\n";
    $configContent .= "if (!isset(\$dbname)) \$dbname = " . var_export($db_name, true) . ";\n";
    $configContent .= "if (!isset(\$dbuser)) \$dbuser = " . var_export($db_user, true) . ";\n";
    $configContent .= "if (!isset(\$dbpass)) \$dbpass = " . var_export($db_pass, true) . ";\n\n";
    $configContent .= "\$url_site = " . var_export($site_url, true) . ";\n";
    $configContent .= "?>";

    $configPath = __DIR__ . '/../inc/url_bd.php';
    if (!file_put_contents($configPath, $configContent)) {
        throw new Exception("No se pudo escribir /inc/url_bd.php (revisa permisos de escritura).");
    }

    header('Location: ../admin/');
    exit;

} catch (PDOException $e) {
    die('<b>Error de base de datos:</b> ' . htmlspecialchars($e->getMessage()));
} catch (Exception $e) {
    die('<b>Error:</b> ' . nl2br(htmlspecialchars($e->getMessage())));
}
