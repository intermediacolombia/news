<?php /* tabs/tab_email.php */ ?>

<div class="card mb-3">
  <div class="card-header bg-light"><strong>Servidor de Correo (SMTP)</strong></div>
  <div class="card-body">

    <div class="mb-3">
      <label class="form-label">Remitente</label>
      <input type="text" name="mail_sender" class="form-control"
             value="<?= htmlspecialchars($configs['mail_sender'], ENT_QUOTES, 'UTF-8') ?>">
    </div>

    <div class="mb-3">
      <label class="form-label">Host SMTP</label>
      <input type="text" name="mail_smtp_host" class="form-control"
             value="<?= htmlspecialchars($configs['mail_smtp_host'], ENT_QUOTES, 'UTF-8') ?>">
    </div>

    <div class="mb-3">
      <label class="form-label">Usuario SMTP</label>
      <input type="text" name="mail_smtp_user" class="form-control"
             value="<?= htmlspecialchars($configs['mail_smtp_user'], ENT_QUOTES, 'UTF-8') ?>">
    </div>

    <div class="mb-3">
      <label class="form-label">Contrasena SMTP</label>
      <input type="password" name="mail_smtp_pass" class="form-control"
             value="<?= htmlspecialchars($configs['mail_smtp_pass'], ENT_QUOTES, 'UTF-8') ?>">
    </div>

    <div class="mb-3">
      <label class="form-label">Puerto SMTP</label>
      <select name="mail_smtp_port" class="form-select">
        <?php foreach ([25, 465, 587, 2525] as $p): ?>
          <option value="<?= $p ?>" <?= ($configs['mail_smtp_port'] == $p) ? 'selected' : '' ?>><?= $p ?></option>
        <?php endforeach; ?>
      </select>
    </div>

  </div>
</div>

<div class="card mb-3">
  <div class="card-header bg-light"><strong>Mensajes de Email por Estado de Pedido</strong></div>
  <div class="card-body">

    <?php
    $emailMessages = [
        'mail_new_order_message' => 'Pedido Nuevo (Pago confirmado)',
        'mail_shipped_message'   => 'Pedido Enviado',
        'mail_delivered_message' => 'Pedido Entregado',
    ];
    foreach ($emailMessages as $key => $label):
    ?>
    <div class="card mb-3 border-secondary">
      <div class="card-header d-flex justify-content-between align-items-center">
        <span><?= $label ?></span>
        <div class="form-check form-switch mb-0">
          <input class="form-check-input" type="checkbox"
                 name="<?= $key ?>_enabled" value="1"
                 <?= !empty($configs_enabled[$key]) ? 'checked' : '' ?>>
          <label class="form-check-label">Activo</label>
        </div>
      </div>
      <div class="card-body">
        <textarea name="<?= $key ?>" class="form-control summernote" rows="3"><?= htmlspecialchars($configs[$key], ENT_QUOTES, 'UTF-8') ?></textarea>
      </div>
    </div>
    <?php endforeach; ?>

  </div>
</div>

<div class="card mb-3">
  <div class="card-header bg-light"><strong>Mensajes de WhatsApp por Estado de Pedido</strong></div>
  <div class="card-body">

    <div class="mb-3">
      <label class="form-label">API WhatsApp (token/numero)</label>
      <input type="text" name="api_whatsapp" class="form-control"
             value="<?= htmlspecialchars($configs['api_whatsapp'], ENT_QUOTES, 'UTF-8') ?>">
    </div>

    <?php
    $wsMessages = [
        'ws_new_order_message' => 'Pedido Nuevo (Pago confirmado)',
        'ws_shipped_message'   => 'Pedido Enviado',
        'ws_delivered_message' => 'Pedido Entregado',
    ];
    foreach ($wsMessages as $key => $label):
    ?>
    <div class="card mb-3 border-success">
      <div class="card-header d-flex justify-content-between align-items-center">
        <span><i class="fab fa-whatsapp text-success me-1"></i> <?= $label ?></span>
        <div class="form-check form-switch mb-0">
          <input class="form-check-input" type="checkbox"
                 name="<?= $key ?>_enabled" value="1"
                 <?= !empty($configs_enabled[$key]) ? 'checked' : '' ?>>
          <label class="form-check-label">Activo</label>
        </div>
      </div>
      <div class="card-body">
        <textarea name="<?= $key ?>" class="form-control" rows="3"><?= htmlspecialchars($configs[$key], ENT_QUOTES, 'UTF-8') ?></textarea>
      </div>
    </div>
    <?php endforeach; ?>

  </div>
</div>