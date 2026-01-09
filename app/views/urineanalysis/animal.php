<div class="container">
    <div class="breadcrumb">
        <a href="/">Pracoviště</a> /
        <a href="/urineanalysis/workplace/<?= $animal['workplace_id'] ?>">
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
                <a href="/workplace/<?= $animal['workplace_id'] ?>/animals/<?= $animal['id'] ?>?from=urineanalysis" class="btn btn-outline-edit">
                    Upravit
                </a>
                <button class="btn btn-primary" onclick="showAddTestModal()">+ Přidat vyšetření</button>
            </div>
        <?php endif; ?>
    </div>

    <!-- Reference Source Selector -->
    <div class="reference-selector">
        <div style="display: flex; align-items: center; gap: 15px;">
            <label for="referenceSource">Referenční zdroj:</label>
            <select id="referenceSource" class="form-control" onchange="updateReferenceRanges()">
                <?php foreach ($referenceSources as $source): ?>
                    <option value="<?= htmlspecialchars($source) ?>" <?= $source === 'Synlab' ? 'selected' : '' ?>>
                        <?= htmlspecialchars($source) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
        <a href="/urineanalysis/animal/<?= $animal['id'] ?>/comprehensive-table" class="btn btn-primary">Zobrazit kompletní tabulku</a>
    </div>

    <!-- Urine Analysis Section -->
    <div class="test-section">
        <h2>Analýza moči</h2>

        <?php if (empty($urineTests)): ?>
            <div class="alert alert-info">
                Žádné záznamy o vyšetřeních moči.
            </div>
        <?php else: ?>
            <?php foreach ($urineTests as $test): ?>
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
                        <div>
                            <span class="badge badge-source"><?= htmlspecialchars($test['reference_source']) ?></span>
                        </div>
                    </div>

                    <?php if ($test['notes']): ?>
                        <div class="test-notes">
                            <strong>Poznámky:</strong> <?= nl2br(htmlspecialchars($test['notes'])) ?>
                        </div>
                    <?php endif; ?>

                    <?php
                    // Group results by category
                    $chemicalParams = [];
                    $sedimentParams = [];
                    $urineParams = [];

                    foreach ($test['results'] as $result) {
                        $paramName = $result['parameter_name'];

                        // Chemical parameters
                        if (in_array($paramName, ['Glukóza', 'Bílkovina', 'Bilirubin', 'Urobilinogen', 'pH', 'Krev', 'Ketony', 'Nitrity', 'Leukocyty', 'Specifická hustota'])) {
                            $chemicalParams[] = $result;
                        }
                        // Sediment parameters
                        elseif (in_array($paramName, ['Erytrocyty elementy', 'Erytrocyty', 'Leukocyty elementy', 'Leukocyty', 'Bakterie', 'Drť', 'Hlen'])) {
                            $sedimentParams[] = $result;
                        }
                        // Urine parameters
                        else {
                            $urineParams[] = $result;
                        }
                    }
                    ?>

                    <?php if (!empty($chemicalParams)): ?>
                        <div class="results-section">
                            <h3>Moč chemicky</h3>
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
                                        <?php foreach ($chemicalParams as $result): ?>
                                            <tr>
                                                <td><strong><?= htmlspecialchars($result['parameter_name']) ?></strong></td>
                                                <td><?= htmlspecialchars($result['value']) ?></td>
                                                <td><?= htmlspecialchars($result['unit']) ?></td>
                                                <td class="reference-range"><?= $result['reference_range'] ? htmlspecialchars($result['reference_range']) : '-' ?></td>
                                                <td class="evaluation <?= isset($result['evaluation_class']) ? 'eval-' . $result['evaluation_class'] : '' ?>">
                                                    <?= $result['evaluation'] ? htmlspecialchars($result['evaluation']) : '-' ?>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    <?php endif; ?>

                    <?php if (!empty($sedimentParams)): ?>
                        <div class="results-section">
                            <h3>Močový sediment</h3>
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
                                        <?php foreach ($sedimentParams as $result): ?>
                                            <tr>
                                                <td><strong><?= htmlspecialchars($result['parameter_name']) ?></strong></td>
                                                <td><?= htmlspecialchars($result['value']) ?></td>
                                                <td><?= htmlspecialchars($result['unit']) ?></td>
                                                <td class="reference-range"><?= $result['reference_range'] ? htmlspecialchars($result['reference_range']) : '-' ?></td>
                                                <td class="evaluation <?= isset($result['evaluation_class']) ? 'eval-' . $result['evaluation_class'] : '' ?>">
                                                    <?= $result['evaluation'] ? htmlspecialchars($result['evaluation']) : '-' ?>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    <?php endif; ?>

                    <?php if (!empty($urineParams)): ?>
                        <div class="results-section">
                            <h3>Močové parametry</h3>
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
                                        <?php foreach ($urineParams as $result): ?>
                                            <tr>
                                                <td><strong><?= htmlspecialchars($result['parameter_name']) ?></strong></td>
                                                <td><?= htmlspecialchars($result['value']) ?></td>
                                                <td><?= htmlspecialchars($result['unit']) ?></td>
                                                <td class="reference-range"><?= $result['reference_range'] ? htmlspecialchars($result['reference_range']) : '-' ?></td>
                                                <td class="evaluation <?= isset($result['evaluation_class']) ? 'eval-' . $result['evaluation_class'] : '' ?>">
                                                    <?= $result['evaluation'] ? htmlspecialchars($result['evaluation']) : '-' ?>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</div>

