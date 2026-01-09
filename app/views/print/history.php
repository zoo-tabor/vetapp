<!DOCTYPE html>
<html lang="cs">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tisk historie - <?= htmlspecialchars($workplace['name']) ?></title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: Arial, sans-serif;
            font-size: 12pt;
            line-height: 1.4;
            padding: 20px;
        }

        .no-print {
            margin-bottom: 20px;
            border: 2px solid #3498db;
            padding: 15px;
            background-color: #ecf0f1;
        }

        .no-print h3 {
            margin-bottom: 10px;
            color: #2c3e50;
        }

        .no-print button {
            background-color: #3498db;
            color: white;
            border: none;
            padding: 10px 20px;
            font-size: 14px;
            cursor: pointer;
            border-radius: 4px;
            margin-right: 10px;
        }

        .no-print button:hover {
            background-color: #2980b9;
        }

        .no-print textarea {
            width: 100%;
            min-height: 60px;
            padding: 8px;
            font-size: 12pt;
            border: 1px solid #bdc3c7;
            border-radius: 4px;
            font-family: Arial, sans-serif;
        }

        .animal-section {
            margin-bottom: 40px;
            page-break-inside: avoid;
        }

        h1 {
            text-align: center;
            margin-bottom: 20px;
            font-size: 18pt;
        }

        h2 {
            margin-top: 20px;
            margin-bottom: 10px;
            font-size: 14pt;
            border-bottom: 2px solid #000;
            padding-bottom: 5px;
        }

        .animal-info {
            margin-bottom: 15px;
            border: 1px solid #000;
            padding: 10px;
        }

        .animal-info p {
            margin-bottom: 5px;
        }

        .animal-info strong {
            display: inline-block;
            width: 150px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }

        th, td {
            border: 1px solid #000;
            padding: 8px;
            text-align: left;
        }

        th {
            background-color: #e8e8e8;
            font-weight: bold;
        }

        .positive {
            background-color: #ffcccc;
        }

        .negative {
            background-color: #ccffcc;
        }

        .conclusion-box {
            border: 1px solid #000;
            padding: 10px;
            min-height: 100px;
            margin-top: 15px;
            background-color: #f9f9f9;
        }

        .conclusion-label {
            font-weight: bold;
            margin-bottom: 5px;
        }

        @media print {
            .no-print {
                display: none;
            }

            body {
                padding: 0;
            }

            .animal-section {
                margin-bottom: 0;
            }
        }
    </style>
</head>
<body>

<div class="no-print">
    <h3>📝 Před tiskem vyplňte závěry pro jednotlivá zvířata</h3>
    <p>Pro každé zvíře níže můžete vyplnit pole "Závěr", které bude vytištěno pod historií vyšetření.</p>
    <button onclick="window.print()">🖨️ Tisknout</button>
    <button onclick="window.close()">✕ Zavřít</button>
</div>

<h1>Historie vyšetření - <?= htmlspecialchars($workplace['name']) ?></h1>
<p style="text-align: center; margin-bottom: 20px;">Vytištěno: <?= date('d.m.Y H:i') ?></p>

