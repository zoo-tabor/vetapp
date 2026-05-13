<div class="container">
    <div class="breadcrumb">
        <a href="/warehouse">Sklad</a> /
        <?php if ($item['workplace_id']): ?>
            <a href="/warehouse/workplace/<?= $item['workplace_id'] ?>"><?= htmlspecialchars($item['workplace_name']) ?></a>
        <?php else: ?>
            <a href="/warehouse/central">Centrální sklad</a>
        <?php endif; ?>
        / <span><?= htmlspecialchars($item['name']) ?></span>
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
            <h1><?= htmlspecialchars($item['name']) ?></h1>
            <p>
                <span class="badge badge-<?= $item['category'] === 'food' ? 'food' : 'medicament' ?>">
                    <?= $item['category'] === 'food' ? '🌾 Krmivo' : '💊 Léčivo' ?>
                </span>
                <?php if (!empty($item['item_code'])): ?>
                    <span class="badge badge-item-code">
                        #<?= htmlspecialchars($item['item_code']) ?>
                    </span>
                <?php endif; ?>
            </p>
        </div>
        <div style="display: flex; gap: 10px;">
            <button class="btn btn-primary" onclick="showEditItemModal()">
                ✏️ Upravit
            </button>
            <button class="btn btn-success" onclick="showMovementModal(<?= $item['id'] ?>, '<?= htmlspecialchars($item['name']) ?>', 'in')">
                ➕ Příjem
            </button>
            <button class="btn btn-warning" onclick="showMovementModal(<?= $item['id'] ?>, '<?= htmlspecialchars($item['name']) ?>', 'out')">
                ➖ Výdej
            </button>
            <button class="btn btn-outline" onclick="showMovementModal(<?= $item['id'] ?>, '<?= htmlspecialchars($item['name']) ?>', 'adjustment')">
                📝 Inventura
            </button>
        </div>
    </div>

    <!-- Item Info Cards -->
    <div class="info-cards">
        <div class="info-card">
            <div class="info-label">Aktuální stav</div>
            <div class="info-value <?= ($item['min_stock_level'] && $item['current_stock'] <= $item['min_stock_level']) ? 'low-stock' : '' ?>">
                <?= number_format($item['current_stock'], 2, ',', ' ') ?> <?= htmlspecialchars($item['unit']) ?>
            </div>
        </div>

        <div class="info-card">
            <div class="info-label">Min / Max stav</div>
            <div class="info-value">
                <?= $item['min_stock_level'] ? number_format($item['min_stock_level'], 0, ',', ' ') : '-' ?>
                /
                <?= $item['max_stock_level'] ? number_format($item['max_stock_level'], 0, ',', ' ') : '-' ?>
                <?= htmlspecialchars($item['unit']) ?>
            </div>
        </div>

        <div class="info-card">
            <div class="info-label">Dodavatel</div>
            <div class="info-value">
                <?= htmlspecialchars($item['supplier'] ?? '-') ?>
            </div>
        </div>

        <div class="info-card">
            <div class="info-label">Uložení</div>
            <div class="info-value">
                <?= htmlspecialchars($item['storage_location'] ?? '-') ?>
            </div>
        </div>
    </div>

    <!-- Consumption Planning Section -->
    <div class="section">
        <div class="section-header">
            <h2>📊 Plánování spotřeby</h2>
            <button class="btn btn-sm btn-primary" onclick="showConsumptionModal(<?= $item['id'] ?>, '<?= htmlspecialchars($item['name']) ?>')">
                Upravit spotřebu
            </button>
        </div>

        <div class="consumption-grid">
            <!-- Manual Consumption -->
            <div class="consumption-card">
                <h3>Plánovaná spotřeba (manuální)</h3>
                <?php if ($item['weekly_consumption']): ?>
                    <div class="stat-row">
                        <span class="stat-label">Týdenní spotřeba:</span>
                        <span class="stat-value"><?= number_format($item['weekly_consumption'], 2, ',', ' ') ?> <?= htmlspecialchars($item['unit']) ?>/týden</span>
                    </div>
                    <div class="stat-row">
                        <span class="stat-label">Požadovaná zásoba:</span>
                        <span class="stat-value"><?= $item['desired_weeks_stock'] ?> týdnů</span>
                    </div>
                    <div class="stat-row">
                        <span class="stat-label">Cílové množství:</span>
                        <span class="stat-value highlight">
                            <?= number_format($item['weekly_consumption'] * $item['desired_weeks_stock'], 2, ',', ' ') ?> <?= htmlspecialchars($item['unit']) ?>
                        </span>
                    </div>
                    <div class="stat-row">
                        <span class="stat-label">Potřeba nakoupit:</span>
                        <?php
                        $targetStock = $item['weekly_consumption'] * $item['desired_weeks_stock'];
                        $needToBuy = max(0, $targetStock - $item['current_stock']);
                        $buyClass = $needToBuy > 0 ? 'need-buy' : 'sufficient';
                        ?>
                        <span class="stat-value <?= $buyClass ?>">
                            <?= number_format($needToBuy, 2, ',', ' ') ?> <?= htmlspecialchars($item['unit']) ?>
                        </span>
                    </div>
                    <?php if ($item['consumption_notes']): ?>
                        <div class="notes">
                            <strong>Poznámka:</strong> <?= nl2br(htmlspecialchars($item['consumption_notes'])) ?>
                        </div>
                    <?php endif; ?>
                <?php else: ?>
                    <p class="no-data">Týdenní spotřeba není nastavena.</p>
                    <button class="btn btn-sm btn-outline" onclick="showConsumptionModal(<?= $item['id'] ?>, '<?= htmlspecialchars($item['name']) ?>')">
                        Nastavit spotřebu
                    </button>
                <?php endif; ?>
            </div>

            <!-- Actual Consumption -->
            <div class="consumption-card">
                <h3>Skutečná spotřeba (posledních 8 týdnů)</h3>
                <?php if ($actualWeeklyConsumption > 0): ?>
                    <div class="stat-row">
                        <span class="stat-label">Průměrná týdenní spotřeba:</span>
                        <span class="stat-value"><?= number_format($actualWeeklyConsumption, 2, ',', ' ') ?> <?= htmlspecialchars($item['unit']) ?>/týden</span>
                    </div>
                    <?php if ($item['weekly_consumption']): ?>
                        <?php
                        $difference = $actualWeeklyConsumption - $item['weekly_consumption'];
                        $percentDiff = ($item['weekly_consumption'] > 0) ? ($difference / $item['weekly_consumption'] * 100) : 0;
                        $diffClass = $difference > 0 ? 'higher' : 'lower';
                        ?>
                        <div class="stat-row">
                            <span class="stat-label">Rozdíl od plánu:</span>
                            <span class="stat-value <?= $diffClass ?>">
                                <?= $difference > 0 ? '+' : '' ?><?= number_format($difference, 2, ',', ' ') ?> <?= htmlspecialchars($item['unit']) ?>
                                (<?= $difference > 0 ? '+' : '' ?><?= number_format($percentDiff, 1, ',', ' ') ?>%)
                            </span>
                        </div>
                        <div class="recommendation">
                            <?php if (abs($percentDiff) > 20): ?>
                                💡 <strong>Doporučení:</strong> Zvažte úpravu plánované spotřeby, skutečná spotřeba se značně liší.
                            <?php else: ?>
                                ✅ Plánovaná spotřeba odpovídá skutečnosti.
                            <?php endif; ?>
                        </div>
                    <?php endif; ?>
                <?php else: ?>
                    <p class="no-data">Nedostatek dat o výdejích za posledních 8 týdnů.</p>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Batches Section -->
    <?php if (!empty($batches)): ?>
        <div class="section">
            <h2>📦 Šarže a expirace</h2>
            <div class="batches-table-wrapper">
                <table class="batches-table">
                    <thead>
                        <tr>
                            <th>Číslo šarže</th>
                            <th>Množství</th>
                            <th>Datum přijetí</th>
                            <th>Expirace</th>
                            <th>Poznámka</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($batches as $batch): ?>
                            <?php
                            $daysToExpiry = null;
                            $expiryClass = '';
                            if ($batch['expiration_date']) {
                                $daysToExpiry = (strtotime($batch['expiration_date']) - time()) / (60 * 60 * 24);
                                if ($daysToExpiry < 0) {
                                    $expiryClass = 'expired';
                                } elseif ($daysToExpiry <= 30) {
                                    $expiryClass = 'expiring-soon';
                                }
                            }
                            ?>
                            <tr class="<?= $expiryClass ?>">
                                <td><?= htmlspecialchars($batch['batch_number'] ?? '-') ?></td>
                                <td><?= number_format($batch['quantity'], 2, ',', ' ') ?> <?= htmlspecialchars($item['unit']) ?></td>
                                <td><?= date('d.m.Y', strtotime($batch['received_date'])) ?></td>
                                <td>
                                    <?php if ($batch['expiration_date']): ?>
                                        <?= date('d.m.Y', strtotime($batch['expiration_date'])) ?>
                                        <?php if ($daysToExpiry !== null): ?>
                                            <br><small>
                                                <?php if ($daysToExpiry < 0): ?>
                                                    ❌ Expirováno před <?= abs(ceil($daysToExpiry)) ?> dny
                                                <?php elseif ($daysToExpiry <= 30): ?>
                                                    ⚠️ Zbývá <?= ceil($daysToExpiry) ?> dní
                                                <?php else: ?>
                                                    ✅ Zbývá <?= ceil($daysToExpiry) ?> dní
                                                <?php endif; ?>
                                            </small>
                                        <?php endif; ?>
                                    <?php else: ?>
                                        -
                                    <?php endif; ?>
                                </td>
                                <td><?= htmlspecialchars($batch['notes'] ?? '-') ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    <?php endif; ?>

    <!-- Movement History Section -->
    <div class="section">
        <h2>📋 Historie pohybů (posledních 50)</h2>
        <?php if (empty($movements)): ?>
            <div class="alert alert-info">Žádné pohyby zásob.</div>
        <?php else: ?>
            <div class="movements-table-wrapper">
                <table class="movements-table">
                    <thead>
                        <tr>
                            <th>Datum</th>
                            <th>Typ</th>
                            <th>Množství</th>
                            <th>Doklad</th>
                            <th>Poznámka</th>
                            <th>Vytvořil</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($movements as $movement): ?>
                            <tr class="movement-<?= $movement['movement_type'] ?>">
                                <td><?= date('d.m.Y', strtotime($movement['movement_date'])) ?></td>
                                <td>
                                    <?php if ($movement['movement_type'] === 'in'): ?>
                                        <span class="badge badge-success">➕ Příjem</span>
                                    <?php elseif ($movement['movement_type'] === 'out'): ?>
                                        <span class="badge badge-warning">➖ Výdej</span>
                                    <?php else: ?>
                                        <span class="badge badge-info">📝 Inventura</span>
                                    <?php endif; ?>
                                </td>
                                <td class="quantity-cell <?= $movement['quantity'] > 0 ? 'positive' : 'negative' ?>">
                                    <?= $movement['quantity'] > 0 ? '+' : '' ?><?= number_format($movement['quantity'], 2, ',', ' ') ?> <?= htmlspecialchars($item['unit']) ?>
                                </td>
                                <td><?= htmlspecialchars($movement['reference_document'] ?? '-') ?></td>
                                <td><?= htmlspecialchars($movement['notes'] ?? '-') ?></td>
                                <td><?= htmlspecialchars($movement['created_by_name'] ?? '-') ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>
