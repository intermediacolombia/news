<?php
function repair_database() {
    $results = [
        'permissions' => [],
        'tables' => [],
        'errors' => []
    ];
    
    try {
        $newPermissions = [
            [23, 'Ver Logs', 'Sistema'],
        ];
        
        foreach ($newPermissions as $perm) {
            list($id, $name, $category) = $perm;
            $stmt = db()->prepare("SELECT id FROM permissions WHERE id = ?");
            $stmt->execute([$id]);
            
            if (!$stmt->fetch()) {
                $insert = db()->prepare("INSERT INTO permissions (id, name, category, created_at, updated_at) VALUES (?, ?, ?, NOW(), NOW())");
                $insert->execute([$id, $name, $category]);
                $results['permissions'][] = "Agregado: $name";
            } else {
                $results['permissions'][] = "Ya existe: $name";
            }
        }
        
        $tables = [
            'system_logs' => "
                CREATE TABLE IF NOT EXISTS `system_logs` (
                  `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT,
                  `user_id` int DEFAULT NULL,
                  `username` varchar(50) DEFAULT NULL,
                  `action` varchar(100) NOT NULL,
                  `description` text,
                  `entity_type` varchar(50) DEFAULT NULL,
                  `entity_id` bigint DEFAULT NULL,
                  `ip_address` varchar(45) DEFAULT NULL,
                  `user_agent` text,
                  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
                  PRIMARY KEY (`id`),
                  KEY `idx_user_id` (`user_id`),
                  KEY `idx_action` (`action`),
                  KEY `idx_entity` (`entity_type`, `entity_id`),
                  KEY `idx_created_at` (`created_at`)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
            "
        ];
        
        foreach ($tables as $tableName => $createSQL) {
            $stmt = db()->query("SHOW TABLES LIKE '$tableName'");
            if (!$stmt->fetch()) {
                db()->exec($createSQL);
                $results['tables'][] = "Creada tabla: $tableName";
            } else {
                $results['tables'][] = "Ya existe tabla: $tableName";
            }
        }
        
    } catch (Exception $e) {
        $results['errors'][] = $e->getMessage();
    }
    
    return $results;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'repair') {
    header('Content-Type: application/json');
    $results = repair_database();
    echo json_encode($results);
    exit;
}
?>

<div class="row">
    <div class="col-12">
        <div class="alert alert-info">
            <i class="fa fa-info-circle"></i>
            <strong>Herramienta de reparación de base de datos.</strong>
            Esta herramienta agregará automáticamente permisos y tablas faltantes sin afectar los datos existentes.
        </div>
        
        <div class="mb-3">
            <h5><i class="fa fa-wrench"></i> Reparar Base de Datos</h5>
            <p class="text-muted">Se agregarán automáticamente:</p>
            <ul>
                <li>Permisos nuevos del sistema</li>
                <li>Tablas nuevas necesarias</li>
            </ul>
        </div>
        
        <button type="button" class="btn btn-primary" onclick="runRepair()">
            <i class="fa fa-cogs"></i> Ejecutar Reparación
        </button>
        
        <div id="repairResult" class="repair-result mt-3" style="display: none;"></div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
function runRepair() {
    Swal.fire({
        title: '¿Ejecutar reparación?',
        text: 'Se agregarán los elementos faltantes a la base de datos.',
        icon: 'question',
        showCancelButton: true,
        confirmButtonText: 'Sí, ejecutar',
        cancelButtonText: 'Cancelar'
    }).then((result) => {
        if (result.isConfirmed) {
            const btn = document.querySelector('button.btn-primary');
            btn.disabled = true;
            btn.innerHTML = '<i class="fa fa-spinner fa-spin"></i> Reparando...';
            
            fetch('index.php?tab=sistema', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: 'action=repair'
            })
            .then(r => r.json())
            .then(data => {
                let html = '';
                const resultDiv = document.getElementById('repairResult');
                
                if (data.permissions && data.permissions.length > 0) {
                    html += '<h6>Permisos:</h6>';
                    data.permissions.forEach(p => {
                        html += `<div class="text-success">✓ ${p}</div>`;
                    });
                }
                
                if (data.tables && data.tables.length > 0) {
                    html += '<h6 class="mt-2">Tablas:</h6>';
                    data.tables.forEach(t => {
                        html += `<div class="text-success">✓ ${t}</div>`;
                    });
                }
                
                if (data.errors && data.errors.length > 0) {
                    html += '<h6 class="mt-2 text-danger">Errores:</h6>';
                    data.errors.forEach(e => {
                        html += `<div class="text-danger">✗ ${e}</div>`;
                    });
                }
                
                resultDiv.innerHTML = html;
                resultDiv.style.display = 'block';
                
                btn.disabled = false;
                btn.innerHTML = '<i class="fa fa-cogs"></i> Ejecutar Reparación';
                
                Swal.fire('¡Completado!', 'La base de datos ha sido reparada.', 'success');
            })
            .catch(err => {
                Swal.fire('Error', 'No se pudo completar la reparación.', 'error');
                btn.disabled = false;
                btn.innerHTML = '<i class="fa fa-cogs"></i> Ejecutar Reparación';
            });
        }
    });
}
</script>
