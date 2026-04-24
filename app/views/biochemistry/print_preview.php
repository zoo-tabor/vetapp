<!-- Print Settings Sidebar -->
<div class="print-settings-sidebar">
    <h3>Nastavení tisku</h3>

    <div class="setting-group">
        <label>Tabulka:</label>
        <select id="tableSelect" onchange="updatePreview()">
            <option value="biochemistry" <?= $tableType === 'biochemistry' ? 'selected' : '' ?>>Biochemie</option>
            <option value="hematology" <?= $tableType === 'hematology' ? 'selected' : '' ?>>Hematologie</option>
            <option value="both" <?= $tableType === 'both' ? 'selected' : '' ?>>Obě tabulky</option>
        </select>
    </div>

    <div class="setting-group">
        <label>Referenční zdroj:</label>
        <select id="sourceSelect" onchange="updatePreview()">
            <option value="Laboklin" <?= $referenceSource === 'Laboklin' ? 'selected' : '' ?>>Laboklin</option>
            <option value="Idexx" <?= $referenceSource === 'Idexx' ? 'selected' : '' ?>>Idexx</option>
            <option value="Synlab" <?= $referenceSource === 'Synlab' ? 'selected' : '' ?>>Synlab</option>
            <option value="ZIMS" <?= $referenceSource === 'ZIMS' ? 'selected' : '' ?>>ZIMS</option>
        </select>
    </div>

    <div class="setting-group">
        <label>Velikost písma:</label>
        <select id="fontSizeSelect" onchange="updateFontSize()">
            <option value="7">7px - Extra malé</option>
            <option value="8" selected>8px - Velmi malé</option>
            <option value="9">9px - Malé</option>
            <option value="10">10px - Střední</option>
        </select>
    </div>

    <div class="button-group">
        <button onclick="window.print()" class="btn-print">
            🖨️ Tisknout
        </button>
        <a href="/biochemistry/animal/<?= $animal['id'] ?>/comprehensive-table" class="btn-back">
            ← Zpět
        </a>
    </div>
</div>

