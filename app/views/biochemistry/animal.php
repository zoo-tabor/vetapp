<div class="container">
    <div class="breadcrumb">
        <a href="/">Pracoviště</a> /
        <a href="/biochemistry/workplace/<?= $animal['workplace_id'] ?>">
            <?= htmlspecialchars($animal['workplace_name'] ?? 'Pracoviště') ?>
        </a> /
        <span><?= htmlspecialchars($animal['name']) ?></span>
    </div>

    <?php if (isset($_SESSION['success'])): ?>
        <div class="alert alert-success">
            <?= htmlspecialchars($_SESSION['success']) ?>
        </div>
        <?php unset($_SESSION['success']); ?>
    <?php endif; ?>

    <?php if (isset($_SESSION['error'])): ?>
        <div class="alert alert-error">
            <?= htmlspecialchars($_SESSION['error']) ?>
        </div>
        <?php unset($_SESSION['error']); ?>
    <?php endif; ?>

    <div class="page-header">
        <div>
            <h1><?= htmlspecialchars($animal['name']) ?></h1>
            <p>
                <strong>ID:</strong> <?= htmlspecialchars($animal['identifier']) ?> |
                <strong>Druh:</strong> <?= htmlspecialchars($animal['species']) ?> |
                <strong>Výběh:</strong> <?= htmlspecialchars($animal['enclosure_name'] ?? '-') ?>
            </p>
        </div>
        <?php if ($canEdit): ?>
            <div style="display: flex; gap: 10px;">
                <a href="/workplace/<?= $animal['workplace_id'] ?>/animals/<?= $animal['id'] ?>?from=biochemistry" class="btn btn-outline-edit">
                    Upravit
                </a>
                <button class="btn btn-primary" onclick="showAddTestModal('biochemistry')">+ Nová biochemie</button>
                <button class="btn btn-primary" onclick="showAddTestModal('hematology')">+ Nová hematologie</button>
            </div>
        <?php endif; ?>
    </div>

    <!-- Vyhodnocení běží podle laboratoře uložené u konkrétního odběru
         (viz výběr v hlavičce každé karty), ne podle jednoho zdroje pro vše. -->
    <div class="reference-selector">
        <div class="reference-hint">
            Výsledky se vyhodnocují podle referenčních mezí laboratoře přiřazené u daného odběru.
            Zdroj lze dočasně přepnout v hlavičce konkrétního odběru.
        </div>
        <a href="/biochemistry/animal/<?= $animal['id'] ?>/comprehensive-table" class="btn btn-outline">
            Zobrazit kompletní tabulku
        </a>
    </div>

    <!-- Biochemistry Section -->
    <div class="test-section">
        <h2>Biochemie</h2>

        <?php if (empty($biochemTests)): ?>
            <div class="alert alert-info">
                Žádné záznamy o biochemických testech.
            </div>
        <?php else: ?>
            <?php foreach ($biochemTests as $test): ?>
                <div class="test-card">
                    <div class="test-header">
                        <div>
                            <strong>Datum:</strong> <?= date('d.m.Y', strtotime($test['test_date'])) ?>
                            <?php if ($test['test_location']): ?>
                                | <strong>Místo:</strong> <?= htmlspecialchars($test['test_location']) ?>
                            <?php endif; ?>
                            <?php if ($test['created_by_name']): ?>
                                | <strong>Záznam vytvořil:</strong> <?= htmlspecialchars($test['created_by_name']) ?>
                            <?php endif; ?>
                        </div>
                        <div class="test-source-picker">
                            <label>Referenční meze:</label>
                            <?php $__testSource = trim((string)($test['reference_source'] ?? '')); ?>
                            <select class="test-source-select" onchange="changeTestSource(this)"
                                    title="Laboratoř přiřazená k tomuto odběru (u editorů se změna uloží)">
                                <?php if ($__testSource === ''): ?>
                                    <option value="" selected>— nezadáno —</option>
                                <?php endif; ?>
                                <?php foreach ($referenceSources as $source): ?>
                                    <option value="<?= htmlspecialchars($source) ?>" <?= $source === $__testSource ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($source) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>

                    <?php if ($test['notes']): ?>
                        <div class="test-notes">
                            <strong>Poznámky:</strong> <?= nl2br(htmlspecialchars($test['notes'])) ?>
                        </div>
                    <?php endif; ?>

                    <div class="results-table-wrapper">
                        <table class="results-table">
                            <thead>
                                <tr>
                                    <th>Parametr</th>
                                    <th>Hodnota</th>
                                    <th>Jednotka</th>
                                    <th>Referenční rozsah</th>
                                    <th>Vyhodnocení %</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                // Indexy kvality vzorku (lipemický/hemolytický) řadíme nahoru.
                                $__qRes = []; $__oRes = [];
                                foreach ($test['results'] as $__res) {
                                    if (isSampleQualityParam($__res['parameter_name'] ?? '')) { $__qRes[] = $__res; }
                                    else { $__oRes[] = $__res; }
                                }
                                foreach (array_merge($__qRes, $__oRes) as $result):
                                ?>
                                    <tr class="result-row"
                                        data-parameter="<?= htmlspecialchars($result['parameter_name']) ?>"
                                        data-value="<?= $result['value'] ?>"
                                        data-species="<?= htmlspecialchars($animal['species']) ?>"
                                        data-source="<?= htmlspecialchars($__testSource) ?>"
                                        data-test-type="biochemistry"
                                        data-result-id="<?= $result['id'] ?>"
                                        data-test-id="<?= $test['id'] ?>"
                                        data-unit="<?= htmlspecialchars($result['unit']) ?>">
                                        <td class="editable-cell" onclick="openEditModal(this.parentElement)"><strong><?= htmlspecialchars($result['parameter_name']) ?></strong></td>
                                        <td class="editable-cell" onclick="openEditModal(this.parentElement)">
                                            <?php
                                            // Check if value is numeric, if so format it, otherwise display as-is
                                            if (is_numeric($result['value'])) {
                                                echo number_format($result['value'], 2, ',', ' ');
                                            } else {
                                                echo htmlspecialchars($result['value']);
                                            }
                                            ?>
                                        </td>
                                        <td><?= htmlspecialchars($result['unit']) ?></td>
                                        <td class="reference-range">Načítání...</td>
                                        <td class="evaluation">-</td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>

    <!-- Hematology Section -->
    <div class="test-section">
        <h2>Hematologie</h2>

        <?php if (empty($hematoTests)): ?>
            <div class="alert alert-info">
                Žádné záznamy o hematologických testech.
            </div>
        <?php else: ?>
            <?php foreach ($hematoTests as $test): ?>
                <div class="test-card">
                    <div class="test-header">
                        <div>
                            <strong>Datum:</strong> <?= date('d.m.Y', strtotime($test['test_date'])) ?>
                            <?php if ($test['test_location']): ?>
                                | <strong>Místo:</strong> <?= htmlspecialchars($test['test_location']) ?>
                            <?php endif; ?>
                            <?php if ($test['created_by_name']): ?>
                                | <strong>Záznam vytvořil:</strong> <?= htmlspecialchars($test['created_by_name']) ?>
                            <?php endif; ?>
                        </div>
                        <div class="test-source-picker">
                            <label>Referenční meze:</label>
                            <?php $__testSource = trim((string)($test['reference_source'] ?? '')); ?>
                            <select class="test-source-select" onchange="changeTestSource(this)"
                                    title="Laboratoř přiřazená k tomuto odběru (u editorů se změna uloží)">
                                <?php if ($__testSource === ''): ?>
                                    <option value="" selected>— nezadáno —</option>
                                <?php endif; ?>
                                <?php foreach ($referenceSources as $source): ?>
                                    <option value="<?= htmlspecialchars($source) ?>" <?= $source === $__testSource ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($source) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>

                    <?php if ($test['notes']): ?>
                        <div class="test-notes">
                            <strong>Poznámky:</strong> <?= nl2br(htmlspecialchars($test['notes'])) ?>
                        </div>
                    <?php endif; ?>

                    <div class="results-table-wrapper">
                        <table class="results-table">
                            <thead>
                                <tr>
                                    <th>Parametr</th>
                                    <th>Hodnota</th>
                                    <th>Jednotka</th>
                                    <th>Referenční rozsah</th>
                                    <th>Vyhodnocení %</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                // Indexy kvality vzorku (lipemický/hemolytický) řadíme nahoru.
                                $__qRes = []; $__oRes = [];
                                foreach ($test['results'] as $__res) {
                                    if (isSampleQualityParam($__res['parameter_name'] ?? '')) { $__qRes[] = $__res; }
                                    else { $__oRes[] = $__res; }
                                }
                                foreach (array_merge($__qRes, $__oRes) as $result):
                                ?>
                                    <tr class="result-row"
                                        data-parameter="<?= htmlspecialchars($result['parameter_name']) ?>"
                                        data-value="<?= $result['value'] ?>"
                                        data-species="<?= htmlspecialchars($animal['species']) ?>"
                                        data-source="<?= htmlspecialchars($__testSource) ?>"
                                        data-test-type="hematology"
                                        data-result-id="<?= $result['id'] ?>"
                                        data-test-id="<?= $test['id'] ?>"
                                        data-unit="<?= htmlspecialchars($result['unit']) ?>">
                                        <td class="editable-cell" onclick="openEditModal(this.parentElement)"><strong><?= htmlspecialchars($result['parameter_name']) ?></strong></td>
                                        <td class="editable-cell" onclick="openEditModal(this.parentElement)">
                                            <?php
                                            // Check if value is numeric, if so format it, otherwise display as-is
                                            if (is_numeric($result['value'])) {
                                                echo number_format($result['value'], 2, ',', ' ');
                                            } else {
                                                echo htmlspecialchars($result['value']);
                                            }
                                            ?>
                                        </td>
                                        <td><?= htmlspecialchars($result['unit']) ?></td>
                                        <td class="reference-range">Načítání...</td>
                                        <td class="evaluation">-</td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</div>

