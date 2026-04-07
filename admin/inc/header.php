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
document.addEventListener('DOMContentLoaded', function() {
    function showToast(message, type = 'success') {
        const lastShown = localStorage.getItem('update_toast_shown');
        const now = Date.now();
        if (lastShown && (now - parseInt(lastShown)) < 86400000) {
            return;
        }
        localStorage.setItem('update_toast_shown', now);
        
        const toast = document.createElement('div');
        toast.style.cssText = `
            position: fixed;
            bottom: 20px;
            right: 20px;
            background: ${type === 'success' ? '#28a745' : '#dc3545'};
            color: #fff;
            padding: 12px 20px;
            border-radius: 8px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.2);
            z-index: 10000;
            font-size: 14px;
            animation: slideIn 0.3s ease;
        `;
        toast.innerHTML = `<i class="fas fa-${type === 'success' ? 'check-circle' : 'info-circle'} mr-2"></i>${message}`;
        document.body.appendChild(toast);
        setTimeout(() => toast.remove(), 4000);
    }
    
    function showUpdatingNotification() {
        const existingNotification = document.getElementById('updating-notification');
        if (existingNotification) return;
        
        const notification = document.createElement('div');
        notification.id = 'updating-notification';
        notification.innerHTML = `
            <div style="position: fixed; top: 0; left: 0; right: 0; bottom: 0; background: rgba(0,0,0,0.7); z-index: 9999; display: flex; align-items: center; justify-content: center;">
                <div style="background: #fff; padding: 30px 50px; border-radius: 10px; text-align: center; box-shadow: 0 10px 40px rgba(0,0,0,0.3);">
                    <i class="fas fa-sync-alt fa-spin" style="font-size: 40px; color: var(--primary-color); margin-bottom: 15px;"></i>
                    <h4 style="margin: 0; color: #333;">Actualizando sistema...</h4>
                    <p style="margin: 10px 0 0; color: #666; font-size: 14px;">Por favor espera mientras se aplican las actualizaciones</p>
                </div>
            </div>
        `;
        document.body.appendChild(notification);
    }
    
    function hideUpdatingNotification() {
        const notification = document.getElementById('updating-notification');
        if (notification) {
            notification.remove();
        }
    }
    
    window.checkForUpdates = function(e) {
        if (e) e.preventDefault();
        showUpdatingNotification();
        
        fetch('<?= $url ?>/admin/inc/auto-update.php?action=force_check', {
            method: 'GET',
            headers: { 'Content-Type': 'application/json' }
        })
        .then(response => response.json())
        .then(data => {
            hideUpdatingNotification();
            if (data.update_available) {
                showToast('Nueva versión disponible: ' + data.latest + ' - Actualizando...', 'success');
                setTimeout(() => location.reload(), 2000);
            } else {
                showToast('Sistema actualizado. Hash: ' + data.current_hash, 'success');
            }
        })
        .catch(err => {
            hideUpdatingNotification();
            showToast('Error al verificar', 'error');
        });
    };
    
    window.resetUpdateStatus = function(e) {
        if (e) e.preventDefault();
        if (!confirm('¿Resetear el estado de actualizaciones? Esto marcará el sistema como needing update.')) return;
        
        fetch('<?= $url ?>/admin/inc/auto-update.php?action=reset', {
            method: 'GET',
            headers: { 'Content-Type': 'application/json' }
        })
        .then(response => response.json())
        .then(data => {
            showToast('Estado reseteado. Hash: ' + data.hash, 'success');
        })
        .catch(err => {
            showToast('Error al resetear', 'error');
        });
    };
    
    function doUpdate() {
        showUpdatingNotification();
        
        fetch('<?= $url ?>/admin/inc/auto-update.php?action=update&key=autoupdate', {
            method: 'GET',
            headers: { 'Content-Type': 'application/json' }
        })
        .then(res => res.json())
        .then(updateResult => {
            hideUpdatingNotification();
            if (updateResult.success) {
                alert('✅ Actualización aplicada!\n\nNuevo hash: ' + updateResult.new_hash + '\n\nEl sistema se recargará...');
                location.reload();
            } else {
                alert('❌ Error: ' + (updateResult.message || updateResult.output || 'No se pudo actualizar'));
            }
        })
        .catch(err => {
            hideUpdatingNotification();
            alert('Error al actualizar: ' + err);
        });
    }
    
    fetch('<?= $url ?>/admin/inc/auto-update.php?action=check', {
        method: 'GET',
        headers: { 'Content-Type': 'application/json' }
    })
    .then(response => response.json())
    .then(data => {
        console.log('Update check result:', data);
        
        if (data.update_available) {
            if (confirm('🔄 Hay actualizaciones pendientes!\n\nCommits locales: ' + data.current_count + '\nCommits guardados: ' + data.saved_count + '\n\n¿Deseas actualizar ahora?')) {
                doUpdate();
            }
        } else {
            console.log('Sistema actualizado. Commits: ' + data.current_count);
        }
    })
    .catch(error => {
        console.log('Error al verificar actualizaciones:', error);
    });
});
</script>

<!-- Script custom 
<script src="<?= htmlspecialchars($url) ?>/template/assets/js/departamentos.js" crossorigin="anonymous"></script>
-->