<?php foreach ($printData as $index => $data): ?>
    <div class="animal-section">
        <div class="animal-info">
            <p><strong>Jméno/Identifikátor:</strong> <?= htmlspecialchars($data['animal']['name'] ?? $data['animal']['identifier']) ?></p>
            <p><strong>Druh:</strong> <?= htmlspecialchars($data['animal']['species']) ?></p>
            <p><strong>Pohlaví:</strong>
                <?php
                $genders = ['male' => 'Samec', 'female' => 'Samice', 'unknown' => 'Neznámé'];
                echo $genders[$data['animal']['gender']] ?? 'Neznámé';
                ?>
            </p>
            <?php if (!empty($data['animal']['birth_date'])): ?>
                <p><strong>Datum narození:</strong> <?= date('d.m.Y', strtotime($data['animal']['birth_date'])) ?></p>
            <?php endif; ?>
            <?php if (!empty($data['enclosure'])): ?>
                <p><strong>Výběh:</strong> <?= htmlspecialchars($data['enclosure']['name']) ?></p>
            <?php endif; ?>
            <?php if (!empty($data['animal']['enclosure_name'])): ?>
                <p><strong>Aktuální výběh:</strong> <?= htmlspecialchars($data['animal']['enclosure_name']) ?></p>
            <?php endif; ?>
        </div>

        <h2>Vyšetření (posledních <?= $printCount ?>)</h2>
        <?php if (empty($data['examinations'])): ?>
            <p><em>Žádná vyšetření k zobrazení</em></p>
        <?php else:
            // Group examinations by date + institution
            $groupedExams = [];
            foreach ($data['examinations'] as $exam) {
                $key = $exam['examination_date'] . '|' . ($exam['institution'] ?? '');
                if (!isset($groupedExams[$key])) {
                    $groupedExams[$key] = [
                        'date' => $exam['examination_date'],
                        'institution' => $exam['institution'] ?? '-',
                        'exams' => []
                    ];
                }
                $groupedExams[$key]['exams'][] = $exam;
            }
        ?>
            <table>
                <thead>
                    <tr>
                        <th>Datum</th>
                        <th>Instituce</th>
                        <th>Typ vzorku</th>
                        <th>Výsledek</th>
                        <th>Nalezený parazit</th>
                        <th>Intenzita</th>
                        <th>Poznámky</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($groupedExams as $group):
                        // Determine if any exam in group is positive
                        $hasPositive = false;
                        foreach ($group['exams'] as $e) {
                            if ($e['finding_status'] === 'positive') {
                                $hasPositive = true;
                                break;
                            }
                        }
                        $rowClass = $hasPositive ? 'positive' : 'negative';
                    ?>
                        <tr class="<?= $rowClass ?>">
                            <td><?= date('d.m.Y', strtotime($group['date'])) ?></td>
                            <td><?= htmlspecialchars($group['institution']) ?></td>
                            <td>
                                <?php
                                $types = [];
                                foreach ($group['exams'] as $e) {
                                    $types[] = htmlspecialchars($e['sample_type']);
                                }
                                echo implode('<br>', $types);
                                ?>
                            </td>
                            <td>
                                <?php
                                $results = [];
                                foreach ($group['exams'] as $e) {
                                    $results[] = $e['finding_status'] === 'positive' ? 'Pozitivní' : 'Negativní';
                                }
                                echo implode('<br>', $results);
                                ?>
                            </td>
                            <td>
                                <?php
                                $parasites = [];
                                foreach ($group['exams'] as $e) {
                                    $parasites[] = htmlspecialchars($e['parasite_found'] ?? '-');
                                }
                                echo implode('<br>', $parasites);
                                ?>
                            </td>
                            <td>
                                <?php
                                $intensities = [];
                                foreach ($group['exams'] as $e) {
                                    $intensities[] = htmlspecialchars($e['intensity'] ?? '-');
                                }
                                echo implode('<br>', $intensities);
                                ?>
                            </td>
                            <td><?= htmlspecialchars($group['exams'][0]['notes'] ?? '-') ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>

        <h2>Odčervení (posledních <?= $printCount ?>)</h2>
        <?php if (empty($data['dewormings'])): ?>
            <p><em>Žádná odčervení k zobrazení</em></p>
        <?php else: ?>
            <table>
                <thead>
                    <tr>
                        <th>Datum</th>
                        <th>Přípravek</th>
                        <th>Dávka</th>
                        <th>Důvod</th>
                        <th>Poznámky</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($data['dewormings'] as $dew): ?>
                        <tr>
                            <td><?= date('d.m.Y', strtotime($dew['deworming_date'])) ?></td>
                            <td><?= htmlspecialchars($dew['medication'] ?? '-') ?></td>
                            <td><?= htmlspecialchars($dew['dosage'] ?? '-') ?></td>
                            <td><?= htmlspecialchars($dew['reason'] ?? '-') ?></td>
                            <td><?= htmlspecialchars($dew['notes'] ?? '-') ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>

        <div class="conclusion-label">Závěr:</div>
        <div class="conclusion-box no-print">
            <textarea
                id="conclusion_<?= $index ?>"
                placeholder="Zde napište závěr pro toto zvíře..."
                oninput="updateConclusion(<?= $index ?>)"
            ></textarea>
        </div>
        <div class="conclusion-box conclusion-print" id="conclusion_print_<?= $index ?>" style="display: none;">
            <!-- Will be filled by JavaScript when printing -->
        </div>
    </div>
<?php endforeach; ?>

<script>
function updateConclusion(index) {
    const textarea = document.getElementById('conclusion_' + index);
    const printDiv = document.getElementById('conclusion_print_' + index);
    printDiv.textContent = textarea.value;
}

// Before printing, show the conclusion text
window.addEventListener('beforeprint', function() {
    document.querySelectorAll('.conclusion-print').forEach(el => {
        el.style.display = 'block';
    });
});

// After printing, hide the conclusion text
window.addEventListener('afterprint', function() {
    document.querySelectorAll('.conclusion-print').forEach(el => {
        el.style.display = 'none';
    });
});
</script>

</body>
</html>
