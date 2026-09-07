<?php $perPage = isset($_GET['per_page']) ? max(1, min(60, (int)$_GET['per_page'])) : 10; ?>
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

    <?php
    // Vyhodnocuje se podle laboratoře uložené u odběru; tady lze zdroj přepnout
    // u konkrétního odběru (jen pro tisk, do databáze se nic nezapisuje).
    $__sourcePickerGroups = [];
    if ($tableType === 'biochemistry' || $tableType === 'both') {
        $__sourcePickerGroups['Biochemie'] = $biochemTests;
    }
    if ($tableType === 'hematology' || $tableType === 'both') {
        $__sourcePickerGroups['Hematologie'] = $hematoTests;
    }
    ?>
    <div class="setting-group">
        <label>Referenční meze (laboratoř):</label>
        <div class="source-picker-list">
            <?php $__anySource = false; ?>
            <?php foreach ($__sourcePickerGroups as $__groupLabel => $__groupTests): ?>
                <?php if (empty($__groupTests)) continue; ?>
                <?php $__anySource = true; ?>
                <div class="source-picker-group"><?= htmlspecialchars($__groupLabel) ?></div>
                <?php foreach ($__groupTests as $__t): ?>
                    <?php $__tSource = trim((string)($__t['reference_source'] ?? '')); ?>
                    <div class="source-picker-row">
                        <span class="source-picker-date">
                            <?= date('d.m.Y', strtotime($__t['test_date'])) ?>
                            <?php if (!empty($__t['test_location'])): ?>
                                <small><?= htmlspecialchars($__t['test_location']) ?></small>
                            <?php endif; ?>
                        </span>
                        <select class="test-source-select"
                                data-test-key="<?= htmlspecialchars($__t['key']) ?>"
                                data-stored-source="<?= htmlspecialchars(trim((string)($__t['stored_source'] ?? ''))) ?>"
                                onchange="updatePreview()">
                            <?php if ($__tSource === ''): ?>
                                <option value="" selected>— nezadáno —</option>
                            <?php endif; ?>
                            <?php foreach ($referenceSources as $__src): ?>
                                <option value="<?= htmlspecialchars($__src) ?>" <?= $__src === $__tSource ? 'selected' : '' ?>><?= htmlspecialchars($__src) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                <?php endforeach; ?>
            <?php endforeach; ?>
            <?php if (!$__anySource): ?>
                <div class="source-picker-empty">Žádné odběry k zobrazení.</div>
            <?php endif; ?>
        </div>
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
        <div class="scale-row">
            <input type="number" id="fontSizeInput" min="3" max="40" step="0.5" value="8"
                   oninput="updateFontSize()" onchange="updateFontSize()">
            <span>px</span>
        </div>
        <small class="setting-hint">Libovolné číslo 3–40 (i desetinné, např. 8.5).</small>
    </div>

    <div class="setting-group">
        <label>Odběrů na stránku:</label>
        <div class="scale-row">
            <input type="number" id="perPageInput" min="1" max="60" step="1" value="<?= $perPage ?>"
                   onchange="updatePreview()">
            <span>ks</span>
        </div>
        <small class="setting-hint">Libovolné číslo 1–60; datumy se rozdělí na víc stránek.</small>
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

    // Ke každému sloupci najdeme odpovídající odběr (datum + místo) – jednou pro
    // celý dokument, ne zvlášť pro každou stránku.
    $colBiochemByKey = [];
    $colHematoByKey = [];
    foreach ($allTestsFull as $__col) {
        $__key = $__col['test_date'] . '|' . ($__col['test_location'] ?? '');
        foreach ($biochemTests as $__bt) {
            if ($__bt['test_date'] === $__col['test_date'] && ($__bt['test_location'] ?? '') === ($__col['test_location'] ?? '')) {
                $colBiochemByKey[$__key] = $__bt;
                break;
            }
        }
        foreach ($hematoTests as $__ht) {
            if ($__ht['test_date'] === $__col['test_date'] && ($__ht['test_location'] ?? '') === ($__col['test_location'] ?? '')) {
                $colHematoByKey[$__key] = $__ht;
                break;
            }
        }
    }

    // Laboratoře za celý dokument – rozhodují, jak se vypíše sloupec s mezemi.
    // Musí to být přes všechny stránky, jinak by stránka jen s jednou laboratoří
    // vypadala jinak než stránka, kde se laboratoře míchají.
    $docBiochemSources = labUsedSources($colBiochemByKey);
    $docHematoSources = labUsedSources($colHematoByKey);
    $docAllSources = array_values(array_unique(array_merge(
        ($tableType === 'biochemistry' || $tableType === 'both') ? $docBiochemSources : [],
        ($tableType === 'hematology' || $tableType === 'both') ? $docHematoSources : []
    )));

    // Datumové sloupce rozdělíme po blocích na samostatné tiskové stránky, aby se
    // nic neslučovalo ani neusekávalo. Levé sloupce (parametr/meze/jednotky) se
    // opakují na každé stránce.
    $perPage = isset($_GET['per_page']) ? max(1, min(60, (int)$_GET['per_page'])) : 10;
    $blocks = array_chunk($allTestsFull, $perPage);
    if (empty($blocks)) { $blocks = [[]]; }

    foreach ($blocks as $__blockIdx => $allTests):
        $colBiochemTest = [];
        $colHematoTest = [];
        foreach ($allTests as $__colIdx => $__col) {
            $__key = $__col['test_date'] . '|' . ($__col['test_location'] ?? '');
            if (isset($colBiochemByKey[$__key])) { $colBiochemTest[$__colIdx] = $colBiochemByKey[$__key]; }
            if (isset($colHematoByKey[$__key])) { $colHematoTest[$__colIdx] = $colHematoByKey[$__key]; }
        }
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
                    <th class="ref-col">Referenční meze<br><small>(<?= count($docAllSources) === 1 ? htmlspecialchars($docAllSources[0]) : 'dle laboratoře odběru' ?>)</small></th>
                    <th class="unit-col">Jednotky</th>
                    <?php foreach ($allTests as $colIdx => $test): ?>
                        <?php
                        // Laboratoř sloupce (u "obou tabulek" může být pro bioch. a hem. jiná).
                        $__colSources = array_values(array_unique(array_filter([
                            ($tableType === 'biochemistry' || $tableType === 'both') ? trim((string)($colBiochemTest[$colIdx]['reference_source'] ?? '')) : '',
                            ($tableType === 'hematology' || $tableType === 'both') ? trim((string)($colHematoTest[$colIdx]['reference_source'] ?? '')) : ''
                        ], 'strlen')));
                        ?>
                        <?php
                        // Laboratoř vypisujeme jen když se sloupce liší (jinak je
                        // uvedená v hlavičce sloupce s mezemi). Když se kryje s místem
                        // odběru, povýšíme rovnou ten řádek – ať tam není dvakrát.
                        $__loc = trim((string)($test['test_location'] ?? ''));
                        $__showColSource = !empty($__colSources) && count($docAllSources) > 1;
                        $__locIsSource = $__showColSource && $__loc !== '' && $__colSources === [$__loc];
                        ?>
                        <th class="date-col">
                            <?= date('d.m.Y', strtotime($test['test_date'])) ?>
                            <?php if ($__locIsSource): ?>
                                <span class="col-source"><?= htmlspecialchars($__loc) ?></span>
                            <?php elseif ($__loc !== ''): ?>
                                <br><small><?= htmlspecialchars($__loc) ?></small>
                            <?php endif; ?>
                            <?php if ($__showColSource && !$__locIsSource): ?>
                                <span class="col-source"><?= htmlspecialchars(implode(' / ', $__colSources)) ?></span>
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

                        $paramRanges = $referenceRanges['biochemistry'][$paramName] ?? [];
                        $refText = labRefCellHtml($paramRanges, $docBiochemSources);
                        // Meze rozepsané po laboratořích = víc řádků; takové buňky
                        // nezvětšujeme s nastavenou velikostí písma (viz updateFontSize).
                        $refMulti = strpos($refText, 'ref-line') !== false;
                    ?>
                        <tr>
                            <td class="param-cell"><?= htmlspecialchars($paramName) ?></td>
                            <td class="ref-cell<?= $refMulti ? ' ref-cell-multi' : '' ?>"><?= $refText ?></td>
                            <td class="unit-cell"><?= htmlspecialchars($paramInfo['unit']) ?></td>
                            <?php foreach ($allTests as $colIdx => $test):
                                $biochemTest = $colBiochemTest[$colIdx] ?? null;

                                // Meze podle laboratoře přiřazené tomuto odběru.
                                $refRange = $biochemTest
                                    ? ($paramRanges[trim((string)($biochemTest['reference_source'] ?? ''))] ?? null)
                                    : null;

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
                                                $evalText = labEvalArrow('down') . ' ' . number_format($percentage, 2, ',', '') . '%';
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
                                                $evalText = labEvalArrow('up') . ' ' . number_format($percentage, 2, ',', '') . '%';
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
                                        $evalText = labEvalArrow('down');
                                        $evalClass = 'deviation-low';
                                        $valueClass = 'deviation-low';
                                    } elseif ($max !== null && $numValue > $max) {
                                        $evalText = labEvalArrow('up');
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
                        $paramRanges = $referenceRanges['hematology'][$paramName] ?? [];
                        $refText = labRefCellHtml($paramRanges, $docHematoSources);
                        // Meze rozepsané po laboratořích = víc řádků; takové buňky
                        // nezvětšujeme s nastavenou velikostí písma (viz updateFontSize).
                        $refMulti = strpos($refText, 'ref-line') !== false;
                    ?>
                        <tr>
                            <td class="param-cell"><?= htmlspecialchars($paramName) ?></td>
                            <td class="ref-cell<?= $refMulti ? ' ref-cell-multi' : '' ?>"><?= $refText ?></td>
                            <td class="unit-cell"><?= htmlspecialchars($paramInfo['unit']) ?></td>
                            <?php foreach ($allTests as $colIdx => $test):
                                $hematoTest = $colHematoTest[$colIdx] ?? null;

                                // Meze podle laboratoře přiřazené tomuto odběru.
                                $refRange = $hematoTest
                                    ? ($paramRanges[trim((string)($hematoTest['reference_source'] ?? ''))] ?? null)
                                    : null;

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
                                                $evalText = labEvalArrow('down') . ' ' . number_format($percentage, 2, ',', '') . '%';
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
                                                $evalText = labEvalArrow('up') . ' ' . number_format($percentage, 2, ',', '') . '%';
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
                                        $evalText = labEvalArrow('down');
                                        $evalClass = 'deviation-low';
                                        $valueClass = 'deviation-low';
                                    } elseif ($max !== null && $numValue > $max) {
                                        $evalText = labEvalArrow('up');
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

/* Výběr laboratoře po jednotlivých odběrech (vyhodnocení běží podle ní) */
.source-picker-list {
    max-height: 220px;
    overflow-y: auto;
    background: #34495e;
    border-radius: 4px;
    padding: 6px 8px;
}

.source-picker-group {
    font-size: 11px;
    font-weight: 700;
    color: #92d050;
    margin: 6px 0 4px;
}

.source-picker-group:first-child {
    margin-top: 0;
}

.source-picker-row {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 8px;
    margin-bottom: 4px;
}

.source-picker-date {
    font-size: 11px;
    color: #ecf0f1;
    white-space: nowrap;
}

.source-picker-date small {
    display: block;
    color: #bdc3c7;
    font-size: 10px;
}

.source-picker-list .test-source-select {
    width: auto;
    max-width: 110px;
    padding: 3px 4px;
    border: none;
    border-radius: 3px;
    font-size: 11px;
    background: #2c3e50;
    color: white;
}

.source-picker-empty {
    font-size: 11px;
    color: #bdc3c7;
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

.setting-hint {
    display: block;
    margin-top: 6px;
    font-size: 11px;
    color: #bdc3c7;
    opacity: .8;
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

/* Meze se mezi laboratořemi liší -> vypisují se po zdrojích, každý na svůj řádek */
.ref-cell .ref-line {
    display: block;
    white-space: nowrap;
}

.ref-cell .ref-line-source {
    font-weight: 700;
    color: #555;
}

/* Laboratoř sloupce – čte se stejně velká jako datum (ne <small>), na vlastním
   řádku a v případě potřeby se zalomí do šířky sloupce. */
.column-header .col-source {
    display: block;
    font-weight: 600;
    white-space: normal;
    overflow-wrap: anywhere;
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

/* Šipka odchylky: dolů modře, nahoru červeně, tučně. Barvy sedí s obarvením
   hodnoty ve vedlejším sloupci. Vlastní pravidlo na <span> přebije i barvu
   nastavenou na buňce přes !important (dědičnost prohrává s přímým pravidlem). */
.eval-cell .eval-arrow {
    font-weight: bold;
}

.eval-cell .eval-arrow.up {
    color: #c0392b;
}

.eval-cell .eval-arrow.down {
    color: #2563eb;
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

/* Sloupce "vs. referenční meze" – výrazná šedá, ať se jasně oddělí od hodnot.
   Buňky mimo meze si pozadí přebijí (růžová/modrá výše, mají !important). */
.alt-col {
    background-color: #ebebeb;
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
        background-color: #ebebeb !important;
        -webkit-print-color-adjust: exact !important;
        print-color-adjust: exact !important;
    }

    .eval-cell .eval-arrow.up { color: #c0392b !important; }
    .eval-cell .eval-arrow.down { color: #2563eb !important; }

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

// Volné číselné pole: prázdné/nesmyslné zůstane na záložní hodnotě, čárka projde
// jako desetinná tečka (prohlížeč ji u type=number podle locale vrátit může i nemusí).
function readNumberInput(id, fallback) {
    const el = document.getElementById(id);
    if (!el) return fallback;
    const raw = String(el.value == null ? '' : el.value).trim().replace(',', '.');
    const value = parseFloat(raw);
    return isFinite(value) ? value : fallback;
}

function clampNumber(value, min, max) {
    return Math.min(max, Math.max(min, value));
}

function updatePreview() {
    const table = document.getElementById('tableSelect').value;
    const perPage = Math.round(clampNumber(readNumberInput('perPageInput', 10), 1, 60));

    // Vyčištěnou hodnotu vrátíme do pole, ať uživatel vidí, s čím se opravdu tiskne.
    const perPageEl = document.getElementById('perPageInput');
    if (perPageEl) perPageEl.value = perPage;

    const params = new URLSearchParams();
    params.set('table', table);
    params.set('per_page', perPage);

    // V URL veze jen skutečné přepnutí oproti laboratoři uložené u odběru
    // (do databáze se nic nezapisuje).
    document.querySelectorAll('.test-source-select[data-test-key]').forEach(select => {
        if (select.value && select.value !== (select.dataset.storedSource || '')) {
            params.set(`src[${select.dataset.testKey}]`, select.value);
        }
    });

    window.location.href = `/biochemistry/animal/${animalId}/print?${params.toString()}`;
}

let lastFontSize = 8;
let fontSizeTimer = null;

// Meze rozepsané po laboratořích ("Laboklin: 0.00 - 1650.00" na dvou řádcích) jsou
// zdaleka nejširší buňka v řádku. Kdyby rostly s nastavenou velikostí, sloupec se
// roztáhne, fit zoom pak zmenší celou tabulku a výsledky vyjdou menší, ne větší.
// Držíme je proto na zlomku nastavené velikosti.
const REF_MULTI_FONT_RATIO = 0.7;

// "vs. referenční meze" je jen nadpis sloupce. Kdyby rostl s daty, roztáhne
// sloupec na dvojnásobek toho, co potřebuje "↑ 42,50%" – a to 16x vedle sebe,
// takže fit zoom pak celou tabulku pořádně zmenší. Držíme ho menší.
const EVAL_HEADER_FONT_RATIO = 0.55;

function updateFontSize() {
    const fontSize = clampNumber(readNumberInput('fontSizeInput', lastFontSize), 3, 40);
    lastFontSize = fontSize;

    let styleEl = document.getElementById('fontSizeOverride');
    if (!styleEl) {
        styleEl = document.createElement('style');
        styleEl.id = 'fontSizeOverride';
        document.head.appendChild(styleEl);
    }

    // Přepíšeme pevné px velikosti v jednotlivých buňkách, jinak by pole nic nedělalo.
    let css =
        '.print-table, .print-table td, .print-table th,' +
        '.param-cell, .ref-cell, .unit-cell, .value-cell, .eval-cell {' +
        'font-size: ' + fontSize + 'px !important; }';

    // Hlavičku sloupce zmenšujeme spolu s buňkami – jinak by šířku sloupce
    // určoval nápis "Referenční meze" a zmenšení buněk by nic nepřineslo.
    if (document.querySelector('.ref-cell-multi')) {
        const refSize = Math.max(3, Math.round(fontSize * REF_MULTI_FONT_RATIO * 10) / 10);
        css += '.print-table td.ref-cell, .print-table th.ref-col {' +
               'font-size: ' + refSize + 'px !important; }';
    }

    css += '.print-table th.eval-col {' +
           'font-size: ' + Math.max(3, Math.round(fontSize * EVAL_HEADER_FONT_RATIO * 10) / 10) + 'px !important; }';

    styleEl.textContent = css;

    // Přepočet měřítka je drahý – při psaní do pole ho necháme doběhnout až po pauze.
    clearTimeout(fontSizeTimer);
    fontSizeTimer = setTimeout(function () {
        requestAnimationFrame(applyPageSetup);
    }, 200);
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
        // Meze bereme o pixel mensi, at je rezerva na zaokrouhleni pri tisku.
        const limitW = s.availW - 1;
        const limitH = s.availH - 1;
        if (mode !== 'custom') {
            for (let i = 0; i < 6; i++) {
                // Merime tabulku, ne obalovy .print-fit - ten je blokovy, takze
                // by mel vzdycky sirku stranky a kontrola sirky by nic nerekla.
                // getBoundingClientRect vraci uz zoomovanou velikost a je
                // desetinny (offsetWidth zaokrouhluje nahoru a hlasil by
                // preteceni i tam, kde zadne neni).
                const r = table.getBoundingClientRect();
                if (!r.width || !r.height) break;
                const over = Math.max(
                    r.width / limitW,
                    mode === 'page' ? (r.height + titleH) / limitH : 0
                );
                if (over <= 1) break;
                z = Math.max(0.1, z / over * 0.999);
                table.style.zoom = z;
            }
        }
        lastZoom = z;

        totalPages += Math.max(
            1,
            Math.ceil((table.getBoundingClientRect().height + titleH) / s.availH - 0.01)
        );
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
            fontSize: document.getElementById('fontSizeInput').value
        }));
    } catch (e) { /* privátní režim apod. – jen se nic nezapamatuje */ }
}

function loadSetup() {
    let saved = null;
    try { saved = JSON.parse(localStorage.getItem(SETTINGS_KEY) || 'null'); } catch (e) {}
    if (!saved) return;
    const map = {
        paper: 'paperSelect', orient: 'orientSelect', margin: 'marginSelect',
        scaleMode: 'scaleModeSelect', scaleValue: 'scaleValue', fontSize: 'fontSizeInput'
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
