<?php
require_once __DIR__ . '/../../inc/config.php';
require_once __DIR__ . '/../login/session.php';

$permisopage = 'Editar Configuraciones';
require_once __DIR__ . '/../login/restriction.php';

if (!headers_sent()) header('Content-Type: text/html; charset=UTF-8');
ini_set('default_charset', 'UTF-8');
mb_internal_encoding('UTF-8');
db()->exec("SET NAMES utf8mb4");

$currentLang = $_GET['lang'] ?? $sys['admin_language'] ?? 'es';
$availableLangs = ['es' => 'Español', 'en' => 'English'];

$keys = get_all_translation_keys();

$translationsByKey = [];
foreach ($keys as $key) {
    $translationsByKey[$key] = get_translations_by_key($key);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($_POST['translations'])) {
    foreach ($_POST['translations'] as $key => $langValues) {
        foreach ($langValues as $lang => $value) {
            save_translation($lang, $key, $value);
        }
    }
    setFlash('success', 'Traducciones guardadas correctamente.');
    header('Location: ?tab=idioma');
    exit;
}
?>

<div class="card mb-3">
    <div class="card-header bg-light d-flex justify-content-between align-items-center">
        <strong><i class="fas fa-language me-2"></i>Editor de Traducciones</strong>
        <div>
            <?php foreach ($availableLangs as $code => $name): ?>
                <a href="?tab=idioma&lang=<?= $code ?>" 
                   class="btn btn-sm <?= $currentLang === $code ? 'btn-primary' : 'btn-outline-secondary' ?>">
                    <?= $name ?>
                </a>
            <?php endforeach; ?>
        </div>
    </div>
    <div class="card-body">
        <form method="post" id="translationForm">
            <div class="mb-3">
                <label class="form-label">Buscar traducción</label>
                <input type="text" class="form-control" id="searchTranslation" placeholder="Escribe para buscar...">
            </div>
            
            <div class="table-responsive" style="max-height: 500px; overflow-y: auto;">
                <table class="table table-bordered table-sm" id="translationsTable">
                    <thead class="table-light sticky-top">
                        <tr>
                            <th style="width: 30%;">Clave</th>
                            <th style="width: 35%;">Español</th>
                            <th style="width: 35%;">English</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($translationsByKey as $key => $values): ?>
                            <tr data-key="<?= htmlspecialchars($key) ?>">
                                <td>
                                    <code class="small"><?= htmlspecialchars($key) ?></code>
                                </td>
                                <td>
                                    <input type="text" 
                                           class="form-control form-control-sm" 
                                           name="translations[<?= $key ?>][es]"
                                           value="<?= htmlspecialchars($values['es'] ?? '') ?>"
                                           placeholder="Traducción en español">
                                </td>
                                <td>
                                    <input type="text" 
                                           class="form-control form-control-sm" 
                                           name="translations[<?= $key ?>][en]"
                                           value="<?= htmlspecialchars($values['en'] ?? '') ?>"
                                           placeholder="English translation">
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            
            <div class="mt-3">
                <button type="submit" class="btn btn-success">
                    <i class="bi bi-check-circle"></i> Guardar traducciones
                </button>
                <button type="button" class="btn btn-outline-secondary" onclick="resetTranslations()">
                    <i class="bi bi-arrow-counterclockwise"></i> Restablecer valores
                </button>
            </div>
        </form>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const searchInput = document.getElementById('searchTranslation');
    const tableRows = document.querySelectorAll('#translationsTable tbody tr');
    
    searchInput.addEventListener('input', function() {
        const searchTerm = this.value.toLowerCase();
        
        tableRows.forEach(row => {
            const key = row.dataset.key.toLowerCase();
            const esValue = row.querySelector('input[name$="[es]"]').value.toLowerCase();
            const enValue = row.querySelector('input[name$="[en]"]').value.toLowerCase();
            
            if (key.includes(searchTerm) || esValue.includes(searchTerm) || enValue.includes(searchTerm)) {
                row.style.display = '';
            } else {
                row.style.display = 'none';
            }
        });
    });
    
    window.resetTranslations = function() {
        if (confirm('¿Restablecer todas las traducciones a valores por defecto?')) {
            document.getElementById('translationForm').reset();
        }
    };
});
</script>
