<?php
require_once __DIR__ . '/../../inc/config.php';
require_once __DIR__ . '/../login/session.php';
header('Content-Type: application/json');

$permisopage = 'Editar Configuraciones';
require_once __DIR__ . '/../login/restriction.php';

try {
    db()->exec("SET NAMES utf8mb4");

    if (isset($_GET['delete']) && $_GET['delete'] == '1' && !empty($_GET['id'])) {
        $stmt = db()->prepare("DELETE FROM popups WHERE id = ?");
        $stmt->execute([$_GET['id']]);
        log_system_action('Eliminar Popup', json_encode(['id' => $_GET['id']]), 'popups', $_GET['id']);
        echo json_encode(['success' => true, 'message' => 'Popup eliminado']);
        exit;
    }

    if (!empty($_GET['id'])) {
        $stmt = db()->prepare("SELECT * FROM popups WHERE id = ?");
        $stmt->execute([$_GET['id']]);
        $popup = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($popup) {
            echo json_encode(['success' => true, 'popup' => $popup]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Popup no encontrado']);
        }
        exit;
    }

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $id = !empty($_POST['id']) ? $_POST['id'] : null;
        $title = $_POST['title'] ?? '';
        $content = $_POST['content'] ?? '';
        $popup_type = $_POST['popup_type'] ?? 'modal';
        $position = $_POST['position'] ?? 'center';
        $width = $_POST['width'] ?? '500px';
        $background_color = $_POST['background_color'] ?? '#ffffff';
        $text_color = $_POST['text_color'] ?? '#333333';
        $delay_seconds = intval($_POST['delay_seconds'] ?? 3);
        $auto_close_seconds = intval($_POST['auto_close_seconds'] ?? 0);
        $button_text = $_POST['button_text'] ?? 'Cerrar';
        $button_color = $_POST['button_color'] ?? '#007bff';
        $button_text_color = $_POST['button_text_color'] ?? '#ffffff';
        $action_type = $_POST['action_type'] ?? 'none';
        $action_url = $_POST['action_url'] ?? '';
        $action_new_tab = isset($_POST['action_new_tab']) ? 1 : 0;
        $overlay_enabled = isset($_POST['overlay_enabled']) ? 1 : 0;
        $show_title = isset($_POST['show_title']) ? 1 : 0;
        $status = $_POST['status'] ?? 'inactive';
        $show_on_pages = $_POST['show_on_pages'] ?? 'all';
        $show_on_all_pages = $show_on_pages === 'all' ? 1 : 0;
        $show_once_per_visit = $_POST['show_once_per_visit'] ?? '1';

        $imagePath = $_POST['existing_image'] ?? '';
        $uploadDir = __DIR__ . '/../../public/uploads/popups/';
        
        if (!empty($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK && !empty($_FILES['image']['name'])) {
            if (!is_dir($uploadDir)) {
                if (!mkdir($uploadDir, 0755, true)) {
                    throw new Exception('No se pudo crear la carpeta de uploads: ' . $uploadDir);
                }
            }
            
            $ext = strtolower(pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION));
            $allowed = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
            if (!in_array($ext, $allowed)) {
                throw new Exception('Tipo de archivo no permitido. Solo: jpg, jpeg, png, gif, webp');
            }
            
            $filename = 'popup_' . time() . '.' . $ext;
            $dest = $uploadDir . $filename;
            
            if (!is_writable($uploadDir)) {
                throw new Exception('La carpeta uploads no tiene permisos de escritura');
            }
            
            if (move_uploaded_file($_FILES['image']['tmp_name'], $dest)) {
                $imagePath = 'public/uploads/popups/' . $filename;
            } else {
                throw new Exception('Error al mover el archivo subido');
            }
        }

        $popupAction = $id ? 'Editar Popup' : 'Crear Popup';
        if ($id) {
            $sql = "UPDATE popups SET
                title = ?, content = ?, popup_type = ?, position = ?, width = ?,
                background_color = ?, text_color = ?, delay_seconds = ?, auto_close_seconds = ?,
                button_text = ?, button_color = ?, button_text_color = ?, action_type = ?, action_url = ?,
                action_new_tab = ?, overlay_enabled = ?, show_title = ?, status = ?, show_on_all_pages = ?, show_once_per_visit = ?";
            $params = [$title, $content, $popup_type, $position, $width, $background_color, $text_color, 
                $delay_seconds, $auto_close_seconds, $button_text, $button_color, $button_text_color,
                $action_type, $action_url, $action_new_tab, $overlay_enabled, $show_title, $status, $show_on_all_pages, $show_once_per_visit];
            
            if ($imagePath) {
                $sql .= ", image = ?";
                $params[] = $imagePath;
            }
            $sql .= " WHERE id = ?";
            $params[] = $id;
            
            $stmt = db()->prepare($sql);
            $stmt->execute($params);
        } else {
$sql = "INSERT INTO popups (title, content, image, popup_type, position, width, background_color, text_color,
                delay_seconds, auto_close_seconds, button_text, button_color, button_text_color, action_type, action_url,
                action_new_tab, overlay_enabled, show_title, status, show_on_all_pages, show_once_per_visit)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
            $stmt = db()->prepare($sql);
            $stmt->execute([$title, $content, $imagePath, $popup_type, $position, $width, $background_color, $text_color,
                $delay_seconds, $auto_close_seconds, $button_text, $button_color, $button_text_color, $action_type, $action_url,
                $action_new_tab, $overlay_enabled, $show_title, $status, $show_on_all_pages, $show_once_per_visit]);
        }

        log_system_action($popupAction, json_encode(['id' => $id, 'title' => $title, 'status' => $status]), 'popups', $id);
        echo json_encode(['success' => true, 'message' => 'Popup guardado correctamente']);
    }

} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
}