</div>

<!-- Edit Item Modal -->
<div id="editItemModal" class="modal">
    <div class="modal-content">
        <div class="modal-header">
            <h2>Upravit položku</h2>
            <span class="close" onclick="closeEditItemModal()">&times;</span>
        </div>
        <div class="modal-body">
            <form method="POST" action="/warehouse/items/update">
                <input type="hidden" name="item_id" value="<?= $item['id'] ?>">
                <input type="hidden" name="redirect" value="<?= $_SERVER['REQUEST_URI'] ?>">

                <div class="form-group">
                    <label for="edit_category">Kategorie *</label>
                    <select id="edit_category" name="category" class="form-control" required>
                        <option value="food" <?= $item['category'] === 'food' ? 'selected' : '' ?>>🌾 Krmivo</option>
                        <option value="medicament" <?= $item['category'] === 'medicament' ? 'selected' : '' ?>>💊 Léčivo</option>
                    </select>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="edit_item_code">Číslo položky *</label>
                        <input type="text" id="edit_item_code" name="item_code" class="form-control" value="<?= htmlspecialchars($item['item_code'] ?? '') ?>" required>
                        <small class="form-help">Unikátní číslo pro snadnou identifikaci</small>
                    </div>
                    <div class="form-group">
                        <label for="edit_unit">Jednotka *</label>
                        <input type="text" id="edit_unit" name="unit" class="form-control" value="<?= htmlspecialchars($item['unit']) ?>" required>
                    </div>
                </div>

                <div class="form-group">
                    <label for="edit_name">Název položky *</label>
                    <input type="text" id="edit_name" name="name" class="form-control" value="<?= htmlspecialchars($item['name']) ?>" required>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="edit_current_stock">Aktuální stav skladu *</label>
                        <input type="number" step="0.01" id="edit_current_stock" name="current_stock" class="form-control" value="<?= $item['current_stock'] ?>" required>
                    </div>
                    <div class="form-group">
                        <label for="edit_min_stock_level">Minimální stav (pro upozornění)</label>
                        <input type="number" step="0.01" id="edit_min_stock_level" name="min_stock_level" class="form-control" value="<?= $item['min_stock_level'] ?? '' ?>">
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="edit_max_stock_level">Maximální stav (cílový)</label>
                        <input type="number" step="0.01" id="edit_max_stock_level" name="max_stock_level" class="form-control" value="<?= $item['max_stock_level'] ?? '' ?>">
                    </div>
                    <div class="form-group">
                        <label for="edit_supplier">Dodavatel</label>
                        <input type="text" id="edit_supplier" name="supplier" class="form-control" value="<?= htmlspecialchars($item['supplier'] ?? '') ?>">
                    </div>
                </div>

                <div class="form-group">
                    <label for="edit_storage_location">Místo uložení</label>
                    <input type="text" id="edit_storage_location" name="storage_location" class="form-control" value="<?= htmlspecialchars($item['storage_location'] ?? '') ?>">
                </div>

                <div class="form-group">
                    <label for="edit_notes">Poznámky</label>
                    <textarea id="edit_notes" name="notes" class="form-control" rows="3"><?= htmlspecialchars($item['notes'] ?? '') ?></textarea>
                </div>

                <div class="form-group">
                    <label style="display:flex; align-items:center; gap:8px; cursor:pointer;">
                        <input type="checkbox" name="is_active" value="1" <?= ($item['is_active'] ?? 1) ? 'checked' : '' ?>>
                        <span>Aktivní položka</span>
                    </label>
                    <small class="form-help">Neaktivní položky se přesunou do záložky „Neaktivní" a nebudou v hlavním seznamu</small>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-outline" onclick="closeEditItemModal()">Zrušit</button>
                    <button type="submit" class="btn btn-primary">Uložit změny</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function showEditItemModal() {
    document.getElementById('editItemModal').style.display = 'block';
}

