<!-- Page header - not sticky -->
<div class="page-sticky-header">
    <div class="header-flex">
        <div class="header-left">
            <div class="breadcrumb">
                <a href="/">Pracoviště</a> /
                <a href="/urineanalysis/workplace/<?= $animal['workplace_id'] ?>">
                    <?= htmlspecialchars($animal['workplace_name'] ?? 'Pracoviště') ?>
                </a> /
                <a href="/urineanalysis/animal/<?= $animal['id'] ?>">
                    <?= htmlspecialchars($animal['name']) ?>
                </a> /
                <span>Kompletní tabulka</span>
            </div>
            <h1 class="page-title">Kompletní tabulka výsledků</h1>
            <p class="page-subtitle">
                <strong><?= htmlspecialchars($animal['name']) ?></strong> |
                ID: <?= htmlspecialchars($animal['identifier']) ?> |
                Druh: <?= htmlspecialchars($animal['species']) ?>
            </p>
        </div>
        <div class="header-right">
            <a href="/urineanalysis/animal/<?= $animal['id'] ?>/graph" class="btn btn-success">
                📊 Vytvořit graf
            </a>
            <a href="/urineanalysis/animal/<?= $animal['id'] ?>" class="btn btn-primary">
                ← Zpět na detail
            </a>
        </div>
    </div>
</div>

<div class="container">

    <?php if (empty($urineTests)): ?>
        <div class="alert alert-info">
            Žádné testy k zobrazení.
        </div>
    <?php else: ?>
        <!-- Urine Analysis Section -->
        <div class="table-area section">
            <h2 class="section-title">Analýza moči</h2>
            <div class="table-responsive">
                <table class="examination-history-table">
                    <thead>
                        <tr>
                            <th class="sticky-col">Referenční zdroj</th>
                            <th class="sticky-col-2">Parametr</th>
                            <?php foreach ($urineTests as $test): ?>
                                <th colspan="2" class="date-header"><?= date('d.m.Y', strtotime($test['test_date'])) ?></th>
                            <?php endforeach; ?>
                        </tr>
                        <tr>
                            <th class="sticky-col"></th>
                            <th class="sticky-col-2"></th>
                            <?php foreach ($urineTests as $test): ?>
                                <th class="value-header">Hodnota</th>
                                <th class="eval-header">vs. ref.</th>
                            <?php endforeach; ?>
                        </tr>
                    </thead>
                    <tbody>
                        <!-- Reference Source Selector Row -->
                        <tr class="source-selector-row">
                            <td class="sticky-col">
                                <select id="referenceSourceSelect" class="reference-source-select" onchange="switchReferenceSource(this.value)">
                                    <?php
                                    $sources = ['Idexx', 'Laboklin', 'Synlab', 'ZIMS'];
                                    foreach ($sources as $source):
                                    ?>
                                        <option value="<?= $source ?>" <?= $source === 'Synlab' ? 'selected' : '' ?>><?= $source ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </td>
                            <td class="sticky-col-2"></td>
                            <?php foreach ($urineTests as $test): ?>
                                <td colspan="2"></td>
                            <?php endforeach; ?>
                        </tr>

                        <?php
                        $rowIndex = 0;
                        foreach ($allParameters as $paramName => $paramInfo):
                            $rowIndex++;
                            $ref = $referenceRanges[$paramName] ?? null;
                            $referenceText = '';

                            if ($ref) {
                                if (!empty($ref['reference_text'])) {
                                    $referenceText = $ref['reference_text'];
                                } elseif ($ref['min_value'] !== null || $ref['max_value'] !== null) {
                                    if ($ref['min_value'] !== null && $ref['max_value'] !== null) {
                                        $referenceText = $ref['min_value'] . ' - ' . $ref['max_value'];
                                    } elseif ($ref['min_value'] !== null) {
                                        $referenceText = '> ' . $ref['min_value'];
                                    } elseif ($ref['max_value'] !== null) {
                                        $referenceText = '< ' . $ref['max_value'];
                                    }
                                }
                            }
                        ?>
                            <tr class="result-row" data-parameter="<?= htmlspecialchars($paramName) ?>">
                                <td class="sticky-col reference-range-cell" data-param="<?= htmlspecialchars($paramName) ?>">
                                    <?= $referenceText ? htmlspecialchars($referenceText) : '-' ?>
                                </td>
                                <td class="sticky-col-2">
                                    <strong><?= htmlspecialchars($paramName) ?></strong>
                                    <span class="unit"><?= htmlspecialchars($paramInfo['unit']) ?></span>
                                </td>
                                <?php foreach ($urineTests as $test): ?>
                                    <?php
                                    $result = $testResults[$test['key']][$paramName] ?? null;
                                    $value = $result['value'] ?? null;
                                    $resultId = $result['id'] ?? null;
                                    $unit = $result['unit'] ?? '';
                                    ?>
                                    <td class="value-col editable-cell"
                                        data-test-key="<?= $test['key'] ?>"
                                        data-value="<?= $value ?>"
                                        data-species="<?= htmlspecialchars($animal['species']) ?>"
                                        data-source="<?= htmlspecialchars($test['reference_source']) ?>"
                                        data-result-id="<?= $resultId ?>"
                                        data-parameter="<?= htmlspecialchars($paramName) ?>"
                                        data-unit="<?= htmlspecialchars($unit) ?>"
                                        onclick="openEditModal(this)">
                                        <?= $value !== null ? htmlspecialchars($value) : '-' ?>
                                    </td>
                                    <td class="eval-col evaluation"
                                        data-for="<?= $test['key'] ?>">
                                        -
                                    </td>
                                <?php endforeach; ?>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    <?php endif; ?>