<?php include __DIR__ . '/add_test_modal.php'; ?>

<!-- Edit Parameter Modal -->
<div id="editModal" class="edit-modal">
    <div class="edit-modal-content">
        <div class="edit-modal-header">
            <h3>Upravit parametr</h3>
            <span class="edit-modal-close" onclick="closeEditModal()">&times;</span>
        </div>
        <div class="edit-modal-body">
            <div class="edit-form-group">
                <label for="edit-parameter">Parametr:</label>
                <input type="text" id="edit-parameter" class="edit-form-control" readonly>
            </div>
            <div class="edit-form-group">
                <label for="edit-value">Hodnota: *</label>
                <input type="number" step="0.01" id="edit-value" class="edit-form-control" required autofocus>
            </div>
            <div class="edit-form-group">
                <label for="edit-unit">Jednotka:</label>
                <input type="text" id="edit-unit" class="edit-form-control" readonly>
            </div>
        </div>
        <div class="edit-modal-footer">
            <button type="button" class="btn btn-outline" onclick="closeEditModal()">Zrušit</button>
            <button type="button" class="btn btn-primary" onclick="saveEdit()">Uložit</button>
        </div>
    </div>
</div>

<style>
.breadcrumb {
    margin-bottom: 20px;
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

.page-header {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    margin-bottom: 30px;
}

.page-header h1 {
    margin: 0 0 10px 0;
    color: #2c3e50;
}

.page-header p {
    margin: 0;
    color: #7f8c8d;
    font-size: 14px;
}

.btn-outline-edit {
    background: white;
    border: 2px solid #27ae60;
    color: #27ae60;
    padding: 10px 20px;
    border-radius: 4px;
    text-decoration: none;
    font-size: 14px;
    font-weight: 600;
    transition: all 0.2s;
    display: inline-block;
}

.btn-outline-edit:hover {
    background: #27ae60;
    color: white;
    transform: translateY(-1px);
    box-shadow: 0 4px 12px rgba(39, 174, 96, 0.3);
}

.reference-selector {
    background: white;
    padding: 20px;
    border-radius: 8px;
    margin-bottom: 30px;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.reference-selector label {
    font-weight: 600;
    color: #2c3e50;
    margin: 0;
}

.reference-hint {
    color: #5d6d7e;
    font-size: 14px;
    line-height: 1.5;
    max-width: 640px;
}

/* Výběr laboratoře v hlavičce karty odběru – vzhledově navazuje na dřívější badge. */
.test-source-picker {
    display: flex;
    align-items: center;
    gap: 8px;
    white-space: nowrap;
}

.test-source-picker label {
    font-size: 13px;
    font-weight: 600;
    color: #7f8c8d;
    margin: 0;
}

.test-source-select {
    background: #c0392b;
    color: white;
    border: 1px solid #a93226;
    padding: 5px 10px;
    border-radius: 4px;
    font-size: 13px;
    font-weight: 600;
    cursor: pointer;
}

.test-source-select:focus {
    outline: 2px solid #e6b0aa;
}

.btn-outline {
    background: white;
    border: 2px solid #c0392b;
    color: #c0392b;
    padding: 10px 20px;
    border-radius: 4px;
    font-size: 14px;
    font-weight: 600;
    text-decoration: none;
    cursor: pointer;
    transition: all 0.2s;
}

.btn-outline:hover {
    background: #c0392b;
    color: white;
}

.test-section {
    margin-bottom: 40px;
}

.test-section h2 {
    color: #c0392b;
    margin-bottom: 20px;
    padding-bottom: 10px;
    border-bottom: 3px solid #c0392b;
}

.test-card {
    background: white;
    border-radius: 8px;
    padding: 20px;
    margin-bottom: 20px;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
}

.test-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 15px;
    padding-bottom: 15px;
    border-bottom: 2px solid #f0f0f0;
}

.test-notes {
    background: #f8f9fa;
    padding: 12px;
    border-radius: 6px;
    margin-bottom: 15px;
    font-size: 14px;
}

.results-table-wrapper {
    overflow-x: auto;
}

.results-table {
    width: 100%;
    border-collapse: collapse;
}

.results-table thead {
    background: linear-gradient(135deg, #c0392b 0%, #a93226 100%);
    color: white;
}

.results-table th {
    padding: 12px;
    text-align: left;
    font-weight: 600;
    font-size: 14px;
}

.results-table td {
    padding: 10px 12px;
    border-bottom: 1px solid #f0f0f0;
}

.results-table tbody tr:hover {
    background-color: #f8f9fa;
}

.results-table tbody tr:last-child td {
    border-bottom: none;
}

.evaluation {
    font-weight: 600;
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

.alert {
    padding: 20px;
    border-radius: 8px;
    margin-bottom: 20px;
}

.alert-info {
    background-color: #d1ecf1;
    border: 1px solid #bee5eb;
    color: #0c5460;
}

.alert-success {
    background-color: #d4edda;
    border: 1px solid #c3e6cb;
    color: #155724;
}

.alert-error {
    background-color: #f8d7da;
    border: 1px solid #f5c6cb;
    color: #721c24;
}

.editable-cell {
    cursor: pointer;
    position: relative;
    transition: background-color 0.2s;
}

.editable-cell:hover {
    background-color: #e8f5e9 !important;
}

.editable-cell:hover::after {
    content: "✎";
    position: absolute;
    right: 8px;
    color: #27ae60;
    font-size: 14px;
}

/* Edit Modal */
.edit-modal {
    display: none;
    position: fixed;
    z-index: 2000;
    left: 0;
    top: 0;
    width: 100%;
    height: 100%;
    overflow: auto;
    background-color: rgba(0, 0, 0, 0.6);
}

.edit-modal-content {
    background-color: #fff;
    margin: 10% auto;
    padding: 0;
    border-radius: 8px;
    width: 90%;
    max-width: 500px;
    box-shadow: 0 4px 20px rgba(0, 0, 0, 0.3);
}

.edit-modal-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 20px;
    background: linear-gradient(135deg, #c0392b 0%, #a93226 100%);
    color: white;
    border-radius: 8px 8px 0 0;
}

.edit-modal-header h3 {
    margin: 0;
    font-size: 18px;
}

.edit-modal-close {
    color: white;
    font-size: 28px;
    font-weight: bold;
    cursor: pointer;
    line-height: 1;
}

.edit-modal-close:hover {
    opacity: 0.8;
}

.edit-modal-body {
    padding: 25px;
}

.edit-form-group {
    margin-bottom: 20px;
}

.edit-form-group label {
    display: block;
    margin-bottom: 8px;
    font-weight: 600;
    color: #2c3e50;
}

.edit-form-control {
    width: 100%;
    padding: 10px;
    border: 2px solid #e0e0e0;
    border-radius: 4px;
    font-size: 15px;
    transition: border-color 0.2s;
}

.edit-form-control:focus {
    outline: none;
    border-color: #c0392b;
}

.edit-modal-footer {
    display: flex;
    gap: 10px;
    justify-content: flex-end;
    padding: 15px 25px 25px;
}
</style>

<script>
// Meze pro všechny parametry a všechny nabízené laboratoře posílá rovnou server
// (dřív se tahaly po jedné přes /api/reference-ranges – desítky sériových requestů).
// Tvar: referenceRanges[typ testu][parametr][laboratoř] = {min_value, max_value, unit}
const referenceRanges = <?= json_encode($referenceRanges ?? [], JSON_UNESCAPED_UNICODE | JSON_HEX_TAG) ?>;

// Editoři: změna laboratoře u odběru se ukládá do DB (ne jen do náhledu).
const canEdit = <?= !empty($canEdit) ? 'true' : 'false' ?>;

function getReferenceRange(testType, parameter, source) {
    if (!source) return null;
    const byParam = referenceRanges[testType];
    if (!byParam) return null;
    const bySource = byParam[parameter];
    if (!bySource) return null;
    return bySource[source] || null;
}

// Vykreslí rozmezí + vyhodnocení do jednoho řádku podle předaných mezí.
function renderRowEvaluation(row, range) {
    const value = parseFloat(row.dataset.value);
    const rangeCell = row.querySelector('.reference-range');
    const evalCell = row.querySelector('.evaluation');

    const hasMin = range && range.min_value !== null && range.min_value !== '';
    const hasMax = range && range.max_value !== null && range.max_value !== '';

    if (!hasMin && !hasMax) {
        rangeCell.textContent = row.dataset.source ? 'Není k dispozici' : 'Zdroj nezadán';
        evalCell.textContent = '-';
        evalCell.className = 'evaluation';
        return;
    }

    const min = parseFloat(range.min_value);
    const max = parseFloat(range.max_value);

    // Text rozmezí – podporuje i otevřenou mez (> min / < max)
    if (hasMin && hasMax) {
        rangeCell.textContent = `${range.min_value} - ${range.max_value} ${range.unit}`;
    } else if (hasMin) {
        rangeCell.textContent = `> ${range.min_value} ${range.unit}`;
    } else {
        rangeCell.textContent = `< ${range.max_value} ${range.unit}`;
    }

    let status = 'normal';
    let displayText = 'OK';

    if (isNaN(value)) {
        displayText = '-';
        status = '';
    } else if (hasMin && value < min) {
        status = 'low';
        const pct = min !== 0 ? ((min - value) / Math.abs(min) * 100).toFixed(2) : null;
        displayText = pct !== null ? `↓ ${pct}%` : '↓';
    } else if (hasMax && value > max) {
        status = 'high';
        const pct = max !== 0 ? ((value - max) / Math.abs(max) * 100).toFixed(2) : null;
        displayText = pct !== null ? `↑ ${pct}%` : '↑';
    }

    evalCell.textContent = displayText;
    evalCell.className = `evaluation ${status}`.trim();
}

// Zdroj se bere z řádku (= laboratoř odběru), ne z jednoho výběru pro celou stránku.
function updateSingleRow(row) {
    renderRowEvaluation(row, getReferenceRange(
        row.dataset.testType,
        row.dataset.parameter,
        row.dataset.source
    ));
}

function updateReferenceRanges(scope) {
    (scope || document).querySelectorAll('.result-row').forEach(updateSingleRow);
}

// Ruční přepnutí laboratoře u konkrétního odběru – platí jen pro zobrazení
// (v databázi zůstává zdroj přiřazený při zadávání).
function changeTestSource(select) {
    const card = select.closest('.test-card');
    if (!card) return;

    card.querySelectorAll('.result-row').forEach(row => {
        row.dataset.source = select.value;
    });
    updateReferenceRanges(card);

    // Editoři: uložit laboratoř natrvalo do DB (dřív jen dočasná změna náhledu).
    // Typ/ID odběru vezmeme z první výsledkové řádky karty.
    if (canEdit) {
        const row = card.querySelector('.result-row');
        if (row && row.dataset.testId) {
            persistTestSource(row.dataset.testType, row.dataset.testId, select.value);
        }
    }
}

async function persistTestSource(testType, testId, source) {
    if (!testType || !testId) return;
    try {
        const response = await fetch(`/biochemistry/test/${testType}/${testId}/reference-source`, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ source: source })
        });
        if (!response.ok) {
            const err = await response.json().catch(() => ({}));
            alert('Laboratoř se nepodařilo uložit: ' + (err.error || 'neznámá chyba'));
        }
    } catch (e) {
        console.error('persistTestSource error', e);
        alert('Chyba při ukládání laboratoře.');
    }
}