<?php include __DIR__ . '/add_urine_test_modal.php'; ?>

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

.btn {
    padding: 10px 20px;
    border-radius: 4px;
    text-decoration: none;
    display: inline-block;
    font-size: 14px;
    cursor: pointer;
    border: none;
    transition: all 0.2s;
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
}

.btn-outline-edit:hover {
    background: #27ae60;
    color: white;
}

.btn-primary {
    background: linear-gradient(135deg, #f39c12 0%, #e67e22 100%);
    color: white;
}

.btn-primary:hover {
    background: linear-gradient(135deg, #e67e22 0%, #d35400 100%);
}

.test-section {
    margin-bottom: 40px;
}

.test-section h2 {
    color: #f39c12;
    margin-bottom: 20px;
    font-size: 24px;
    border-bottom: 2px solid #f39c12;
    padding-bottom: 10px;
}

.test-card {
    background: white;
    border-radius: 8px;
    padding: 20px;
    margin-bottom: 20px;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
    border-left: 4px solid #f39c12;
}

.test-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 15px;
    padding-bottom: 15px;
    border-bottom: 1px solid #ecf0f1;
}

.test-notes {
    background: #fef5e7;
    padding: 10px 15px;
    border-radius: 4px;
    margin-bottom: 15px;
    border-left: 3px solid #f39c12;
}

.badge-source {
    background: #f39c12;
    color: white;
    padding: 4px 12px;
    border-radius: 12px;
    font-size: 12px;
    font-weight: 600;
}

.results-section {
    margin-top: 20px;
}

.results-section h3 {
    color: #f39c12;
    font-size: 18px;
    margin-bottom: 10px;
    font-weight: 600;
}

.results-table-wrapper {
    overflow-x: auto;
    border-radius: 8px;
    border: 1px solid #ecf0f1;
    margin-bottom: 15px;
}

.results-table {
    width: 100%;
    border-collapse: collapse;
}

.results-table thead {
    background: linear-gradient(135deg, #f39c12 0%, #e67e22 100%);
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
    border-bottom: 1px solid #ecf0f1;
}

.results-table tbody tr:last-child td {
    border-bottom: none;
}

.results-table tbody tr:hover {
    background: #fef5e7;
}

.alert {
    padding: 15px 20px;
    border-radius: 8px;
    margin-bottom: 20px;
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

.alert-info {
    background: #fef5e7;
    border: 1px solid #f39c12;
    color: #7f6007;
}

.reference-selector {
    background: white;
    padding: 20px;
    border-radius: 8px;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
    margin-bottom: 30px;
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.reference-selector label {
    font-weight: 600;
    color: #2c3e50;
}

.reference-selector .form-control {
    width: 200px;
    padding: 8px 12px;
    border: 1px solid #ddd;
    border-radius: 4px;
    font-size: 14px;
}

.reference-selector .form-control:focus {
    outline: none;
    border-color: #f39c12;
}

.eval-low {
    color: #3498db;
    font-weight: bold;
}

.eval-ok {
    color: #27ae60;
    font-weight: bold;
}

.eval-high {
    color: #e74c3c;
    font-weight: bold;
}

.eval-abnormal {
    color: #e67e22;
    font-weight: bold;
}
</style>

<script>
function showAddTestModal() {
    document.getElementById('addTestModal').style.display = 'block';
}

function closeAddTestModal() {
    document.getElementById('addTestModal').style.display = 'none';
    document.getElementById('addTestForm').reset();
}

// Close modal when clicking outside
window.onclick = function(event) {
    const modal = document.getElementById('addTestModal');
    if (event.target == modal) {
        closeAddTestModal();
    }
}

function updateReferenceRanges() {
    // Placeholder function for updating reference ranges
    // This will be implemented later with actual reference data
    console.log('Reference source changed to:', document.getElementById('referenceSource').value);
}
</script>
