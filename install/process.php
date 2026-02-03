<?php
if (file_exists(__DIR__ . '/../inc/url_bd.php')) {
    die('El sistema ya está instalado. Elimina /inc/url_bd.php para reinstalar.');
}

$db_host = $_POST['db_host'] ?? '';
$db_name = $_POST['db_name'] ?? '';
$db_user = $_POST['db_user'] ?? '';
$db_pass = $_POST['db_pass'] ?? '';
$site_url = rtrim($_POST['site_url'] ?? '', '/');

$admin_name = $_POST['admin_name'] ?? '';
$admin_lastname = $_POST['admin_lastname'] ?? '';
$admin_email = $_POST['admin_email'] ?? '';
$admin_username = $_POST['admin_username'] ?? '';
$admin_password = $_POST['admin_password'] ?? '';

if (!$db_host || !$db_name || !$db_user || !$site_url || !$admin_name || !$admin_email || !$admin_username || !$admin_password) {
    die('Error: Todos los campos son obligatorios.');
}

try {
    // 1) Conectar sin dbname para poder crear la BD
    $pdo = new PDO(
        "mysql:host={$db_host};charset=utf8mb4",
        $db_user,
        $db_pass,
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_EMULATE_PREPARES => false,
        ]
    );

    // 2) Crear BD + USE
    $pdo->exec("CREATE DATABASE IF NOT EXISTS `{$db_name}` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
    $pdo->exec("USE `{$db_name}`");

    // 3) Ejecutar db.sql de forma robusta (por líneas, acumulando hasta ';')
    $sqlFile = __DIR__ . '/db.sql';
    if (!file_exists($sqlFile)) {
        throw new Exception("No se encontró /install/db.sql");
    }

    $handle = fopen($sqlFile, 'r');
    if (!$handle) {
        throw new Exception("No se pudo abrir /install/db.sql");
    }

    $statement = '';
    while (($line = fgets($handle)) !== false) {
        $trim = trim($line);

        // Saltar líneas vacías y comentarios
        if ($trim === '' || str_starts_with($trim, '--') || str_starts_with($trim, '/*') || str_starts_with($trim, '*/')) {
            continue;
        }

        // Saltar comandos tipo /*!40101 ... */ (compat phpMyAdmin)
        if (str_starts_with($trim, '/*!') && str_ends_with($trim, '*/')) {
            continue;
        }

        $statement .= $line;

        // Si termina en ; entonces ejecutamos
        if (preg_match('/;\s*$/', $trim)) {
            $pdo->exec($statement);
            $statement = '';
        }
    }
    fclose($handle);

    // 4) Verificar que la tabla usuarios existe ANTES de insertar
    $exists = $pdo->query("SHOW TABLES LIKE 'usuarios'")->fetchColumn();
    if (!$exists) {
        throw new Exception("No se creó la tabla 'usuarios'. Revisa el contenido de /install/db.sql");
    }

    // 5) Insertar admin
    $passwordHash = password_hash($admin_password, PASSWORD_BCRYPT);

    $stmt = $pdo->prepare("
        INSERT INTO usuarios (nombre, apellido, correo, username, password, rol, estado, borrado, created_at)
        VALUES (?, ?, ?, ?, ?, 'admin', 0, 0, NOW())
    ");
    $stmt->execute([$admin_name, $admin_lastname, $admin_email, $admin_username, $passwordHash]);
    $adminId = (int)$pdo->lastInsertId();

    // 6) Crear rol admin en roles (si no existe)
    // Nota: tu schema tiene tabla roles con `name`
    $pdo->exec("INSERT INTO roles (name, description) VALUES ('admin', 'Administrador del sistema')");
    $adminRoleId = (int)$pdo->lastInsertId();

    // 7) Asignar todos los permisos al rol admin
    $permIds = $pdo->query("SELECT id FROM permissions")->fetchAll(PDO::FETCH_COLUMN);
    $stmtPerm = $pdo->prepare("INSERT INTO role_permissions (role_id, permission_id) VALUES (?, ?)");
    foreach ($permIds as $pid) {
        $stmtPerm->execute([$adminRoleId, (int)$pid]);
    }

    // 8) Vincular rol_id al usuario admin
    $upd = $pdo->prepare("UPDATE usuarios SET rol_id = ? WHERE id = ?");
    $upd->execute([$adminRoleId, $adminId]);

    // 9) Crear /inc/url_bd.php
    $configContent = "<?php\n";
    $configContent .= "if (!isset(\$host))   \$host   = " . var_export($db_host, true) . ";\n";
    $configContent .= "if (!isset(\$dbname)) \$dbname = " . var_export($db_name, true) . ";\n";
    $configContent .= "if (!isset(\$dbuser)) \$dbuser = " . var_export($db_user, true) . ";\n";
    $configContent .= "if (!isset(\$dbpass)) \$dbpass = " . var_export($db_pass, true) . ";\n\n";
    $configContent .= "\$url_site = " . var_export($site_url, true) . ";\n";
    $configContent .= "?>";

    $configPath = __DIR__ . '/../inc/url_bd.php';
    if (!file_put_contents($configPath, $configContent)) {
        throw new Exception("No se pudo escribir /inc/url_bd.php (permisos).");
    }

    header('Location: ../admin/');
    exit;

} catch (PDOException $e) {
    die('Error de base de datos: ' . $e->getMessage());
} catch (Exception $e) {
    die('Error: ' . $e->getMessage());
}