<!-- Print Preview Area -->
<div class="print-preview-area">
    <div class="print-page" id="printPage">
        <?php
        // Merge all tests for unified table
        $allTests = [];
        if ($tableType === 'biochemistry' || $tableType === 'both') {
            foreach ($biochemTests as $test) {
                $allTests[$test['test_date']] = $test;
            }
        }
        if ($tableType === 'hematology' || $tableType === 'both') {
            foreach ($hematoTests as $test) {
                if (!isset($allTests[$test['test_date']])) {
                    $allTests[$test['test_date']] = $test;
                }
            }
        }
        ksort($allTests);
        $allTests = array_values($allTests);
        ?>

        <table class="print-table">
            <!-- Header -->
            <thead>
                <tr class="main-header">
                    <th class="title-cell" colspan="3">BIOCHEMIE A HEMATOLOGIE</th>
                    <th class="animal-name" colspan="<?= max(1, count($allTests) * 2) ?>"><?= strtoupper(htmlspecialchars($animal['name'])) ?></th>
                </tr>
                <tr class="column-header">
                    <th class="param-col"></th>
                    <th class="ref-col">Referenční meze<br><small>(<?= htmlspecialchars($referenceSource) ?>)</small></th>
                    <th class="unit-col">Jednotky</th>
                    <?php foreach ($allTests as $colIdx => $test): ?>
                        <th class="date-col">
                            <?= date('d.m.Y', strtotime($test['test_date'])) ?>
                            <?php if (!empty($test['test_location'])): ?>
                                <br><small><?= htmlspecialchars($test['test_location']) ?></small>
                            <?php endif; ?>
                        </th>
                        <th class="eval-col alt-col">vs. referenční<br>meze</th>
                    <?php endforeach; ?>
                </tr>
            </thead>
            <tbody>
                <?php if ($tableType === 'biochemistry' || $tableType === 'both'): ?>
                    <!-- Biochemistry Section Header -->
                    <tr class="section-header">
                        <td colspan="<?= 3 + count($allTests) * 2 ?>"><strong>Biochemie</strong></td>
                    </tr>
                    <?php
                    $biochemParams = array_filter($allParameters, function($param) {
                        return $param['type'] === 'biochemistry';
                    });

                    foreach ($biochemParams as $paramName => $paramInfo):
                        $refRange = $referenceRanges['biochemistry'][$paramName] ?? null;
                        $refText = '';
                        if ($refRange && $refRange['min_value'] !== null && $refRange['max_value'] !== null) {
                            $refText = $refRange['min_value'] . ' - ' . $refRange['max_value'];
                        } elseif ($refRange && $refRange['min_value'] !== null) {
                            $refText = '> ' . $refRange['min_value'];
                        } elseif ($refRange && $refRange['max_value'] !== null) {
                            $refText = '< ' . $refRange['max_value'];
                        }
                    ?>
                        <tr>
                            <td class="param-cell"><?= htmlspecialchars($paramName) ?></td>
                            <td class="ref-cell"><?= $refText ?></td>
                            <td class="unit-cell"><?= htmlspecialchars($paramInfo['unit']) ?></td>
                            <?php foreach ($allTests as $colIdx => $test):
                                // Find the biochemistry test for this date
                                $biochemTest = null;
                                foreach ($biochemTests as $bt) {
                                    if ($bt['test_date'] === $test['test_date']) {
                                        $biochemTest = $bt;
                                        break;
                                    }
                                }

                                $result = $biochemTest ? ($testResults[$biochemTest['key']][$paramName] ?? null) : null;
                                $value = $result['value'] ?? null;

                                // Calculate evaluation
                                $evalText = '';
                                $evalClass = '';
                                $valueClass = '';
                                if ($value !== null && is_numeric($value) && $refRange) {
                                    $numValue = floatval($value);
                                    $min = $refRange['min_value'] !== null ? floatval($refRange['min_value']) : null;
                                    $max = $refRange['max_value'] !== null ? floatval($refRange['max_value']) : null;

                                    if ($min !== null && $max !== null) {
                                        if ($numValue < $min) {
                                            $percentage = (($min - $numValue) / $min * 100);
                                            $evalText = '↓ ' . number_format($percentage, 2, ',', '') . '%';
                                            $evalClass = 'deviation';
                                            $valueClass = 'deviation';
                                        } elseif ($numValue > $max) {
                                            $percentage = (($numValue - $max) / $max * 100);
                                            $evalText = '↑ ' . number_format($percentage, 2, ',', '') . '%';
                                            $evalClass = 'deviation';
                                            $valueClass = 'deviation';
                                        } else {
                                            $evalText = 'OK';
                                            $evalClass = 'ok';
                                        }
                                    } elseif ($min !== null && $numValue < $min) {
                                        $evalText = '↓';
                                        $evalClass = 'deviation';
                                        $valueClass = 'deviation';
                                    } elseif ($max !== null && $numValue > $max) {
                                        $evalText = '↑';
                                        $evalClass = 'deviation';
                                        $valueClass = 'deviation';
                                    } else {
                                        $evalText = 'OK';
                                        $evalClass = 'ok';
                                    }
                                }

                                $displayValue = '';
                                if ($value !== null) {
                                    if (is_numeric($value)) {
                                        $displayValue = number_format($value, 2, ',', ' ');
                                    } else {
                                        $displayValue = htmlspecialchars($value);
                                    }
                                }
                            ?>
                                <td class="value-cell <?= $valueClass ?>"><?= $displayValue ?></td>
                                <td class="eval-cell alt-col <?= $evalClass ?>"><?= $evalText ?></td>
                            <?php endforeach; ?>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>

                <?php if ($tableType === 'hematology' || $tableType === 'both'): ?>
                    <!-- Hematology Section Header -->
                    <tr class="section-header">
                        <td colspan="<?= 3 + count($allTests) * 2 ?>"><strong>Hematologie</strong></td>
                    </tr>
                    <?php
                    $hematoParams = array_filter($allParameters, function($param) {
                        return $param['type'] === 'hematology';
                    });

                    foreach ($hematoParams as $paramName => $paramInfo):
                        $refRange = $referenceRanges['hematology'][$paramName] ?? null;
                        $refText = '';
                        if ($refRange && $refRange['min_value'] !== null && $refRange['max_value'] !== null) {
                            $refText = $refRange['min_value'] . ' - ' . $refRange['max_value'];
                        } elseif ($refRange && $refRange['min_value'] !== null) {
                            $refText = '> ' . $refRange['min_value'];
                        } elseif ($refRange && $refRange['max_value'] !== null) {
                            $refText = '< ' . $refRange['max_value'];
                        }
                    ?>
                        <tr>
                            <td class="param-cell"><?= htmlspecialchars($paramName) ?></td>
                            <td class="ref-cell"><?= $refText ?></td>
                            <td class="unit-cell"><?= htmlspecialchars($paramInfo['unit']) ?></td>
                            <?php foreach ($allTests as $colIdx => $test):
                                // Find the hematology test for this date
                                $hematoTest = null;
                                foreach ($hematoTests as $ht) {
                                    if ($ht['test_date'] === $test['test_date']) {
                                        $hematoTest = $ht;
                                        break;
                                    }
                                }

                                $result = $hematoTest ? ($testResults[$hematoTest['key']][$paramName] ?? null) : null;
                                $value = $result['value'] ?? null;

                                // Calculate evaluation
                                $evalText = '';
                                $evalClass = '';
                                $valueClass = '';
                                if ($value !== null && is_numeric($value) && $refRange) {
                                    $numValue = floatval($value);
                                    $min = $refRange['min_value'] !== null ? floatval($refRange['min_value']) : null;
                                    $max = $refRange['max_value'] !== null ? floatval($refRange['max_value']) : null;

                                    if ($min !== null && $max !== null) {
                                        if ($numValue < $min) {
                                            $percentage = (($min - $numValue) / $min * 100);
                                            $evalText = '↓ ' . number_format($percentage, 2, ',', '') . '%';
                                            $evalClass = 'deviation';
                                            $valueClass = 'deviation';
                                        } elseif ($numValue > $max) {
                                            $percentage = (($numValue - $max) / $max * 100);
                                            $evalText = '↑ ' . number_format($percentage, 2, ',', '') . '%';
                                            $evalClass = 'deviation';
                                            $valueClass = 'deviation';
                                        } else {
                                            $evalText = 'OK';
                                            $evalClass = 'ok';
                                        }
                                    } elseif ($min !== null && $numValue < $min) {
                                        $evalText = '↓';
                                        $evalClass = 'deviation';
                                        $valueClass = 'deviation';
                                    } elseif ($max !== null && $numValue > $max) {
                                        $evalText = '↑';
                                        $evalClass = 'deviation';
                                        $valueClass = 'deviation';
                                    } else {
                                        $evalText = 'OK';
                                        $evalClass = 'ok';
                                    }
                                }

                                $displayValue = '';
                                if ($value !== null) {
                                    if (is_numeric($value)) {
                                        $displayValue = number_format($value, 2, ',', ' ');
                                    } else {
                                        $displayValue = htmlspecialchars($value);
                                    }
                                }
                            ?>
                                <td class="value-cell <?= $valueClass ?>"><?= $displayValue ?></td>
                                <td class="eval-cell alt-col <?= $evalClass ?>"><?= $evalText ?></td>
                            <?php endforeach; ?>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<style>
