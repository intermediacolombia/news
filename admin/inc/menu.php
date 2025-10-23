<div class="menu">
        <div class="logo-container">
            <img src="<?php echo URLBASE; ?><?php echo SITE_LOGO; ?>?<?php echo time()?>" alt="Logo">
			 <br><br>
   <?php echo htmlspecialchars($nombre . " " . $apellido); ?>
    <p><strong><?php echo htmlspecialchars($rolUser); ?></strong></p>			
    <!-- Resto del contenido de tu dashboard -->  
        </div>      
	<a href="<?php echo URLBASE;?>/admin/" onclick="closeSubmenus()"><i class="fa fa-home"></i> Inicio</a>

	
	
	
	
	<a href="#" class="has-submenu" onclick="toggleSubmenu(event)">
        <i class="fa-solid fa-newspaper"></i>Blog <i class="fa fa-chevron-down"></i>
    </a>
    <div class="submenu">
            <a href="<?php echo URLBASE;?>/admin/blog/" onclick="closeSubmenus()">- Entradas
            </a>
		<a href="<?php echo URLBASE;?>/admin/blog/categories.php" onclick="closeSubmenus()">- Categorias
            </a>		
    </div>
	
	<a href="<?php echo URLBASE;?>/admin/ads/" onclick="closeSubmenus()"><i class="fa-solid fa-money-bill-trend-up"></i> Publicidad</a>

	<?php
// Verificar si el usuario tiene al menos uno de los permisos requeridos
if (
    isset($_SESSION["user_permissions"]) && 
    (in_array('Ver Clientes', $_SESSION["user_permissions"]) || in_array('Ver Clientes Pre-inscritos', $_SESSION["user_permissions"])|| in_array('Ver Asistencias', $_SESSION["user_permissions"]))
): 
?>
	<a href="#" class="has-submenu" onclick="toggleSubmenu(event)">
        <i class="far fa-address-book"></i>  Clientes <i class="fa fa-chevron-down"></i>
    </a>
    <div class="submenu">
		<?php if (isset($_SESSION["user_permissions"]) && in_array('Ver Clientes', $_SESSION["user_permissions"])): ?>  
            <a href="<?php echo URLBASE; ?>/admin/clients/" onclick="closeSubmenus()">- Todos
            </a> 
		<a href="<?php echo URLBASE; ?>/admin/clients/client_search.php" onclick="closeSubmenus()">- Búsqueda Avanzada
            </a>  
        <?php endif; ?>
		
        <?php if (isset($_SESSION["user_permissions"]) && in_array('Ver Clientes Pre-inscritos', $_SESSION["user_permissions"])): ?>
            <a href="<?php echo URLBASE; ?>/admin/clients/pre-registered.php" onclick="closeSubmenus()">- Pre Inscritos
            </a>
		<?php endif; ?>
		
		<?php if (isset($_SESSION["user_permissions"]) && in_array('Ver Asistencias', $_SESSION["user_permissions"])): ?>
            <a href="<?php echo URLBASE; ?>/admin/clients/asistencias.php" onclick="closeSubmenus()">- Asistencias
            </a>
		<?php endif; ?>
		
    </div>	
	<?php endif; ?>
	
	
	
	
	<?php if (isset($_SESSION["user_permissions"]) && in_array('Manejar Valoraciones', $_SESSION["user_permissions"])): ?>
	<a href="<?php echo URLBASE;?>/admin/valoraciones" onclick="closeSubmenus()"><i class="fa fa-heartbeat"></i> Valoraciones</a>
	<?php endif; ?>
	
	<?php if (isset($_SESSION["user_permissions"]) && in_array('Ver Planes', $_SESSION["user_permissions"])): ?>
	<a href="<?php echo URLBASE;?>/admin/plans" onclick="closeSubmenus()"><i class="fa fa-fire-alt"></i> Planes</a>
	<?php endif; ?>
	
	
	<?php
