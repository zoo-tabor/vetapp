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
        <label>Velikost papíru:</label>
        <select id="paperSelect" onchange="applyPageSetup()">
            <option value="A4" selected>A4 (21 × 29,7 cm)</option>
            <option value="A3">A3 (29,7 × 42 cm)</option>
        </select>
    </div>

    <div class="setting-group">
        <label>Orientace stránky:</label>
        <select id="orientSelect" onchange="applyPageSetup()">
            <option value="landscape" selected>Na šířku</option>
            <option value="portrait">Na výšku</option>
        </select>
    </div>

    <div class="setting-group">
        <label>Okraje:</label>
        <select id="marginSelect" onchange="applyPageSetup()">
            <option value="5" selected>Úzké (5 mm)</option>
            <option value="10">Normální (10 mm)</option>
            <option value="15">Široké (15 mm)</option>
            <option value="0">Žádné</option>
        </select>
    </div>

    <div class="setting-group">
        <label>Měřítko:</label>
        <select id="scaleModeSelect" onchange="applyPageSetup()">
            <option value="page" selected>Přizpůsobit stránce</option>
            <option value="width">Přizpůsobit šířce</option>
            <option value="custom">Vlastní číslo</option>
        </select>
        <div class="scale-row">
            <input type="number" id="scaleValue" min="10" max="300" step="5" value="100"
                   oninput="applyPageSetup()" disabled>
            <span>%</span>
        </div>
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

    <div class="page-total" id="pageTotal"></div>

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

        <div class="print-fit">
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
    /* Stránky pod sebou (jako náhled tisku v Google Sheets), ne vedle sebe. */
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 24px;
    overflow: auto;
}

/* Jeden list papíru. Rozměry (šířka/výška/okraje) nastavuje JS podle
   zvoleného formátu – náhled tak odpovídá tomu, co vyjede z tiskárny.
   Výška je jen minimální, aby se při "přizpůsobit šířce" nic neuseklo. */
.print-page {
    background: white;
    box-shadow: 0 2px 10px rgba(0,0,0,0.15);
    box-sizing: border-box;
    position: relative;
    flex: 0 0 auto;
    background-origin: content-box;
    background-clip: content-box;
}

/* Číslo listu v rohu náhledu (netiskne se). */
.print-page-num {
    position: absolute;
    right: 6px;
    bottom: 4px;
    font-size: 10px;
    color: #b0b7bb;
}

.scale-row {
    display: flex;
    align-items: center;
    gap: 6px;
    margin-top: 6px;
}

.scale-row input {
    width: 100%;
    padding: 7px 8px;
    border: none;
    border-radius: 4px;
    font-size: 12px;
    background: #34495e;
    color: white;
}

.scale-row input:disabled {
    opacity: .65;
}

.scale-row span {
    font-size: 12px;
    color: #bdc3c7;
}

.page-total {
    margin-top: 18px;
    padding-top: 12px;
    border-top: 1px solid #34495e;
    font-size: 12px;
    color: #bdc3c7;
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
    width: auto;
}

.print-table th,
.print-table td {
    border: 1px solid #000;
    padding: 2px 4px;
    white-space: nowrap;
}

/* Při přetečení tabulky na další stránku opakovat hlavičku a nelámat řádky. */
.print-table thead { display: table-header-group; }
.print-table tr { break-inside: avoid; page-break-inside: avoid; }

/* Wrapper, který zmenší (scale) tabulku tak, aby se vešla na šířku stránky. */
.print-fit {
    transform-origin: top left;
}
.print-fit > table {
    transform-origin: top left;
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
        /* Okraje řeší @page margin, ne padding listu. */
        padding: 0 !important;
        width: auto !important;
        height: auto !important;
        min-height: 0 !important;
        min-width: auto !important;
        background-image: none !important;
        page-break-after: always;
        break-after: page;
    }

    .print-page-num {
        display: none !important;
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

    /* Záloha, kdyby nedoběhl JS – skutečný formát nastavuje applyPageSetup(). */
    @page {
        size: A4 landscape;
        margin: 5mm;
    }
}
</style>

