<?php $layout = 'main'; ?>

<div class="container">
    <div class="breadcrumb">
        <a href="/">Pracoviště</a> /
        <a href="/urineanalysis">Analýza moči</a> /
        <span>Správa referenčních hodnot</span>
    </div>

    <div class="page-header">
        <h1>Správa referenčních hodnot - Analýza moči</h1>
    </div>

    <!-- Filters -->
    <div class="filters-card">
        <div class="filter-group">
            <label for="speciesFilter">Druh zvířete:</label>
            <select id="speciesFilter" class="form-control" onchange="applyFilters()">
                <option value="">-- Vyberte druh --</option>
                <?php foreach ($allSpecies as $species): ?>
                    <option value="<?= htmlspecialchars($species) ?>" <?= $filterSpecies === $species ? 'selected' : '' ?>>
                        <?= htmlspecialchars($species) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
    </div>

    <!-- Reference Source Tabs -->
    <div class="source-tabs">
        <?php foreach ($referenceSources as $source): ?>
            <button class="source-tab <?= $filterSource === $source ? 'active' : '' ?>"
                    onclick="selectSource('<?= htmlspecialchars($source) ?>')">
                <?= htmlspecialchars($source) ?>
            </button>
        <?php endforeach; ?>
    </div>

    <!-- Reference Ranges Table -->
    <div class="reference-table-card">
        <?php if (empty($filterSpecies)): ?>
            <div class="alert alert-info">
                Prosím vyberte druh zvířete pro zobrazení a úpravu referenčních hodnot.
            </div>
        <?php else: ?>
            <div class="table-header">
                <h3>Referenční hodnoty pro: <?= htmlspecialchars($filterSpecies) ?> (<?= htmlspecialchars($filterSource) ?>)</h3>
                <button class="btn btn-primary" onclick="saveReferenceRanges()">Uložit změny</button>
            </div>

            <div id="saveStatus" class="alert" style="display: none;"></div>

            <div class="parameters-sections">
                <?php foreach ($standardParameters as $category => $parameters): ?>
                    <div class="parameter-category">
                        <h4><?= htmlspecialchars($category) ?></h4>
                        <table class="reference-table">
                            <thead>
                                <tr>
                                    <th>Parametr</th>
                                    <th>Textová hodnota</th>
                                    <th>Min. hodnota</th>
                                    <th>Max. hodnota</th>
                                    <th>Jednotka</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($parameters as $paramName): ?>
                                    <?php
                                    // Find existing range for this parameter
                                    $existingRange = null;
                                    foreach ($referenceRanges as $range) {
                                        if ($range['parameter_name'] === $paramName) {
                                            $existingRange = $range;
                                            break;
                                        }
                                    }
                                    ?>
                                    <tr data-parameter="<?= htmlspecialchars($paramName) ?>">
                                        <td><strong><?= htmlspecialchars($paramName) ?></strong></td>
                                        <td>
                                            <input type="text"
                                                   class="form-control reference-text"
                                                   value="<?= $existingRange ? htmlspecialchars($existingRange['reference_text'] ?? '') : '' ?>"
                                                   placeholder="např. negativní">
                                        </td>
                                        <td>
                                            <input type="number"
                                                   step="0.001"
                                                   class="form-control min-value"
                                                   value="<?= $existingRange && $existingRange['min_value'] !== null ? htmlspecialchars($existingRange['min_value']) : '' ?>"
                                                   placeholder="Min">
                                        </td>
                                        <td>
                                            <input type="number"
                                                   step="0.001"
                                                   class="form-control max-value"
                                                   value="<?= $existingRange && $existingRange['max_value'] !== null ? htmlspecialchars($existingRange['max_value']) : '' ?>"
                                                   placeholder="Max">
                                        </td>
                                        <td>
                                            <input type="text"
                                                   class="form-control unit-value"
                                                   value="<?= $existingRange ? htmlspecialchars($existingRange['unit']) : '' ?>"
                                                   placeholder="Jednotka">
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endforeach; ?>

                <!-- Custom Parameters Section -->
                <div class="parameter-category">
                    <h4>Vlastní parametry</h4>
                    <button class="btn btn-secondary" onclick="addCustomParameter()">+ Přidat jiný parametr</button>
                    <table class="reference-table" id="customParametersTable">
                        <thead>
                            <tr>
                                <th>Parametr</th>
                                <th>Textová hodnota</th>
                                <th>Min. hodnota</th>
                                <th>Max. hodnota</th>
                                <th>Jednotka</th>
                                <th>Akce</th>
                            </tr>
                        </thead>
                        <tbody id="customParametersBody">
                            <?php
                            // Show custom parameters (those not in standard list)
                            $allStandardParams = array_merge(...array_values($standardParameters));
                            foreach ($referenceRanges as $range):
                                if (!in_array($range['parameter_name'], $allStandardParams)):
                            ?>
                                <tr data-parameter="<?= htmlspecialchars($range['parameter_name']) ?>">
                                    <td>
                                        <input type="text"
                                               class="form-control param-name"
                                               value="<?= htmlspecialchars($range['parameter_name']) ?>"
                                               readonly>
                                    </td>
                                    <td>
                                        <input type="text"
                                               class="form-control reference-text"
                                               value="<?= htmlspecialchars($range['reference_text'] ?? '') ?>">
                                    </td>
                                    <td>
                                        <input type="number"
                                               step="0.001"
                                               class="form-control min-value"
                                               value="<?= $range['min_value'] !== null ? htmlspecialchars($range['min_value']) : '' ?>">
                                    </td>
                                    <td>
                                        <input type="number"
                                               step="0.001"
                                               class="form-control max-value"
                                               value="<?= $range['max_value'] !== null ? htmlspecialchars($range['max_value']) : '' ?>">
                                    </td>
                                    <td>
                                        <input type="text"
                                               class="form-control unit-value"
                                               value="<?= htmlspecialchars($range['unit']) ?>">
                                    </td>
                                    <td>
                                        <button class="btn btn-danger btn-sm" onclick="removeCustomParameter(this)">Smazat</button>
                                    </td>
                                </tr>
                            <?php
                                endif;
                            endforeach;
                            ?>
                        </tbody>
                    </table>
                </div>
            </div>
        <?php endif; ?>
    </div>