</div>

<!-- Edit Value Modal -->
<div id="editModal" class="modal">
    <div class="modal-content" style="max-width: 500px;">
        <div class="modal-header">
            <h2>Upravit hodnotu</h2>
            <span class="modal-close" onclick="closeEditModal()">&times;</span>
        </div>
        <div class="modal-body">
            <form id="editForm" onsubmit="saveEdit(event)">
                <div class="form-group">
                    <label>Parametr:</label>
                    <input type="text" id="editParameter" class="form-control" readonly>
                </div>
                <div class="form-group">
                    <label>Hodnota: *</label>
                    <input type="text" id="editValue" class="form-control" required>
                </div>
                <div class="form-group">
                    <label>Jednotka:</label>
                    <input type="text" id="editUnit" class="form-control" readonly>
                </div>
                <input type="hidden" id="editResultId">
                <input type="hidden" id="editCellElement">
                <div style="margin-top: 20px; display: flex; gap: 10px; justify-content: flex-end;">
                    <button type="submit" class="btn btn-primary">Uložit</button>
                    <button type="button" class="btn btn-outline" onclick="closeEditModal()">Zrušit</button>
                </div>
            </form>
        </div>
    </div>
</div>

<style>
/* Page layout */
.main-content .container {
    max-width: 100% !important;
    padding: 0 !important;
}

/* Page header - not sticky */
.page-sticky-header {
    background: white;
    border-bottom: 2px solid #ddd;
    padding: 15px 20px;
}

.header-flex {
    display: flex;
    justify-content: space-between;
    align-items: center;
    gap: 20px;
}

.header-left {
    flex-shrink: 0;
}

.header-right {
    flex: 1;
    display: flex;
    justify-content: flex-end;
}

.breadcrumb {
    margin: 0 0 5px 0;
    font-size: 14px;
    color: #666;
}

.breadcrumb a {
    color: #f39c12;
    text-decoration: none;
}

.breadcrumb a:hover {
    text-decoration: underline;
}

.page-title {
    margin: 0;
    font-size: 24px;
    font-weight: bold;
}

.page-subtitle {
    margin: 5px 0 0 0;
    color: #666;
    font-size: 14px;
}

/* Table area */
.table-area {
    padding: 20px;
    overflow-x: auto;
    overflow-y: visible;
}

.section-title {
    margin: 0 0 15px 0;
    color: #f39c12;
    font-size: 20px;
    font-weight: bold;
}

.table-responsive {
    overflow-x: auto;
    overflow-y: visible;
}

/* Table headers - sticky */
.examination-history-table thead {
    position: sticky;
    top: 0;
    z-index: 20;
}

