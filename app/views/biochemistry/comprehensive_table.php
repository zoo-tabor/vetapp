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
            <a href="/biochemistry/animal/<?= $animal['id'] ?>/graph" class="btn btn-success">
                📊 Vytvořit graf
            </a>
            <a href="/biochemistry/animal/<?= $animal['id'] ?>/print?table=biochemistry" class="btn btn-info">
                🖨️ Tisk
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
            <div class="table-area section" data-section-type="biochemistry">
                <h2 class="section-title">Biochemie</h2>
                <div class="table-responsive">
                    <table class="examination-history-table">
                        <thead>
                            <tr>
                                <th class="sticky-col">Referenční meze</th>
                                <th class="sticky-col-2">Parametr</th>
                                <?php foreach ($biochemTests as $test): ?>
                                    <th colspan="2" class="date-header">
                                        <?= date('d.m.Y', strtotime($test['test_date'])) ?>
                                        <?php if (!empty($test['test_location'])): ?>
                                            <br><span class="test-location"><?= htmlspecialchars($test['test_location']) ?></span>
                                        <?php endif; ?>
                                        <?php
                                        // Laboratoř přiřazená k tomuto odběru – řídí vyhodnocení
                                        // celého sloupce; přepnutí platí jen pro zobrazení.
                                        $__testSource = trim((string)($test['reference_source'] ?? ''));
                                        ?>
                                        <br><select class="test-source-select"
                                                    data-test-key="<?= $test['key'] ?>"
                                                    onchange="changeTestSource(this)"
                                                    title="Laboratoř přiřazená k tomuto odběru – změna platí jen pro zobrazení">
                                            <?php if ($__testSource === ''): ?>
                                                <option value="" selected>— nezadáno —</option>
                                            <?php endif; ?>
                                            <?php foreach ($referenceSources as $source): ?>
                                                <option value="<?= htmlspecialchars($source) ?>" <?= $source === $__testSource ? 'selected' : '' ?>><?= htmlspecialchars($source) ?></option>
                                            <?php endforeach; ?>
                                        </select>
                                    </th>
                                <?php endforeach; ?>
                            </tr>
                            <tr>
                                <th class="sticky-col header-select-cell">
                                    <span class="ref-source-note">dle laboratoře odběru</span>
                                </th>
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

                            // Indexy kvality vzorku patří nahoru (samostatná pod-skupina);
                            // zůstávají ale technicky v biochemii kvůli vyhodnocení a ref. mezím.
                            $qualityParams = [];
                            $normalBiochem = [];
                            foreach ($biochemParams as $qpName => $qpInfo) {
                                if (isSampleQualityParam($qpName)) { $qualityParams[$qpName] = $qpInfo; }
                                else { $normalBiochem[$qpName] = $qpInfo; }
                            }
                            $orderedBiochem = [];
                            if (!empty($qualityParams)) {
                                $orderedBiochem['__quality__'] = ['__section__' => 'Kvalita vzorku'];
                                foreach ($qualityParams as $k => $v) { $orderedBiochem[$k] = $v; }
                                $orderedBiochem['__analytes__'] = ['__section__' => 'Analyty'];
                            }
                            foreach ($normalBiochem as $k => $v) { $orderedBiochem[$k] = $v; }

                            foreach ($orderedBiochem as $paramName => $paramInfo):
                                if (isset($paramInfo['__section__'])):
                            ?>
                                <tr class="subsection-row"><td colspan="100" style="background:#eef2f7;font-weight:600;padding:6px 10px;"><?= htmlspecialchars($paramInfo['__section__']) ?></td></tr>
                            <?php
                                    continue;
                                endif;
                            ?>
                                <tr class="result-row" data-parameter="<?= htmlspecialchars($paramName) ?>" data-test-type="biochemistry">
                                    <td class="sticky-col reference-range-cell" data-param="<?= htmlspecialchars($paramName) ?>" data-test-type="biochemistry">
                                        -
                                    </td>
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
                                        // Zdroj se musí načíst i tady – hlavička je samostatný cyklus.
                                        $__testSource = trim((string)($test['reference_source'] ?? ''));
                                        ?>
                                        <td class="value-col editable-cell"
                                            data-test-key="<?= $test['key'] ?>"
                                            data-value="<?= $value ?>"
                                            data-species="<?= htmlspecialchars($animal['species']) ?>"
                                            data-source="<?= htmlspecialchars($__testSource) ?>"
                                            data-test-type="biochemistry"
                                            data-test-id="<?= $test['id'] ?>"
                                            data-result-id="<?= $resultId ?>"
                                            data-parameter="<?= htmlspecialchars($paramName) ?>"
                                            data-unit="<?= htmlspecialchars($unit !== '' ? $unit : ($paramInfo['unit'] ?? '')) ?>"
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
                <?php if (!empty($canEdit)): ?>
                    <div class="add-param-bar">
                        <button type="button" class="btn btn-success btn-add-param" onclick="openAddParamModal('biochemistry')">
                            ➕ Přidat parametr
                        </button>
                    </div>
                <?php endif; ?>
            </div>
        <?php endif; ?>

        <!-- Hematology Section -->
        <?php if (!empty($hematoTests)): ?>
            <div class="table-area section" data-section-type="hematology">
                <h2 class="section-title">Hematologie</h2>
                <div class="table-responsive">
                    <table class="examination-history-table">
                        <thead>
                            <tr>
                                <th class="sticky-col">Referenční meze</th>
                                <th class="sticky-col-2">Parametr</th>
                                <?php foreach ($hematoTests as $test): ?>
                                    <th colspan="2" class="date-header">
                                        <?= date('d.m.Y', strtotime($test['test_date'])) ?>
                                        <?php if (!empty($test['test_location'])): ?>
                                            <br><span class="test-location"><?= htmlspecialchars($test['test_location']) ?></span>
                                        <?php endif; ?>
                                        <?php
                                        // Laboratoř přiřazená k tomuto odběru – řídí vyhodnocení
                                        // celého sloupce; přepnutí platí jen pro zobrazení.
                                        $__testSource = trim((string)($test['reference_source'] ?? ''));
                                        ?>
                                        <br><select class="test-source-select"
                                                    data-test-key="<?= $test['key'] ?>"
                                                    onchange="changeTestSource(this)"
                                                    title="Laboratoř přiřazená k tomuto odběru – změna platí jen pro zobrazení">
                                            <?php if ($__testSource === ''): ?>
                                                <option value="" selected>— nezadáno —</option>
                                            <?php endif; ?>
                                            <?php foreach ($referenceSources as $source): ?>
                                                <option value="<?= htmlspecialchars($source) ?>" <?= $source === $__testSource ? 'selected' : '' ?>><?= htmlspecialchars($source) ?></option>
                                            <?php endforeach; ?>
                                        </select>
                                    </th>
                                <?php endforeach; ?>
                            </tr>
                            <tr>
                                <th class="sticky-col header-select-cell">
                                    <span class="ref-source-note">dle laboratoře odběru</span>
                                </th>
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

                            foreach ($hematoParams as $paramName => $paramInfo):
                            ?>
                                <tr class="result-row" data-parameter="<?= htmlspecialchars($paramName) ?>" data-test-type="hematology">
                                    <td class="sticky-col reference-range-cell" data-param="<?= htmlspecialchars($paramName) ?>" data-test-type="hematology">
                                        -
                                    </td>
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
                                        // Zdroj se musí načíst i tady – hlavička je samostatný cyklus.
                                        $__testSource = trim((string)($test['reference_source'] ?? ''));
                                        ?>
                                        <td class="value-col editable-cell"
                                            data-test-key="<?= $test['key'] ?>"
                                            data-value="<?= $value ?>"
                                            data-species="<?= htmlspecialchars($animal['species']) ?>"
                                            data-source="<?= htmlspecialchars($__testSource) ?>"
                                            data-test-type="hematology"
                                            data-test-id="<?= $test['id'] ?>"
                                            data-result-id="<?= $resultId ?>"
                                            data-parameter="<?= htmlspecialchars($paramName) ?>"
                                            data-unit="<?= htmlspecialchars($unit !== '' ? $unit : ($paramInfo['unit'] ?? '')) ?>"
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
                <?php if (!empty($canEdit)): ?>
                    <div class="add-param-bar">
                        <button type="button" class="btn btn-success btn-add-param" onclick="openAddParamModal('hematology')">
                            ➕ Přidat parametr
                        </button>
                    </div>
                <?php endif; ?>
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
            <h2 id="editModalTitle">Upravit hodnotu</h2>
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

