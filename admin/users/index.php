<?php 
include('../login/session.php'); 
$permisopage = 'Ver y Editar Usuarios';
include('../login/restriction.php');
session_start();

require_once __DIR__ . '/../../inc/config.php';

// ===== Conexión única PDO =====
try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $dbuser, $dbpass, [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
    ]);
} catch (Throwable $e) {
    die("DB error: " . $e->getMessage());
}

// ===== Roles activos para selects =====
try {
    $stmtRoles = $pdo->prepare("SELECT * FROM roles WHERE borrado = 0");
    $stmtRoles->execute();
    $roles = $stmtRoles->fetchAll();
} catch (PDOException $e) {
    $roles = [];
}

// ===== Usuarios (no borrados) con nombre de rol =====
try {
    $sql = "SELECT u.*, r.name AS rol
            FROM usuarios u
            LEFT JOIN roles r ON u.rol_id = r.id
            WHERE u.borrado = 0";
    $stmtUsers = $pdo->query($sql);
    $usuarios  = $stmtUsers->fetchAll();
} catch (PDOException $e) {
    $usuarios = [];
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <title>Listado de Usuarios</title>
  <?php include('../inc/header.php'); ?>
  
  <style>
    /* Estilos de la tabla según lo solicitado */
    #formularios tbody tr,
    #formularios tbody tr td {
      cursor: pointer !important;
      transition: background-color 0.1s ease;
    }
    #formularios thead th {
      background-color: #214A82;
      color: white;
    }
    #formularios tbody tr:hover {
      background-color: #4972AA !important;
    }
    #formularios tbody tr:hover td {
      color: white !important;
    }
    /* La columna de acción no dispara redirección */
    .no-click {
      cursor: default !important;
    }
  </style>
</head>
<body>
<div class="container" style="padding: 0px; background:rgba(0,0,0,0.00)">
  <div class="portada">
    <h1>Listado de Usuarios</h1>

    <!-- BS5: data-bs-* -->
    <button type="button" class="btn btn-primary float-end" data-bs-toggle="modal" data-bs-target="#newUserModal">
      <i class="fas fa-user-plus"></i> Nuevo Usuario
    </button>
  </div>
</div>

<?php include('../inc/menu.php'); ?>

<div class="container mt-4">
  <!-- Alertas de Bootstrap para mensajes de sesión -->
  <?php if(isset($_SESSION['error'])): ?>
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
      <?php echo $_SESSION['error']; unset($_SESSION['error']); ?>
      <!-- BS5 close -->
      <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Cerrar"></button>
    </div>
  <?php endif; ?>
  <?php if(isset($_SESSION['success'])): ?>
    <div class="alert alert-success alert-dismissible fade show" role="alert">
      <?php echo $_SESSION['success']; unset($_SESSION['success']); ?>
      <!-- BS5 close -->
      <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Cerrar"></button>
    </div>
  <?php endif; ?>

  <!-- Tabla de usuarios -->
  <table id="formularios" class="table table-striped table-bordered">
    <thead>
      <tr>
        <th>Nombre de Usuario</th>
        <th>Nombre</th>
        <th>Apellido</th>
        <th>Correo</th>
        <th>Rol</th>
        <th>Estado</th>
        <th>Acción</th>
      </tr>
    </thead>
    <tbody>
      <?php if (!empty($usuarios)): ?>
        <?php foreach ($usuarios as $row): ?>
          <tr class="user-row" 
              data-id="<?= (int)$row['id'] ?>"
              data-username="<?= htmlspecialchars($row['username']) ?>"
              data-nombre="<?= htmlspecialchars($row['nombre']) ?>"
              data-apellido="<?= htmlspecialchars($row['apellido']) ?>"
              data-correo="<?= htmlspecialchars($row['correo']) ?>"
              data-rol="<?= htmlspecialchars($row['rol_id']) ?>"
              data-estado="<?= htmlspecialchars($row['estado']) ?>">
            <td><?= htmlspecialchars($row['username']) ?></td>
            <td><?= htmlspecialchars($row['nombre']) ?></td>
            <td><?= htmlspecialchars($row['apellido']) ?></td>
            <td><?= htmlspecialchars($row['correo']) ?></td>
            <td><?= htmlspecialchars($row['rol'] ?? 'Sin Rol') ?></td>
            <td>
              <?php if ((int)$row['estado'] === 0): ?>
                <span class="badge bg-success">Activo</span>
              <?php else: ?>
                <span class="badge bg-danger">Inactivo</span>
              <?php endif; ?>
            </td>
            <td class="no-click text-center">
              <button type="button" class="btn btn-sm btn-outline-danger delete-btn" data-id="<?= (int)$row['id'] ?>">
                <i class="fas fa-trash"></i>
              </button>
            </td>
          </tr>
        <?php endforeach; ?>
      <?php else: ?>
        <tr><td colspan="7" class="text-center">No hay usuarios.</td></tr>
      <?php endif; ?>
    </tbody>
  </table>
