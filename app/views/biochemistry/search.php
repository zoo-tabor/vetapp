<div class="container">
    <div class="page-header">
        <div class="breadcrumb">
            <a href="/">Pracoviště</a> /
            <a href="/biochemistry/workplace/<?= $animal['workplace_id'] ?>">
                <?= htmlspecialchars($animal['workplace_name'] ?? 'Pracoviště') ?>
            </a> /
            <a href="/biochemistry/animal/<?= $animal['id'] ?>">
                <?= htmlspecialchars($animal['name']) ?>
            </a> /
            <span>Graf</span>
        </div>

        <h1>Vytvoření grafu</h1>
        <p class="subtitle">
            <strong><?= htmlspecialchars($animal['name']) ?></strong> |
            ID: <?= htmlspecialchars($animal['identifier']) ?> |
            Druh: <?= htmlspecialchars($animal['species']) ?>
        </p>
    </div>

    <div class="search-container">
        <form id="searchForm" method="POST" action="/biochemistry/animal/<?= $animal['id'] ?>/graph">
            <input type="hidden" name="parameters" id="parametersInput">

            <div class="search-section">
                <h2>1. Vyberte parametry</h2>
                <p class="instruction">Zaškrtněte parametry, které chcete zobrazit v grafu</p>

                <div class="parameters-tabs">
                    <button type="button" class="param-tab active" data-tab="biochemistry">
                        Biochemie (<?= count($biochemParams) ?>)
                    </button>
                    <button type="button" class="param-tab" data-tab="hematology">
                        Hematologie (<?= count($hematoParams) ?>)
                    </button>
                </div>

                <!-- Biochemistry Parameters -->
                <div id="biochemistry-params" class="params-grid">
                    <?php if (empty($biochemParams)): ?>
                        <p class="no-data">Žádné biochemické parametry nejsou k dispozici</p>
                    <?php else: ?>
                        <?php foreach ($biochemParams as $param): ?>
                            <div class="param-checkbox">
                                <label>
                                    <input type="checkbox"
                                           name="selected_params[]"
                                           value="<?= htmlspecialchars($param['parameter_name']) ?>"
                                           data-type="biochemistry"
                                           data-unit="<?= htmlspecialchars($param['unit']) ?>">
                                    <span><?= htmlspecialchars($param['parameter_name']) ?></span>
                                    <small><?= htmlspecialchars($param['unit']) ?></small>
                                </label>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>

                <!-- Hematology Parameters -->
                <div id="hematology-params" class="params-grid" style="display: none;">
                    <?php if (empty($hematoParams)): ?>
                        <p class="no-data">Žádné hematologické parametry nejsou k dispozici</p>
                    <?php else: ?>
                        <?php foreach ($hematoParams as $param): ?>
                            <div class="param-checkbox">
                                <label>
                                    <input type="checkbox"
                                           name="selected_params[]"
                                           value="<?= htmlspecialchars($param['parameter_name']) ?>"
                                           data-type="hematology"
                                           data-unit="<?= htmlspecialchars($param['unit']) ?>">
                                    <span><?= htmlspecialchars($param['parameter_name']) ?></span>
                                    <small><?= htmlspecialchars($param['unit']) ?></small>
                                </label>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>

            <div class="search-section">
                <h2>2. Nastavte barvy parametrů</h2>
                <p class="instruction">Vyberte barvu pro každý vybraný parametr</p>

                <div id="colorSettings" class="color-settings">
                    <p class="no-selection">Nejprve vyberte alespoň jeden parametr</p>
                </div>
            </div>

            <div class="search-section">
                <h2>3. Počet vzorků</h2>
                <p class="instruction">Kolik posledních výsledků zobrazit v grafu</p>

                <div class="sample-count-section">
                    <div class="sample-count-input-wrapper">
                        <label for="sample_count" class="input-label">Počet vzorků:</label>
                        <input type="number"
                               id="sample_count"
                               name="sample_count"
                               min="1"
                               max="100"
                               value="5"
                               class="sample-count-input"
                               placeholder="Zadejte počet">
                        <small class="input-help">Zadejte číslo od 1 do 100, nebo ponechte prázdné pro všechny vzorky</small>
                    </div>
                </div>
            </div>

            <div class="search-section">
                <h2>4. Referenční rozsahy (volitelné)</h2>
                <p class="instruction">Vyberte zdroj referenčních hodnot pro zobrazení v grafu</p>

                <div class="reference-section">
                    <div class="reference-input-wrapper">
                        <label for="reference_source" class="input-label">Zdroj referenčních hodnot:</label>
                        <select id="reference_source" name="reference_source" class="reference-select">
                            <option value="">Nezobrazovat referenční rozsahy</option>
                            <?php
                            // Get available reference sources
                            require_once __DIR__ . '/../../core/Database.php';
                            $db = Database::getInstance()->getConnection();
                            $stmt = $db->query("SELECT source_name FROM reference_sources ORDER BY source_name ASC");
                            $sources = $stmt->fetchAll(PDO::FETCH_COLUMN);

                            // If no sources exist, use defaults
                            if (empty($sources)) {
                                $sources = ['Idexx', 'Laboklin', 'Synlab', 'ZIMS'];
                            }

                            foreach ($sources as $source):
                            ?>
                                <option value="<?= htmlspecialchars($source) ?>"><?= htmlspecialchars($source) ?></option>
                            <?php endforeach; ?>
                        </select>
                        <small class="input-help">Referenční rozsahy budou zobrazeny jako přerušované čáry v grafu</small>
                    </div>
                </div>
            </div>

            <div class="form-actions">
                <a href="/biochemistry/animal/<?= $animal['id'] ?>/comprehensive-table" class="btn btn-outline">
                    ← Zpět
                </a>
                <button type="submit" class="btn btn-primary" id="submitBtn" disabled>
                    📊 Zobrazit graf
                </button>
            </div>
        </form>
    </div>