<!-- Add Parameter Modal -->
<div id="addParamModal" class="modal">
    <div class="modal-content" style="max-width: 500px;">
        <div class="modal-header">
            <h2 id="addParamTitle">Přidat parametr</h2>
            <span class="modal-close" onclick="closeAddParamModal()">&times;</span>
        </div>
        <div class="modal-body">
            <form id="addParamForm" onsubmit="saveAddParam(event)">
                <div class="form-group">
                    <label>Odběr (datum): *</label>
                    <select id="addParamTest" class="form-control" required></select>
                </div>
                <div class="form-group">
                    <label>Parametr: *</label>
                    <input type="text" id="addParamName" class="form-control" list="addParamNameList" required
                           placeholder="Vyberte nebo napište název" oninput="prefillAddParamUnit()" autocomplete="off">
                    <datalist id="addParamNameList"></datalist>
                    <small class="text-muted">Můžete vybrat existující parametr nebo zadat nový.</small>
                </div>
                <div class="form-group">
                    <label>Hodnota: *</label>
                    <input type="text" id="addParamValue" class="form-control" required placeholder="např. 5,4">
                </div>
                <div class="form-group">
                    <label>Jednotka:</label>
                    <input type="text" id="addParamUnit" class="form-control" placeholder="např. mmol/l">
                </div>
                <input type="hidden" id="addParamType">
                <div style="margin-top: 20px; display: flex; gap: 10px; justify-content: flex-end;">
                    <button type="submit" class="btn btn-primary">Přidat</button>
                    <button type="button" class="btn btn-outline" onclick="closeAddParamModal()">Zrušit</button>
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
    gap: 10px;
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