/* Main layout */
body {
    display: flex;
    min-height: 100vh;
    margin: 0;
    font-family: Arial, sans-serif;
}

/* Settings sidebar */
.print-settings-sidebar {
    width: 220px;
    background: #2c3e50;
    color: white;
    padding: 20px;
    position: fixed;
    left: 0;
    top: 0;
    bottom: 0;
    overflow-y: auto;
    z-index: 100;
}

.print-settings-sidebar h3 {
    margin: 0 0 20px 0;
    font-size: 16px;
    padding-bottom: 10px;
    border-bottom: 2px solid #92d050;
}

.setting-group {
    margin-bottom: 15px;
}

.setting-group label {
    display: block;
    margin-bottom: 5px;
    font-size: 12px;
    color: #bdc3c7;
}

.setting-group select {
    width: 100%;
    padding: 8px 10px;
    border: none;
    border-radius: 4px;
    font-size: 12px;
    background: #34495e;
    color: white;
}

.button-group {
    margin-top: 30px;
    display: flex;
    flex-direction: column;
    gap: 10px;
}

.btn-print {
    background: linear-gradient(135deg, #27ae60 0%, #229954 100%);
    color: white;
    border: none;
    padding: 12px 20px;
    border-radius: 4px;
    font-size: 14px;
    font-weight: 600;
    cursor: pointer;
}

.btn-print:hover {
    background: linear-gradient(135deg, #229954 0%, #1e8449 100%);
}

.btn-back {
    background: #34495e;
    color: white;
    border: none;
    padding: 10px 20px;
    border-radius: 4px;
    font-size: 12px;
    cursor: pointer;
    text-decoration: none;
    text-align: center;
}

/* Preview area */
.print-preview-area {
    flex: 1;
    margin-left: 220px;
    padding: 20px;
    background: #ecf0f1;
    display: flex;
    justify-content: center;
    overflow: auto;
}

.print-page {
    background: white;
    box-shadow: 0 2px 10px rgba(0,0,0,0.1);
    padding: 5mm;
    width: fit-content;
    min-width: 297mm;
    transform-origin: top center;
}

/* Print table - spreadsheet style */
.print-table {
    border-collapse: collapse;
    font-size: 8px;
    width: 100%;
}

.print-table th,
.print-table td {
    border: 1px solid #000;
    padding: 2px 4px;
    white-space: nowrap;
}

/* Main header row */
.main-header th {
    background: #92d050;
    font-weight: bold;
    font-size: 10px;
    padding: 4px 8px;
    text-align: left;
}

.main-header .title-cell {
    font-weight: bold;
}

.main-header .animal-name {
    font-weight: bold;
    text-align: left;
}

/* Column header row */
.column-header th {
    background: #c6efce;
    font-weight: bold;
    font-size: 7px;
    text-align: center;
    padding: 3px 4px;
    vertical-align: bottom;
}

.column-header th small {
    font-weight: normal;
    font-size: 6px;
}

.column-header .param-col {
    min-width: 100px;
    text-align: left;
}

.column-header .ref-col {
    min-width: 70px;
}

.column-header .unit-col {
    min-width: 50px;
}

.column-header .date-col {
    min-width: 55px;
}

.column-header .eval-col {
    min-width: 55px;
    font-size: 6px;
}

/* Section header */
.section-header td {
    background: #c6efce;
    font-weight: bold;
    font-size: 9px;
    padding: 3px 4px;
}

/* Data cells */
.param-cell {
    font-weight: normal;
    text-align: left;
    font-size: 8px;
}

.ref-cell {
    text-align: right;
    font-size: 7px;
    color: #333;
}

.unit-cell {
    text-align: center;
    font-size: 7px;
    color: #666;
}

.value-cell {
    text-align: right;
    font-size: 8px;
}

.eval-cell {
    text-align: right;
    font-size: 7px;
}

/* Evaluation colors with background */
.eval-cell.ok {
    color: #000;
}

/* All deviations are red - matching Google Sheets style */
.eval-cell.deviation,
.value-cell.deviation {
    background-color: #ffc7ce !important;
    color: #9c0006;
}

/* Alternate column colors for better readability */
.alt-col {
    background-color: #f5f5f5;
}

.alt-col.deviation {
    background-color: #ffc7ce !important;
    color: #9c0006;
}

/* Print styles */
@media print {
    body {
        background: white !important;
    }

    .print-settings-sidebar {
        display: none !important;
    }

    .print-preview-area {
        margin-left: 0 !important;
        padding: 0 !important;
        background: white !important;
    }

    .print-page {
        box-shadow: none !important;
        padding: 2mm !important;
        width: 100% !important;
        min-width: auto !important;
    }

    .print-table th,
    .print-table td {
        border: 1px solid #000 !important;
    }

    .main-header th {
        background: #92d050 !important;
        -webkit-print-color-adjust: exact !important;
        print-color-adjust: exact !important;
    }

    .eval-cell.deviation,
    .value-cell.deviation {
        background-color: #ffc7ce !important;
        color: #9c0006 !important;
        -webkit-print-color-adjust: exact !important;
        print-color-adjust: exact !important;
    }

    .section-header td {
        background: #c6efce !important;
        -webkit-print-color-adjust: exact !important;
        print-color-adjust: exact !important;
    }

    .column-header th {
        background: #c6efce !important;
        -webkit-print-color-adjust: exact !important;
        print-color-adjust: exact !important;
    }

    .alt-col {
        background-color: #f5f5f5 !important;
        -webkit-print-color-adjust: exact !important;
        print-color-adjust: exact !important;
    }

    .alt-col.deviation {
        background-color: #ffc7ce !important;
        color: #9c0006 !important;
        -webkit-print-color-adjust: exact !important;
        print-color-adjust: exact !important;
    }

    @page {
        size: landscape;
        margin: 5mm;
    }
}
</style>

<script>
const animalId = <?= $animal['id'] ?>;

function updatePreview() {
    const table = document.getElementById('tableSelect').value;
    const source = document.getElementById('sourceSelect').value;
    window.location.href = `/biochemistry/animal/${animalId}/print?table=${table}&source=${source}`;
}

function updateFontSize() {
    const fontSize = document.getElementById('fontSizeSelect').value;
    document.querySelector('.print-table').style.fontSize = fontSize + 'px';
}
</script>