</div>

<style>
.container {
    max-width: 1400px;
    margin: 0 auto;
    padding: 20px;
}

.page-header {
    margin-bottom: 30px;
}

.breadcrumb {
    margin-bottom: 15px;
    color: #7f8c8d;
    font-size: 14px;
}

.breadcrumb a {
    color: #c0392b;
    text-decoration: none;
}

.breadcrumb a:hover {
    text-decoration: underline;
}

.page-header h1 {
    margin: 0 0 10px 0;
    color: #2c3e50;
}

.subtitle {
    color: #7f8c8d;
    font-size: 14px;
    margin: 0;
}

.search-container {
    background: white;
    border-radius: 8px;
    padding: 30px;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
}

.search-section {
    margin-bottom: 40px;
    padding-bottom: 30px;
    border-bottom: 2px solid #f0f0f0;
}

.search-section:last-of-type {
    border-bottom: none;
}

.search-section h2 {
    margin: 0 0 10px 0;
    color: #c0392b;
    font-size: 20px;
}

.instruction {
    color: #7f8c8d;
    font-size: 14px;
    margin: 0 0 20px 0;
}

.parameters-tabs {
    display: flex;
    gap: 10px;
    margin-bottom: 20px;
    border-bottom: 2px solid #e0e0e0;
}

.param-tab {
    background: none;
    border: none;
    padding: 12px 24px;
    font-size: 15px;
    font-weight: 600;
    color: #7f8c8d;
    cursor: pointer;
    border-bottom: 3px solid transparent;
    transition: all 0.2s;
}

.param-tab:hover {
    color: #c0392b;
}

.param-tab.active {
    color: #c0392b;
    border-bottom-color: #c0392b;
}

.params-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
    gap: 15px;
}

.param-checkbox {
    background: #f8f9fa;
    border: 2px solid #e0e0e0;
    border-radius: 6px;
    padding: 12px 15px;
    transition: all 0.2s;
}

.param-checkbox:hover {
    border-color: #c0392b;
    background: #fff;
}

.param-checkbox label {
    display: flex;
    align-items: center;
    gap: 10px;
    cursor: pointer;
    margin: 0;
}

.param-checkbox input[type="checkbox"] {
    width: 18px;
    height: 18px;
    cursor: pointer;
}

.param-checkbox span {
    flex: 1;
    font-weight: 500;
    color: #2c3e50;
}

.param-checkbox small {
    color: #7f8c8d;
    font-size: 12px;
}

.no-data {
    color: #7f8c8d;
    font-style: italic;
    padding: 20px;
    text-align: center;
}

.color-settings {
    display: flex;
    flex-direction: column;
    gap: 15px;
}

.no-selection {
    color: #7f8c8d;
    font-style: italic;
    text-align: center;
    padding: 20px;
}

.color-setting-row {
    display: flex;
    align-items: center;
    gap: 15px;
    padding: 12px 15px;
    background: #f8f9fa;
    border-radius: 6px;
    border: 2px solid #e0e0e0;
}

.color-setting-row label {
    flex: 1;
    font-weight: 500;
    color: #2c3e50;
    margin: 0;
}

.color-setting-row small {
    color: #7f8c8d;
    font-size: 12px;
}

.color-setting-row input[type="color"] {
    width: 50px;
    height: 40px;
    border: 2px solid #e0e0e0;
    border-radius: 4px;
    cursor: pointer;
}

.sample-count-section {
    margin-top: 15px;
}

.sample-count-input-wrapper {
    display: flex;
    flex-direction: column;
    gap: 10px;
    max-width: 400px;
}

.input-label {
    font-weight: 600;
    color: #2c3e50;
    font-size: 15px;
}

.sample-count-input {
    padding: 12px 15px;
    border: 2px solid #e0e0e0;
    border-radius: 6px;
    font-size: 15px;
    font-weight: 500;
    color: #2c3e50;
    transition: all 0.2s;
    background: #f8f9fa;
}

.sample-count-input:focus {
    outline: none;
    border-color: #c0392b;
    background: white;
}

.sample-count-input:hover {
    border-color: #c0392b;
    background: white;
}

