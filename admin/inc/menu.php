<?php require_once __DIR__ . '/topbar.php'; ?>

<div class="menu">
    <div class="logo-container">
        <img src="<?php echo URLBASE . SITE_LOGO; ?>?<?php echo time(); ?>" alt="Logo">
        <div class="user-info">
            <span class="user-name"><?php echo htmlspecialchars($nombre . " " . $apellido); ?></span>
            <span class="user-role"><?php echo htmlspecialchars($rolUser); ?></span>
        </div>
    </div>      

    <!-- INICIO -->
    <a href="<?php echo URLBASE; ?>/admin/" onclick="closeSubmenus()">
        <i class="fas fa-home"></i> <?php echo t('menu_inicio'); ?>
    </a>

    <!-- BLOG -->
    <?php if (
        isset($_SESSION["user_permissions"]) && (
            in_array('Ver Blogs', $_SESSION["user_permissions"]) ||
            in_array('Crear Entrada', $_SESSION["user_permissions"]) ||
            in_array('Editar Entrada', $_SESSION["user_permissions"]) ||
            in_array('Borrar Entrada', $_SESSION["user_permissions"]) ||
            in_array('Ver Categorias', $_SESSION["user_permissions"]) ||
            in_array('Crear Categorias', $_SESSION["user_permissions"]) ||
            in_array('Editar Categorias', $_SESSION["user_permissions"]) ||
            in_array('Borrar Categorias', $_SESSION["user_permissions"])
        )
    ): ?>
        <a href="#" class="has-submenu" onclick="toggleSubmenu(event)">
            <i class="fas fa-newspaper"></i> <?php echo t('menu_blog'); ?> <i class="fas fa-chevron-down"></i>
        </a>
        <div class="submenu">
            <?php if (
                in_array('Ver Blogs', $_SESSION["user_permissions"]) ||
                in_array('Crear Entrada', $_SESSION["user_permissions"]) ||
                in_array('Editar Entrada', $_SESSION["user_permissions"]) ||
                in_array('Borrar Entrada', $_SESSION["user_permissions"])
            ): ?>
                <a href="<?php echo URLBASE; ?>/admin/blog/" onclick="closeSubmenus()"><i class="fas fa-file-alt"></i> <?php echo t('menu_entradas'); ?></a>
            <?php endif; ?>

            <?php if (
                in_array('Ver Categorias', $_SESSION["user_permissions"]) ||
                in_array('Crear Categorias', $_SESSION["user_permissions"]) ||
                in_array('Editar Categorias', $_SESSION["user_permissions"]) ||
                in_array('Borrar Categorias', $_SESSION["user_permissions"])
            ): ?>
                <a href="<?php echo URLBASE; ?>/admin/blog/categories.php" onclick="closeSubmenus()"><i class="fas fa-tags"></i> <?php echo t('menu_categorias'); ?></a>
            <?php endif; ?>
        </div>
    <?php endif; ?>

    <!-- COMENTARIOS -->
    <?php if (isset($_SESSION["user_permissions"]) && (in_array('Gestionar Comentarios', $_SESSION["user_permissions"]) || in_array('admin', $_SESSION["user_permissions"]))): ?>
        <?php
        // Get pending comments count
        $pendingStmt = db()->query("SELECT COUNT(*) FROM comments WHERE estado = 'pending' AND borrado = 0");
        $pendingCount = $pendingStmt->fetchColumn();
        ?>
        <a href="<?php echo URLBASE; ?>/admin/comments/" onclick="closeSubmenus()">
            <i class="fas fa-comments"></i> <?php echo t('menu_comentarios'); ?>
            <?php if ($pendingCount > 0): ?>
            <span class="badge"><?php echo $pendingCount; ?></span>
            <?php endif; ?>
        </a>
    <?php endif; ?>

    <!-- MULTIMEDIA -->
<?php if (isset($_SESSION["user_permissions"]) && in_array('Gestionar Multimedia', $_SESSION["user_permissions"])): ?>
    <a href="<?php echo URLBASE; ?>/admin/multimedia/" onclick="closeSubmenus()">
        <i class="fas fa-photo-video"></i> <?php echo t('menu_multimedia'); ?>
    </a>
