<?php
if (!defined('DIRECT_ACCESS') && !isset($config)) {
    require_once $_SERVER['DOCUMENT_ROOT'] . '/inc/config.php';
}

$sql = "
    SELECT nombre, apellido
    FROM usuarios
    WHERE es_columnista = 1 
      AND estado = 0 
      AND borrado = 0
    ORDER BY nombre, apellido
";

try {
    $stmt = db()->query($sql);
    $columnistas = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    error_log("Error al listar columnistas: " . $e->getMessage());
    $columnistas = [];
}
?>

<div class="container py-5">
    <h1 class="text-center mb-4">Nuestros Columnistas</h1>

    <?php if (!empty($columnistas)): ?>
        <div class="row g-4">
            <?php foreach ($columnistas as $col): 
                $fullName = $col['nombre'] . ' ' . $col['apellido'];
                $slug = strtolower(trim(preg_replace('/[^A-Za-z0-9]+/', '-', $fullName)));
                $url = URLBASE . '/columnista/' . $slug;
            ?>
                <div class="col-md-4">
                    <div class="card">
                        <div class="card-body text-center">
                            <h5 class="card-title"><?= htmlspecialchars($fullName) ?></h5>
                            <a href="<?= $url ?>" class="btn btn-primary">Ver columnas</a>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php else: ?>
        <p class="text-center">No hay columnistas disponibles.</p>
    <?php endif; ?>
</div>