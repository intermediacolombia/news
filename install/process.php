<?php
// Verificar si ya está instalado
if (file_exists(__DIR__ . '/../inc/url_bd.php')) {
    die('El sistema ya está instalado. Elimina /inc/url_bd.php para reinstalar.');
}

// Capturar datos del formulario
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

// Validaciones
if (empty($db_host) || empty($db_name) || empty($db_user) || empty($site_url) || 
    empty($admin_name) || empty($admin_email) || empty($admin_username) || empty($admin_password)) {
    die('Error: Todos los campos son obligatorios.');
}

if (strlen($admin_password) < 6) {
    die('Error: La contraseña debe tener al menos 6 caracteres.');
}

try {
    // 1. Conectar a MySQL
    $pdo = new PDO(
        "mysql:host=$db_host;charset=utf8mb4",
        $db_user,
        $db_pass,
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
    );

    // 2. Crear la base de datos
    $pdo->exec("CREATE DATABASE IF NOT EXISTS `$db_name` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
    $pdo->exec("USE `$db_name`");

    // 3. Leer el archivo db.sql
    $sqlFile = __DIR__ . '/db.sql';
    if (!file_exists($sqlFile)) {
        die('Error: No se encontró el archivo db.sql en /install/');
    }

    $sql = file_get_contents($sqlFile);
    
    // 4. Ejecutar el SQL (dividir por punto y coma)
    $statements = array_filter(array_map('trim', explode(';', $sql)));
    foreach ($statements as $statement) {
        if (!empty($statement) && !preg_match('/^--/', $statement)) {
            $pdo->exec($statement);
        }
    }

    // 5. Crear usuario administrador
    $passwordHash = password_hash($admin_password, PASSWORD_BCRYPT);
    
    $stmt = $pdo->prepare("
        INSERT INTO usuarios (nombre, apellido, correo, username, password, rol, estado, borrado, created_at)
        VALUES (?, ?, ?, ?, ?, 'admin', 1, 0, NOW())
    ");
    $stmt->execute([$admin_name, $admin_lastname, $admin_email, $admin_username, $passwordHash]);
    
    $adminId = $pdo->lastInsertId();

    // 6. Crear rol "admin" en la tabla roles
    $pdo->exec("INSERT INTO roles (name, description) VALUES ('admin', 'Administrador del sistema')");
    $adminRoleId = $pdo->lastInsertId();

    // 7. Asignar TODOS los permisos al rol admin
    $permisos = $pdo->query("SELECT id FROM permissions")->fetchAll(PDO::FETCH_COLUMN);
    $stmtPerm = $pdo->prepare("INSERT INTO role_permissions (role_id, permission_id) VALUES (?, ?)");
    foreach ($permisos as $permId) {
        $stmtPerm->execute([$adminRoleId, $permId]);
    }

    // 8. Actualizar el usuario admin con el rol_id
    $pdo->exec("UPDATE usuarios SET rol_id = $adminRoleId WHERE id = $adminId");

    // 9. Generar el archivo /inc/url_bd.php (TAL CUAL lo pediste)
    $configContent = "<?php\n";
    $configContent .= "if (!isset(\$host))   \$host   = " . var_export($db_host, true) . ";\n";
    $configContent .= "if (!isset(\$dbname)) \$dbname = " . var_export($db_name, true) . ";\n";
    $configContent .= "if (!isset(\$dbuser)) \$dbuser = " . var_export($db_user, true) . ";\n";
    $configContent .= "if (!isset(\$dbpass)) \$dbpass = " . var_export($db_pass, true) . ";\n\n";
    $configContent .= "\$url_site = " . var_export($site_url, true) . ";\n\n";
    $configContent .= "?>";

    $configPath = __DIR__ . '/../inc/url_bd.php';
    if (!file_put_contents($configPath, $configContent)) {
        throw new Exception('No se pudo crear el archivo /inc/url_bd.php. Verifica permisos de escritura.');
    }

    // 10. Redirigir al login
    header('Location: ../admin/login.php?installed=1');
    exit;

} catch (PDOException $e) {
    die('Error de base de datos: ' . $e->getMessage());
} catch (Exception $e) {
    die('Error: ' . $e->getMessage());
}