function closeEditItemModal() {
    document.getElementById('editItemModal').style.display = 'none';
}

// Close modal when clicking outside
window.addEventListener('click', function(event) {
    const modal = document.getElementById('editItemModal');
    if (event.target === modal) {
        closeEditItemModal();
    }
});
</script>

<?php
// Set redirect for modals
$_GET['redirect'] = $_SERVER['REQUEST_URI'];
require __DIR__ . '/_modals.php';
?>

<style>
.container {
    max-width: 1400px;
    margin: 0 auto;
    padding: 20px;
}

.breadcrumb {
    margin-bottom: 15px;
    color: #7f8c8d;
    font-size: 14px;
}

.breadcrumb a {
    color: #27ae60;
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

.info-cards {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 20px;
    margin-bottom: 30px;
}

.info-card {
    background: white;
    border-radius: 8px;
    padding: 20px;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
}

.info-label {
    font-size: 12px;
    color: #7f8c8d;
    text-transform: uppercase;
    margin-bottom: 8px;
    font-weight: 600;
}

.info-value {
    font-size: 20px;
    font-weight: 600;
    color: #2c3e50;
}

.info-value.low-stock {
    color: #e67e22;
}

.section {
    background: white;
    border-radius: 8px;
    padding: 25px;
    margin-bottom: 20px;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
}

.section h2 {
    margin: 0 0 20px 0;
    color: #2c3e50;
    font-size: 20px;
}

.section-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 20px;
}

