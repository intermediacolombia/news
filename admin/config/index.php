<?php
require_once __DIR__ . '/../../inc/config.php';
require_once __DIR__ . '/../login/session.php';
$permisopage = 'Editar Configuraciones';
require_once __DIR__ . '/../login/restriction.php';
require_once __DIR__ . '/../inc/flash_helpers.php';

if (!headers_sent()) header('Content-Type: text/html; charset=UTF-8');
ini_set('default_charset', 'UTF-8');
mb_internal_encoding('UTF-8');
db()->exec("SET NAMES utf8mb4");

$activeTab = $_GET['tab'] ?? 'generales';

$stmt = db()->query("SELECT setting_name, value, enabled FROM system_settings ORDER BY setting_name ASC");
$configs = []; $configs_enabled = [];
foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $row) {
    $configs[$row['setting_name']]         = $row['value'];
    $configs_enabled[$row['setting_name']] = $row['enabled'];
}

$defaults = [
    'site_name'=>'','site_logo'=>'','site_favicon'=>'','banner_inferior'=>'',
    'enable_text_to_speech'=>'0','site_email'=>'','business_address'=>'',
    'business_phone'=>'','info_footer'=>'','business_map'=>'',
    'about_us'=>'','terms-and-conditions'=>'','privacy-policy'=>'','return-policy'=>'',
    'code_head'=>'','code_footer'=>'','code_sliderbar'=>'','code_player'=>'',
    'player_height'=>'70','enable_stop_player'=>'0',
    'facebook'=>'','instagram'=>'','youtube'=>'','tiktok'=>'',
    'whatsapp'=>'','twitter'=>'','hashtag'=>'',
    'seo_home_title'=>'','seo_home_description'=>'','seo_home_keywords'=>'',
    'adsense_publisher_id'=>'','adsense_auto_ads'=>'0',
    'verify_google'=>'','verify_bing'=>'','verify_yandex'=>'',
    'verify_meta'=>'','verify_pinterest'=>'','gtm_container_id'=>'',
    'free_shipping'=>'','mercadopago_access_token'=>'','mercadopago_public_key'=>'',
    'api_whatsapp'=>'',
    'mail_new_order_message'=>'','mail_shipped_message'=>'','mail_delivered_message'=>'',
    'ws_new_order_message'=>'','ws_shipped_message'=>'','ws_delivered_message'=>'',
    'mail_smtp_host'=>'','mail_smtp_user'=>'','mail_smtp_pass'=>'',
    'mail_smtp_port'=>'587','mail_sender'=>'',
    'feature1_icon'=>'fa-truck','feature1_text'=>'We ship worldwide',
    'feature2_icon'=>'fa-headset','feature2_text'=>'Call +1 800 789 0000',
    'feature3_icon'=>'fa-money-bill','feature3_text'=>'Money Back Guarantee',
    'feature4_icon'=>'fa-undo','feature4_text'=>'30 days return',
    'special_menu_text'=>'','special_menu_link'=>'#',
    'primary'=>'#5fca00','color-hover-link'=>'#214A82','site_theme'=>'',
    'admin_language'=>'es','site_language'=>'es',
];
foreach ($defaults as $k => $v) {
    if (!isset($configs[$k]))         $configs[$k]         = $v;
    if (!isset($configs_enabled[$k])) $configs_enabled[$k] = 1;
}
?>
<!doctype html>
<html lang="es">
<head>
  <meta charset="utf-8">
  <title>Configuraciones del Sistema</title>
  <?php require_once __DIR__ . '/../inc/header.php'; ?>
</head>
<body>

<div class="container" style="padding:0; background:rgba(0,0,0,0)">
  <div class="portada"><h1 class="mb-4">Configuraciones del Sistema</h1></div>
</div>

<?php require_once __DIR__ . '/../inc/menu.php'; ?>