</div>

<!-- Modal Nuevo Usuario (BS5) -->
<div class="modal fade" id="newUserModal" tabindex="-1" aria-labelledby="newUserModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <form method="post" action="new.php" class="needs-validation" novalidate>
        <div class="modal-header">
          <h5 class="modal-title" id="newUserModalLabel">Nuevo Usuario</h5>
          <!-- BS5 close -->
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
        </div>
        <div class="modal-body">
          <!-- Campos de registro -->
          <div class="mb-3">
            <label for="newNombre" class="form-label">Nombre</label>
            <input type="text" class="form-control" id="newNombre" name="nombre" required>
            <div class="invalid-feedback">Ingrese su nombre.</div>
          </div>
          <div class="mb-3">
            <label for="newApellido" class="form-label">Apellido</label>
            <input type="text" class="form-control" id="newApellido" name="apellido" required>
            <div class="invalid-feedback">Ingrese su apellido.</div>
          </div>
          <div class="mb-3">
            <label for="newCorreo" class="form-label">Correo</label>
            <input type="email" class="form-control" id="newCorreo" name="correo" required>
            <div class="invalid-feedback">Ingrese un correo válido.</div>
          </div>
          <div class="mb-3">
            <label for="newUsername" class="form-label">Nombre de Usuario</label>
            <input type="text" class="form-control" id="newUsername" name="username" required oninput="this.value = this.value.toLowerCase()">
            <div class="invalid-feedback">Ingrese un nombre de usuario.</div>
          </div>

          <div class="row">
            <div class="col-md-6 mb-3">
              <label for="newPassword" class="form-label">Contraseña</label>
              <div class="input-group">
                <input type="password" class="form-control" id="newPassword" name="password" required>
                <button class="btn btn-outline-secondary toggle-password" type="button" data-target="#newPassword">
                  <i class="fas fa-eye"></i>
                </button>
                <div class="invalid-feedback">Ingrese una contraseña.</div>
              </div>
            </div>
            <div class="col-md-6 mb-3">
              <label for="newConfirmPassword" class="form-label">Confirmar Contraseña</label>
              <div class="input-group">
                <input type="password" class="form-control" id="newConfirmPassword" name="confirm_password" required>
                <button class="btn btn-outline-secondary toggle-password" type="button" data-target="#newConfirmPassword">
                  <i class="fas fa-eye"></i>
                </button>
              </div>
              <small id="newPasswordHelp" class="text-danger" style="display:none;">Las contraseñas no coinciden.</small>
            </div>
          </div>

          <div class="row">
            <div class="col-md-6 mb-3">
              <label for="newRol" class="form-label">Rol</label>
              <select class="form-select" id="newRol" name="rol" required>
                <option value="">Seleccione un rol</option>
                <?php foreach ($roles as $role): ?>
                  <option value="<?= htmlspecialchars($role['id']) ?>">
                    <?= htmlspecialchars($role['name']) ?>
                  </option>
                <?php endforeach; ?>
              </select>
              <div class="invalid-feedback">Seleccione un rol.</div>
            </div>
            <div class="col-md-6 mb-3">
              <label for="newEstado" class="form-label">Estado</label>
              <select class="form-select" id="newEstado" name="estado" required>
                <option value="">Seleccione un estado</option>
                <option value="0">Activo</option>
                <option value="1">Inactivo</option>
              </select>
              <div class="invalid-feedback">Seleccione un estado.</div>
            </div>
          </div>
        </div>
        <div class="modal-footer">
          <!-- BS5 dismiss -->
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
          <button type="submit" class="btn btn-primary" id="newRegisterBtn" disabled>
            <i class="fa fa-save"></i> Registrar
          </button>
        </div>
      </form>
    </div>
  </div>
