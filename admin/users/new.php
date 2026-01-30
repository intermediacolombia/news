<?php
session_start(); // SIEMPRE PRIMERO

require_once '../../inc/config.php';    // Debe cargar db()
require_once '../login/session.php';    // Carga datos usuario

$permisopage = 'Ver y Editar Usuarios';
require_once '../login/restriction.php';

// Manejo del envío del formulario
if ($_SERVER["REQUEST_METHOD"] == "POST") {

    // Recuperar y sanitizar los datos del formulario
    $nombre      = trim($_POST['nombre'] ?? '');
    $apellido    = trim($_POST['apellido'] ?? '');
    $correo      = trim($_POST['correo'] ?? '');
    $username    = trim($_POST['username'] ?? '');
    $password    = $_POST['password'] ?? '';
    $rol         = trim($_POST['rol'] ?? '');
    $estado      = trim($_POST['estado'] ?? '');
    $es_columnista = isset($_POST['es_columnista']) ? 1 : 0;

    // Manejo de la imagen de perfil (MISMO SISTEMA QUE EL BLOG)
    $foto_perfil = null;
    $uploadDir   = '../../public/images/users/';
    
    // Crear directorio si no existe
    if (!file_exists($uploadDir)) {
        mkdir($uploadDir, 0755, true);
    }

    if (isset($_FILES['foto_perfil']) && $_FILES['foto_perfil']['error'] === UPLOAD_ERR_OK) {
        $file = $_FILES['foto_perfil'];
        $fileExtension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        $allowedExtensions = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
        
        // Validar extensión
        if (in_array($fileExtension, $allowedExtensions)) {
            // Validar tamaño (2MB máximo)
            if ($file['size'] <= 2 * 1024 * 1024) {
                // Generar nombre único para el archivo (MISMO FORMATO QUE EL BLOG)
                $timestamp = time();
                $newFileName = $timestamp . '_' . preg_replace('/[^a-zA-Z0-9_.-]/', '_', $file['name']);
                $uploadPath = $uploadDir . $newFileName;
                
                if (move_uploaded_file($file['tmp_name'], $uploadPath)) {
                    $foto_perfil = 'public/images/users/' . $newFileName;
                }
            } else {
                $_SESSION['error'] = "La imagen no debe superar 2MB.";
                header("Location: $url/admin/users");
                exit();
            }
        } else {
            $_SESSION['error'] = "Formato de imagen no permitido. Use JPG, PNG, GIF o WebP.";
            header("Location: $url/admin/users");
            exit();
        }
    }

    // Verificar si existe un registro con ese correo o nombre de usuario
    $sqlCheck = "SELECT * FROM usuarios WHERE correo = :correo OR username = :username LIMIT 1";
    $stmtCheck = db()->prepare($sqlCheck);
    $stmtCheck->execute([
        ':correo'   => $correo,
        ':username' => $username
    ]);

    if ($stmtCheck->rowCount() > 0) {
        $existingUser = $stmtCheck->fetch(PDO::FETCH_ASSOC);
        
        // Si el registro existe y está activo (borrado = 0), se rechaza el registro.
        if ($existingUser['borrado'] == 0) {
            $_SESSION['error'] = "El correo o el nombre de usuario ya están registrados.";
            header("Location: $url/admin/users");
            exit();
        } else {
            // Si el registro existe pero estaba borrado, se actualizan TODOS los datos con los nuevos.
            $passwordHash = password_hash($password, PASSWORD_DEFAULT);
            
            // Si hay una foto nueva, eliminar la anterior si existe
            if ($foto_perfil && !empty($existingUser['foto_perfil']) && file_exists('../../' . $existingUser['foto_perfil'])) {
                unlink('../../' . $existingUser['foto_perfil']);
            }
            
            $sqlUpdate = "UPDATE usuarios 
                          SET nombre = :nombre, 
                              apellido = :apellido, 
                              correo = :correo, 
                              username = :username, 
                              password = :password, 
                              rol_id = :rol_id, 
                              estado = :estado, 
                              es_columnista = :es_columnista,
                              foto_perfil = :foto_perfil,
                              borrado = 0 
                          WHERE id = :id";
            $stmtUpdate = db()->prepare($sqlUpdate);
            try {
                $stmtUpdate->execute([
                    ':nombre'        => $nombre,
                    ':apellido'      => $apellido,
                    ':correo'        => $correo,
                    ':username'      => $username,
                    ':password'      => $passwordHash,
                    ':rol_id'        => $rol,
                    ':estado'        => $estado,
                    ':es_columnista' => $es_columnista,
                    ':foto_perfil'   => $foto_perfil,
                    ':id'            => $existingUser['id']
                ]);
                $_SESSION['success'] = "Usuario registrado correctamente.";
            } catch (PDOException $e) {
                $_SESSION['error'] = "Error al actualizar el usuario: " . $e->getMessage();
            }
            header("Location: $url/admin/users");
            exit();
        }
    } else {
        // Si no se encontró ningún registro, se inserta un nuevo usuario.
        $passwordHash = password_hash($password, PASSWORD_DEFAULT);
        $sqlInsert = "INSERT INTO usuarios (nombre, apellido, correo, username, password, rol_id, estado, es_columnista, foto_perfil) 
                      VALUES (:nombre, :apellido, :correo, :username, :password, :rol_id, :estado, :es_columnista, :foto_perfil)";
        $stmtInsert = db()->prepare($sqlInsert);
        try {
            $stmtInsert->execute([
                ':nombre'        => $nombre,
                ':apellido'      => $apellido,
                ':correo'        => $correo,
                ':username'      => $username,
                ':password'      => $passwordHash,
                ':rol_id'        => $rol,
                ':estado'        => $estado,
                ':es_columnista' => $es_columnista,
                ':foto_perfil'   => $foto_perfil
            ]);
            $_SESSION['success'] = "Usuario registrado correctamente.";
        } catch (PDOException $e) {
            $_SESSION['error'] = "Error al registrar el usuario: " . $e->getMessage();
        }
        header("Location: $url/admin/users");
        exit();
    }
}
?>







