<?php
session_start();

require_once '../../inc/config.php';
require_once '../login/session.php';

$permisopage = 'Ver y Editar Usuarios';
require_once '../login/restriction.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    
    // Recuperar datos del formulario
    $id          = intval($_POST['id'] ?? 0);
    $nombre      = trim($_POST['nombre'] ?? '');
    $apellido    = trim($_POST['apellido'] ?? '');
    $correo      = trim($_POST['correo'] ?? '');
    $rol         = trim($_POST['rol'] ?? '');
    $estado      = trim($_POST['estado'] ?? '');
    $password    = $_POST['password'] ?? '';
    $es_columnista = isset($_POST['es_columnista']) ? 1 : 0;
    $foto_actual = $_POST['foto_actual'] ?? '';
    $remove_foto = intval($_POST['remove_foto'] ?? 0);

    if ($id <= 0) {
        $_SESSION['error'] = "ID de usuario inválido.";
        header("Location: $url/admin/users");
        exit();
    }

    try {
        // Verificar que el usuario existe
        $stmtCheck = db()->prepare("SELECT * FROM usuarios WHERE id = :id LIMIT 1");
        $stmtCheck->execute([':id' => $id]);
        $user = $stmtCheck->fetch(PDO::FETCH_ASSOC);

        if (!$user) {
            $_SESSION['error'] = "Usuario no encontrado.";
            header("Location: $url/admin/users");
            exit();
        }

        // Manejo de imagen de perfil (MISMO SISTEMA QUE EL BLOG)
        $foto_perfil = $foto_actual;
        $uploadDir = '../../public/images/users/';
        
        // Crear directorio si no existe
        if (!file_exists($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }

        // Si se marcó para eliminar la foto
        if ($remove_foto === 1) {
            if (!empty($foto_actual) && file_exists('../../' . $foto_actual)) {
                unlink('../../' . $foto_actual);
            }
            $foto_perfil = null;
        }

        // Si se subió una nueva imagen
        if (isset($_FILES['foto_perfil']) && $_FILES['foto_perfil']['error'] === UPLOAD_ERR_OK) {
            $file = $_FILES['foto_perfil'];
            $fileExtension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
            $allowedExtensions = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
            
            // Validar extensión
            if (in_array($fileExtension, $allowedExtensions)) {
                // Validar tamaño (2MB máximo)
                if ($file['size'] <= 2 * 1024 * 1024) {
                    // Eliminar foto anterior si existe
                    if (!empty($foto_actual) && file_exists('../../' . $foto_actual)) {
                        unlink('../../' . $foto_actual);
                    }
                    
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

        // Preparar la consulta de actualización
        if (!empty($password)) {
            // Si se proporciona una nueva contraseña, actualizar también la contraseña
            $passwordHash = password_hash($password, PASSWORD_DEFAULT);
            $sqlUpdate = "UPDATE usuarios 
                          SET nombre = :nombre, 
                              apellido = :apellido, 
                              correo = :correo, 
                              password = :password, 
                              rol_id = :rol_id, 
                              estado = :estado,
                              es_columnista = :es_columnista,
                              foto_perfil = :foto_perfil
                          WHERE id = :id";
            $params = [
                ':nombre'        => $nombre,
                ':apellido'      => $apellido,
                ':correo'        => $correo,
                ':password'      => $passwordHash,
                ':rol_id'        => $rol,
                ':estado'        => $estado,
                ':es_columnista' => $es_columnista,
                ':foto_perfil'   => $foto_perfil,
                ':id'            => $id
            ];
        } else {
            // Si no se proporciona contraseña, no actualizar ese campo
            $sqlUpdate = "UPDATE usuarios 
                          SET nombre = :nombre, 
                              apellido = :apellido, 
                              correo = :correo, 
                              rol_id = :rol_id, 
                              estado = :estado,
                              es_columnista = :es_columnista,
                              foto_perfil = :foto_perfil
                          WHERE id = :id";
            $params = [
                ':nombre'        => $nombre,
                ':apellido'      => $apellido,
                ':correo'        => $correo,
                ':rol_id'        => $rol,
                ':estado'        => $estado,
                ':es_columnista' => $es_columnista,
                ':foto_perfil'   => $foto_perfil,
                ':id'            => $id
            ];
        }

        $stmtUpdate = db()->prepare($sqlUpdate);
        $stmtUpdate->execute($params);

        $_SESSION['success'] = "Usuario actualizado correctamente.";
        header("Location: $url/admin/users");
        exit();

    } catch (PDOException $e) {
        $_SESSION['error'] = "Error al actualizar el usuario: " . $e->getMessage();
        header("Location: $url/admin/users");
        exit();
    }

} else {
    $_SESSION['error'] = "Método de solicitud no válido.";
    header("Location: $url/admin/users");
    exit();
}
?>