<script>
const animalId = <?= $animal['id'] ?>;

// Rozměry papíru na výšku [šířka, výška] v mm.
const PAPER_MM = { A4: [210, 297], A3: [297, 420] };
const PX_PER_MM = 96 / 25.4;          // CSS px na milimetr (1in = 96px = 25,4 mm)
const SETTINGS_KEY = 'biochemPrintSetup';

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
    requestAnimationFrame(applyPageSetup);
}

// Aktuální nastavení stránky (formát, orientace, okraje) + odvozená tisknutelná plocha.
function getPageSetup() {
    const paper = document.getElementById('paperSelect').value;
    const orient = document.getElementById('orientSelect').value;
    const margin = parseFloat(document.getElementById('marginSelect').value) || 0;
    const dims = PAPER_MM[paper] || PAPER_MM.A4;
    const wMm = orient === 'landscape' ? dims[1] : dims[0];
    const hMm = orient === 'landscape' ? dims[0] : dims[1];
    return {
        paper: paper,
        orient: orient,
        margin: margin,
        wMm: wMm,
        hMm: hMm,
        availW: (wMm - 2 * margin) * PX_PER_MM,
        availH: (hMm - 2 * margin) * PX_PER_MM
    };
}

// Nastaví @page pro tisk a stejné rozměry i pro náhled, pak přepočítá měřítko.
function applyPageSetup() {
    const s = getPageSetup();
    let styleEl = document.getElementById('pageSetup');
    if (!styleEl) {
        styleEl = document.createElement('style');
        styleEl.id = 'pageSetup';
        document.head.appendChild(styleEl);
    }
    // Vodicí čára po každé tisknutelné výšce – v náhledu je vidět, kde se stránka zalomí.
    const guide = s.availH.toFixed(2) + 'px';
    styleEl.textContent =
        '@page { size: ' + s.paper + ' ' + s.orient + '; margin: ' + s.margin + 'mm; }\n' +
        '@media screen {\n' +
        '  .print-page {\n' +
        '    width: ' + s.wMm + 'mm;\n' +
        '    min-height: ' + s.hMm + 'mm;\n' +
        '    padding: ' + s.margin + 'mm;\n' +
        '    background-image: repeating-linear-gradient(to bottom,' +
        ' transparent 0, transparent calc(' + guide + ' - 1px),' +
        ' rgba(231,76,60,.35) calc(' + guide + ' - 1px), rgba(231,76,60,.35) ' + guide + ');\n' +
        '  }\n' +
        '}';

    fitPrintTables();
    saveSetup();
}

