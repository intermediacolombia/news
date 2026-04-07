<meta name="viewport" content="width=device-width, initial-scale=1">
<style>
        :root {
            --primary-color: <?= COLOR_PRIMARY ?? '#007bff' ?>;
            --color-hover-link: <?= COLOR_PRIMARY_HOVER_LINK  ?? '#0056b3' ?>;
            --color-TEST1: <?= COLOR_PRIMARY ?>;
            --color-TEST2: <?= COLOR_PRIMARY_HOVER_LINK ?>;
        }
    </style>
<!-- Favicon -->
<link rel="shortcut icon" type="image/x-icon" href="<?php echo URLBASE; ?><?php echo FAVICON ?>">
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<!-- Bootstrap 5 -->
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">

<!-- Estilos del admin -->
<link href="<?= htmlspecialchars($url) ?>/admin/assets/css/admin.css?cache=<?= time(); ?>" rel="stylesheet">
<link rel="stylesheet" href="<?= htmlspecialchars($url) ?>/admin/assets/css/menu.css?cache=<?= time(); ?>">
<link rel="stylesheet" href="<?= htmlspecialchars($url) ?>/admin/assets/css/style.css?cache=<?= time(); ?>">
<link rel="stylesheet" href="<?= htmlspecialchars($url) ?>/admin/assets/css/cards.css?cache=<?= time(); ?>">

<!-- Font Awesome -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">

<!-- SweetAlert2 -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<!-- intl-tel-input -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/intl-tel-input/17.0.8/css/intlTelInput.css" />

<!-- DataTables Bootstrap 5 (versión unificada 1.13.7) -->
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.7/css/dataTables.bootstrap5.min.css">

<!-- Flatpickr -->
<link href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css" rel="stylesheet">
<link rel="stylesheet" type="text/css" href="https://npmcdn.com/flatpickr/dist/themes/material_red.css">

<!-- Google Material Icons -->
<link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">