.examination-history-table thead th {
    background-color: #f39c12;
    color: white;
    padding: 12px 8px;
    text-align: left;
    border: 1px solid #e67e22;
    font-weight: bold;
    font-size: 15px;
}

/* Sticky columns */
.sticky-col {
    position: sticky !important;
    left: 0 !important;
    z-index: 10 !important;
    background-color: white !important;
    border-right: 2px solid #ddd !important;
    font-size: 14px !important;
    min-width: 150px !important;
    max-width: 150px !important;
    width: 150px !important;
}

.sticky-col-2 {
    position: sticky !important;
    left: 150px !important;
    z-index: 10 !important;
    background-color: white !important;
    border-right: 2px solid #ddd !important;
    font-size: 14px !important;
    min-width: 200px !important;
    max-width: 200px !important;
    width: 200px !important;
}

/* Sticky column headers */
th.sticky-col,
th.sticky-col-2 {
    z-index: 30 !important;
    background-color: #f39c12 !important;
}

/* Table styling */
.examination-history-table {
    width: 100%;
    border-collapse: collapse;
    font-size: 13px;
}

.examination-history-table td {
    padding: 8px;
    border: 1px solid #ddd;
    vertical-align: top;
}

.examination-history-table tbody tr:hover td {
    background-color: #fef5e7;
}

/* Date and value headers */
.date-header {
    text-align: center;
}

.value-header,
.eval-header {
    font-size: 13px;
}

.source-selector {
    display: flex;
    flex-direction: column;
    gap: 8px;
    padding: 10px;
}

.badge-source {
    background: white;
    color: #f39c12;
    border: 2px solid #f39c12;
    padding: 8px 12px;
    border-radius: 4px;
    font-size: 12px;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.2s;
    width: 100%;
    text-align: center;
}

.badge-source:hover {
    background: #f39c12;
    color: white;
    transform: translateY(-1px);
    box-shadow: 0 2px 8px rgba(243, 156, 18, 0.3);
}