// Zmenší (zoom) každou tabulku tak, aby se blok vešel na stránku. Zoom – na rozdíl
// od transform – mění i layout, takže tisk odpovídá náhledu. Tabulka zůstává
// v přirozené šířce, jen se proporcionálně zmenší, text se neláme po znacích.
function fitPrintTables() {
    const s = getPageSetup();
    const modeEl = document.getElementById('scaleModeSelect');
    const scaleEl = document.getElementById('scaleValue');
    const mode = modeEl ? modeEl.value : 'page';
    let lastZoom = 1;
    let totalPages = 0;
    let pending = false;

    document.querySelectorAll('.print-page').forEach(function (page, idx) {
        const fit = page.querySelector('.print-fit');
        const table = fit ? fit.querySelector('table') : null;
        if (!table) return;

        // Měříme v původní velikosti, jinak by se zoom skládal sám na sebe.
        fit.style.zoom = '';
        table.style.zoom = '';
        const tw = table.offsetWidth;
        const th = table.offsetHeight;
        if (!tw || !th) { pending = true; return; }

        const title = page.querySelector('.print-animal-title');
        const titleH = title
            ? title.offsetHeight + (parseFloat(getComputedStyle(title).marginBottom) || 0)
            : 0;

        let z;
        if (mode === 'custom') {
            z = (parseFloat(scaleEl.value) || 100) / 100;
        } else if (mode === 'width') {
            z = Math.min(1, s.availW / tw);
        } else {
            // "Přizpůsobit stránce": zmenšit i na výšku, jinak z každého bloku
            // vypadne na další list tenký zbytek (tři bloky = šest listů).
            z = Math.min(1, s.availW / tw, (s.availH - titleH) / th);
        }
        z = Math.max(0.1, z);
        table.style.zoom = z;

        // Zoom přepočítá layout, takže výška každého řádku se zaokrouhlí nahoru –
        // u ~70 řádků je výsledek o desítky px vyšší, než vychází z th * z, a blok
        // by o kousek přetekl na další list. Doměříme skutečnou velikost (fit
        // obaluje zoomovanou tabulku, takže má její reálné rozměry) a dorovnáme.
        if (mode !== 'custom') {
            for (let i = 0; i < 6; i++) {
                const rw = fit.offsetWidth;
                const rh = fit.offsetHeight;
                if (!rw || !rh) break;
                const over = Math.max(
                    rw / s.availW,
                    mode === 'page' ? (rh + titleH) / s.availH : 0
                );
                if (over <= 1) break;
                z = Math.max(0.1, z / over * 0.998);
                table.style.zoom = z;
            }
        }
        lastZoom = z;

        totalPages += Math.max(1, Math.ceil((fit.offsetHeight + titleH) / s.availH - 0.01));
        setPageNumber(page, idx);
    });

    // Ještě nedoměřeno (tabulka nemá layout) – zkusit v dalším snímku.
    if (pending) { requestAnimationFrame(fitPrintTables); return; }

    if (scaleEl) {
        scaleEl.disabled = (mode !== 'custom');
        if (mode !== 'custom') scaleEl.value = Math.round(lastZoom * 100);
    }
    showTotal(totalPages);
}

function setPageNumber(page, idx) {
    let num = page.querySelector('.print-page-num');
    if (!num) {
        num = document.createElement('div');
        num.className = 'print-page-num';
        page.appendChild(num);
    }
    num.textContent = idx + 1;
}

function showTotal(n) {
    const el = document.getElementById('pageTotal');
    if (!el) return;
    const word = n === 1 ? 'stránka' : (n < 5 ? 'stránky' : 'stránek');
    el.textContent = 'Celkem: ' + n + ' ' + word;
}

// Nastavení vzhledu stránky si pamatujeme mezi tisky (formát se většinou nemění).
function saveSetup() {
    try {
        localStorage.setItem(SETTINGS_KEY, JSON.stringify({
            paper: document.getElementById('paperSelect').value,
            orient: document.getElementById('orientSelect').value,
            margin: document.getElementById('marginSelect').value,
            scaleMode: document.getElementById('scaleModeSelect').value,
            scaleValue: document.getElementById('scaleValue').value,
            fontSize: document.getElementById('fontSizeSelect').value
        }));
    } catch (e) { /* privátní režim apod. – jen se nic nezapamatuje */ }
}

function loadSetup() {
    let saved = null;
    try { saved = JSON.parse(localStorage.getItem(SETTINGS_KEY) || 'null'); } catch (e) {}
    if (!saved) return;
    const map = {
        paper: 'paperSelect', orient: 'orientSelect', margin: 'marginSelect',
        scaleMode: 'scaleModeSelect', scaleValue: 'scaleValue', fontSize: 'fontSizeSelect'
    };
    Object.keys(map).forEach(function (k) {
        const el = document.getElementById(map[k]);
        if (el && saved[k] != null && saved[k] !== '') el.value = saved[k];
    });
}

document.addEventListener('DOMContentLoaded', function () {
    loadSetup();
    updateFontSize();   // dovnitř volá applyPageSetup()
});
window.addEventListener('load', applyPageSetup);
window.addEventListener('beforeprint', fitPrintTables);
</script>
