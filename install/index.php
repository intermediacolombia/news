<?php
// Verificar si ya está instalado
if (file_exists(__DIR__ . '/../inc/url_bd.php')) {
    die('
    <!DOCTYPE html>
    <html lang="es">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Ya instalado</title>
        <style>
            body { font-family: Arial, sans-serif; background: #f4f4f4; display: flex; align-items: center; justify-content: center; height: 100vh; margin: 0; }
            .box { background: #fff; padding: 40px; border-radius: 8px; box-shadow: 0 4px 20px rgba(0,0,0,0.1); text-align: center; max-width: 500px; }
            h1 { color: #e74c3c; margin-bottom: 20px; }
            p { color: #555; line-height: 1.6; }
            a { color: #3498db; text-decoration: none; font-weight: bold; }
        </style>
    </head>
    <body>
        <div class="box">
            <h1>⚠️ Sistema ya instalado</h1>
            <p>El sistema ya ha sido instalado previamente.</p>
            <p>Si deseas reinstalar, elimina el archivo <code>/inc/url_bd.php</code> manualmente.</p>
            <p><a href="/">← Volver al inicio</a></p>
        </div>
    </body>
    </html>
    ');
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Instalación del CMS</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); display: flex; align-items: center; justify-content: center; min-height: 100vh; padding: 20px; }
        .container { background: #fff; padding: 40px; border-radius: 12px; box-shadow: 0 10px 40px rgba(0,0,0,0.2); max-width: 600px; width: 100%; }
        h1 { color: #333; margin-bottom: 10px; font-size: 28px; text-align: center; }
        .subtitle { text-align: center; color: #777; margin-bottom: 30px; font-size: 14px; }
        .form-group { margin-bottom: 20px; }
        label { display: block; margin-bottom: 8px; color: #555; font-weight: 600; font-size: 14px; }
        input[type="text"], input[type="password"], input[type="email"] { width: 100%; padding: 12px 15px; border: 2px solid #e0e0e0; border-radius: 6px; font-size: 14px; transition: border 0.3s; }
        input:focus { outline: none; border-color: #667eea; }
        .btn { width: 100%; padding: 14px; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: #fff; border: none; border-radius: 6px; font-size: 16px; font-weight: bold; cursor: pointer; transition: transform 0.2s; }
        .btn:hover { transform: translateY(-2px); }
        .section-title { background: #f8f9fa; padding: 10px 15px; border-left: 4px solid #667eea; margin: 30px 0 20px 0; font-weight: bold; color: #333; }
        .help-text { font-size: 12px; color: #999; margin-top: 5px; }
    </style>
</head>
<body>
    <div class="container">
        <h1>🚀 Instalación del CMS</h1>
        <p class="subtitle">Configura tu sistema en pocos pasos</p>

        <form action="process.php" method="POST">
            
            <!-- Datos de Base de Datos -->
            <div class="section-title">📊 Configuración de Base de Datos</div>
            
            <div class="form-group">
                <label for="db_host">Host de la Base de Datos</label>
                <input type="text" id="db_host" name="db_host" value="localhost" required>
                <div class="help-text">Generalmente es "localhost"</div>
            </div>

            <div class="form-group">
                <label for="db_name">Nombre de la Base de Datos</label>
                <input type="text" id="db_name" name="db_name" required>
                <div class="help-text">La base de datos será creada automáticamente</div>
            </div>

            <div class="form-group">
                <label for="db_user">Usuario de MySQL</label>
                <input type="text" id="db_user" name="db_user" required>
            </div>

            <div class="form-group">
                <label for="db_pass">Contraseña de MySQL</label>
                <input type="password" id="db_pass" name="db_pass">
            </div>

            <div class="form-group">
                <label for="site_url">URL del Sitio</label>
                <input type="text" id="site_url" name="site_url" value="http://localhost" required>
                <div class="help-text">Ejemplo: http://misitio.com (sin barra final)</div>
            </div>

            <!-- Datos del Administrador -->
            <div class="section-title">👤 Usuario Administrador</div>

            <div class="form-group">
                <label for="admin_name">Nombre</label>
                <input type="text" id="admin_name" name="admin_name" required>
            </div>

            <div class="form-group">
                <label for="admin_lastname">Apellido</label>
                <input type="text" id="admin_lastname" name="admin_lastname" required>
            </div>

            <div class="form-group">
                <label for="admin_email">Email</label>
                <input type="email" id="admin_email" name="admin_email" required>
            </div>

            <div class="form-group">
                <label for="admin_username">Nombre de Usuario</label>
                <input type="text" id="admin_username" name="admin_username" required>
            </div>

            <div class="form-group">
                <label for="admin_password">Contraseña</label>
                <input type="password" id="admin_password" name="admin_password" required>
                <div class="help-text">Mínimo 6 caracteres</div>
            </div>

            <button type="submit" class="btn">Instalar Sistema</button>
        </form>
    </div>
</body>
</html>