</div>

<style>
.breadcrumb {
    margin-bottom: 20px;
    color: #7f8c8d;
    font-size: 14px;
}

.breadcrumb a {
    color: #f39c12;
    text-decoration: none;
}

.breadcrumb a:hover {
    color: #e67e22;
    text-decoration: underline;
}

.page-header {
    margin-bottom: 30px;
}

.page-header h1 {
    margin: 0;
    color: #2c3e50;
}

.filters-card {
    background: white;
    padding: 20px;
    border-radius: 8px;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
    margin-bottom: 20px;
}

.filter-group {
    display: flex;
    align-items: center;
    gap: 15px;
}

.filter-group label {
    font-weight: 600;
    color: #2c3e50;
    min-width: 120px;
}

.form-control {
    padding: 8px 12px;
    border: 1px solid #ddd;
    border-radius: 4px;
    font-size: 14px;
}

.form-control:focus {
    outline: none;
    border-color: #f39c12;
    box-shadow: 0 0 0 2px rgba(243, 156, 18, 0.1);
}

.source-tabs {
    display: flex;
    gap: 10px;
    margin-bottom: 20px;
}

.source-tab {
    padding: 12px 24px;
    background: white;
    border: 2px solid #ecf0f1;
    border-radius: 8px 8px 0 0;
    color: #7f8c8d;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.2s;
}

.source-tab:hover {
    border-color: #f39c12;
    color: #f39c12;
}

.source-tab.active {
    background: linear-gradient(135deg, #f39c12 0%, #e67e22 100%);
    color: white;
    border-color: #f39c12;
}

.reference-table-card {
    background: white;
    padding: 30px;
    border-radius: 8px;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
}

.table-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 25px;
}

.table-header h3 {
    margin: 0;
    color: #2c3e50;
    font-size: 20px;
}

.btn {
    padding: 10px 20px;
    border-radius: 4px;
    border: none;
    font-size: 14px;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.2s;
}