<script>
document.addEventListener('DOMContentLoaded', function () {

    // ── Utilidad: escapar HTML ────────────────────────────────────────────────
    function escHtml(str) {
        return String(str)
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;');
    }

    // ── Modal: información de actualización disponible ────────────────────────
    function showUpdateModal(data) {
        const count = data.commits_behind || 0;

        let commitRows = '';
        if (data.new_commits && data.new_commits.length) {
            data.new_commits.slice(0, 6).forEach(function (c) {
                commitRows += `
                    <tr>
                        <td style="font-family:monospace;color:#888;white-space:nowrap">${escHtml(c.hash)}</td>
                        <td style="text-align:left;padding-left:8px">${escHtml(c.message)}</td>
                        <td style="white-space:nowrap;color:#888;padding-left:8px;font-size:11px">${escHtml(c.date)}</td>
                    </tr>`;
            });
        }

        const commitsHtml = commitRows ? `
            <div style="max-height:180px;overflow-y:auto;margin:12px 0;border:1px solid #e0e0e0;border-radius:6px;">
                <table style="width:100%;border-collapse:collapse;font-size:12px;">
                    <tbody>${commitRows}</tbody>
                </table>
            </div>` : '';

        Swal.fire({
            title: '<i class="fas fa-cloud-download-alt" style="color:var(--primary-color)"></i>&nbsp; Nueva versión disponible',
            html: `
                <p style="margin:0 0 6px">
                    Se encontraron <strong>${count}</strong> cambio${count !== 1 ? 's' : ''} nuevo${count !== 1 ? 's' : ''}
                    en el repositorio.
                </p>
                <p style="font-size:12px;color:#888;margin:0 0 4px">
                    Local:&nbsp;<code>${escHtml(data.local_hash)}</code>
                    &nbsp;→&nbsp;
                    Remoto:&nbsp;<code>${escHtml(data.remote_hash)}</code>
                </p>
                ${commitsHtml}
            `,
            icon: false,
            showCancelButton: true,
            confirmButtonText: '<i class="fas fa-sync-alt"></i>&nbsp; Actualizar ahora',
            cancelButtonText: 'Más tarde',
            confirmButtonColor: 'var(--primary-color)',
            cancelButtonColor: '#6c757d',
            reverseButtons: true,
            width: 560,
        }).then(function (result) {
            if (result.isConfirmed) {
                startStreamUpdate();
            }
        });
    }

    // ── Modal: progreso de actualización estilo servidor ─────────────────────
    var UPDATE_STEPS = [
        { id: 'step-connect',  icon: 'fa-plug',          label: 'Conectando con el servidor...' },
        { id: 'step-download', icon: 'fa-cloud-download-alt', label: 'Descargando cambios...' },
        { id: 'step-apply',    icon: 'fa-cogs',          label: 'Aplicando actualización...' },
        { id: 'step-verify',   icon: 'fa-shield-alt',    label: 'Verificando integridad...' },
        { id: 'step-done',     icon: 'fa-check-circle',  label: 'Completado' },
    ];

    function buildStepsHtml() {
        var html = '<div style="text-align:left;padding:8px 0;">';
        UPDATE_STEPS.forEach(function (s) {
            html += `
                <div id="${s.id}" style="display:flex;align-items:center;gap:12px;padding:10px 0;
                          border-bottom:1px solid #f0f0f0;opacity:.35;transition:opacity .4s;">
                    <div style="width:34px;height:34px;border-radius:50%;background:#f4f4f4;
                                display:flex;align-items:center;justify-content:center;flex-shrink:0;">
                        <i class="fas ${s.icon}" style="font-size:14px;color:#666;"></i>
                    </div>
                    <div style="flex:1;">
                        <div style="font-size:13px;font-weight:600;color:#333">${s.label}</div>
                        <div id="${s.id}-sub" style="font-size:11px;color:#888;margin-top:2px;"></div>
                    </div>
                    <div id="${s.id}-state" style="font-size:18px;"></div>
                </div>`;
        });
        html += '</div>';
        // Barra de progreso general
        html += `
            <div style="margin-top:14px;">
                <div style="display:flex;justify-content:space-between;font-size:11px;
                            color:#888;margin-bottom:4px;">
                    <span id="upd-status-text">Iniciando…</span>
                    <span id="upd-pct-text">0%</span>
                </div>
                <div style="background:#eee;border-radius:10px;height:7px;overflow:hidden;">
                    <div id="upd-bar" style="height:100%;width:0%;border-radius:10px;
                                             background:var(--primary-color);
                                             transition:width .5s ease;"></div>
                </div>
            </div>`;
        return html;
    }

    function activateStep(index, subText) {
        var step = UPDATE_STEPS[index];
        if (!step) return;
        var el = document.getElementById(step.id);
        if (!el) return;
        el.style.opacity = '1';
        var iconEl = el.querySelector('.fas');
        if (iconEl) {
            iconEl.style.color = 'var(--primary-color)';
        }
        var sub = document.getElementById(step.id + '-sub');
        if (sub && subText) sub.textContent = subText;

        var pct = Math.round(((index + 1) / UPDATE_STEPS.length) * 100);
        var bar = document.getElementById('upd-bar');
        var pctText = document.getElementById('upd-pct-text');
        var statusText = document.getElementById('upd-status-text');
        if (bar) bar.style.width = pct + '%';
        if (pctText) pctText.textContent = pct + '%';
        if (statusText) statusText.textContent = step.label;
    }

    function completeStep(index, ok) {
        var step = UPDATE_STEPS[index];
        if (!step) return;
        var stateEl = document.getElementById(step.id + '-state');
        if (stateEl) {
            stateEl.innerHTML = ok
                ? '<i class="fas fa-check" style="color:#28a745"></i>'
                : '<i class="fas fa-times" style="color:#dc3545"></i>';
        }
    }

    function startStreamUpdate() {
        Swal.fire({
            title: '<i class="fas fa-sync-alt fa-spin" style="color:var(--primary-color);margin-right:8px"></i>Actualizando sistema',
            html: buildStepsHtml(),
            allowOutsideClick: false,
            allowEscapeKey: false,
            showConfirmButton: false,
            width: 520,
            didOpen: function () {
                doXhrStream();
            }
        });
    }

    function doXhrStream() {
        var xhr = new XMLHttpRequest();
        var lastLen = 0;
        var success = false;
        var stepIndex = 0;

        // Mapa: texto del stream → paso que activa
        var stepTriggers = [
            { pattern: /Iniciando|fetch|Counting|remote:/i,         step: 0 },
            { pattern: /Receiving objects|Compressing|Unpacking/i,   step: 1 },
            { pattern: /Updating|Fast-forward|Merge|files changed/i, step: 2 },
            { pattern: /DONE:/i,                                      step: 3 },
        ];

        xhr.open('GET', '<?= $url ?>/admin/inc/auto-update.php?action=stream_update', true);

        // Activar primer paso al abrir
        setTimeout(function () { activateStep(0); }, 200);

        xhr.onprogress = function () {
            var chunk = xhr.responseText.substring(lastLen);
            lastLen = xhr.responseText.length;

            if (chunk.indexOf('DONE:success') !== -1) success = true;

            // Avanzar pasos según el contenido recibido
            stepTriggers.forEach(function (trigger) {
                if (trigger.step > stepIndex && trigger.pattern.test(chunk)) {
                    completeStep(stepIndex, true);
                    stepIndex = trigger.step;
                    activateStep(stepIndex);
                }
            });
        };

        xhr.onload = function () {
            if (xhr.responseText.indexOf('DONE:success') !== -1) success = true;

            // Completar pasos restantes
            completeStep(stepIndex, success);
            if (success) {
                activateStep(4);
                completeStep(4, true);
                var bar = document.getElementById('upd-bar');
                if (bar) {
                    bar.style.width = '100%';
                    bar.style.background = '#28a745';
                }
                var pctText = document.getElementById('upd-pct-text');
                if (pctText) pctText.textContent = '100%';
                var statusText = document.getElementById('upd-status-text');
                if (statusText) statusText.textContent = 'Actualización completada';
            } else {
                var bar2 = document.getElementById('upd-bar');
                if (bar2) bar2.style.background = '#dc3545';
            }

            setTimeout(function () {
                Swal.fire({
                    title: success ? '¡Sistema actualizado!' : 'Error en la actualización',
                    text: success
                        ? 'Todos los cambios fueron aplicados correctamente.'
                        : 'Ocurrió un problema. Intenta nuevamente o contacta al administrador.',
                    icon: success ? 'success' : 'error',
                    confirmButtonText: success ? 'Recargar página' : 'Cerrar',
                    confirmButtonColor: success ? '#28a745' : 'var(--primary-color)',
                    allowOutsideClick: false,
                }).then(function () {
                    if (success) location.reload();
                });
            }, 800);
        };

        xhr.onerror = function () {
            Swal.fire('Error de conexión', 'No se pudo conectar con el servidor de actualización.', 'error');
        };

        xhr.send();
    }

    // ── Verificación al cargar la página ─────────────────────────────────────
    fetch('<?= $url ?>/admin/inc/auto-update.php?action=check')
        .then(function (r) { return r.json(); })
        .then(function (data) {
            if (data.update_available) {
                showUpdateModal(data);
            }
        })
        .catch(function (err) {
            console.log('[auto-update] error al verificar:', err);
        });

    // Exponer función para botón manual en menú
    window.checkForUpdates = function (e) {
        if (e) e.preventDefault();
        fetch('<?= $url ?>/admin/inc/auto-update.php?action=force_check')
            .then(function (r) { return r.json(); })
            .then(function (data) {
                if (data.update_available) {
                    showUpdateModal(data);
                } else {
                    Swal.fire({
                        toast: true,
                        position: 'bottom-end',
                        icon: 'success',
                        title: 'Sistema al día (' + data.local_hash + ')',
                        showConfirmButton: false,
                        timer: 3000,
                        timerProgressBar: true,
                    });
                }
            })
            .catch(function () {
                Swal.fire('Error', 'No se pudo verificar actualizaciones.', 'error');
            });
    };

});
</script>

<!-- Script custom 
<script src="<?= htmlspecialchars($url) ?>/template/assets/js/departamentos.js" crossorigin="anonymous"></script>
-->