<?php endif; ?>

    <!-- INSTITUCIONAL -->
    <?php if (
        isset($_SESSION["user_permissions"]) && (
            in_array('Ver Institucional', $_SESSION["user_permissions"]) ||
            in_array('Crear Institucional', $_SESSION["user_permissions"]) ||
            in_array('Editar Institucional', $_SESSION["user_permissions"]) ||
            in_array('Eliminar Institucional', $_SESSION["user_permissions"])
        )
    ): ?>
        <a href="<?php echo URLBASE; ?>/admin/institutional/" onclick="closeSubmenus()">
            <i class="fas fa-building"></i> <?php echo t('menu_marca'); ?>
        </a>
    <?php endif; ?>

    <!-- PUBLICIDAD -->
    <?php if (isset($_SESSION["user_permissions"]) && in_array('Manejar Publicidad', $_SESSION["user_permissions"])): ?>
        <a href="<?php echo URLBASE; ?>/admin/ads/" onclick="closeSubmenus()">
            <i class="fas fa-ad"></i> <?php echo t('menu_publicidad'); ?>
        </a>
    <?php endif; ?>

    <!-- USUARIOS -->
    <?php if (isset($_SESSION["user_permissions"]) && in_array('Ver y Editar Usuarios', $_SESSION["user_permissions"])): ?>
        <a href="#" class="has-submenu" onclick="toggleSubmenu(event)">
            <i class="fas fa-users"></i> <?php echo t('menu_usuarios'); ?> <i class="fas fa-chevron-down"></i>
        </a>
        <div class="submenu">
            <a href="<?php echo URLBASE; ?>/admin/users/" onclick="closeSubmenus()"><i class="fas fa-user"></i> <?php echo t('menu_todos'); ?></a>

            <?php if (in_array('Gestionar Roles', $_SESSION["user_permissions"])): ?>
                <a href="<?php echo URLBASE; ?>/admin/users/roles.php" onclick="closeSubmenus()"><i class="fas fa-user-shield"></i> <?php echo t('menu_roles'); ?></a>
            <?php endif; ?>
        </div>
    <?php endif; ?>

    <!-- CONFIGURACIONES -->
    <?php if (isset($_SESSION["user_permissions"]) && in_array('Editar Configuraciones', $_SESSION["user_permissions"])): ?>
        <a href="<?php echo URLBASE; ?>/admin/config/" onclick="closeSubmenus()">
            <i class="fas fa-cogs"></i> <?php echo t('menu_configuraciones'); ?>
        </a>
        <a href="<?php echo URLBASE; ?>/admin/popups/" onclick="closeSubmenus()">
            <i class="fas fa-bullhorn"></i> <?php echo t('menu_popups'); ?>
        </a>
    <?php endif; ?>

    <!-- PERFIL -->
    <a href="<?php echo URLBASE; ?>/admin/profile/" onclick="closeSubmenus()">
        <i class="fas fa-user-circle"></i> <?php echo t('menu_perfil'); ?>
    </a>

    <!-- HERRAMIENTAS -->
    <?php if (isset($_SESSION["user_permissions"]) && in_array('Editar Configuraciones', $_SESSION["user_permissions"])): ?>
        <a href="#" class="has-submenu" onclick="toggleSubmenu(event)">
            <i class="fas fa-tools"></i> <?php echo t('menu_herramientas'); ?> <i class="fas fa-chevron-down"></i>
        </a>
        <div class="submenu">
            <a href="<?php echo URLBASE; ?>/admin/herramientas/migrar-wordpress.php" onclick="closeSubmenus()"><i class="fab fa-wordpress"></i> <?php echo t('menu_migrar_wp'); ?></a>
            <a href="#" onclick="checkForUpdates(event)"><i class="fas fa-sync-alt"></i> Verificar actualizaciones</a>            
        </div>
    <?php endif; ?>

    <!-- LOGS -->
    <?php if (isset($_SESSION["user_permissions"]) && in_array('Ver Logs', $_SESSION["user_permissions"])): ?>
        <a href="<?php echo URLBASE; ?>/admin/logs/" onclick="closeSubmenus()">
            <i class="fas fa-history"></i> <?php echo t('menu_logs'); ?>
        </a>
    <?php endif; ?>

    <div class="divider"></div>

    <!-- SALIR -->
    <a href="<?php echo URLBASE; ?>/admin/login/logout.php" onclick="closeSubmenus()" class="logout-btn">
        <i class="fas fa-sign-out-alt"></i> <?php echo t('menu_salir'); ?>
    </a>
</div>

<script>
function toggleSubmenu(event) {
    event.preventDefault();
    const hasSubmenu = event.currentTarget;
    hasSubmenu.classList.toggle('active');
}

function closeSubmenus() {
    document.querySelectorAll('.menu .has-submenu').forEach(el => {
        el.classList.remove('active');
    });
}
</script>