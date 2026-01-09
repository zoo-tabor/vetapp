<!-- Page header - not sticky -->
<div class="page-sticky-header">
    <div class="header-flex">
        <div class="header-left">
            <div class="breadcrumb">
                <a href="/">Pracoviště</a> /
                <a href="/biochemistry/workplace/<?= $animal['workplace_id'] ?>">
                    <?= htmlspecialchars($animal['workplace_name'] ?? 'Pracoviště') ?>
                </a> /
                <a href="/biochemistry/animal/<?= $animal['id'] ?>">
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
            <a href="/biochemistry/animal/<?= $animal['id'] ?>/graph" class="btn btn-success" style="margin-right: 10px;">
                📊 Vytvořit graf
            </a>
            <a href="/biochemistry/animal/<?= $animal['id'] ?>" class="btn btn-primary">
                ← Zpět na detail
            </a>
        </div>
    </div>
</div>

<div class="container">

    <?php if (empty($biochemTests) && empty($hematoTests)): ?>
        <div class="alert alert-info">
            Žádné testy k zobrazení.
        </div>
    <?php else: ?>
        <!-- Biochemistry Section -->
        <?php if (!empty($biochemTests)): ?>
            <div class="table-area section">
                <h2 class="section-title">Biochemie</h2>
                <div class="table-responsive">
                    <table class="examination-history-table">
                        <thead>
                            <tr>
                                <th class="sticky-col">Referenční zdroj</th>
                                <th class="sticky-col-2">Parametr</th>
                                <?php foreach ($biochemTests as $test): ?>
                                    <th colspan="2" class="date-header"><?= date('d.m.Y', strtotime($test['test_date'])) ?></th>
                                <?php endforeach; ?>
                            </tr>
                            <tr>
                                <th class="sticky-col"></th>
                                <th class="sticky-col-2"></th>
                                <?php foreach ($biochemTests as $test): ?>
                                    <th class="value-header">Hodnota</th>
                                    <th class="eval-header">vs. ref.</th>
                                <?php endforeach; ?>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            // Filter only biochemistry parameters
                            $biochemParams = array_filter($allParameters, function($param) {
                                return $param['type'] === 'biochemistry';
                            });

                            $rowIndex = 0;
                            foreach ($biochemParams as $paramName => $paramInfo):
                                $rowIndex++;
                            ?>
                                <tr class="result-row" data-parameter="<?= htmlspecialchars($paramName) ?>">
                                    <?php if ($rowIndex === 1): ?>
                                        <td class="sticky-col" rowspan="<?= count($biochemParams) ?>">
                                            <div class="source-selector">
                                                <?php
                                                $sources = ['Laboklin', 'Idexx', 'Synlab', 'ZIMS'];
                                                foreach ($sources as $index => $source):
                                                ?>
                                                    <button class="badge-source <?= $index === 0 ? 'active' : '' ?>" data-source="<?= $source ?>" onclick="switchReferenceSource('biochemistry', '<?= $source ?>')">
                                                        <?= $source ?>
                                                    </button>
                                                <?php endforeach; ?>
                                            </div>
                                        </td>
                                    <?php endif; ?>
                                    <td class="sticky-col-2">
                                        <strong><?= htmlspecialchars($paramName) ?></strong>
                                        <span class="unit"><?= htmlspecialchars($paramInfo['unit']) ?></span>
                                    </td>
                                    <?php foreach ($biochemTests as $test): ?>
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
                                            data-test-type="biochemistry"
                                            data-result-id="<?= $resultId ?>"
                                            data-parameter="<?= htmlspecialchars($paramName) ?>"
                                            data-unit="<?= htmlspecialchars($unit) ?>"
                                            onclick="openEditModal(this)">
                                            <?php
                                            if ($value !== null) {
                                                // Check if value is numeric, if so format it, otherwise display as-is
                                                if (is_numeric($value)) {
                                                    echo number_format($value, 2, ',', ' ');
                                                } else {
                                                    echo htmlspecialchars($value);
                                                }
                                            } else {
                                                echo '-';
                                            }
                                            ?>
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

        <!-- Hematology Section -->
        <?php if (!empty($hematoTests)): ?>
            <div class="table-area section">
                <h2 class="section-title">Hematologie</h2>
                <div class="table-responsive">
                    <table class="examination-history-table">
                        <thead>
                            <tr>
                                <th class="sticky-col">Referenční zdroj</th>
                                <th class="sticky-col-2">Parametr</th>
                                <?php foreach ($hematoTests as $test): ?>
                                    <th colspan="2" class="date-header"><?= date('d.m.Y', strtotime($test['test_date'])) ?></th>
                                <?php endforeach; ?>
                            </tr>
                            <tr>
                                <th class="sticky-col"></th>
                                <th class="sticky-col-2"></th>
                                <?php foreach ($hematoTests as $test): ?>
                                    <th class="value-header">Hodnota</th>
                                    <th class="eval-header">vs. ref.</th>
                                <?php endforeach; ?>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            // Filter only hematology parameters
                            $hematoParams = array_filter($allParameters, function($param) {
                                return $param['type'] === 'hematology';
                            });

                            $rowIndex = 0;
                            foreach ($hematoParams as $paramName => $paramInfo):
                                $rowIndex++;
                            ?>
                                <tr class="result-row" data-parameter="<?= htmlspecialchars($paramName) ?>">
                                    <?php if ($rowIndex === 1): ?>
                                        <td class="sticky-col" rowspan="<?= count($hematoParams) ?>">
                                            <div class="source-selector">
                                                <?php
                                                $sources = ['Laboklin', 'Idexx', 'Synlab', 'ZIMS'];
                                                foreach ($sources as $index => $source):
                                                ?>
                                                    <button class="badge-source <?= $index === 0 ? 'active' : '' ?>" data-source="<?= $source ?>" onclick="switchReferenceSource('hematology', '<?= $source ?>')">
                                                        <?= $source ?>
                                                    </button>
                                                <?php endforeach; ?>
                                            </div>
                                        </td>
                                    <?php endif; ?>
                                    <td class="sticky-col-2">
                                        <strong><?= htmlspecialchars($paramName) ?></strong>
                                        <span class="unit"><?= htmlspecialchars($paramInfo['unit']) ?></span>
                                    </td>
                                    <?php foreach ($hematoTests as $test): ?>
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
                                            data-test-type="hematology"
                                            data-result-id="<?= $resultId ?>"
                                            data-parameter="<?= htmlspecialchars($paramName) ?>"
                                            data-unit="<?= htmlspecialchars($unit) ?>"
                                            onclick="openEditModal(this)">
                                            <?php
                                            if ($value !== null) {
                                                // Check if value is numeric, if so format it, otherwise display as-is
                                                if (is_numeric($value)) {
                                                    echo number_format($value, 2, ',', ' ');
                                                } else {
                                                    echo htmlspecialchars($value);
                                                }
                                            } else {
                                                echo '-';
                                            }
                                            ?>
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
    <?php endif; ?>