.section-header h2 {
    margin: 0;
}

.consumption-grid {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 20px;
}

.consumption-card {
    background: #f8f9fa;
    border-radius: 8px;
    padding: 20px;
    border: 2px solid #ecf0f1;
}

.consumption-card h3 {
    margin: 0 0 15px 0;
    color: #2c3e50;
    font-size: 16px;
}

.stat-row {
    display: flex;
    justify-content: space-between;
    padding: 10px 0;
    border-bottom: 1px solid #ecf0f1;
}

.stat-row:last-child {
    border-bottom: none;
}

.stat-label {
    color: #7f8c8d;
    font-size: 14px;
}

.stat-value {
    font-weight: 600;
    color: #2c3e50;
    font-size: 14px;
}

.stat-value.highlight {
    color: #27ae60;
    font-size: 16px;
}

.stat-value.need-buy {
    color: #e67e22;
    font-size: 16px;
}

.stat-value.sufficient {
    color: #27ae60;
}

.stat-value.higher {
    color: #e74c3c;
}

.stat-value.lower {
    color: #3498db;
}

.recommendation {
    margin-top: 15px;
    padding: 12px;
    background: white;
    border-radius: 6px;
    font-size: 13px;
}

.no-data {
    color: #7f8c8d;
    font-style: italic;
    margin: 10px 0;
}