.add-param-bar {
    margin-top: 10px;
}

.btn-add-param {
    font-size: 14px;
    padding: 6px 14px;
}

/* Prázdné buňky jde editorům doplnit – ukážeme to jemným zvýrazněním při najetí. */
.editable-cell {
    cursor: pointer;
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

/* Místo odběru v hlavičce sloupce – hlavička má tmavě červené pozadí, takže
   drobná šedá byla nečitelná; čte se stejně jako datum nad ním. */
.test-location {
    font-size: 15px;
    font-weight: bold;
    color: #FFFFFF;
    display: block;
}

.value-header,
.eval-header {
    font-size: 13px;
}

/* Hlavička sloupce s mezemi */
.header-select-cell {
    padding: 6px 8px !important;
}

.ref-source-note {
    font-size: 11px;
    font-weight: 500;
    font-style: italic;
    opacity: 0.85;
}

/* Výběr laboratoře v hlavičce konkrétního odběru (sloupce) */
.test-source-select {
    margin-top: 4px;
    max-width: 100%;
    padding: 2px 4px;
    border: none;
    border-radius: 3px;
    font-size: 11px;
    font-weight: 600;
    background: white;
    color: #2c3e50;
    cursor: pointer;
}

.test-source-select:focus {
    outline: 2px solid #e6b0aa;
}

/* Meze se mezi laboratořemi liší -> vypisují se po zdrojích */
.ref-line {
    font-size: 11px;
    line-height: 1.35;
    white-space: nowrap;
}

.ref-line-source {
    font-weight: 600;
    color: #7f8c8d;
}

.reference-range-cell {
    text-align: center;
    font-size: 13px;
    color: #7f8c8d;
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

/* Vyhodnocení (%): černý text, světlé barevné pozadí.
   !important kvůli zebra pruhování tabulky (tr:nth-child(2n) td). */
.evaluation.low {
    background: #dbe9ff !important;
    color: #000;
}

.evaluation.high,
.evaluation.mimo {
    background: #ffd6da !important;
    color: #000;
}

/* Sloupec s hodnotou: průhledné pozadí, barevný text. */
.value-col.val-low {
    color: #2563eb;
    font-weight: bold;
}

.value-col.val-high {
    color: #c0392b;
    font-weight: bold;
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

.btn-info {
    background: linear-gradient(135deg, #3498db 0%, #2980b9 100%);
    color: white;
}

.btn-info:hover {
    background: linear-gradient(135deg, #2980b9 0%, #1f6dad 100%);
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
// Meze pro všechny parametry a všechny nabízené laboratoře posílá rovnou server
// (dřív se tahaly po jedné přes /api/reference-ranges – desítky sériových requestů).
// Tvar: referenceRanges[typ testu][parametr][laboratoř] = {min_value, max_value, unit}
const referenceRanges = <?= json_encode($referenceRanges ?? [], JSON_UNESCAPED_UNICODE | JSON_HEX_TAG) ?>;

// Oprávnění k editaci (přidávání/změny se ukládají do DB) + podklady pro "Přidat parametr".
const canEdit = <?= !empty($canEdit) ? 'true' : 'false' ?>;
const addParamTests = <?= json_encode([
    'biochemistry' => array_map(function ($t) {
        return ['id' => (int)$t['id'], 'label' => date('d.m.Y', strtotime($t['test_date'])) . (!empty($t['test_location']) ? ' – ' . $t['test_location'] : '')];
    }, $biochemTests ?? []),
    'hematology' => array_map(function ($t) {
        return ['id' => (int)$t['id'], 'label' => date('d.m.Y', strtotime($t['test_date'])) . (!empty($t['test_location']) ? ' – ' . $t['test_location'] : '')];
    }, $hematoTests ?? []),
], JSON_UNESCAPED_UNICODE | JSON_HEX_TAG) ?>;
const addParamCatalog = <?= json_encode([
    'biochemistry' => array_map(function ($p) { return ['name' => $p['name'], 'unit' => $p['unit'] ?? '']; }, $biochemParamList ?? []),
    'hematology' => array_map(function ($p) { return ['name' => $p['name'], 'unit' => $p['unit'] ?? '']; }, $hematoParamList ?? []),
], JSON_UNESCAPED_UNICODE | JSON_HEX_TAG) ?>;

function getReferenceRange(testType, parameter, source) {
    if (!source) return null;
    const byParam = referenceRanges[testType];
    if (!byParam) return null;
    const bySource = byParam[parameter];
    if (!bySource) return null;
    return bySource[source] || null;
}

function formatRangeText(range) {
    if (!range) return null;
    const hasMin = range.min_value !== null && range.min_value !== '';
    const hasMax = range.max_value !== null && range.max_value !== '';
    if (hasMin && hasMax) return `${range.min_value} - ${range.max_value}`;
    if (hasMin) return `> ${range.min_value}`;
    if (hasMax) return `< ${range.max_value}`;
    return null;
}

function evaluateAgainstRange(value, range) {
    const hasMin = range && range.min_value !== null && range.min_value !== '';
    const hasMax = range && range.max_value !== null && range.max_value !== '';
    if (!hasMin && !hasMax) {
        return { status: '', text: '-' };
    }

    const min = parseFloat(range.min_value);
    const max = parseFloat(range.max_value);

    if (hasMin && value < min) {
        // Nulová mez by znamenala dělení nulou -> "MIMO MEZ".
        return min !== 0
            ? { status: 'low', text: `↓ ${((min - value) / Math.abs(min) * 100).toFixed(2)}%` }
            : { status: 'high', text: 'MIMO MEZ' };
    }
    if (hasMax && value > max) {
        return max !== 0
            ? { status: 'high', text: `↑ ${((value - max) / Math.abs(max) * 100).toFixed(2)}%` }
            : { status: 'high', text: 'MIMO MEZ' };
    }
    return { status: 'normal', text: 'OK' };
}

// Laboratoře použité ve sloupcích dané sekce (bez duplicit, v pořadí sloupců).
function sectionSources(section) {
    const sources = [];
    section.querySelectorAll('.value-col[data-source]').forEach(cell => {
        const source = cell.dataset.source;
        if (source && !sources.includes(source)) sources.push(source);
    });
    return sources;
}

// Sloupec s mezemi: při jedné laboratoři jeden řádek, při více laboratořích
// vypíšeme meze po zdrojích (nešlo by je jinak poctivě sloučit do jednoho čísla).
function refreshReferenceColumn(section) {
    const testType = section.dataset.sectionType;
    const sources = sectionSources(section);

    // Popisek sloupce: při jedné laboratoři rovnou její název.
    const note = section.querySelector('.ref-source-note');
    if (note) {
        note.textContent = sources.length === 1 ? sources[0] : 'dle laboratoře odběru';
    }

    for (const cell of section.querySelectorAll('.reference-range-cell')) {
        const parameter = cell.dataset.param;

        const parts = sources.map(source => ({
            source: source,
            text: formatRangeText(getReferenceRange(testType, parameter, source)) || '-'
        }));

        cell.textContent = '';
        if (parts.length === 0) {
            cell.textContent = '-';
            continue;
        }

        const distinct = new Set(parts.map(p => p.text));
        if (distinct.size === 1) {
            cell.textContent = parts[0].text;
            continue;
        }

        parts.forEach(part => {
            const line = document.createElement('div');
            line.className = 'ref-line';
            const label = document.createElement('span');
            label.className = 'ref-line-source';
            label.textContent = part.source + ':';
            line.appendChild(label);
            line.appendChild(document.createTextNode(' ' + part.text));
            cell.appendChild(line);
        });
    }
}

function renderCellEvaluation(valueCell) {
    const row = valueCell.closest('tr');
    const evalCell = row?.querySelector(`.evaluation[data-for="${valueCell.dataset.testKey}"]`);
    if (!evalCell) return;

    const value = parseFloat(valueCell.dataset.value);
    if (isNaN(value)) {
        evalCell.textContent = '-';
        evalCell.className = 'eval-col evaluation';
        applyValueColor(evalCell, null);
        return;
    }

    const range = getReferenceRange(
        valueCell.dataset.testType,
        valueCell.dataset.parameter,
        valueCell.dataset.source
    );
    const result = evaluateAgainstRange(value, range);

    evalCell.textContent = result.text;
    evalCell.className = ('eval-col evaluation ' + result.status).trim() + (result.text === 'MIMO MEZ' ? ' mimo' : '');
    applyValueColor(evalCell, result.status || null);
}

function refreshEvaluations(scope) {
    (scope || document).querySelectorAll('.value-col[data-value]').forEach(renderCellEvaluation);
}

// Ruční přepnutí laboratoře u jednoho odběru (sloupce) – platí jen pro zobrazení,
// v databázi zůstává zdroj přiřazený při zadávání.
function changeTestSource(select) {
    const section = select.closest('.section');
    if (!section) return;

    const testKey = select.dataset.testKey;
    section.querySelectorAll(`.value-col[data-test-key="${testKey}"]`).forEach(cell => {
        cell.dataset.source = select.value;
    });

    refreshReferenceColumn(section);
    refreshEvaluations(section);

    // Editoři: uložit laboratoř natrvalo do DB (dřív jen dočasná změna náhledu).
    if (canEdit) {
        persistTestSource(select.dataset.testKey, select.value);
    }
}

// Uloží zvolenou laboratoř k odběru. testKey má tvar "biochem_<id>" / "hemato_<id>".
async function persistTestSource(testKey, source) {
    const sep = testKey.indexOf('_');
    const prefix = testKey.slice(0, sep);
    const testId = testKey.slice(sep + 1);
    const testType = prefix === 'hemato' ? 'hematology' : 'biochemistry';
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

// --- Přidat parametr (nový řádek / hodnota k libovolnému odběru) ---
function openAddParamModal(testType) {
    if (!canEdit) return;

    const tests = addParamTests[testType] || [];
    const testSelect = document.getElementById('addParamTest');
    testSelect.innerHTML = '';
    tests.forEach(t => {
        const opt = document.createElement('option');
        opt.value = t.id;
        opt.textContent = t.label;
        testSelect.appendChild(opt);
    });

    const dl = document.getElementById('addParamNameList');
    dl.innerHTML = '';
    (addParamCatalog[testType] || []).forEach(p => {
        const opt = document.createElement('option');
        opt.value = p.name;
        dl.appendChild(opt);
    });

    document.getElementById('addParamType').value = testType;
    document.getElementById('addParamName').value = '';
    document.getElementById('addParamValue').value = '';
    document.getElementById('addParamUnit').value = '';
    document.getElementById('addParamTitle').textContent =
        testType === 'hematology' ? 'Přidat parametr – hematologie' : 'Přidat parametr – biochemie';

    document.getElementById('addParamModal').style.display = 'block';
    setTimeout(() => document.getElementById('addParamName').focus(), 50);
}

function closeAddParamModal() {
    document.getElementById('addParamModal').style.display = 'none';
    document.getElementById('addParamForm').reset();
}

// Předvyplní jednotku dle číselníku, když uživatel zadá známý parametr.
function prefillAddParamUnit() {
    const testType = document.getElementById('addParamType').value;
    const name = document.getElementById('addParamName').value.trim().toLowerCase();
    const unitField = document.getElementById('addParamUnit');
    if (!name || unitField.value) return;
    const match = (addParamCatalog[testType] || []).find(p => p.name.toLowerCase() === name);
    if (match) unitField.value = match.unit || '';
}

async function saveAddParam(event) {
    event.preventDefault();

    const testType = document.getElementById('addParamType').value;
    const payload = {
        test_type: testType,
        test_id: document.getElementById('addParamTest').value,
        parameter_name: document.getElementById('addParamName').value.trim(),
        value: document.getElementById('addParamValue').value.trim(),
        unit: document.getElementById('addParamUnit').value.trim()
    };

    if (!payload.test_id || !payload.parameter_name || !payload.value) {
        alert('Vyplňte odběr, parametr i hodnotu.');
        return;
    }

    try {
        const response = await fetch('/biochemistry/result/add', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(payload)
        });
        const data = await response.json();
        if (response.ok && data.success) {
            // Nový řádek/hodnota => tabulku překreslíme načtením stránky.
            window.location.reload();
        } else {
            alert('Chyba při přidávání: ' + (data.error || 'Neznámá chyba'));
        }
    } catch (e) {
        console.error('saveAddParam error', e);
        alert('Chyba při přidávání parametru');
    }
}

// Po úpravě hodnoty stačí přepočítat jednu buňku (meze se nemění).
function updateSingleCellEvaluation(cell) {
    renderCellEvaluation(cell);
}

document.addEventListener('DOMContentLoaded', function() {
    document.querySelectorAll('.section[data-section-type]').forEach(section => {
        refreshReferenceColumn(section);
        refreshEvaluations(section);
    });
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
    const addParamModal = document.getElementById('addParamModal');
    if (event.target === graphModal) {
        closeGraphModal();
    }
    if (event.target === editModal) {
        closeEditModal();
    }
    if (event.target === addParamModal) {
        closeAddParamModal();
    }
});

// Edit Modal Functions
function openEditModal(cell) {
    const parameter = cell.dataset.parameter;
    const value = cell.dataset.value;
    const unit = cell.dataset.unit;
    const resultId = cell.dataset.resultId;
    const testType = cell.dataset.testType;

    const hasValue = !!resultId && value !== '' && value !== null;

    // Prázdná buňka: editoři mohou hodnotu doplnit (uloží se jako nový výsledek).
    if (!hasValue && !canEdit) {
        alert('Tuto hodnotu nelze upravovat (není uložena v databázi)');
        return;
    }

    const titleEl = document.getElementById('editModalTitle');
    if (titleEl) titleEl.textContent = hasValue ? 'Upravit hodnotu' : 'Přidat hodnotu';

    document.getElementById('editParameter').value = parameter;
    document.getElementById('editValue').value = hasValue ? value : '';
    document.getElementById('editUnit').value = unit;
    document.getElementById('editResultId').value = hasValue ? resultId : '';
    document.getElementById('editTestType').value = testType;

    // Store reference to the cell element
    window.currentEditCell = cell;

    document.getElementById('editModal').style.display = 'block';
    setTimeout(() => { const v = document.getElementById('editValue'); if (v) v.focus(); }, 50);
}

function closeEditModal() {
    document.getElementById('editModal').style.display = 'none';
    document.getElementById('editForm').reset();
    window.currentEditCell = null;
}

// Zobrazení hodnoty v buňce: číslo hezky zformátujeme, jinak necháme text.
function applyCellValueText(cell, value) {
    const num = parseFloat(String(value).replace(',', '.'));
    if (String(value).trim() !== '' && !isNaN(num) && /^-?[\d.,]+$/.test(String(value).trim())) {
        cell.textContent = num.toLocaleString('cs-CZ', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
    } else {
        cell.textContent = value;
    }
}

// Doplnění hodnoty do prázdné buňky = nový výsledek (INSERT) přes /result/add.
async function saveNewValueForCell(newValue, testType) {
    const cell = window.currentEditCell;
    if (!cell) return;

    try {
        const response = await fetch('/biochemistry/result/add', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({
                test_type: testType,
                test_id: cell.dataset.testId,
                parameter_name: cell.dataset.parameter,
                unit: cell.dataset.unit || '',
                value: newValue
            })
        });
        const data = await response.json();
        if (response.ok && data.success) {
            cell.dataset.resultId = data.result_id;
            cell.dataset.value = data.value;
            if (data.unit) cell.dataset.unit = data.unit;
            applyCellValueText(cell, data.value);
            updateSingleCellEvaluation(cell);
            closeEditModal();
        } else {
            alert('Chyba při ukládání: ' + (data.error || 'Neznámá chyba'));
        }
    } catch (error) {
        console.error('Error:', error);
        alert('Chyba při ukládání hodnoty');
    }
}

async function saveEdit(event) {
    event.preventDefault();

    const resultId = document.getElementById('editResultId').value;
    const newValue = document.getElementById('editValue').value;
    const testType = document.getElementById('editTestType').value;

    // Bez resultId jde o doplnění nové hodnoty do prázdné buňky.
    if (!resultId) {
        return saveNewValueForCell(newValue, testType);
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
            // Update the cell's data-value attribute
            if (window.currentEditCell) {
                window.currentEditCell.dataset.value = newValue;
                window.currentEditCell.textContent = parseFloat(newValue).toLocaleString('cs-CZ', {
                    minimumFractionDigits: 2,
                    maximumFractionDigits: 2
                });

                // Refresh the evaluation for this cell
                updateSingleCellEvaluation(window.currentEditCell);
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

// Obarví sousední buňku s hodnotou podle vyhodnocení (červeně/modře text, průhledné pozadí).
function applyValueColor(evalCell, status) {
    const row = evalCell.closest('tr');
    if (!row) return;
    const vCell = row.querySelector('.value-col[data-test-key="' + evalCell.dataset.for + '"]');
    if (!vCell) return;
    vCell.classList.remove('val-low', 'val-high');
    if (status === 'low') vCell.classList.add('val-low');
    else if (status === 'high') vCell.classList.add('val-high');
}
</script>