</div>

<!-- Graph Configuration Modal -->
<div id="graphModal" class="modal">
    <div class="modal-content" style="max-width: 700px;">
        <div class="modal-header">
            <h2>Nastavení grafu</h2>
            <span class="modal-close" onclick="closeGraphModal()">&times;</span>
        </div>
        <div class="modal-body">
            <div class="form-group">
                <label>Počet posledních vzorků:</label>
                <input type="number"
                       id="sampleCount"
                       class="form-control"
                       min="1"
                       max="50"
                       value="5"
                       placeholder="Zadejte počet vzorků">
                <small class="text-muted">Kolik posledních testů zobrazit v grafu (např. 3, 5, 10)</small>
            </div>

            <hr>

            <div class="parameters-selection">
                <div class="parameters-column">
                    <h3>Biochemie</h3>
                    <div id="biochemParametersList" class="parameters-list"></div>
                </div>

                <div class="parameters-column">
                    <h3>Hematologie</h3>
                    <div id="hematoParametersList" class="parameters-list"></div>
                </div>
            </div>
        </div>
        <div class="modal-footer">
            <button type="button" class="btn btn-success" onclick="generateGraph()">
                Vygenerovat graf
            </button>
            <button type="button" class="btn btn-outline" onclick="closeGraphModal()">
                Zrušit
            </button>
        </div>
    </div>
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
                    <input type="number" step="0.01" id="editValue" class="form-control" required>
                </div>
                <div class="form-group">
                    <label>Jednotka:</label>
                    <input type="text" id="editUnit" class="form-control" readonly>
                </div>
                <input type="hidden" id="editResultId">
                <input type="hidden" id="editTestType">
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
    color: #c0392b;
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
    color: #c0392b;
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
    background-color: #c0392b;
    color: white;
    padding: 12px 8px;
    text-align: left;
    border: 1px solid #a93226;
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
    background-color: #c0392b !important;
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
    background-color: #f9f9f9;
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
    color: #c0392b;
    border: 2px solid #c0392b;
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
    background: #c0392b;
    color: white;
    transform: translateY(-1px);
    box-shadow: 0 2px 8px rgba(192, 57, 43, 0.3);
}

