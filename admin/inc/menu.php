<div class="menu">
    <div class="logo-container">
        <img src="<?php echo URLBASE . SITE_LOGO; ?>?<?php echo time(); ?>" alt="Logo">
        <br><br>
        <?php echo htmlspecialchars($nombre . " " . $apellido); ?>
        <p><strong><?php echo htmlspecialchars($rolUser); ?></strong></p>			
    </div>      

    <!-- INICIO -->
    <a href="<?php echo URLBASE; ?>/admin/" onclick="closeSubmenus()">
        <i class="fa fa-home"></i> Inicio
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
            <i class="fa-solid fa-newspaper"></i> Blog <i class="fa fa-chevron-down"></i>
        </a>
        <div class="submenu">
            <?php if (
                in_array('Ver Blogs', $_SESSION["user_permissions"]) ||
                in_array('Crear Entrada', $_SESSION["user_permissions"]) ||
                in_array('Editar Entrada', $_SESSION["user_permissions"]) ||
                in_array('Borrar Entrada', $_SESSION["user_permissions"])
            ): ?>
                <a href="<?php echo URLBASE; ?>/admin/blog/" onclick="closeSubmenus()">- Entradas</a>
            <?php endif; ?>

            <?php if (
                in_array('Ver Categorias', $_SESSION["user_permissions"]) ||
                in_array('Crear Categorias', $_SESSION["user_permissions"]) ||
                in_array('Editar Categorias', $_SESSION["user_permissions"]) ||
                in_array('Borrar Categorias', $_SESSION["user_permissions"])
            ): ?>
                <a href="<?php echo URLBASE; ?>/admin/blog/categories.php" onclick="closeSubmenus()">- Categorías</a>
            <?php endif; ?>
        </div>
    <?php endif; ?>

    <!-- PUBLICIDAD -->
    <?php if (isset($_SESSION["user_permissions"]) && in_array('Manejar Publicidad', $_SESSION["user_permissions"])): ?>
        <a href="<?php echo URLBASE; ?>/admin/ads/" onclick="closeSubmenus()">
            <i class="fa-solid fa-money-bill-trend-up"></i> Publicidad
        </a>
    <?php endif; ?>

    <!-- USUARIOS -->
    <?php if (isset($_SESSION["user_permissions"]) && in_array('Ver y Editar Usuarios', $_SESSION["user_permissions"])): ?>
        <a href="#" class="has-submenu" onclick="toggleSubmenu(event)">
            <i class="fa-solid fa-users-gear"></i> Usuarios <i class="fa fa-chevron-down"></i>
        </a>
        <div class="submenu">
            <a href="<?php echo URLBASE; ?>/admin/users/" onclick="closeSubmenus()">- Todos</a>

            <?php if (in_array('Gestionar Roles', $_SESSION["user_permissions"])): ?>
                <a href="<?php echo URLBASE; ?>/admin/users/roles.php" onclick="closeSubmenus()">- Roles</a>
            <?php endif; ?>
        </div>
    <?php endif; ?>

    <!-- CONFIGURACIONES -->
    <?php if (isset($_SESSION["user_permissions"]) && in_array('Editar Configuraciones', $_SESSION["user_permissions"])): ?>
        <a href="<?php echo URLBASE; ?>/admin/config/" onclick="closeSubmenus()">
            <i class="fa fa-cog"></i> Configuraciones
        </a>
    <?php endif; ?>

    <!-- PERFIL -->
    <a href="<?php echo URLBASE; ?>/admin/profile/" onclick="closeSubmenus()">
        <i class="fa fa-user"></i> Perfil
    </a>

    <!-- SALIR -->
    <a href="<?php echo URLBASE; ?>/admin/login/logout.php" onclick="closeSubmenus()">
        <i class="fa fa-power-off"></i> Salir
    </a>
</div>

   