// Verificar si el usuario tiene al menos uno de los permisos requeridos
if (
    isset($_SESSION["user_permissions"]) && 
    (in_array('Ver Ejercicios', $_SESSION["user_permissions"]) || in_array('Ver Rutinas', $_SESSION["user_permissions"]))
): 
?>
	<a href="#" class="has-submenu" onclick="toggleSubmenu(event)">
        <i class="material-icons" style="font-size:16px">fitness_center</i> Rutinas <i class="fa fa-chevron-down"></i>
    </a>
    <div class="submenu">
		<?php if (isset($_SESSION["user_permissions"]) && in_array('Ver Rutinas', $_SESSION["user_permissions"])): ?>  
            <a href="<?php echo URLBASE; ?>/admin/routines/" onclick="closeSubmenus()">- Gestionar
            </a>  
        <?php endif; ?>
        <?php if (isset($_SESSION["user_permissions"]) && in_array('Ver Ejercicios', $_SESSION["user_permissions"])): ?>
            <a href="<?php echo URLBASE; ?>/admin/routines/ejercicios.php" onclick="closeSubmenus()">- Ejercicios
            </a>
		<?php endif; ?>
		
    </div>	
	<?php endif; ?>
	
	
	
	
	
	
	
	<?php
// Verificar si el usuario tiene al menos uno de los permisos requeridos
if (
    isset($_SESSION["user_permissions"]) && 
    (in_array('Usar Cajas', $_SESSION["user_permissions"]) || in_array('Ver Todas las Cajas', $_SESSION["user_permissions"]))
): 
?>
	<a href="#" class="has-submenu" onclick="toggleSubmenu(event)">
        <i class="fa fa-cash-register"></i> Caja <i class="fa fa-chevron-down"></i>
    </a>
    <div class="submenu">
        <?php if (isset($_SESSION["user_permissions"]) && in_array('Usar Cajas', $_SESSION["user_permissions"])): ?>
            <a href="<?php echo URLBASE; ?>/admin/caja/" onclick="closeSubmenus()">
                - Mi Caja Abierta
            </a>
            <a href="<?php echo URLBASE; ?>/admin/caja/cajas_list.php" onclick="closeSubmenus()">
                - Mis Cajas Cerradas
            </a>
        <?php endif; ?>
        <?php if (isset($_SESSION["user_permissions"]) && in_array('Ver Todas las Cajas', $_SESSION["user_permissions"])): ?>  
            <a href="<?php echo URLBASE; ?>/admin/caja/cajas_list_all.php" onclick="closeSubmenus()">- Todas las Cajas
            </a>  
        <?php endif; ?>
    </div>
<?php endif; ?>
	<?php if (isset($_SESSION["user_permissions"]) && in_array('Ver y Editar Productos', $_SESSION["user_permissions"])): ?>
	<a href="#" class="has-submenu" onclick="toggleSubmenu(event)"><i class='fa fa-shopping-bag'></i> Productos <i class="fa fa-chevron-down"></i></a>
        <div class="submenu">
	<a href="<?php echo URLBASE;?>/admin/products" onclick="closeSubmenus()">- Productos en Stock</a>
			
	<?php if (isset($_SESSION["user_permissions"]) && in_array('Ver y Editar Bolsillos', $_SESSION["user_permissions"])): ?>
	<a href="<?php echo URLBASE;?>/admin/products/pocket.php" onclick="closeSubmenus()">- Bolsillos</a>
	<?php endif; ?>
	</div>
	<?php endif; ?>	
	<?php