</div>

<!-- Modal Editar Usuario (BS5) -->
<div class="modal fade" id="editModal" tabindex="-1" aria-labelledby="editModalLabel" aria-hidden="true">
 <div class="modal-dialog">
   <div class="modal-content">
     <form method="post" action="update_user.php" class="needs-validation" novalidate id="editForm">
       <div class="modal-header">
         <h5 class="modal-title" id="editModalLabel">Editar Usuario</h5>
         <!-- BS5 close -->
         <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
       </div>
       <div class="modal-body">
         <!-- Campo oculto para el ID -->
         <input type="hidden" id="editId" name="id">

         <div class="mb-3">
           <label for="editUsername" class="form-label">Nombre de Usuario</label>
           <input type="text" class="form-control" id="editUsername" name="username" readonly>
           <small class="text-muted">El nombre de usuario no se puede modificar.</small>
         </div>
         <div class="mb-3">
           <label for="editNombre" class="form-label">Nombre</label>
           <input type="text" class="form-control" id="editNombre" name="nombre" required>
           <div class="invalid-feedback">Ingrese el nombre.</div>
         </div>
         <div class="mb-3">
           <label for="editApellido" class="form-label">Apellido</label>
           <input type="text" class="form-control" id="editApellido" name="apellido" required>
           <div class="invalid-feedback">Ingrese el apellido.</div>
         </div>
         <div class="mb-3">
           <label for="editCorreo" class="form-label">Correo</label>
           <input type="email" class="form-control" id="editCorreo" name="correo" required>
           <div class="invalid-feedback">Ingrese un correo válido.</div>
         </div>
         <div class="mb-3">
           <label for="editRol" class="form-label">Rol</label>
           <select class="form-select" id="editRol" name="rol" required>
             <option value="">Seleccione un rol</option>
             <?php foreach ($roles as $role): ?>
               <option value="<?= htmlspecialchars($role['id']) ?>"><?= htmlspecialchars($role['name']) ?></option>
             <?php endforeach; ?>
           </select>
           <div class="invalid-feedback">Seleccione un rol.</div>
         </div>
         <div class="mb-3">
           <label for="editEstado" class="form-label">Estado</label>
           <select class="form-select" id="editEstado" name="estado" required>
             <option value="">Seleccione un estado</option>
             <option value="0">Activo</option>
             <option value="1">Inactivo</option>
           </select>
           <div class="invalid-feedback">Seleccione un estado.</div>
         </div>

         <!-- Campos de cambio de contraseña -->
         <div class="mb-3">
           <label for="editPassword" class="form-label">Nueva Contraseña (dejar en blanco para no cambiar)</label>
           <div class="input-group">
             <input type="password" class="form-control" id="editPassword" name="password">
             <button class="btn btn-outline-secondary toggle-password" type="button" data-target="#editPassword">
               <i class="fas fa-eye"></i>
             </button>
           </div>
         </div>
         <div class="mb-3">
           <label for="editConfirmPassword" class="form-label">Confirmar Nueva Contraseña</label>
           <div class="input-group">
             <input type="password" class="form-control" id="editConfirmPassword" name="confirm_password">
             <button class="btn btn-outline-secondary toggle-password" type="button" data-target="#editConfirmPassword">
               <i class="fas fa-eye"></i>
             </button>
           </div>
           <small id="editPasswordHelp" class="text-danger" style="display: none;">Las contraseñas no coinciden.</small>
         </div>
       </div>
       <div class="modal-footer">
         <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
         <button type="submit" class="btn btn-primary"><i class="fa fa-save"></i> Guardar Cambios</button>
       </div>
     </form>
   </div>
 </div>
