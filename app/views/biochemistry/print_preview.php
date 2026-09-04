<?php $perPage = isset($_GET['per_page']) ? max(1, min(40, (int)$_GET['per_page'])) : 10; ?>
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

    <div class="setting-group">
        <label>Odběrů na stránku:</label>
        <select id="perPageSelect" onchange="updatePreview()">
            <?php foreach ([5, 8, 10, 12, 15, 20] as $pp): ?>
                <option value="<?= $pp ?>" <?= $perPage === $pp ? 'selected' : '' ?>><?= $pp ?></option>
            <?php endforeach; ?>
        </select>
        <small style="display:block;margin-top:6px;opacity:.7;">Datumy se rozdělí na víc stránek.</small>
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
    <?php
    // Sloučit odběry do jedné časové osy (unikátní datumy).
    $allTestsFull = [];
    // Sloupec = datum + laboratoř. Dva odběry stejného data z jiné laboratoře
    // (např. Laboklin + RegiaVet) tak zůstanou jako dva samostatné sloupce.
    if ($tableType === 'biochemistry' || $tableType === 'both') {
        foreach ($biochemTests as $test) {
            $__k = $test['test_date'] . '|' . ($test['test_location'] ?? '');
            $allTestsFull[$__k] = $test;
        }
    }
    if ($tableType === 'hematology' || $tableType === 'both') {
        foreach ($hematoTests as $test) {
            $__k = $test['test_date'] . '|' . ($test['test_location'] ?? '');
            if (!isset($allTestsFull[$__k])) {
                $allTestsFull[$__k] = $test;
            }
        }
    }
    ksort($allTestsFull);
    $allTestsFull = array_values($allTestsFull);

    // Datumové sloupce rozdělíme po blocích na samostatné tiskové stránky, aby se
    // nic neslučovalo ani neusekávalo. Levé sloupce (parametr/meze/jednotky) se
    // opakují na každé stránce.
    $perPage = isset($_GET['per_page']) ? max(1, min(40, (int)$_GET['per_page'])) : 10;
    $blocks = array_chunk($allTestsFull, $perPage);
    if (empty($blocks)) { $blocks = [[]]; }

    foreach ($blocks as $__blockIdx => $allTests):
    ?>
    <div class="print-page">
        <div class="print-animal-title" contenteditable="true" spellcheck="false" title="Klikněte a upravte (jméno + ID)"><?= htmlspecialchars(trim(strtoupper($animal['name'] ?? '') . (!empty($animal['identifier']) ? ' (' . $animal['identifier'] . ')' : ''))) ?><?php if (count($blocks) > 1): ?> <span class="print-part">— část <?= $__blockIdx + 1 ?>/<?= count($blocks) ?></span><?php endif; ?></div>

        <table class="print-table">
            <?php
            // Poměrné šířky sloupců, aby se tabulka VŽDY vešla na šířku stránky
            // (table-layout: fixed). Levé sloupce pevně, datumy si dělí zbytek.
            $__nd = max(1, count($allTests));
            $__pair = (100 - 12 - 8 - 5) / $__nd; // zbytek na jeden odběr (hodnota+vyhodnocení)
            $__wVal = round($__pair * 0.56, 3);
            $__wEval = round($__pair - $__wVal, 3);
            ?>
            <colgroup>
                <col style="width:12%">
                <col style="width:8%">
                <col style="width:5%">
                <?php foreach ($allTests as $__c): ?>
                    <col style="width:<?= $__wVal ?>%"><col style="width:<?= $__wEval ?>%">
                <?php endforeach; ?>
            </colgroup>
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
                    <?php
                    $biochemParams = array_filter($allParameters, function($param) {
                        return $param['type'] === 'biochemistry';
                    });

                    // Indexy kvality vzorku (lipemický + hemolytický) patří vždy nahoru,
                    // ještě před sekci "Biochemie" – vypovídají o kvalitě vzorku.
                    $qualityParams = [];
                    $normalBiochem = [];
                    foreach ($biochemParams as $pName => $pInfo) {
                        if (isSampleQualityParam($pName)) { $qualityParams[$pName] = $pInfo; }
                        else { $normalBiochem[$pName] = $pInfo; }
                    }

                    // Poskládat pořadí řádků včetně hlaviček sekcí (marker __section__).
                    $orderedBiochem = [];
                    if (!empty($qualityParams)) {
                        $orderedBiochem['__quality__'] = ['__section__' => 'Kvalita vzorku'];
                        foreach ($qualityParams as $k => $v) { $orderedBiochem[$k] = $v; }
                    }
                    $orderedBiochem['__biochem__'] = ['__section__' => 'Biochemie'];
                    foreach ($normalBiochem as $k => $v) { $orderedBiochem[$k] = $v; }

                    foreach ($orderedBiochem as $paramName => $paramInfo):
                        if (isset($paramInfo['__section__'])):
                    ?>
                        <tr class="section-header">
                            <td colspan="<?= 3 + count($allTests) * 2 ?>"><strong><?= htmlspecialchars($paramInfo['__section__']) ?></strong></td>
                        </tr>
                    <?php
                            continue;
                        endif;

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
                                    if ($bt['test_date'] === $test['test_date'] && ($bt['test_location'] ?? '') === ($test['test_location'] ?? '')) {
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
                                            // Nulová mez by znamenala dělení nulou -> "MIMO MEZ" červeně.
                                            if ($min != 0) {
                                                $percentage = ($min - $numValue) / $min * 100;
                                                $evalText = '↓ ' . number_format($percentage, 2, ',', '') . '%';
                                                $evalClass = 'deviation-low';
                                                $valueClass = 'deviation-low';
                                            } else {
                                                $evalText = 'MIMO MEZ';
                                                $evalClass = 'deviation-high mimo';
                                                $valueClass = 'deviation-high';
                                            }
                                        } elseif ($numValue > $max) {
                                            if ($max != 0) {
                                                $percentage = ($numValue - $max) / $max * 100;
                                                $evalText = '↑ ' . number_format($percentage, 2, ',', '') . '%';
                                                $evalClass = 'deviation-high';
                                            } else {
                                                $evalText = 'MIMO MEZ';
                                                $evalClass = 'deviation-high mimo';
                                            }
                                            $valueClass = 'deviation-high';
                                        } else {
                                            $evalText = 'OK';
                                            $evalClass = 'ok';
                                        }
                                    } elseif ($min !== null && $numValue < $min) {
                                        $evalText = '↓';
                                        $evalClass = 'deviation-low';
                                        $valueClass = 'deviation-low';
                                    } elseif ($max !== null && $numValue > $max) {
                                        $evalText = '↑';
                                        $evalClass = 'deviation-high';
                                        $valueClass = 'deviation-high';
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
                                    if ($ht['test_date'] === $test['test_date'] && ($ht['test_location'] ?? '') === ($test['test_location'] ?? '')) {
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
                                            // Nulová mez by znamenala dělení nulou -> "MIMO MEZ" červeně.
                                            if ($min != 0) {
                                                $percentage = ($min - $numValue) / $min * 100;
                                                $evalText = '↓ ' . number_format($percentage, 2, ',', '') . '%';
                                                $evalClass = 'deviation-low';
                                                $valueClass = 'deviation-low';
                                            } else {
                                                $evalText = 'MIMO MEZ';
                                                $evalClass = 'deviation-high mimo';
                                                $valueClass = 'deviation-high';
                                            }
                                        } elseif ($numValue > $max) {
                                            if ($max != 0) {
                                                $percentage = ($numValue - $max) / $max * 100;
                                                $evalText = '↑ ' . number_format($percentage, 2, ',', '') . '%';
                                                $evalClass = 'deviation-high';
                                            } else {
                                                $evalText = 'MIMO MEZ';
                                                $evalClass = 'deviation-high mimo';
                                            }
                                            $valueClass = 'deviation-high';
                                        } else {
                                            $evalText = 'OK';
                                            $evalClass = 'ok';
                                        }
                                    } elseif ($min !== null && $numValue < $min) {
                                        $evalText = '↓';
                                        $evalClass = 'deviation-low';
                                        $valueClass = 'deviation-low';
                                    } elseif ($max !== null && $numValue > $max) {
                                        $evalText = '↑';
                                        $evalClass = 'deviation-high';
                                        $valueClass = 'deviation-high';
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
    <?php endforeach; ?>
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
    margin-bottom: 24px; /* oddělení bloků (stránek) v náhledu */
}
.print-part {
    font-weight: 400;
    font-size: 0.7em;
    color: #7f8c8d;
}