.badge-source.active {
    background: linear-gradient(135deg, #f39c12 0%, #e67e22 100%);
    color: white;
    border-color: #e67e22;
}

.unit {
    display: block;
    font-size: 11px;
    color: #7f8c8d;
    font-weight: normal;
}

.evaluation {
    font-weight: 600;
    font-size: 12px;
}

.evaluation.normal {
    color: #27ae60;
}

.evaluation.low {
    color: #3498db;
}

.evaluation.high {
    color: #e74c3c;
}

.evaluation.abnormal {
    color: #e67e22;
}

/* Buttons */
.btn {
    padding: 10px 20px;
    border-radius: 4px;
    font-size: 14px;
    cursor: pointer;
    text-decoration: none;
    display: inline-block;
    border: none;
    transition: all 0.2s;
}

.btn-primary {
    background: linear-gradient(135deg, #f39c12 0%, #e67e22 100%);
    color: white;
}

.btn-primary:hover {
    background: linear-gradient(135deg, #e67e22 0%, #d35400 100%);
}

.btn-success {
    background: linear-gradient(135deg, #27ae60 0%, #229954 100%);
    color: white;
}

.btn-success:hover {
    background: linear-gradient(135deg, #229954 0%, #1e8449 100%);
}

/* Alerts */
.alert {
    padding: 20px;
    border-radius: 4px;
    margin: 20px;
}

.alert-info {
    background-color: #fef5e7;
    border: 1px solid #f39c12;
    color: #7f6007;
}

.reference-source-select {
    width: 100%;
    padding: 8px 12px;
    border: 2px solid #f39c12;
    border-radius: 4px;
    font-size: 14px;
    font-weight: 600;
    background: white;
    color: #2c3e50;
    cursor: pointer;
    transition: all 0.2s;
}

.reference-source-select option {
    color: #2c3e50;
    background: white;
}

.reference-source-select:hover {
    background: #fef5e7;
}

.reference-source-select:focus {
    outline: none;
    border-color: #e67e22;
}

.source-selector-row td {
    background-color: #fef5e7;
    font-weight: 600;
}

.reference-range-cell {
    text-align: center;
    font-size: 13px;
    color: #7f8c8d;
}

/* Modal Styles */
.modal {
    display: none;
    position: fixed;
    z-index: 1000;
    left: 0;
    top: 0;
    width: 100%;
    height: 100%;
    overflow: auto;
    background-color: rgba(0, 0, 0, 0.5);
}

.modal-content {
    background-color: #fff;
    margin: 5% auto;
    padding: 0;
    border-radius: 8px;
    box-shadow: 0 4px 16px rgba(0, 0, 0, 0.2);
}

.modal-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 20px;
    background: linear-gradient(135deg, #f39c12 0%, #e67e22 100%);
    color: white;
    border-radius: 8px 8px 0 0;
}

.modal-header h2 {
    margin: 0;
    font-size: 20px;
}

.modal-close {
    color: white;
    font-size: 28px;
    font-weight: bold;
    cursor: pointer;
    line-height: 1;
}

.modal-close:hover {
    opacity: 0.8;
}

.modal-body {
    padding: 20px;
}

.form-control {
    width: 100%;
    padding: 8px;
    border: 1px solid #d0d0d0;
    border-radius: 4px;
    font-size: 14px;
}

.form-control:focus {
    outline: none;
    border-color: #f39c12;
}

.form-group {
    margin-bottom: 15px;
}

.form-group label {
    display: block;
    margin-bottom: 5px;
    font-weight: 600;
    color: #2c3e50;
}

.btn-outline {
    background: white;
    border: 2px solid #f39c12;
    color: #f39c12;
    padding: 10px 20px;
    border-radius: 4px;
    cursor: pointer;
    font-size: 14px;
    transition: all 0.2s;
}

.btn-outline:hover {
    background: #f39c12;
    color: white;
}

.editable-cell {
    cursor: pointer;
    position: relative;
    transition: background-color 0.2s;
}

.editable-cell:hover {
    background-color: #fef5e7 !important;
}

.editable-cell:hover::after {
    content: "✎";
    position: absolute;
    right: 8px;
    top: 50%;
    transform: translateY(-50%);
    color: #f39c12;
    font-size: 14px;
}
</style>

<script>
// Cache for reference ranges
let referenceRangesCache = {};

async function loadEvaluations() {
    const rows = document.querySelectorAll('.result-row');

    for (const row of rows) {
        const parameter = row.dataset.parameter;
        const valueCells = row.querySelectorAll('.value-col[data-value]');

        for (const valueCell of valueCells) {
            const value = valueCell.dataset.value;
            if (!value || value === '-') continue;

            const species = valueCell.dataset.species;
            const source = valueCell.dataset.source;
            const testKey = valueCell.dataset.testKey;

            const cacheKey = `urine-${parameter}-${species}-${source}`;

            let range;
            if (referenceRangesCache[cacheKey]) {
                range = referenceRangesCache[cacheKey];
            } else {
                try {
                    const response = await fetch(`/api/urine-reference-ranges?parameter=${encodeURIComponent(parameter)}&species=${encodeURIComponent(species)}&source=${source}`);
                    if (response.ok) {
                        range = await response.json();
                        referenceRangesCache[cacheKey] = range;
                    } else {
                        range = null;
                    }
                } catch (error) {
                    console.error('Error fetching reference range:', error);
                    range = null;
                }
            }

            // Find corresponding evaluation cell
            const evalCell = row.querySelector(`.evaluation[data-for="${testKey}"]`);

            if (range) {
                let status = 'normal';
                let displayText = '-';

                // Text-based reference (qualitative)
                if (range.reference_text) {
                    const normalizedValue = value.toLowerCase().trim();
                    const normalizedRef = range.reference_text.toLowerCase().trim();

                    if (normalizedValue === normalizedRef ||
                        (normalizedRef === 'negativní' && ['neg.', 'neg', 'negativní', 'negative'].includes(normalizedValue))) {
                        status = 'normal';
                        displayText = 'V normě';
                    } else {
                        status = 'abnormal';
                        displayText = 'Mimo normu';
                    }
                }
                // Numeric range
                else if (range.min_value !== null || range.max_value !== null) {
                    const numValue = parseFloat(value.replace(',', '.'));

                    if (!isNaN(numValue)) {
                        const min = range.min_value !== null ? parseFloat(range.min_value) : null;
                        const max = range.max_value !== null ? parseFloat(range.max_value) : null;

                        if (min !== null && max !== null) {
                            if (numValue < min) {
                                status = 'low';
                                if (min != 0) {
                                    const percentage = ((min - numValue) / min * 100).toFixed(1);
                                    displayText = `↓ ${percentage}%`;
                                } else {
                                    displayText = '↓ Nízké';
                                }
                            } else if (numValue > max) {
                                status = 'high';
                                if (max != 0) {
                                    const percentage = ((numValue - max) / max * 100).toFixed(1);
                                    displayText = `↑ ${percentage}%`;
                                } else {
                                    displayText = `↑ ${numValue}`;
                                }
                            } else {
                                status = 'normal';
                                displayText = 'OK';
                            }
                        } else if (min !== null) {
                            if (numValue < min) {
                                status = 'low';
                                displayText = '↓ Nízké';
                            } else {
                                status = 'normal';
                                displayText = 'OK';
                            }
                        } else if (max !== null) {
                            if (numValue > max) {
                                status = 'high';
                                displayText = '↑ Vysoké';
                            } else {
                                status = 'normal';
                                displayText = 'OK';
                            }
                        }
                    }
                }

                evalCell.textContent = displayText;
                evalCell.className = `eval-col evaluation ${status}`;
            } else {
                evalCell.textContent = '-';
                evalCell.className = 'eval-col evaluation';
            }
        }
    }
}

// Switch reference source for all evaluations
async function switchReferenceSource(newSource) {
    const species = document.querySelector('[data-species]')?.dataset.species;
    if (!species) return;

    // Update reference range cells
    const refCells = document.querySelectorAll('.reference-range-cell');

    for (const cell of refCells) {
        const parameter = cell.dataset.param;
        const cacheKey = `urine-${parameter}-${species}-${newSource}`;

        let range;
        if (referenceRangesCache[cacheKey]) {
            range = referenceRangesCache[cacheKey];
        } else {
            try {
                const response = await fetch(`/api/urine-reference-ranges?parameter=${encodeURIComponent(parameter)}&species=${encodeURIComponent(species)}&source=${newSource}`);
                if (response.ok) {
                    range = await response.json();
                    referenceRangesCache[cacheKey] = range;
                } else {
                    range = null;
                }
            } catch (error) {
                console.error('Error fetching reference range:', error);
                range = null;
            }
        }

        let referenceText = '';
        if (range) {
            if (range.reference_text) {
                referenceText = range.reference_text;
            } else if (range.min_value !== null || range.max_value !== null) {
                const min = range.min_value;
                const max = range.max_value;

                if (min !== null && max !== null) {
                    referenceText = min + ' - ' + max;
                } else if (min !== null) {
                    referenceText = '> ' + min;
                } else if (max !== null) {
                    referenceText = '< ' + max;
                }
            }
        }

        cell.textContent = referenceText || '-';
    }

    // Update all value cells to use the new reference source
    const valueCells = document.querySelectorAll('.value-col[data-source]');
    valueCells.forEach(cell => {
        cell.dataset.source = newSource;
    });

    // Reload evaluations with new source
    loadEvaluations();
}

// Edit Modal Functions
function openEditModal(cell) {
    const parameter = cell.dataset.parameter;
    const value = cell.dataset.value;
    const unit = cell.dataset.unit;
    const resultId = cell.dataset.resultId;

    if (!resultId || value === null || value === '') {
        alert('Tuto hodnotu nelze upravovat (není uložena v databázi)');
        return;
    }

    document.getElementById('editParameter').value = parameter;
    document.getElementById('editValue').value = value;
    document.getElementById('editUnit').value = unit;
    document.getElementById('editResultId').value = resultId;

    // Store reference to the cell element
    window.currentEditCell = cell;

    document.getElementById('editModal').style.display = 'block';
}

function closeEditModal() {
    document.getElementById('editModal').style.display = 'none';
    document.getElementById('editForm').reset();
    window.currentEditCell = null;
}

async function saveEdit(event) {
    event.preventDefault();

    const resultId = document.getElementById('editResultId').value;
    const newValue = document.getElementById('editValue').value;

    try {
        const response = await fetch(`/urineanalysis/result/${resultId}/update`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({
                value: newValue
            })
        });

        if (response.ok) {
            // Update the cell's data-value attribute
            if (window.currentEditCell) {
                window.currentEditCell.dataset.value = newValue;
                window.currentEditCell.textContent = newValue;

                // Refresh the evaluation for this cell
                await updateSingleCellEvaluation(window.currentEditCell);
            }

            closeEditModal();
        } else {
            const errorData = await response.json();
            alert('Chyba při ukládání: ' + (errorData.error || 'Neznámá chyba'));
        }
    } catch (error) {
        console.error('Error:', error);
        alert('Chyba při ukládání hodnoty');
    }
}

async function updateSingleCellEvaluation(cell) {
    const parameter = cell.dataset.parameter;
    const value = cell.dataset.value;
    const species = cell.dataset.species;
    const source = cell.dataset.source;
    const testKey = cell.dataset.testKey;

    if (!value || value === '-') return;

    const cacheKey = `urine-${parameter}-${species}-${source}`;

    let range;
    if (referenceRangesCache[cacheKey]) {
        range = referenceRangesCache[cacheKey];
    } else {
        try {
            const response = await fetch(`/api/urine-reference-ranges?parameter=${encodeURIComponent(parameter)}&species=${encodeURIComponent(species)}&source=${source}`);
            if (response.ok) {
                range = await response.json();
                referenceRangesCache[cacheKey] = range;
            } else {
                range = null;
            }
        } catch (error) {
            console.error('Error fetching reference range:', error);
            range = null;
        }
    }

    // Find the row containing this cell
    const row = cell.closest('tr');
    const evalCell = row.querySelector(`.evaluation[data-for="${testKey}"]`);

    if (range) {
        let status = 'normal';
        let displayText = '-';

        // Text-based reference (qualitative)
        if (range.reference_text) {
            const normalizedValue = value.toLowerCase().trim();
            const normalizedRef = range.reference_text.toLowerCase().trim();

            if (normalizedValue === normalizedRef ||
                (normalizedRef === 'negativní' && ['neg.', 'neg', 'negativní', 'negative'].includes(normalizedValue))) {
                status = 'normal';
                displayText = 'V normě';
            } else {
                status = 'abnormal';
                displayText = 'Mimo normu';
            }
        }
        // Numeric range
        else if (range.min_value !== null || range.max_value !== null) {
            const numValue = parseFloat(value.replace(',', '.'));

            if (!isNaN(numValue)) {
                const min = range.min_value !== null ? parseFloat(range.min_value) : null;
                const max = range.max_value !== null ? parseFloat(range.max_value) : null;

                if (min !== null && max !== null) {
                    if (numValue < min) {
                        status = 'low';
                        if (min != 0) {
                            const percentage = ((min - numValue) / min * 100).toFixed(1);
                            displayText = `↓ ${percentage}%`;
                        } else {
                            displayText = '↓ Nízké';
                        }
                    } else if (numValue > max) {
                        status = 'high';
                        if (max != 0) {
                            const percentage = ((numValue - max) / max * 100).toFixed(1);
                            displayText = `↑ ${percentage}%`;
                        } else {
                            displayText = `↑ ${numValue}`;
                        }
                    } else {
                        status = 'normal';
                        displayText = 'OK';
                    }
                } else if (min !== null) {
                    if (numValue < min) {
                        status = 'low';
                        displayText = '↓ Nízké';
                    } else {
                        status = 'normal';
                        displayText = 'OK';
                    }
                } else if (max !== null) {
                    if (numValue > max) {
                        status = 'high';
                        displayText = '↑ Vysoké';
                    } else {
                        status = 'normal';
                        displayText = 'OK';
                    }
                }
            }
        }

        evalCell.textContent = displayText;
        evalCell.className = `eval-col evaluation ${status}`;
    } else {
        evalCell.textContent = '-';
        evalCell.className = 'eval-col evaluation';
    }
}

// Close modal when clicking outside
window.addEventListener('click', function(event) {
    const editModal = document.getElementById('editModal');
    if (event.target === editModal) {
        closeEditModal();
    }
});

// Load evaluations on page load
document.addEventListener('DOMContentLoaded', function() {
    loadEvaluations();
});
</script>