</div>

<?php include('../inc/menu-footer.php'); ?>

<script>
$(document).ready(function() {
  // ===== DataTables (es-ES) =====
  var table = $('#formularios').DataTable({
    language: { url: "//cdn.datatables.net/plug-ins/1.13.7/i18n/es-ES.json" },
    order: [[0, "asc"]],
    pageLength: 50
  });
  
  // ===== Click en fila para abrir modal de edición (excepto botón borrar) =====
  $('#formularios tbody').on('click', 'tr.user-row', function(e) {
    if ($(e.target).closest('.delete-btn').length > 0) return;

    const id       = $(this).data('id');
    const username = $(this).data('username');
    const nombre   = $(this).data('nombre');
    const apellido = $(this).data('apellido');
    const correo   = $(this).data('correo');
    const rol      = $(this).data('rol');
    const estado   = $(this).data('estado');

    // Rellenar el modal de edición
    $('#editId').val(id);
    $('#editUsername').val(username);
    $('#editNombre').val(nombre);
    $('#editApellido').val(apellido);
    $('#editCorreo').val(correo);
    $('#editRol').val(rol);
    $('#editEstado').val(estado);

    // Limpiar campos de contraseña y ocultar mensaje de error
    $('#editPassword').val('');
    $('#editConfirmPassword').val('');
    $('#editPasswordHelp').hide();

    // BS5: abrir modal con API
    const modalEl = document.getElementById('editModal');
    const editBsModal = bootstrap.Modal.getOrCreateInstance(modalEl);
    editBsModal.show();
  });

  // ===== Alternar visibilidad de contraseñas =====
  $('.toggle-password').on('click', function() {
    const targetInput = $($(this).data('target'));
    if (targetInput.attr('type') === 'password') {
      targetInput.attr('type', 'text');
      $(this).html('<i class="fas fa-eye-slash"></i>');
    } else {
      targetInput.attr('type', 'password');
      $(this).html('<i class="fas fa-eye"></i>');
    }
  });

  // ===== Validación en tiempo real (Nuevo) =====
  $('#newConfirmPassword, #newPassword, #newNombre, #newApellido, #newCorreo, #newUsername, #newRol, #newEstado').on('input change', function() {
    const pass = $('#newPassword').val();
    const conf = $('#newConfirmPassword').val();
    if (conf && pass !== conf) {
      $('#newPasswordHelp').show();
    } else {
      $('#newPasswordHelp').hide();
    }
    updateNewRegisterButton();
  });

  function updateNewRegisterButton() {
    let isValid = true;
    const requiredIds = ['newNombre','newApellido','newCorreo','newUsername','newPassword','newRol','newEstado','newConfirmPassword'];
    requiredIds.forEach(function(id) {
      const val = ($('#' + id).val() || '').toString().trim();
      if (!val) isValid = false;
    });
    if ($('#newPassword').val() !== $('#newConfirmPassword').val()) {
      isValid = false;
    }
    $('#newRegisterBtn').prop('disabled', !isValid);
  }

  // ===== Borrado con SweetAlert =====
  $('#formularios').on('click', '.delete-btn', function(e) {
    e.stopPropagation();
    const id = $(this).data('id');
    Swal.fire({
      title: '¿Está seguro?',
      text: "Esta acción borrará el usuario.",
      icon: 'warning',
      showCancelButton: true,
      confirmButtonText: 'Sí, borrar',
      cancelButtonText: 'Cancelar'
    }).then((result) => {
      if (result.isConfirmed) {
        window.location = "dele_user.php?id=" + id;
      }
    });
  });

  // ===== Validación envío (Editar) =====
  $('#editModal form').on('submit', function(event) {
    const pass = $('#editPassword').val();
    const conf = $('#editConfirmPassword').val();
    if (pass !== '' && pass !== conf) {
      event.preventDefault();
      event.stopPropagation();
      $('#editPasswordHelp').show();
      return false;
    }
  });
});
</script>
</body>
</html>
