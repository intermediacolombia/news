<?php
$currentLang = $_GET['lang'] ?? $configs['admin_language'] ?? 'es';
$availableLangs = ['es' => 'Español', 'en' => 'English'];
$currentSection = $_GET['section'] ?? 'tema';

$allKeys = get_all_translation_keys();
$translationsByKey = [];
foreach ($allKeys as $key) {
    $translationsByKey[$key] = get_translations_by_key($key);
}

// Separar claves por prefijo
$adminKeys = [];
$themeKeys = [];
foreach ($allKeys as $key) {
    if (strpos($key, 'theme_') === 0) {
        $themeKeys[] = $key;
    } else {
        $adminKeys[] = $key;
    }
}

$keys = $currentSection === 'admin' ? $adminKeys : $themeKeys;
?>

<div class="card mb-3">
    <div class="card-header bg-light d-flex justify-content-between align-items-center">
        <strong><i class="fas fa-language me-2"></i>Editor de Traducciones</strong>
        <div>
            <div class="btn-group me-2">
                <a href="?tab=idioma&section=tema" 
                   class="btn btn-sm <?= $currentSection === 'tema' ? 'btn-primary' : 'btn-outline-secondary' ?>">
                    Temas
                </a>
                <a href="?tab=idioma&section=admin" 
                   class="btn btn-sm <?= $currentSection === 'admin' ? 'btn-primary' : 'btn-outline-secondary' ?>">
                    Interfaz
                </a>
            </div>
            <?php foreach ($availableLangs as $code => $name): ?>
                <a href="?tab=idioma&section=<?= $currentSection ?>&lang=<?= $code ?>" 
                   class="btn btn-sm <?= $currentLang === $code ? 'btn-primary' : 'btn-outline-secondary' ?>">
                    <?= $name ?>
                </a>
            <?php endforeach; ?>
        </div>
    </div>
    <div class="card-body">
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
                        <?php foreach ($keys as $key): ?>
                            <?php $values = $translationsByKey[$key] ?? []; ?>
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
                <button type="button" class="btn btn-outline-secondary" onclick="resetTranslations()">
                    <i class="bi bi-arrow-counterclockwise"></i> Restablecer valores
                </button>
            </div>
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