.input-help {
    color: #7f8c8d;
    font-size: 13px;
    font-style: italic;
}

.reference-section {
    margin-top: 15px;
}

.reference-input-wrapper {
    display: flex;
    flex-direction: column;
    gap: 10px;
    max-width: 400px;
}

.reference-select {
    padding: 12px 15px;
    border: 2px solid #e0e0e0;
    border-radius: 6px;
    font-size: 15px;
    font-weight: 500;
    color: #2c3e50;
    transition: all 0.2s;
    background: #f8f9fa;
    cursor: pointer;
}

.reference-select:focus {
    outline: none;
    border-color: #c0392b;
    background: white;
}

.reference-select:hover {
    border-color: #c0392b;
    background: white;
}

.form-actions {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-top: 30px;
    padding-top: 20px;
    border-top: 2px solid #f0f0f0;
}

.btn {
    padding: 12px 24px;
    border-radius: 4px;
    border: none;
    cursor: pointer;
    font-size: 15px;
    font-weight: 600;
    transition: all 0.2s;
    text-decoration: none;
    display: inline-block;
}

.btn-primary {
    background: linear-gradient(135deg, #c0392b 0%, #a93226 100%);
    color: white;
}

.btn-primary:hover:not(:disabled) {
    background: linear-gradient(135deg, #a93226 0%, #8f2a20 100%);
    transform: translateY(-1px);
    box-shadow: 0 4px 12px rgba(192, 57, 43, 0.3);
}

.btn-primary:disabled {
    opacity: 0.5;
    cursor: not-allowed;
}

.btn-outline {
    background: white;
    border: 2px solid #c0392b;
    color: #c0392b;
}

.btn-outline:hover {
    background: #c0392b;
    color: white;
}
</style>

<script>
// Predefined colors for parameters
const colors = [
    '#e74c3c', '#3498db', '#2ecc71', '#f39c12', '#9b59b6',
    '#1abc9c', '#e67e22', '#34495e', '#16a085', '#c0392b',
    '#d35400', '#8e44ad', '#27ae60', '#2980b9', '#f1c40f'
];
let colorIndex = 0;
const selectedParameters = new Map();

// Tab switching
document.querySelectorAll('.param-tab').forEach(tab => {
    tab.addEventListener('click', function() {
        // Update active tab
        document.querySelectorAll('.param-tab').forEach(t => t.classList.remove('active'));
        this.classList.add('active');

        // Show corresponding parameters
        const tabType = this.dataset.tab;
        document.getElementById('biochemistry-params').style.display =
            tabType === 'biochemistry' ? 'grid' : 'none';
        document.getElementById('hematology-params').style.display =
            tabType === 'hematology' ? 'grid' : 'none';
    });
});

// Handle parameter selection
document.querySelectorAll('input[name="selected_params[]"]').forEach(checkbox => {
    checkbox.addEventListener('change', function() {
        const paramName = this.value;
        const paramType = this.dataset.type;
        const paramUnit = this.dataset.unit;

        if (this.checked) {
            // Add parameter with next color
            selectedParameters.set(paramName, {
                type: paramType,
                unit: paramUnit,
                color: colors[colorIndex % colors.length]
            });
            colorIndex++;
        } else {
            // Remove parameter
            selectedParameters.delete(paramName);
        }

        updateColorSettings();
        updateSubmitButton();
    });
});

// Update color settings section
function updateColorSettings() {
    const colorSettings = document.getElementById('colorSettings');

    if (selectedParameters.size === 0) {
        colorSettings.innerHTML = '<p class="no-selection">Nejprve vyberte alespoň jeden parametr</p>';
        return;
    }

    let html = '';
    selectedParameters.forEach((data, paramName) => {
        html += `
            <div class="color-setting-row">
                <label>
                    ${paramName}
                    <small>(${data.type === 'biochemistry' ? 'Biochemie' : 'Hematologie'} - ${data.unit})</small>
                </label>
                <input type="color"
                       value="${data.color}"
                       data-param="${paramName}"
                       onchange="updateParameterColor('${paramName}', this.value)">
            </div>
        `;
    });

    colorSettings.innerHTML = html;
}

// Update parameter color
function updateParameterColor(paramName, color) {
    const param = selectedParameters.get(paramName);
    if (param) {
        param.color = color;
    }
}

// Update submit button state
function updateSubmitButton() {
    const submitBtn = document.getElementById('submitBtn');
    submitBtn.disabled = selectedParameters.size === 0;
}

// Form submission
document.getElementById('searchForm').addEventListener('submit', function(e) {
    e.preventDefault();

    if (selectedParameters.size === 0) {
        alert('Vyberte alespoň jeden parametr');
        return;
    }

    // Build parameters array
    const parameters = [];
    selectedParameters.forEach((data, paramName) => {
        parameters.push({
            name: paramName,
            type: data.type,
            color: data.color
        });
    });

    // Set hidden input
    document.getElementById('parametersInput').value = JSON.stringify(parameters);

    // Submit form
    this.submit();
});
</script>