// Verificar si el usuario tiene al menos uno de los permisos requeridos
if (
    isset($_SESSION["user_permissions"]) && 
    (in_array('Ver Nominas', $_SESSION["user_permissions"]) || in_array('Pagar Nominas', $_SESSION["user_permissions"]) || in_array('Ver Empleados', $_SESSION["user_permissions"]))
): 
?>
	<a href="#" class="has-submenu" onclick="toggleSubmenu(event)">
        <i class="far fa-id-card"></i> Nomina <i class="fa fa-chevron-down"></i>
    </a>
    <div class="submenu">
		<?php if (isset($_SESSION["user_permissions"]) && in_array('Ver Empleados', $_SESSION["user_permissions"])): ?>  
            <a href="<?php echo URLBASE; ?>/admin/payroll/employees.php" onclick="closeSubmenus()">- Empleados
            </a>  
        <?php endif; ?>
        <?php if (isset($_SESSION["user_permissions"]) && in_array('Pagar Nominas', $_SESSION["user_permissions"])): ?>
            <a href="<?php echo URLBASE; ?>/admin/payroll/" onclick="closeSubmenus()">- Pagar Nomina
            </a>
		<?php endif; ?>
		<?php if (isset($_SESSION["user_permissions"]) && in_array('Ver Nominas', $_SESSION["user_permissions"])): ?>
		
            <a href="<?php echo URLBASE; ?>/admin/payroll/all_payroll.php" onclick="closeSubmenus()">- Nominas Pagadas
            </a>
        <?php endif; ?>

    </div>	
	<?php endif; ?>
	<?php if (isset($_SESSION["user_permissions"]) && in_array('Manejar Contabilidad', $_SESSION["user_permissions"])): ?>
	<a href="#" class="has-submenu" onclick="toggleSubmenu(event)"><i class='fa fa-coins'></i> Contabilidad <i class="fa fa-chevron-down"></i></a>
        <div class="submenu">			
	<?php if (isset($_SESSION["user_permissions"]) && in_array('Ver Reportes Contabilidad', $_SESSION["user_permissions"])): ?>		
            <a href="<?php echo URLBASE; ?>/admin/contabilidad/reporte.php" onclick="closeSubmenus()">- Reportes
            </a>
        <?php endif; ?>
	<?php if (isset($_SESSION["user_permissions"]) && in_array('Ver Egresos', $_SESSION["user_permissions"])): ?>		
            <a href="<?php echo URLBASE; ?>/admin/contabilidad/expenses.php" onclick="closeSubmenus()">- Egresos
            </a>
        <?php endif; ?>			
			<a href="<?php echo URLBASE;?>/admin/contabilidad/invoices.php" onclick="closeSubmenus()">- Facturas</a>
	</div>	
	<?php endif; ?>
	 <?php if (isset($_SESSION["user_permissions"]) && in_array('Ver y Editar Usuarios', $_SESSION["user_permissions"])): ?>
	<a href="#" class="has-submenu" onclick="toggleSubmenu(event)"><i class="fa-solid fa-users-gear"></i> Usuarios <i class="fa fa-chevron-down"></i></a>
        <div class="submenu">
	 <a href="<?php echo URLBASE;?>/admin/users/" onclick="closeSubmenus()">- Todos</a>			
	<?php if (isset($_SESSION["user_permissions"]) && in_array('Gestionar Roles', $_SESSION["user_permissions"])): ?>			
	<a href="<?php echo URLBASE;?>/admin/users/roles.php" onclick="closeSubmenus()">- Roles</a>
	<?php endif; ?>
	</div>
	 <?php endif; ?>
	
	<a href="<?php echo URLBASE;?>/admin/config/" onclick="closeSubmenus()"><i class="fa fa-cog"></i> Configuraciones</a>            
	
	<a href="<?php echo URLBASE;?>/admin/profile/" onclick="closeSubmenus()"><i class="fa fa-user"></i> Perfil</a>
	
	
	<?php if (isset($_SESSION["user_permissions"]) && in_array('Ver Estadisticas', $_SESSION["user_permissions"])): ?>
	 <a href="<?php echo URLBASE;?>/admin/statistics/" onclick="closeSubmenus()"><i class="fa fa-bar-chart"></i> Estadisticas</a>
	<?php endif; ?>
	
	<?php if (isset($_SESSION["user_permissions"]) && in_array('Configurar Sistema', $_SESSION["user_permissions"])): ?>
	<a href="<?php echo URLBASE;?>/admin/config/" onclick="closeSubmenus()"><i class="fa fa-cog"></i> Configuraciones</a>
	<?php endif; ?>   
    <!--a href="<?php echo URLBASE;?>/admin/support/" onclick="closeSubmenus()"><i class="fas fa-headset"></i> Soporte </a>
    <a href="https://app.360messenger.com/index.php?rp=/login" target="_blank" onclick="closeSubmenus()"><i class="fa fa-whatsapp"></i> API </a>
	<!-- Si la caja está cerrada -->

  <!-- Si la caja está abierta, abre el modal usando data-bs-toggle en lugar de JavaScript manual -->
  <a href="<?php echo URLBASE;?>/admin/login/logout.php" onclick="closeSubmenus()">
    <i class="fa fa-power-off"></i> Salir
  </a>

	<!-- Modal si la caja está abierta -->
</div>
   

