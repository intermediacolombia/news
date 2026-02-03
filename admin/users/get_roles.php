<?php
include('../../inc/config.php');

// Debug: Registrar datos recibidos
error_log("POST recibido: " . print_r($_POST, true));

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'];

    if ($action == "add") {
        // Agregar un nuevo rol
        $name = trim($_POST['name']);
        $description = trim($_POST['description']);
        
        // CORRECCIÓN: Validar que permissions sea un array
        $permissions = isset($_POST['permissions']) && is_array($_POST['permissions']) 
            ? $_POST['permissions'] 
            : [];
        
        error_log("Permisos recibidos para ADD: " . print_r($permissions, true));

        // Verificar si el rol ya existe
        $stmtCheck = db()->prepare("SELECT id, borrado FROM roles WHERE name = :name LIMIT 1");
        $stmtCheck->execute([':name' => $name]);
        $existingRole = $stmtCheck->fetch(PDO::FETCH_ASSOC);

        if ($existingRole) {
            if ($existingRole['borrado'] == 0) {
                echo json_encode(['status' => 'error', 'message' => 'El rol ya existe']);
                exit;
            } else {
                // Reactivar el rol si está marcado como borrado
                $roleId = $existingRole['id'];
                $stmtReactivate = db()->prepare("UPDATE roles SET description = :description, borrado = 0 WHERE id = :id");
                if ($stmtReactivate->execute([':description' => $description, ':id' => $roleId])) {
                    // Eliminar permisos antiguos
                    $stmtDeletePermissions = db()->prepare("DELETE FROM role_permissions WHERE role_id = :role_id");
                    $stmtDeletePermissions->execute([':role_id' => $roleId]);

                    // Insertar nuevos permisos
                    if (!empty($permissions)) {
                        foreach ($permissions as $permissionId) {
                            $stmtInsert = db()->prepare("INSERT INTO role_permissions (role_id, permission_id) VALUES (:role_id, :permission_id)");
                            $stmtInsert->execute([
                                ':role_id' => $roleId,
                                ':permission_id' => $permissionId
                            ]);
                        }
                    }

                    echo json_encode(['status' => 'success', 'message' => 'Rol reactivado correctamente']);
                    exit;
                } else {
                    echo json_encode(['status' => 'error', 'message' => 'Error al reactivar el rol']);
                    exit;
                }
            }
        }

        // Insertar el nuevo rol
        $stmt = db()->prepare("INSERT INTO roles (name, description, borrado) VALUES (:name, :description, 0)");
        if ($stmt->execute([':name' => $name, ':description' => $description])) {
            $roleId = db()->lastInsertId();

            // Asignar permisos
            if (!empty($permissions)) {
                foreach ($permissions as $permissionId) {
                    $stmtInsert = db()->prepare("INSERT INTO role_permissions (role_id, permission_id) VALUES (:role_id, :permission_id)");
                    $stmtInsert->execute([
                        ':role_id' => $roleId,
                        ':permission_id' => $permissionId
                    ]);
                }
            }

            echo json_encode(['status' => 'success', 'message' => 'Rol agregado correctamente']);
            exit;
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Error al agregar el rol']);
            exit;
        }

    } elseif ($action == "edit") {
        // Editar un rol existente
        $roleId = trim($_POST['id']);
        $name = trim($_POST['name']);
        $description = trim($_POST['description']);
        
        // CORRECCIÓN: Validar que permissions sea un array
        $permissions = isset($_POST['permissions']) && is_array($_POST['permissions']) 
            ? $_POST['permissions'] 
            : [];
        
        error_log("Permisos recibidos para EDIT (ID: $roleId): " . print_r($permissions, true));

        // Actualizar el rol
        $stmt = db()->prepare("UPDATE roles SET name = :name, description = :description WHERE id = :id");
        $stmt->execute([':name' => $name, ':description' => $description, ':id' => $roleId]);

        // Eliminar permisos antiguos
        $stmt = db()->prepare("DELETE FROM role_permissions WHERE role_id = :role_id");
        $stmt->execute([':role_id' => $roleId]);

        // Insertar nuevos permisos (solo si hay permisos seleccionados)
        if (!empty($permissions)) {
            error_log("Insertando " . count($permissions) . " permisos para el rol $roleId");
            
            foreach ($permissions as $permissionId) {
                $stmtInsert = db()->prepare("INSERT INTO role_permissions (role_id, permission_id) VALUES (:role_id, :permission_id)");
                $result = $stmtInsert->execute([
                    ':role_id' => $roleId,
                    ':permission_id' => $permissionId
                ]);
                
                if (!$result) {
                    error_log("Error insertando permiso $permissionId para rol $roleId");
                }
            }
        } else {
            error_log("ADVERTENCIA: No se recibieron permisos para el rol $roleId");
        }

        echo json_encode(['status' => 'success', 'message' => 'Rol actualizado correctamente']);
        exit;

    } elseif ($action == "delete") {
        // Marcar un rol como borrado (borrado lógico)
        $roleId = trim($_POST['id']);

        // Actualizar el estado de borrado
        $stmt = db()->prepare("UPDATE roles SET borrado = 1 WHERE id = :id");
        if ($stmt->execute([':id' => $roleId])) {
            echo json_encode(['status' => 'success', 'message' => 'Rol borrado correctamente']);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Error al borrar el rol']);
        }
        exit;

    } elseif ($action == "get") {
        // Obtener detalles de un rol específico
        $roleId = trim($_POST['id']);

        // Obtener el rol
        $stmt = db()->prepare("SELECT * FROM roles WHERE id = :id");
        $stmt->execute([':id' => $roleId]);
        $role = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$role) {
            echo json_encode(['status' => 'error', 'message' => 'Rol no encontrado']);
            exit;
        }

        // Obtener los permisos del rol
        $stmt = db()->prepare("SELECT permission_id FROM role_permissions WHERE role_id = :role_id");
        $stmt->execute([':role_id' => $roleId]);
        $permissions = $stmt->fetchAll(PDO::FETCH_COLUMN);
        
        error_log("Permisos del rol $roleId: " . print_r($permissions, true));

        echo json_encode([
            'status' => 'success',
            'data' => [
                'id' => $role['id'],
                'name' => $role['name'],
                'description' => $role['description'],
                'permissions' => $permissions
            ]
        ]);
        exit;
    }

} elseif ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['action']) && $_GET['action'] == "fetch") {
    // Listar todos los roles activos (borrado = 0)
    $stmt = db()->query("SELECT id, name, description FROM roles WHERE borrado = 0");
    $roles = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo json_encode(['data' => $roles]);
    exit;
}
?>