<div class="container py-4">
  <?php require_once __DIR__ . '/../inc/flash_simple.php'; ?>

  <ul class="nav nav-tabs" id="configTabs" role="tablist">
    <li class="nav-item"><a class="nav-link <?= $activeTab === 'generales' ? 'active' : '' ?>" data-bs-toggle="tab" href="#generales"><i class="fa fa-cog"></i> Generales</a></li>
    <li class="nav-item"><a class="nav-link <?= $activeTab === 'identidad' ? 'active' : '' ?>" data-bs-toggle="tab" href="#identidad"><i class="fa fa-user"></i> Identidad</a></li>
    <li class="nav-item"><a class="nav-link <?= $activeTab === 'code' ? 'active' : '' ?>" data-bs-toggle="tab" href="#code"><i class="fa fa-code"></i> Codigo HTML</a></li>
    <li class="nav-item"><a class="nav-link <?= $activeTab === 'seo' ? 'active' : '' ?>" data-bs-toggle="tab" href="#seo"><i class="fa fa-google"></i> SEO</a></li>
    <li class="nav-item"><a class="nav-link <?= $activeTab === 'email' ? 'active' : '' ?>" data-bs-toggle="tab" href="#email"><i class="fa fa-envelope"></i> Email</a></li>
    <li class="nav-item"><a class="nav-link <?= $activeTab === 'apariencia' ? 'active' : '' ?>" data-bs-toggle="tab" href="#apariencia"><i class="fas fa-brush"></i> Apariencia</a></li>
    <li class="nav-item"><a class="nav-link <?= $activeTab === 'idioma' ? 'active' : '' ?>" data-bs-toggle="tab" href="#idioma"><i class="fas fa-language"></i> Editor de Idioma</a></li>
  </ul>

  <form id="configForm" method="post" action="save_config.php"
        accept-charset="UTF-8" enctype="multipart/form-data">
    <div class="tab-content p-3 border border-top-0 rounded-bottom">

      <div class="tab-pane fade <?= $activeTab === 'generales' ? 'show active' : '' ?>" id="generales">
        <?php require_once __DIR__ . '/tabs/tab_generales.php'; ?>
      </div>

      <div class="tab-pane fade" id="identidad">
        <?php require_once __DIR__ . '/tabs/tab_identidad.php'; ?>
      </div>

      <div class="tab-pane fade" id="code">
        <?php require_once __DIR__ . '/tabs/tab_code.php'; ?>
      </div>

      <?php require_once __DIR__ . '/tabs/tab_seo.php'; ?>

      <div class="tab-pane fade" id="email">
        <?php require_once __DIR__ . '/tabs/tab_email.php'; ?>
      </div>

      <div class="tab-pane fade" id="apariencia">
        <?php require_once __DIR__ . '/tabs/tab_apariencia.php'; ?>
      </div>

      <div class="tab-pane fade <?= $activeTab === 'idioma' ? 'show active' : '' ?>" id="idioma">
        <?php require_once __DIR__ . '/tabs/tab_idioma.php'; ?>
      </div>

    </div>

    <div class="text-end mt-3">
      <button type="submit" class="btn btn-success" id="btnGuardar">
        <i class="bi bi-check-circle"></i> Guardar cambios
      </button>
    </div>
  </form>
</div>

<?php require_once __DIR__ . '/../inc/menu-footer.php'; ?>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<?php if (!empty($_SESSION['flash'])): $flashes = $_SESSION['flash']; unset($_SESSION['flash']); ?>
<script>
document.addEventListener('DOMContentLoaded', function () {
  const queue   = <?= json_encode($flashes, JSON_UNESCAPED_UNICODE) ?>;
  const iconMap = { success:'success', error:'error', warning:'warning', info:'info' };
  (async () => { for (const f of queue) await Swal.fire({ icon: iconMap[f.type]||'info', title: f.msg, confirmButtonText:'OK' }); })();
});
</script>
<?php endif; ?>

<script>
document.addEventListener('DOMContentLoaded', function () {
  const urlParams = new URLSearchParams(window.location.search);
  const tab = urlParams.get('tab');
  if (tab) {
    const triggerEl = document.querySelector(`[href="#${tab}"]`);
    if (triggerEl) {
      bootstrap.Tab.getOrCreateInstance(triggerEl).show();
    }
  }

  /* Eliminar banner */
  const btnDel = document.getElementById('deleteBannerBtn');
  if (btnDel) {
    btnDel.addEventListener('click', function () {
      Swal.fire({ icon:'warning', title:'Eliminar banner inferior?',
        showCancelButton:true, confirmButtonText:'Si, eliminar', cancelButtonText:'Cancelar'
      }).then(r => {
        if (!r.isConfirmed) return;
        const fd = new FormData();
        fd.append('delete_banner_inferior', '1');
        fetch('save_config.php', { method:'POST', body:fd })
          .then(r => r.json())
          .then(d => {
            if (d.success) Swal.fire({ icon:'success', title:d.message, timer:1500, showConfirmButton:false }).then(() => location.reload());
            else Swal.fire({ icon:'error', title:d.message });
          });
      });
    });
  }

  /* Guardar AJAX */
  const form    = document.getElementById('configForm');
  const btnSave = document.getElementById('btnGuardar');

  form.addEventListener('submit', async function (e) {
    e.preventDefault();
    btnSave.disabled  = true;
    btnSave.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Guardando...';
    try {
      const res  = await fetch('save_config.php', { method:'POST', body: new FormData(form) });
      const data = await res.json();
      if (data.success) await Swal.fire({ icon:'success', title:data.message, timer:2000, showConfirmButton:false });
      else Swal.fire({ icon:'error', title:'Error', text:data.message });
    } catch (err) {
      Swal.fire({ icon:'error', title:'Error de red', text:err.message });
    } finally {
      btnSave.disabled  = false;
      btnSave.innerHTML = '<i class="bi bi-check-circle"></i> Guardar cambios';
    }
  });
});
</script>

<?php require_once __DIR__ . '/../inc/summernote.php'; ?>
</body>
</html>




