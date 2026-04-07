<div class="menu">
    <div class="logo-container">
        <img src="<?php echo URLBASE . SITE_LOGO; ?>?<?php echo time(); ?>" alt="Logo">
        <br><br>
        <?php echo htmlspecialchars($nombre . " " . $apellido); ?>
        <p><strong><?php echo htmlspecialchars($rolUser); ?></strong></p>			
    </div>      

    <!-- INICIO -->
    <a href="<?php echo URLBASE; ?>/admin/" onclick="closeSubmenus()">
        <i class="fa fa-home"></i> <?php echo t('menu_inicio'); ?>
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
            <i class="fa fa-newspaper"></i> <?php echo t('menu_blog'); ?> <i class="fa fa-chevron-down"></i>
        </a>
        <div class="submenu">
            <?php if (
                in_array('Ver Blogs', $_SESSION["user_permissions"]) ||
                in_array('Crear Entrada', $_SESSION["user_permissions"]) ||
                in_array('Editar Entrada', $_SESSION["user_permissions"]) ||
                in_array('Borrar Entrada', $_SESSION["user_permissions"])
            ): ?>
                <a href="<?php echo URLBASE; ?>/admin/blog/" onclick="closeSubmenus()">- <?php echo t('menu_entradas'); ?></a>
            <?php endif; ?>

            <?php if (
                in_array('Ver Categorias', $_SESSION["user_permissions"]) ||
                in_array('Crear Categorias', $_SESSION["user_permissions"]) ||
                in_array('Editar Categorias', $_SESSION["user_permissions"]) ||
                in_array('Borrar Categorias', $_SESSION["user_permissions"])
            ): ?>
                <a href="<?php echo URLBASE; ?>/admin/blog/categories.php" onclick="closeSubmenus()">- <?php echo t('menu_categorias'); ?></a>
            <?php endif; ?>
        </div>
    <?php endif; ?>

    <!-- MULTIMEDIA -->
<?php if (isset($_SESSION["user_permissions"]) && in_array('Gestionar Multimedia', $_SESSION["user_permissions"])): ?>
    <a href="<?php echo URLBASE; ?>/admin/multimedia/" onclick="closeSubmenus()">
        <i class="fa fa-photo"></i> <?php echo t('menu_multimedia'); ?>
    </a>
<?php endif; ?>

    <!-- MARCA (INSTITUCIONAL) -->
    <?php if (
        isset($_SESSION["user_permissions"]) && (
            in_array('Ver Institucional', $_SESSION["user_permissions"]) ||
            in_array('Crear Institucional', $_SESSION["user_permissions"]) ||
            in_array('Editar Institucional', $_SESSION["user_permissions"]) ||
            in_array('Eliminar Institucional', $_SESSION["user_permissions"])
        )
    ): ?>
        <a href="<?php echo URLBASE; ?>/admin/institutional/" onclick="closeSubmenus()">
            <i class="fa fa-building"></i> <?php echo t('menu_marca'); ?>
        </a>
    <?php endif; ?>

    <!-- PUBLICIDAD -->
    <?php if (isset($_SESSION["user_permissions"]) && in_array('Manejar Publicidad', $_SESSION["user_permissions"])): ?>
        <a href="<?php echo URLBASE; ?>/admin/ads/" onclick="closeSubmenus()">
            <i class="fa fa-money-bill"></i> <?php echo t('menu_publicidad'); ?>
        </a>
    <?php endif; ?>

    <!-- USUARIOS -->
    <?php if (isset($_SESSION["user_permissions"]) && in_array('Ver y Editar Usuarios', $_SESSION["user_permissions"])): ?>
        <a href="#" class="has-submenu" onclick="toggleSubmenu(event)">
            <i class="fa fa-users"></i> <?php echo t('menu_usuarios'); ?> <i class="fa fa-chevron-down"></i>
        </a>
        <div class="submenu">
            <a href="<?php echo URLBASE; ?>/admin/users/" onclick="closeSubmenus()">- <?php echo t('menu_todos'); ?></a>

            <?php if (in_array('Gestionar Roles', $_SESSION["user_permissions"])): ?>
                <a href="<?php echo URLBASE; ?>/admin/users/roles.php" onclick="closeSubmenus()">- <?php echo t('menu_roles'); ?></a>
            <?php endif; ?>
        </div>
    <?php endif; ?>

    <!-- CONFIGURACIONES -->
    <?php if (isset($_SESSION["user_permissions"]) && in_array('Editar Configuraciones', $_SESSION["user_permissions"])): ?>
        <a href="<?php echo URLBASE; ?>/admin/config/" onclick="closeSubmenus()">
            <i class="fa fa-cog"></i> <?php echo t('menu_configuraciones'); ?>
        </a>
        <a href="<?php echo URLBASE; ?>/admin/popups/" onclick="closeSubmenus()">
            <i class="fa fa-bullhorn"></i> <?php echo t('menu_popups'); ?>
        </a>
    <?php endif; ?>

    <!-- PERFIL -->
    <a href="<?php echo URLBASE; ?>/admin/profile/" onclick="closeSubmenus()">
        <i class="fa fa-user"></i> <?php echo t('menu_perfil'); ?>
    </a>

    <!-- HERRAMIENTAS -->
    <?php if (isset($_SESSION["user_permissions"]) && in_array('Editar Configuraciones', $_SESSION["user_permissions"])): ?>
        <a href="#" class="has-submenu" onclick="toggleSubmenu(event)">
            <i class="fas fa-tools"></i> <?php echo t('menu_herramientas'); ?> <i class="fa fa-chevron-down"></i>
        </a>
        <div class="submenu">
            <a href="<?php echo URLBASE; ?>/admin/herramientas/migrar-wordpress.php" onclick="closeSubmenus()">- <?php echo t('menu_migrar_wp'); ?></a>
            <a href="#" onclick="checkForUpdates(event)">- Verificar actualizaciones</a>
            <a href="#" onclick="resetUpdateStatus(event)">- Resetear estado</a>
        </div>
    <?php endif; ?>

    <!-- SALIR -->
    <a href="<?php echo URLBASE; ?>/admin/login/logout.php" onclick="closeSubmenus()">
        <i class="fa fa-power-off"></i> <?php echo t('menu_salir'); ?>
    </a>
</div>

   

