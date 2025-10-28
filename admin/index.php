<?php
require_once __DIR__ . '/../inc/config.php';
require_once __DIR__ . '/login/session.php';
?>
<!doctype html>
<html lang="es">
<head>
  <meta charset="utf-8">
  <title>Sistema</title>
  <?php require_once __DIR__ . '/inc/header.php'; ?>
 
	
  <style>
    
	  
	  .text-primary {
    color: #000!important;
}
    .card-custom {
      background: #fff;
      border-radius: 12px;
      padding: 30px;
      box-shadow: 0 4px 10px rgba(0,0,0,0.1);
    }
    .section-title {
      font-size: 1.25rem;
      margin-bottom: 15px;
      border-bottom: 2px solid #eee;
      padding-bottom: 5px;
		font-weight: bold;
    }
	  
	  
	  /* Estilos para la tabla */
.custom-table {
  width: 100%;              /* Ajusta el ancho de la tabla */
  font-size: 12px!important;          /* Tamaño de fuente; cámbialo según necesites */
  border-collapse: collapse; /* Elimina espacios entre celdas */
}

/* Estilos para encabezados y celdas */
.custom-table thead {
  background-color: #343a40; /* Color de fondo del encabezado */
  color: #fff;               /* Color de texto del encabezado */
}

.custom-table th,
.custom-table td {
  padding: 12px;             /* Espaciado interno */
  border: 1px solid #ddd;    /* Borde de celdas */
  text-align: left;          /* Alineación del texto */
}

/* Efecto hover en filas */
.custom-table tbody tr:hover {
  background-color: #f8f9fa; /* Color de fondo al pasar el ratón */
}
	  

	  /* contenedor de bienvenida */
.welcome-wrapper{
  position: relative;                 /*  ⤵  el contador se posiciona sobre él   */
}

/* tarjeta–contador: misma línea, pegada al borde derecho */
.asistencias-counter{
  position: absolute;                 /* sale del flujo normal */
  right: 0;                           /* borde derecho */
  top: 50%;                           /* centrado vertical respecto al h1 */
  transform: translateY(-50%);        /* ajusta al centro exacto */
  width: 250px;
  background: linear-gradient(135deg,#E21F0C 0%,#8A0002 100%);
  border-radius: 12px;
  color: #fff;
  box-shadow: 0 4px 10px rgba(0,0,0,.15);
}
.asistencias-counter .display-4{
  font-size: 40px;
  font-weight: 700;
  line-height: 1;
}

  </style>
	
	

</head>
<body>
	
	
	
	
  <?php require_once __DIR__ . '/inc/menu.php'; ?>
	
	<?php
session_start();
if (isset($_SESSION['error'])): ?>
  <div class="alert alert-danger alert-dismissible fade show shadow-sm border-0 mt-3 mx-auto" role="alert" style="max-width:600px;">
    <i class="bi bi-exclamation-triangle-fill me-2"></i>
    <?= htmlspecialchars($_SESSION['error']) ?>
    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Cerrar"></button>
  </div>
<?php 
  unset($_SESSION['error']);
endif; 
?>


  <div class="container my-5">
    <!-- ——— Bienvenida + contador en una sola línea ——— -->
<div class="welcome-wrapper position-relative mb-4">
  <h1 class="welcome-title text-primary text-center m-0">
    Bienvenido, <br><span style="color:#DDC686;"><?php echo htmlspecialchars($nombre . " " . $apellido); ?></span>
  </h1>	
</div>


	  
    
<?php require_once __DIR__ . '/inc/menu-footer.php'; ?>

	
</body>
</html>
