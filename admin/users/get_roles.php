<?php
include('../../inc/config.php');

// Función helper para logging (opcional, puedes comentarla en producción)
function logDebug($message, $data = null) {
    error_log("ROLES DEBUG: " . $message . ($data ? " - " . json_encode($data) : ""));
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    
    // Log para debug
    logDebug("Action recibida", $action);
    logDebug("POST data", $_POST);

    if ($action == "add") {
        // Agregar un nuevo rol
        $name = trim($_POST['name']);
        $description = trim($_POST['description']);
        $permissions = isset($_POST['permissions']) && is_array($_POST['permissions']) ? $_POST['permissions'] : [];
        
        logDebug("Agregando rol", ['name' => $name, 'permissions_count' => count($permissions)]);

        // Verificar si el rol ya existe
        $stmtCheck = db()->prepare("SELECT id, borrado FROM roles WHERE name = :name LIMIT 1");
        $stmtCheck->execute([':name' => $name]);
        $existingRole = $stmtCheck->fetch(PDO::FETCH_ASSOC);

        if ($existingRole) {
            if ($existingRole['borrado'] == 0) {
                // El rol ya existe y no está borrado
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
                            $stmtPerm = db()->prepare("INSERT INTO role_permissions (role_id, permission_id) VALUES (:role_id, :permission_id)");
                            $stmtPerm->execute([
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
            
            logDebug("Rol creado con ID", $roleId);

            // Asignar permisos
            if (!empty($permissions)) {
                foreach ($permissions as $permissionId) {
                    $stmtPerm = db()->prepare("INSERT INTO role_permissions (role_id, permission_id) VALUES (:role_id, :permission_id)");
                    $stmtPerm->execute([
                        ':role_id' => $roleId,
                        ':permission_id' => $permissionId
                    ]);
                }
                logDebug("Permisos insertados", count($permissions));
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
        $permissions = isset($_POST['permissions']) && is_array($_POST['permissions']) ? $_POST['permissions'] : [];
        
        logDebug("Editando rol", [
            'role_id' => $roleId, 
            'name' => $name, 
            'permissions_count' => count($permissions),
            'permissions' => $permissions
        ]);

        // Actualizar el rol
        $stmt = db()->prepare("UPDATE roles SET name = :name, description = :description WHERE id = :id");
        $stmt->execute([':name' => $name, ':description' => $description, ':id' => $roleId]);
        
        logDebug("Rol actualizado");

        // Eliminar permisos antiguos
        $stmt = db()->prepare("DELETE FROM role_permissions WHERE role_id = :role_id");
        $stmt->execute([':role_id' => $roleId]);
        
        logDebug("Permisos antiguos eliminados");

        // Insertar nuevos permisos
        if (!empty($permissions)) {
            $insertedCount = 0;
            foreach ($permissions as $permissionId) {
                try {
                    $stmtPerm = db()->prepare("INSERT INTO role_permissions (role_id, permission_id) VALUES (:role_id, :permission_id)");
                    $stmtPerm->execute([
                        ':role_id' => $roleId,
                        ':permission_id' => $permissionId
                    ]);
                    $insertedCount++;
                } catch (PDOException $e) {
                    logDebug("Error insertando permiso", ['permission_id' => $permissionId, 'error' => $e->getMessage()]);
                }
            }
            logDebug("Nuevos permisos insertados", $insertedCount);
        } else {
            logDebug("ADVERTENCIA: Array de permisos vacío");
        }

        echo json_encode([
            'status' => 'success', 
            'message' => 'Rol actualizado correctamente',
            'debug' => [
                'permissions_received' => count($permissions),
                'role_id' => $roleId
            ]
        ]);
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

        // Obtener los permisos del rol
        $stmt = db()->prepare("SELECT permission_id FROM role_permissions WHERE role_id = :role_id");
        $stmt->execute([':role_id' => $roleId]);
        $permissions = $stmt->fetchAll(PDO::FETCH_COLUMN);
        
        logDebug("Obteniendo rol", ['role_id' => $roleId, 'permissions_count' => count($permissions)]);

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

// Si llegamos aquí, acción no reconocida
echo json_encode(['status' => 'error', 'message' => 'Acción no reconocida']);
?>