.btn-primary {
    background: linear-gradient(135deg, #f39c12 0%, #e67e22 100%);
    color: white;
}

.btn-primary:hover {
    background: linear-gradient(135deg, #e67e22 0%, #d35400 100%);
}

.btn-secondary {
    background: white;
    border: 2px solid #f39c12;
    color: #f39c12;
    margin-bottom: 15px;
}

.btn-secondary:hover {
    background: #f39c12;
    color: white;
}

.btn-danger {
    background: #e74c3c;
    color: white;
}

.btn-danger:hover {
    background: #c0392b;
}

.btn-sm {
    padding: 6px 12px;
    font-size: 12px;
}

.parameters-sections {
    display: flex;
    flex-direction: column;
    gap: 30px;
}

.parameter-category h4 {
    color: #f39c12;
    font-size: 18px;
    margin-bottom: 15px;
    font-weight: 600;
    border-bottom: 2px solid #f39c12;
    padding-bottom: 8px;
}

.reference-table {
    width: 100%;
    border-collapse: collapse;
    margin-bottom: 20px;
}

.reference-table thead {
    background: linear-gradient(135deg, #f39c12 0%, #e67e22 100%);
    color: white;
}

.reference-table th {
    padding: 12px;
    text-align: left;
    font-weight: 600;
    font-size: 14px;
}

.reference-table td {
    padding: 10px 12px;
    border-bottom: 1px solid #ecf0f1;
}

.reference-table tbody tr:hover {
    background: #fef5e7;
}

.reference-table input[type="number"],
.reference-table input[type="text"] {
    width: 100%;
}

.alert {
    padding: 15px 20px;
    border-radius: 8px;
    margin-bottom: 20px;
}

.alert-info {
    background: #fef5e7;
    border: 1px solid #f39c12;
    color: #7f6007;
}

.alert-success {
    background: #d4edda;
    border: 1px solid #c3e6cb;
    color: #155724;
}

.alert-error {
    background: #f8d7da;
    border: 1px solid #f5c6cb;
    color: #721c24;
}
</style>

<script>
function applyFilters() {
    const species = document.getElementById('speciesFilter').value;
    const currentSource = '<?= htmlspecialchars($filterSource) ?>';
    window.location.href = `/urineanalysis/reference-ranges?species=${encodeURIComponent(species)}&source=${encodeURIComponent(currentSource)}`;
}

function selectSource(source) {
    const species = document.getElementById('speciesFilter').value;
    if (!species) {
        alert('Nejdříve vyberte druh zvířete');
        return;
    }
    window.location.href = `/urineanalysis/reference-ranges?species=${encodeURIComponent(species)}&source=${encodeURIComponent(source)}`;
}

function addCustomParameter() {
    const paramName = prompt('Zadejte název parametru:');
    if (!paramName) return;

    const tbody = document.getElementById('customParametersBody');
    const row = document.createElement('tr');
    row.setAttribute('data-parameter', paramName);
    row.innerHTML = `
        <td>
            <input type="text" class="form-control param-name" value="${escapeHtml(paramName)}" readonly>
        </td>
        <td>
            <input type="text" class="form-control reference-text" placeholder="např. negativní">
        </td>
        <td>
            <input type="number" step="0.001" class="form-control min-value" placeholder="Min">
        </td>
        <td>
            <input type="number" step="0.001" class="form-control max-value" placeholder="Max">
        </td>
        <td>
            <input type="text" class="form-control unit-value" placeholder="Jednotka">
        </td>
        <td>
            <button class="btn btn-danger btn-sm" onclick="removeCustomParameter(this)">Smazat</button>
        </td>
    `;
    tbody.appendChild(row);
}

function removeCustomParameter(btn) {
    if (confirm('Opravdu chcete smazat tento parametr?')) {
        btn.closest('tr').remove();
    }
}

async function saveReferenceRanges() {
    const species = document.getElementById('speciesFilter').value;
    const source = '<?= htmlspecialchars($filterSource) ?>';

    if (!species) {
        alert('Vyberte druh zvířete');
        return;
    }

    const ranges = [];

    // Collect all rows from all tables
    document.querySelectorAll('.reference-table tbody tr').forEach(row => {
        const paramElement = row.querySelector('.param-name');
        const paramName = paramElement ? paramElement.value : row.getAttribute('data-parameter');
        const referenceText = row.querySelector('.reference-text')?.value || '';
        const minValue = row.querySelector('.min-value').value;
        const maxValue = row.querySelector('.max-value').value;
        const unit = row.querySelector('.unit-value').value;

        // Only save if at least one value is filled
        if (referenceText || minValue || maxValue || unit) {
            ranges.push({
                species: species,
                source: source,
                parameter: paramName,
                referenceText: referenceText,
                min: minValue,
                max: maxValue,
                unit: unit
            });
        }
    });

    if (ranges.length === 0) {
        alert('Žádné hodnoty k uložení');
        return;
    }

    try {
        const response = await fetch('/urineanalysis/reference-ranges/save', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({ ranges: ranges })
        });

        const result = await response.json();

        const statusDiv = document.getElementById('saveStatus');
        if (result.success) {
            statusDiv.className = 'alert alert-success';
            statusDiv.textContent = 'Referenční hodnoty byly úspěšně uloženy';
            statusDiv.style.display = 'block';
            setTimeout(() => {
                statusDiv.style.display = 'none';
            }, 3000);
        } else {
            statusDiv.className = 'alert alert-error';
            statusDiv.textContent = 'Chyba při ukládání: ' + result.error;
            statusDiv.style.display = 'block';
        }
    } catch (error) {
        const statusDiv = document.getElementById('saveStatus');
        statusDiv.className = 'alert alert-error';
        statusDiv.textContent = 'Chyba při ukládání: ' + error.message;
        statusDiv.style.display = 'block';
    }
}

function escapeHtml(text) {
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}
</script>
