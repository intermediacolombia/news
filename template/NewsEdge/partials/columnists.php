<?php
/* ===== Consulta: Obtener Columnistas (Usuarios con es_columnista = 1) ===== */
$sqlColumnistas = "
    SELECT u.id, u.nombre, u.apellido, u.foto_perfil,
           p.title, p.slug, p.image, p.created_at
    FROM usuarios u
    INNER JOIN blog_posts p ON p.author = CONCAT(u.nombre, ' ', u.apellido)
    WHERE u.es_columnista = 1 
      AND u.estado = 1 
      AND u.borrado = 0
      AND p.status = 'published' 
      AND p.deleted = 0
    GROUP BY u.id, p.id
    ORDER BY p.created_at DESC
    LIMIT 6
";
$columnistas = db()->query($sqlColumnistas)->fetchAll();

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
                $postUrl = URLBASE . "/noticias/post/" . htmlspecialchars($col['slug']);
                $nombreCompleto = htmlspecialchars($col['nombre'] . ' ' . $col['apellido']);
                $fotoPerfil = !empty($col['foto_perfil']) ? img_url($col['foto_perfil']) : img_url($col['image']);
            ?>
            <div class="img-overlay-70-c">
                <div class="mask-content-sm">
                    <div class="topic-box-sm color-cod-gray mb-20">
                        <?= $nombreCompleto ?>
                    </div>
                    <h3 class="title-medium-light">
                        <a href="<?= $postUrl ?>">
                            <?= truncate_text($col['title'], 70) ?>
                        </a>
                    </h3>
                </div>
                
                <!-- Icono de Lápiz para Columnista -->
                <div class="text-center">
                    <a class="play-btn" href="<?= $postUrl ?>">
                        <i class="fa fa-pencil-square-o text-white" style="font-size: 40px; background: rgba(0,0,0,0.5); padding: 15px; border-radius: 50%;"></i>
                    </a>
                </div>

                <!-- Imagen de Perfil del Columnista (o imagen del post si no tiene foto) -->
                <img src="<?= $fotoPerfil ?>" 
                     alt="<?= $nombreCompleto ?>" 
                     class="img-fluid width-100"
                     style="height: 350px; object-fit: cover;">
            </div>
            <?php endforeach; ?>

        </div>
    </div>
</section>

<?php endif; // Fin de validación si existen columnistas ?>