/* Editovatelný titulní řádek: jméno zvířete + ID */
.print-animal-title {
    font-size: 16px;
    font-weight: 700;
    color: #000;
    margin: 0 0 3mm 0;
    padding: 2px 6px;
    border: 1px dashed #bbb;   /* náznak editovatelnosti (jen na obrazovce) */
    border-radius: 4px;
    outline: none;
}
.print-animal-title:focus {
    border-color: #3498db;
    background: #f4f9fd;
}

/* Print table - spreadsheet style */
.print-table {
    border-collapse: collapse;
    font-size: 8px;
    width: 100%;
    table-layout: fixed; /* sloupce se drží poměrných šířek -> tabulka se vejde na stránku */
}

.print-table th,
.print-table td {
    border: 1px solid #000;
    padding: 2px 4px;
    white-space: normal;
    overflow-wrap: break-word;
    overflow: hidden;
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

/* Sloupec s hodnotou: průhledné pozadí, barevný text (červeně nad, modře pod). */
.value-cell.deviation-high { background: transparent !important; color: #c0392b !important; font-weight: bold; }
.value-cell.deviation-low  { background: transparent !important; color: #2563eb !important; font-weight: bold; }

/* Sloupec s vyhodnocením (%): černý text, světlé barevné pozadí. */
.eval-cell.deviation-high { background-color: #ffd6da !important; color: #000 !important; }
.eval-cell.deviation-low  { background-color: #dbe9ff !important; color: #000 !important; }
/* MIMO MEZ vypadá stejně jako procenta: černý text, světle červené pozadí. */
.eval-cell.mimo { background-color: #ffd6da !important; color: #000 !important; }

/* Alternate column colors for better readability */
.alt-col {
    background-color: #f5f5f5;
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
        page-break-after: always;
        break-after: page;
    }
    .print-page:last-child {
        page-break-after: auto;
        break-after: auto;
    }

    .print-animal-title {
        border: none !important;
        padding: 0 !important;
        margin: 0 0 2mm 0 !important;
        font-size: 14px !important;
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

    .value-cell.deviation-high {
        background: transparent !important;
        color: #c0392b !important;
        -webkit-print-color-adjust: exact !important;
        print-color-adjust: exact !important;
    }

    .value-cell.deviation-low {
        background: transparent !important;
        color: #2563eb !important;
        -webkit-print-color-adjust: exact !important;
        print-color-adjust: exact !important;
    }

    .eval-cell.deviation-high {
        background-color: #ffd6da !important;
        color: #000 !important;
        -webkit-print-color-adjust: exact !important;
        print-color-adjust: exact !important;
    }

    .eval-cell.deviation-low {
        background-color: #dbe9ff !important;
        color: #000 !important;
        -webkit-print-color-adjust: exact !important;
        print-color-adjust: exact !important;
    }

    .eval-cell.mimo {
        background-color: #ffd6da !important;
        color: #000 !important;
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

    .eval-cell.alt-col.deviation-high { background-color: #ffd6da !important; }
    .eval-cell.alt-col.deviation-low { background-color: #dbe9ff !important; }

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
    const perPageEl = document.getElementById('perPageSelect');
    const perPage = perPageEl ? perPageEl.value : 10;
    window.location.href = `/biochemistry/animal/${animalId}/print?table=${table}&source=${source}&per_page=${perPage}`;
}

function updateFontSize() {
    const fontSize = parseInt(document.getElementById('fontSizeSelect').value, 10);
    let styleEl = document.getElementById('fontSizeOverride');
    if (!styleEl) {
        styleEl = document.createElement('style');
        styleEl.id = 'fontSizeOverride';
        document.head.appendChild(styleEl);
    }
    // Přepíšeme pevné px velikosti v jednotlivých buňkách, jinak by select nic nedělal.
    styleEl.textContent =
        '.print-table, .print-table td, .print-table th,' +
        '.param-cell, .ref-cell, .unit-cell, .value-cell, .eval-cell {' +
        'font-size: ' + fontSize + 'px !important; }';
}

// Aplikovat výchozí velikost hned po načtení.
document.addEventListener('DOMContentLoaded', updateFontSize);
</script>
