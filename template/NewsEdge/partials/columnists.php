<?php
/* ===== Consulta: Obtener Columnistas Activos (solo ellos, no sus posts) ===== */
$sqlColumnistas = "
    SELECT u.id, 
           u.nombre, 
           u.apellido, 
           u.username,
           u.foto_perfil
    FROM usuarios u
    WHERE u.es_columnista = 1 
      AND u.estado = 0
      AND u.borrado = 0
    ORDER BY u.nombre ASC
    LIMIT 6
";

try {
    $columnistas = db()->query($sqlColumnistas)->fetchAll();
} catch (PDOException $e) {
    error_log("Error en consulta columnistas: " . $e->getMessage());
    $columnistas = [];
}

// Solo mostramos la sección si existen columnistas
if (!empty($columnistas)):
?>
<section class="bg-secondary-body section-space-default">
    <div class="container">
        <div class="row">
            <div class="col-12">
                <div class="topic-border color-cinnabar mb-30 width-100">
                    <div class="topic-box-lg color-cinnabar">NUESTROS COLUMNISTAS</div>
                </div>
            </div>
        </div>
        
        <div class="ne-carousel nav-control-top2 color-white2" 
             data-loop="true" data-items="3" data-margin="10" data-autoplay="true"
             data-autoplay-timeout="5000" data-smart-speed="2000" data-dots="false" data-nav="true" 
             data-nav-speed="false" data-r-x-small="1" data-r-x-small-nav="true" 
             data-r-x-small-dots="false" data-r-x-medium="1" data-r-x-medium-nav="true"
             data-r-x-medium-dots="false" data-r-small="2" data-r-small-nav="true" 
             data-r-small-dots="false" data-r-medium="2" data-r-medium-nav="true" 
             data-r-medium-dots="false" data-r-Large="3" data-r-Large-nav="true" 
             data-r-Large-dots="false">
            
            <?php foreach ($columnistas as $col): 
                $nombreCompleto = htmlspecialchars($col['nombre'] . ' ' . $col['apellido']);
                
                // Si tiene foto de perfil, usarla; si no, usar una imagen por defecto
                if (!empty($col['foto_perfil'])) {
                    $fotoPerfil = img_url($col['foto_perfil']);
                } else {
                    // Avatar por defecto con iniciales
                    $fotoPerfil = 'data:image/svg+xml;base64,' . base64_encode('
                    <svg width="400" height="400" xmlns="http://www.w3.org/2000/svg">
                        <rect width="400" height="400" fill="#667eea"/>
                        <text x="50%" y="50%" font-size="120" fill="white" text-anchor="middle" dy=".35em" font-family="Arial">
                            ' . strtoupper(substr($col['nombre'], 0, 1) . substr($col['apellido'], 0, 1)) . '
                        </text>
                    </svg>');
                }
                
                // URL podría ir a un perfil del columnista o a sus artículos
                // Por ahora lo dejo como # pero puedes cambiarlo
                $columnistaUrl = URLBASE . "/columnista/" . htmlspecialchars($col['username']);
            ?>
            <div class="img-overlay-70-c">
                <div class="mask-content-sm">
                    <div class="topic-box-sm color-cod-gray mb-20">
                        COLUMNISTA
                    </div>
                    <h3 class="title-medium-light">
                        <a href="<?= $columnistaUrl ?>">
                            <?= $nombreCompleto ?>
                        </a>
                    </h3>
                </div>
                
                <!-- Icono de Lápiz para Columnista -->
                <div class="text-center">
                    <a class="play-btn" href="<?= $columnistaUrl ?>">
                        <i class="fa fa-pencil-square-o text-white" 
                           style="font-size: 40px; background: rgba(0,0,0,0.5); padding: 15px; border-radius: 50%;"></i>
                    </a>
                </div>
                
                <!-- Foto de Perfil del Columnista -->
                <img src="<?= $fotoPerfil ?>" 
                     alt="<?= $nombreCompleto ?>" 
                     class="img-fluid width-100"
                     style="height: 350px; object-fit: cover;">
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>
<?php endif; ?>