.badge-source.active {
    background: linear-gradient(135deg, #c0392b 0%, #a93226 100%);
    color: white;
    border-color: #a93226;
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
    background-color: #c0392b;
    color: white;
}

.btn-primary:hover {
    background-color: #a93226;
}

/* Alerts */
.alert {
    padding: 20px;
    border-radius: 4px;
    margin: 20px;
}

.alert-info {
    background-color: #d1ecf1;
    border: 1px solid #bee5eb;
    color: #0c5460;
}

/* Graph Modal Styles */
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
    background: linear-gradient(135deg, #c0392b 0%, #a93226 100%);
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

.btn-success {
    background: linear-gradient(135deg, #27ae60 0%, #229954 100%);
    color: white;
    border: none;
    padding: 10px 20px;
    border-radius: 4px;
    cursor: pointer;
    font-size: 14px;
    transition: all 0.2s;
}

.btn-success:hover {
    background: linear-gradient(135deg, #229954 0%, #1e8449 100%);
    transform: translateY(-1px);
    box-shadow: 0 4px 12px rgba(39, 174, 96, 0.3);
}

.parameters-selection {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 20px;
    margin-top: 20px;
}

.parameters-column h3 {
    color: #2c3e50;
    font-size: 16px;
    margin-bottom: 15px;
    padding-bottom: 10px;
    border-bottom: 2px solid #c0392b;
}

.parameters-list {
    max-height: 400px;
    overflow-y: auto;
    border: 1px solid #ddd;
    border-radius: 4px;
    padding: 10px;
}

.parameter-checkbox-item {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 8px;
    margin-bottom: 8px;
    border-radius: 4px;
    background: #f8f9fa;
    transition: background 0.2s;
}

.parameter-checkbox-item:hover {
    background: #e9ecef;
}

.parameter-checkbox-label {
    display: flex;
    align-items: center;
    cursor: pointer;
    margin: 0;
    flex: 1;
}

.parameter-checkbox-label input[type="checkbox"] {
    margin-right: 10px;
    cursor: pointer;
}

.parameter-checkbox-label span {
    font-size: 14px;
    color: #2c3e50;
}

.color-picker {
    width: 40px;
    height: 30px;
    border: 1px solid #ddd;
    border-radius: 4px;
    cursor: pointer;
}

.modal-footer {
    padding: 20px 30px;
    border-top: 2px solid #f0f0f0;
    display: flex;
    justify-content: flex-end;
    gap: 10px;
}

.text-muted {
    font-size: 12px;
    color: #6c757d;
    display: block;
    margin-top: 5px;
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
    top: 50%;
    transform: translateY(-50%);
    color: #27ae60;
    font-size: 14px;
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
    border-color: #27ae60;
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
    border: 2px solid #c0392b;
    color: #c0392b;
    padding: 10px 20px;
    border-radius: 4px;
    cursor: pointer;
    font-size: 14px;
    transition: all 0.2s;
}

.btn-outline:hover {
    background: #c0392b;
    color: white;
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
            const value = parseFloat(valueCell.dataset.value);
            if (isNaN(value)) continue;

            const species = valueCell.dataset.species;
            const source = valueCell.dataset.source;
            const testType = valueCell.dataset.testType;
            const testKey = valueCell.dataset.testKey;

            const cacheKey = `${testType}-${parameter}-${species}-${source}`;

            let range;
            if (referenceRangesCache[cacheKey]) {
                range = referenceRangesCache[cacheKey];
            } else {
                try {
                    const response = await fetch(`/api/reference-ranges?test_type=${testType}&parameter=${encodeURIComponent(parameter)}&species=${encodeURIComponent(species)}&source=${source}`);
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

            if (range && range.min_value !== null && range.max_value !== null) {
                const min = parseFloat(range.min_value);
                const max = parseFloat(range.max_value);

                let status = 'normal';
                let displayText = 'OK';

                if (value < min) {
                    status = 'low';
                    const percentage = ((min - value) / min * 100).toFixed(2);
                    displayText = `↓ ${percentage}%`;
                } else if (value > max) {
                    status = 'high';
                    const percentage = ((value - max) / max * 100).toFixed(2);
                    displayText = `↑ ${percentage}%`;
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

// Switch reference source for all evaluations in a section
function switchReferenceSource(testType, newSource) {
    // Update active state of buttons
    const section = event.target.closest('.section');
    const buttons = section.querySelectorAll('.badge-source');
    buttons.forEach(btn => {
        if (btn.dataset.source === newSource) {
            btn.classList.add('active');
        } else {
            btn.classList.remove('active');
        }
    });

    // Update all value cells to use the new reference source
    const valueCells = section.querySelectorAll('.value-col[data-test-type]');
    valueCells.forEach(cell => {
        cell.dataset.source = newSource;
    });

    // Reload evaluations with new source
    loadEvaluationsForSection(section, testType);
}

async function loadEvaluationsForSection(section, testType) {
    const rows = section.querySelectorAll('.result-row');

    for (const row of rows) {
        const parameter = row.dataset.parameter;
        const valueCells = row.querySelectorAll('.value-col[data-value]');

        for (const valueCell of valueCells) {
            const value = parseFloat(valueCell.dataset.value);
            if (isNaN(value)) continue;

            const species = valueCell.dataset.species;
            const source = valueCell.dataset.source;
            const testKey = valueCell.dataset.testKey;

            const cacheKey = `${testType}-${parameter}-${species}-${source}`;

            let range;
            if (referenceRangesCache[cacheKey]) {
                range = referenceRangesCache[cacheKey];
            } else {
                try {
                    const response = await fetch(`/api/reference-ranges?test_type=${testType}&parameter=${encodeURIComponent(parameter)}&species=${encodeURIComponent(species)}&source=${source}`);
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

            if (range && range.min_value !== null && range.max_value !== null) {
                const min = parseFloat(range.min_value);
                const max = parseFloat(range.max_value);

                let status = 'normal';
                let displayText = 'OK';

                if (value < min) {
                    status = 'low';
                    const percentage = ((min - value) / min * 100).toFixed(2);
                    displayText = `↓ ${percentage}%`;
                } else if (value > max) {
                    status = 'high';
                    const percentage = ((value - max) / max * 100).toFixed(2);
                    displayText = `↑ ${percentage}%`;
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

// Load evaluations on page load
document.addEventListener('DOMContentLoaded', function() {
    loadEvaluations();
});

// Graph Modal Functions
function openGraphModal() {
    document.getElementById('graphModal').style.display = 'block';
    populateParameterList();
}

function closeGraphModal() {
    document.getElementById('graphModal').style.display = 'none';
}

function populateParameterList() {
    const biochemContainer = document.getElementById('biochemParametersList');
    const hematoContainer = document.getElementById('hematoParametersList');

    biochemContainer.innerHTML = '';
    hematoContainer.innerHTML = '';

    // Get all biochemistry parameters
    const biochemRows = document.querySelectorAll('.examination-history-table tbody tr[data-parameter]');
    const addedBiochem = new Set();

    biochemRows.forEach(row => {
        const param = row.dataset.parameter;
        if (!addedBiochem.has(param)) {
            addedBiochem.add(param);
            const checkbox = createParameterCheckbox(param, 'biochemistry');
            biochemContainer.appendChild(checkbox);
        }
    });

    // Get all hematology parameters from the second table if it exists
    const hematoTables = document.querySelectorAll('.examination-history-table');
    if (hematoTables.length > 1) {
        const hematoRows = hematoTables[1].querySelectorAll('tbody tr[data-parameter]');
        const addedHemato = new Set();

        hematoRows.forEach(row => {
            const param = row.dataset.parameter;
            if (!addedHemato.has(param)) {
                addedHemato.add(param);
                const checkbox = createParameterCheckbox(param, 'hematology');
                hematoContainer.appendChild(checkbox);
            }
        });
    }
}

function createParameterCheckbox(paramName, type) {
    const div = document.createElement('div');
    div.className = 'parameter-checkbox-item';

    const randomColor = getRandomColor();

    div.innerHTML = `
        <label class="parameter-checkbox-label">
            <input type="checkbox"
                   name="graph_params[]"
                   value="${paramName}"
                   data-type="${type}"
                   onchange="toggleColorPicker(this)">
            <span>${paramName}</span>
        </label>
        <input type="color"
               class="color-picker"
               value="${randomColor}"
               data-param="${paramName}"
               style="display: none;">
    `;

    return div;
}

function toggleColorPicker(checkbox) {
    const colorPicker = checkbox.closest('.parameter-checkbox-item').querySelector('.color-picker');
    if (checkbox.checked) {
        colorPicker.style.display = 'inline-block';
    } else {
        colorPicker.style.display = 'none';
    }
}

function getRandomColor() {
    const colors = ['#e74c3c', '#3498db', '#2ecc71', '#f39c12', '#9b59b6', '#1abc9c', '#e67e22', '#34495e'];
    return colors[Math.floor(Math.random() * colors.length)];
}

function generateGraph() {
    const checkboxes = document.querySelectorAll('input[name="graph_params[]"]:checked');
    if (checkboxes.length === 0) {
        alert('Prosím vyberte alespoň jeden parametr');
        return;
    }

    const sampleCount = document.getElementById('sampleCount').value;
    if (!sampleCount || sampleCount < 1) {
        alert('Prosím zadejte počet vzorků');
        return;
    }

    // Collect selected parameters with their colors and types
    const params = [];
    checkboxes.forEach(checkbox => {
        const paramName = checkbox.value;
        const type = checkbox.dataset.type;
        const colorPicker = document.querySelector(`.color-picker[data-param="${paramName}"]`);
        const color = colorPicker ? colorPicker.value : '#e74c3c';

        params.push({
            name: paramName,
            type: type,
            color: color
        });
    });

    // Create form and submit to new page
    const form = document.createElement('form');
    form.method = 'POST';
    form.action = '/biochemistry/animal/<?= $animal['id'] ?>/graph';
    form.target = '_blank';

    const paramsInput = document.createElement('input');
    paramsInput.type = 'hidden';
    paramsInput.name = 'parameters';
    paramsInput.value = JSON.stringify(params);
    form.appendChild(paramsInput);

    const sampleInput = document.createElement('input');
    sampleInput.type = 'hidden';
    sampleInput.name = 'sample_count';
    sampleInput.value = sampleCount;
    form.appendChild(sampleInput);

    document.body.appendChild(form);
    form.submit();
    document.body.removeChild(form);

    closeGraphModal();
}

// Close modal when clicking outside
window.addEventListener('click', function(event) {
    const graphModal = document.getElementById('graphModal');
    const editModal = document.getElementById('editModal');
    if (event.target === graphModal) {
        closeGraphModal();
    }
    if (event.target === editModal) {
        closeEditModal();
    }
});

// Edit Modal Functions
function openEditModal(cell) {
    const parameter = cell.dataset.parameter;
    const value = cell.dataset.value;
    const unit = cell.dataset.unit;
    const resultId = cell.dataset.resultId;
    const testType = cell.dataset.testType;

    if (!resultId || value === null || value === '') {
        alert('Tuto hodnotu nelze upravovat (není uložena v databázi)');
        return;
    }

    document.getElementById('editParameter').value = parameter;
    document.getElementById('editValue').value = value;
    document.getElementById('editUnit').value = unit;
    document.getElementById('editResultId').value = resultId;
    document.getElementById('editTestType').value = testType;

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
    const testType = document.getElementById('editTestType').value;

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
            // Update the cell's data-value attribute
            if (window.currentEditCell) {
                window.currentEditCell.dataset.value = newValue;
                window.currentEditCell.textContent = parseFloat(newValue).toLocaleString('cs-CZ', {
                    minimumFractionDigits: 2,
                    maximumFractionDigits: 2
                });

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
    const value = parseFloat(cell.dataset.value);
    const species = cell.dataset.species;
    const source = cell.dataset.source;
    const testType = cell.dataset.testType;
    const testKey = cell.dataset.testKey;

    if (isNaN(value)) return;

    const cacheKey = `${testType}-${parameter}-${species}-${source}`;

    let range;
    if (referenceRangesCache[cacheKey]) {
        range = referenceRangesCache[cacheKey];
    } else {
        try {
            const response = await fetch(`/api/reference-ranges?test_type=${testType}&parameter=${encodeURIComponent(parameter)}&species=${encodeURIComponent(species)}&source=${source}`);
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

    if (range && range.min_value !== null && range.max_value !== null) {
        const min = parseFloat(range.min_value);
        const max = parseFloat(range.max_value);

        let status = 'normal';
        let displayText = 'OK';

        if (value < min) {
            status = 'low';
            const percentage = ((min - value) / min * 100).toFixed(2);
            displayText = `↓ ${percentage}%`;
        } else if (value > max) {
            status = 'high';
            const percentage = ((value - max) / max * 100).toFixed(2);
            displayText = `↑ ${percentage}%`;
        }

        evalCell.textContent = displayText;
        evalCell.className = `eval-col evaluation ${status}`;
    } else {
        evalCell.textContent = '-';
        evalCell.className = 'eval-col evaluation';
    }
}
</script>