.notes {
    margin-top: 15px;
    padding: 10px;
    background: white;
    border-radius: 6px;
    font-size: 13px;
}

.batches-table-wrapper,
.movements-table-wrapper {
    overflow-x: auto;
}

.batches-table,
.movements-table {
    width: 100%;
    border-collapse: collapse;
}

.batches-table th,
.movements-table th {
    background: #f8f9fa;
    padding: 12px;
    text-align: left;
    font-weight: 600;
    font-size: 13px;
    color: #2c3e50;
    border-bottom: 2px solid #ecf0f1;
}

.batches-table td,
.movements-table td {
    padding: 12px;
    border-bottom: 1px solid #ecf0f1;
    font-size: 14px;
}

.batches-table tr.expiring-soon {
    background-color: #fff3cd;
}

.batches-table tr.expired {
    background-color: #f8d7da;
}

.quantity-cell.positive {
    color: #27ae60;
    font-weight: 600;
}

.quantity-cell.negative {
    color: #e74c3c;
    font-weight: 600;
}

.badge-food {
    background-color: #d4edda;
    color: #155724;
}

.badge-medicament {
    background-color: #d1ecf1;
    color: #0c5460;
}

.alert {
    padding: 15px 20px;
    border-radius: 8px;
    margin-bottom: 20px;
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

.alert-info {
    background-color: #d1ecf1;
    border: 1px solid #bee5eb;
    color: #0c5460;
}

.badge {
    display: inline-block;
    padding: 4px 10px;
    border-radius: 12px;
    font-size: 12px;
    font-weight: 600;
}

.badge-success {
    background-color: #d4edda;
    color: #155724;
}

.badge-warning {
    background-color: #fff3cd;
    color: #856404;
}

.badge-info {
    background-color: #d1ecf1;
    color: #0c5460;
}

.badge-item-code {
    background-color: #2c3e50;
    color: white;
    margin-left: 8px;
    font-weight: 600;
    font-size: 13px;
}

.btn {
    padding: 10px 20px;
    border-radius: 6px;
    border: none;
    cursor: pointer;
    font-size: 14px;
    font-weight: 600;
    transition: all 0.2s;
}

.btn-sm {
    padding: 6px 12px;
    font-size: 13px;
}

.btn-primary {
    background: linear-gradient(135deg, #27ae60 0%, #229954 100%);
    color: white;
}

.btn-primary:hover {
    background: linear-gradient(135deg, #229954 0%, #1e8449 100%);
    transform: translateY(-1px);
    box-shadow: 0 4px 12px rgba(39, 174, 96, 0.3);
}

.btn-success {
    background-color: #27ae60;
    color: white;
}

.btn-success:hover {
    background-color: #229954;
}

.btn-warning {
    background-color: #f39c12;
    color: white;
}

.btn-warning:hover {
    background-color: #e67e22;
}

.btn-outline {
    background: white;
    border: 2px solid #27ae60;
    color: #27ae60;
}

.btn-outline:hover {
    background: #27ae60;
    color: white;
}

@media (max-width: 768px) {
    .consumption-grid {
        grid-template-columns: 1fr;
    }
}
</style>