// Load reference ranges on page load
document.addEventListener('DOMContentLoaded', function() {
    updateReferenceRanges();
});

// Edit Modal Functions
let currentEditRow = null;

function openEditModal(row) {
    currentEditRow = row;
    const parameter = row.dataset.parameter;
    const value = parseFloat(row.dataset.value);
    const unit = row.dataset.unit;

    document.getElementById('edit-parameter').value = parameter;
    document.getElementById('edit-value').value = value;
    document.getElementById('edit-unit').value = unit;
    document.getElementById('editModal').style.display = 'block';

    // Focus on value input
    setTimeout(() => {
        document.getElementById('edit-value').select();
    }, 100);
}

function closeEditModal() {
    document.getElementById('editModal').style.display = 'none';
    currentEditRow = null;
}

async function saveEdit() {
    if (!currentEditRow) return;

    const newValue = parseFloat(document.getElementById('edit-value').value);
    const resultId = currentEditRow.dataset.resultId;
    const testType = currentEditRow.dataset.testType;

    if (isNaN(newValue)) {
        alert('Prosím zadejte platnou číselnou hodnotu');
        return;
    }

    try {
        const response = await fetch(`/biochemistry/result/${resultId}/update`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({
                value: newValue,
                test_type: testType
            })
        });

        if (response.ok) {
            // Update the row data and display
            currentEditRow.dataset.value = newValue;
            const valueCell = currentEditRow.querySelector('.editable-cell:nth-child(2)');
            valueCell.textContent = newValue.toLocaleString('cs-CZ', { minimumFractionDigits: 2, maximumFractionDigits: 2 });

            // Refresh reference range and evaluation
            updateSingleRow(currentEditRow);

            closeEditModal();
        } else {
            const error = await response.json();
            alert('Chyba při ukládání: ' + (error.error || 'Neznámá chyba'));
        }
    } catch (error) {
        console.error('Error:', error);
        alert('Chyba při komunikaci se serverem');
    }
}

// Close modal when clicking outside
window.onclick = function(event) {
    const modal = document.getElementById('editModal');
    if (event.target == modal) {
        closeEditModal();
    }
}

// Handle Enter key in value input
document.addEventListener('DOMContentLoaded', function() {
    document.getElementById('edit-value').addEventListener('keypress', function(e) {
        if (e.key === 'Enter') {
            saveEdit();
        }
    });
});

</script>
