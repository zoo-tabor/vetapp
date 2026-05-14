<?php
require_once __DIR__ . '/../../core/Database.php';
?>
<div class="container">
    <div class="breadcrumb">
        <a href="/warehouse">Sklad</a> /
        <span><?= htmlspecialchars($workplace['name']) ?></span>
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
            <h1><?= htmlspecialchars($workplace['name']) ?></h1>
            <p>Správa zásob krmiv a léčiv</p>
        </div>
        <div style="display: flex; gap: 10px;">
            <?php if (!isset($isCentral) || !$isCentral): ?>
                <?php if ($hasEditPermission ?? false): ?>
                    <a href="/warehouse/inventory/<?= $workplace['id'] ?>" class="btn btn-outline">📋 Inventura</a>
                <?php endif; ?>
            <?php endif; ?>
            <button class="btn btn-primary" onclick="showAddItemModal()">+ Přidat položku</button>
        </div>
    </div>

    <!-- Alerts Section -->
    <?php if (!empty($lowStockItems) || !empty($expiringItems)): ?>
        <div class="alerts-section">
            <?php if (!empty($lowStockItems)): ?>
                <div class="alert alert-warning alert-collapsible">
                    <button class="alert-toggle" onclick="toggleAlert('lowstock')" type="button">
                        <strong>⚠️ Nízké stavy zásob (<?= count($lowStockItems) ?>)</strong>
                        <span class="alert-toggle-icon" id="lowstock-icon">▼</span>
                    </button>
                    <ul id="lowstock-list" style="display:none; margin-top:10px;">
                        <?php foreach ($lowStockItems as $item): ?>
                            <li>
                                <a href="/warehouse/item/<?= $item['id'] ?>">
                                    <?= htmlspecialchars($item['name']) ?>
                                </a>
                                <?php if (isset($isCentral) && $isCentral && !empty($item['workplace_name'])): ?>
                                    (<?= htmlspecialchars($item['workplace_name']) ?>)
                                <?php endif; ?>
                                - aktuálně <?= number_format($item['current_stock'], 2, ',', ' ') ?> <?= htmlspecialchars($item['unit']) ?>
                                (minimum: <?= number_format($item['min_stock_level'], 2, ',', ' ') ?> <?= htmlspecialchars($item['unit']) ?>)
                            </li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            <?php endif; ?>

            <?php if (!empty($expiringItems)): ?>
                <div class="alert alert-danger alert-collapsible">
                    <button class="alert-toggle" onclick="toggleAlert('expiring')" type="button">
                        <strong>🔔 Blížící se expirace (<?= count($expiringItems) ?> šarží)</strong>
                        <span class="alert-toggle-icon" id="expiring-icon">▼</span>
                    </button>
                    <ul id="expiring-list" style="display:none; margin-top:10px;">
                        <?php foreach ($expiringItems as $batch): ?>
                            <li>
                                <?= htmlspecialchars($batch['name']) ?>
                                <?php if (isset($isCentral) && $isCentral && !empty($batch['workplace_name'])): ?>
                                    (<?= htmlspecialchars($batch['workplace_name']) ?>)
                                <?php endif; ?>
                                <?php if ($batch['batch_number']): ?>
                                    - šarže: <?= htmlspecialchars($batch['batch_number']) ?>
                                <?php endif; ?>
                                - expirace: <?= date('d.m.Y', strtotime($batch['expiration_date'])) ?>
                                - množství: <?= number_format($batch['quantity'], 2, ',', ' ') ?>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            <?php endif; ?>
        </div>
    <?php endif; ?>

    <!-- Tabs for Food, Medicaments, and Inactive -->
    <?php
    $activeItems   = array_filter($items, fn($i) => ($i['is_active'] ?? 1) == 1);
    $inactiveItems = array_filter($items, fn($i) => ($i['is_active'] ?? 1) == 0);

    $weeksLabel = function($weeks) {
        $w = (int) round($weeks);
        if ($w === 1) return '1 týden';
        if ($w >= 2 && $w <= 4) return "$w týdny";
        return "$w týdnů";
    };
    ?>
    <div class="tabs">
        <button class="tab active" onclick="switchTab('food')">🌾 Krmiva</button>
        <button class="tab" onclick="switchTab('medicament')">💊 Léčiva</button>
        <?php if (!empty($inactiveItems)): ?>
        <button class="tab" onclick="switchTab('inactive')">📦 Neaktivní (<?= count($inactiveItems) ?>)</button>
        <?php endif; ?>
    </div>

    <!-- Food Items -->
    <div id="food-content" class="tab-content active">
        <h2>Krmiva</h2>
        <?php
        $foodItems = array_filter($activeItems, function($item) { return $item['category'] === 'food'; });
        if (empty($foodItems)):
        ?>
            <div class="alert alert-info">Žádná krmiva v zásobách.</div>
        <?php else: ?>
            <div class="items-table-wrapper">
                <table class="items-table" id="food-table">
                    <thead>
                        <tr>
                            <th onclick="sortTable('food-table', 0)" class="sortable" style="width: 50px;">
                                # <span class="sort-arrow">⇅</span>
                            </th>
                            <th onclick="sortTable('food-table', 1)" class="sortable">
                                Název <span class="sort-arrow">⇅</span>
                            </th>
                            <?php if (isset($isCentral) && $isCentral): ?>
                                <th onclick="sortTable('food-table', 2)" class="sortable">
                                    Pracoviště <span class="sort-arrow">⇅</span>
                                </th>
                            <?php endif; ?>
                            <th onclick="sortTable('food-table', <?= isset($isCentral) && $isCentral ? '3' : '2' ?>)" class="sortable">
                                Sklad <span class="sort-arrow">⇅</span>
                            </th>
                            <th onclick="sortTable('food-table', <?= isset($isCentral) && $isCentral ? '4' : '3' ?>)" class="sortable">
                                Jednotka <span class="sort-arrow">⇅</span>
                            </th>
                            <th onclick="sortTable('food-table', <?= isset($isCentral) && $isCentral ? '5' : '4' ?>)" class="sortable">
                                Min/Max <span class="sort-arrow">⇅</span>
                            </th>
                            <th onclick="sortTable('food-table', <?= isset($isCentral) && $isCentral ? '6' : '5' ?>)" class="sortable">
                                Týdenní spotřeba <span class="sort-arrow">⇅</span>
                            </th>
                            <th onclick="sortTable('food-table', <?= isset($isCentral) && $isCentral ? '7' : '6' ?>)" class="sortable">
                                Dodavatel <span class="sort-arrow">⇅</span>
                            </th>
                            <th>Aktuální zásoba</th>
                            <th>Akce</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($foodItems as $item): ?>
                            <?php
                            $isLowStock = $item['min_stock_level'] !== null && $item['current_stock'] <= $item['min_stock_level'];
                            $stockClass = $isLowStock ? 'low-stock' : '';
                            ?>
                            <tr class="<?= $stockClass ?>">
                                <td><?= htmlspecialchars($item['item_code'] ?? $item['id']) ?></td>
                                <td>
                                    <strong><a href="/warehouse/item/<?= $item['id'] ?>"><?= htmlspecialchars($item['name']) ?></a></strong>
                                    <?php if ($item['storage_location']): ?>
                                        <br><small>📍 <?= htmlspecialchars($item['storage_location']) ?></small>
                                    <?php endif; ?>
                                </td>
                                <?php if (isset($isCentral) && $isCentral): ?>
                                    <td><?= htmlspecialchars($item['workplace_name'] ?? '-') ?></td>
                                <?php endif; ?>
                                <td class="stock-cell">
                                    <?= number_format($item['current_stock'], 2, ',', ' ') ?>
                                </td>
                                <td><?= htmlspecialchars($item['unit']) ?></td>
                                <td>
                                    <?php if ($item['min_stock_level'] !== null): ?>
                                        <?= number_format($item['min_stock_level'], 0, ',', ' ') ?>
                                    <?php else: ?>
                                        -
                                    <?php endif; ?>
                                    /
                                    <?php if ($item['max_stock_level'] !== null): ?>
                                        <?= number_format($item['max_stock_level'], 0, ',', ' ') ?>
                                    <?php else: ?>
                                        -
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if ($item['weekly_consumption'] !== null): ?>
                                        <?= number_format($item['weekly_consumption'], 2, ',', ' ') ?> <?= htmlspecialchars($item['unit']) ?>/týden
                                    <?php else: ?>
                                        <button class="btn-link" onclick="showConsumptionModal(<?= $item['id'] ?>, '<?= htmlspecialchars($item['name']) ?>')">Nastavit</button>
                                    <?php endif; ?>
                                </td>
                                <td><?= htmlspecialchars($item['supplier'] ?? '-') ?></td>
                                <td>
                                    <?php if (!empty($item['weekly_consumption']) && $item['weekly_consumption'] > 0): ?>
                                        <?php
                                        $weeks = $item['current_stock'] / $item['weekly_consumption'];
                                        $desired = $item['desired_weeks_stock'] ?? 4;
                                        $wColor = $weeks >= $desired ? '#27ae60' : ($weeks >= 2 ? '#e67e22' : '#e74c3c');
                                        ?>
                                        <span style="color:<?= $wColor ?>; font-weight:600;"><?= $weeksLabel($weeks) ?></span>
                                    <?php else: ?>
                                        —
                                    <?php endif; ?>
                                </td>
                                <td class="actions-cell">
                                    <button class="btn btn-sm btn-success" onclick="showMovementModal(<?= $item['id'] ?>, '<?= htmlspecialchars($item['name']) ?>', 'in')">➕</button>
                                    <button class="btn btn-sm btn-warning" onclick="showMovementModal(<?= $item['id'] ?>, '<?= htmlspecialchars($item['name']) ?>', 'out')">➖</button>
                                    <a href="/warehouse/item/<?= $item['id'] ?>" class="btn btn-sm btn-outline">📊</a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>

    <!-- Medicament Items -->
    <div id="medicament-content" class="tab-content">
        <h2>Léčiva</h2>
        <?php
        $medicamentItems = array_filter($activeItems, function($item) { return $item['category'] === 'medicament'; });
        if (empty($medicamentItems)):
        ?>
            <div class="alert alert-info">Žádná léčiva v zásobách.</div>
        <?php else: ?>
            <div class="items-table-wrapper">
                <table class="items-table" id="medicament-table">
                    <thead>
                        <tr>
                            <th onclick="sortTable('medicament-table', 0)" class="sortable" style="width: 50px;">
                                # <span class="sort-arrow">⇅</span>
                            </th>
                            <th onclick="sortTable('medicament-table', 1)" class="sortable">
                                Název <span class="sort-arrow">⇅</span>
                            </th>
                            <?php if (isset($isCentral) && $isCentral): ?>
                                <th onclick="sortTable('medicament-table', 2)" class="sortable">
                                    Pracoviště <span class="sort-arrow">⇅</span>
                                </th>
                            <?php endif; ?>
                            <th onclick="sortTable('medicament-table', <?= isset($isCentral) && $isCentral ? '3' : '2' ?>)" class="sortable">
                                Sklad <span class="sort-arrow">⇅</span>
                            </th>
                            <th onclick="sortTable('medicament-table', <?= isset($isCentral) && $isCentral ? '4' : '3' ?>)" class="sortable">
                                Jednotka <span class="sort-arrow">⇅</span>
                            </th>
                            <th onclick="sortTable('medicament-table', <?= isset($isCentral) && $isCentral ? '5' : '4' ?>)" class="sortable">
                                Min/Max <span class="sort-arrow">⇅</span>
                            </th>
                            <th onclick="sortTable('medicament-table', <?= isset($isCentral) && $isCentral ? '6' : '5' ?>)" class="sortable">
                                Týdenní spotřeba <span class="sort-arrow">⇅</span>
                            </th>
                            <th onclick="sortTable('medicament-table', <?= isset($isCentral) && $isCentral ? '7' : '6' ?>)" class="sortable">
                                Dodavatel <span class="sort-arrow">⇅</span>
                            </th>
                            <th>Aktuální zásoba</th>
                            <th>Akce</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($medicamentItems as $item): ?>
                            <?php
                            $isLowStock = $item['min_stock_level'] !== null && $item['current_stock'] <= $item['min_stock_level'];
                            $stockClass = $isLowStock ? 'low-stock' : '';
                            ?>
                            <tr class="<?= $stockClass ?>">
                                <td><?= htmlspecialchars($item['item_code'] ?? $item['id']) ?></td>
                                <td>
                                    <strong><a href="/warehouse/item/<?= $item['id'] ?>"><?= htmlspecialchars($item['name']) ?></a></strong>
                                    <?php if ($item['storage_location']): ?>
                                        <br><small>📍 <?= htmlspecialchars($item['storage_location']) ?></small>
                                    <?php endif; ?>
                                </td>
                                <?php if (isset($isCentral) && $isCentral): ?>
                                    <td><?= htmlspecialchars($item['workplace_name'] ?? '-') ?></td>
                                <?php endif; ?>
                                <td class="stock-cell">
                                    <?= number_format($item['current_stock'], 2, ',', ' ') ?>
                                </td>
                                <td><?= htmlspecialchars($item['unit']) ?></td>
                                <td>
                                    <?php if ($item['min_stock_level'] !== null): ?>
                                        <?= number_format($item['min_stock_level'], 0, ',', ' ') ?>
                                    <?php else: ?>
                                        -
                                    <?php endif; ?>
                                    /
                                    <?php if ($item['max_stock_level'] !== null): ?>
                                        <?= number_format($item['max_stock_level'], 0, ',', ' ') ?>
                                    <?php else: ?>
                                        -
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if ($item['weekly_consumption'] !== null): ?>
                                        <?= number_format($item['weekly_consumption'], 2, ',', ' ') ?> <?= htmlspecialchars($item['unit']) ?>/týden
                                    <?php else: ?>
                                        <button class="btn-link" onclick="showConsumptionModal(<?= $item['id'] ?>, '<?= htmlspecialchars($item['name']) ?>')">Nastavit</button>
                                    <?php endif; ?>
                                </td>
                                <td><?= htmlspecialchars($item['supplier'] ?? '-') ?></td>
                                <td>
                                    <?php if (!empty($item['weekly_consumption']) && $item['weekly_consumption'] > 0): ?>
                                        <?php
                                        $weeks = $item['current_stock'] / $item['weekly_consumption'];
                                        $desired = $item['desired_weeks_stock'] ?? 4;
                                        $wColor = $weeks >= $desired ? '#27ae60' : ($weeks >= 2 ? '#e67e22' : '#e74c3c');
                                        ?>
                                        <span style="color:<?= $wColor ?>; font-weight:600;"><?= $weeksLabel($weeks) ?></span>
                                    <?php else: ?>
                                        —
                                    <?php endif; ?>
                                </td>
                                <td class="actions-cell">
                                    <button class="btn btn-sm btn-success" onclick="showMovementModal(<?= $item['id'] ?>, '<?= htmlspecialchars($item['name']) ?>', 'in')">➕</button>
                                    <button class="btn btn-sm btn-warning" onclick="showMovementModal(<?= $item['id'] ?>, '<?= htmlspecialchars($item['name']) ?>', 'out')">➖</button>
                                    <a href="/warehouse/item/<?= $item['id'] ?>" class="btn btn-sm btn-outline">📊</a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>

    <!-- Inactive Items Tab -->
    <?php if (!empty($inactiveItems)): ?>
    <div id="inactive-content" class="tab-content">
        <h2>Neaktivní položky</h2>
        <div class="items-table-wrapper">
            <table class="items-table" id="inactive-table">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Název</th>
                        <th>Kategorie</th>
                        <th>Sklad</th>
                        <th>Jednotka</th>
                        <th>Akce</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($inactiveItems as $item): ?>
                        <tr style="opacity: 0.65;">
                            <td><?= htmlspecialchars($item['item_code'] ?? $item['id']) ?></td>
                            <td>
                                <strong><a href="/warehouse/item/<?= $item['id'] ?>"><?= htmlspecialchars($item['name']) ?></a></strong>
                            </td>
                            <td><?= $item['category'] === 'food' ? '🌾 Krmivo' : '💊 Léčivo' ?></td>
                            <td><?= number_format($item['current_stock'], 2, ',', ' ') ?></td>
                            <td><?= htmlspecialchars($item['unit']) ?></td>
                            <td class="actions-cell">
                                <a href="/warehouse/item/<?= $item['id'] ?>" class="btn btn-sm btn-outline">📊 Detail</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
    <?php endif; ?>
</div>

<?php require __DIR__ . '/_modals.php'; ?>

<style>
.container {
    max-width: 1600px;
    margin: 0 auto;
    padding: 20px;
}

.breadcrumb {
    margin-bottom: 15px;
    color: #7f8c8d;
    font-size: 14px;
}

.alert-collapsible {
    padding: 0;
}

.alert-toggle {
    width: 100%;
    background: none;
    border: none;
    padding: 12px 16px;
    text-align: left;
    cursor: pointer;
    display: flex;
    justify-content: space-between;
    align-items: center;
    font-size: inherit;
    color: inherit;
}

.alert-toggle:hover {
    opacity: 0.85;
}

.alert-toggle-icon {
    font-size: 12px;
    transition: transform 0.2s;
}

.alert-toggle-icon.open {
    transform: rotate(180deg);
}

.alert-collapsible ul {
    padding: 0 16px 12px 32px;
    margin: 0;
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

.page-header p {
    margin: 0;
    color: #7f8c8d;
    font-size: 16px;
}

.alerts-section {
    margin-bottom: 30px;
}

.alert {
    padding: 15px 20px;
    border-radius: 8px;
    margin-bottom: 15px;
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

.alert-warning {
    background-color: #fff3cd;
    border: 1px solid #ffeaa7;
    color: #856404;
}

.alert-danger {
    background-color: #f8d7da;
    border: 1px solid #f5c6cb;
    color: #721c24;
}

.alert ul {
    margin: 10px 0 0 0;
    padding-left: 20px;
}

.alert li {
    margin-bottom: 5px;
}

.alert a {
    color: inherit;
    text-decoration: underline;
}

.tabs {
    display: flex;
    gap: 10px;
    margin-bottom: 20px;
    border-bottom: 2px solid #ecf0f1;
}

.tab {
    padding: 12px 24px;
    border: none;
    background: transparent;
    cursor: pointer;
    font-size: 16px;
    font-weight: 600;
    color: #7f8c8d;
    border-bottom: 3px solid transparent;
    transition: all 0.2s;
}

.tab:hover {
    color: #27ae60;
}

.tab.active {
    color: #27ae60;
    border-bottom-color: #27ae60;
}

.tab-content {
    display: none;
}

.tab-content.active {
    display: block;
}

.tab-content h2 {
    margin: 0 0 20px 0;
    color: #2c3e50;
    font-size: 22px;
}

.items-table-wrapper {
    background: white;
    border-radius: 8px;
    overflow: hidden;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
}

.items-table {
    width: 100%;
    border-collapse: collapse;
}

.items-table thead {
    background: linear-gradient(135deg, #27ae60 0%, #229954 100%);
    color: white;
}

.items-table th {
    padding: 15px;
    text-align: left;
    font-weight: 600;
    font-size: 14px;
}

.items-table td {
    padding: 12px 15px;
    border-bottom: 1px solid #ecf0f1;
}

.items-table tbody tr:hover {
    background-color: #f8f9fa;
}

.items-table tbody tr.low-stock {
    background-color: #fff3cd;
}

.items-table tbody tr.low-stock:hover {
    background-color: #ffe8a1;
}

.stock-cell {
    font-weight: 600;
    font-size: 16px;
    color: #27ae60;
}

.items-table a {
    color: #27ae60;
    text-decoration: none;
    transition: color 0.2s;
}

.items-table a:hover {
    color: #229954;
    text-decoration: underline;
}

.low-stock .stock-cell {
    color: #e67e22;
}

.actions-cell {
    display: flex;
    gap: 5px;
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

.btn-primary {
    background: linear-gradient(135deg, #27ae60 0%, #229954 100%);
    color: white;
}

.btn-primary:hover {
    background: linear-gradient(135deg, #229954 0%, #1e8449 100%);
    transform: translateY(-1px);
    box-shadow: 0 4px 12px rgba(39, 174, 96, 0.3);
}

.btn-sm {
    padding: 6px 12px;
    font-size: 13px;
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

.btn-link {
    background: none;
    border: none;
    color: #3498db;
    cursor: pointer;
    text-decoration: underline;
    padding: 0;
    font-size: 14px;
}

.btn-link:hover {
    color: #2980b9;
}

.sortable {
    cursor: pointer;
    user-select: none;
    position: relative;
    transition: background-color 0.2s;
}

.sortable:hover {
    background-color: rgba(255, 255, 255, 0.1);
}

.sort-arrow {
    font-size: 12px;
    margin-left: 5px;
    opacity: 0.5;
}

.sortable.sorted-asc .sort-arrow::after {
    content: ' ▲';
    opacity: 1;
}

.sortable.sorted-desc .sort-arrow::after {
    content: ' ▼';
    opacity: 1;
}
</style>

<script>
function toggleAlert(key) {
    const list = document.getElementById(key + '-list');
    const icon = document.getElementById(key + '-icon');
    const isOpen = list.style.display !== 'none';
    list.style.display = isOpen ? 'none' : 'block';
    icon.classList.toggle('open', !isOpen);
}

function switchTab(category) {
    // Update tab buttons
    document.querySelectorAll('.tab').forEach(tab => {
        tab.classList.remove('active');
    });
    event.target.classList.add('active');

    // Update content
    document.querySelectorAll('.tab-content').forEach(content => {
        content.classList.remove('active');
    });
    document.getElementById(category + '-content').classList.add('active');
}

function sortTable(tableId, columnIndex) {
    const table = document.getElementById(tableId);
    const tbody = table.querySelector('tbody');
    const rows = Array.from(tbody.querySelectorAll('tr'));
    const header = table.querySelectorAll('th')[columnIndex];

    // Determine sort direction
    let isAscending = true;
    if (header.classList.contains('sorted-asc')) {
        isAscending = false;
    }

    // Remove sorting classes from all headers
    table.querySelectorAll('th').forEach(th => {
        th.classList.remove('sorted-asc', 'sorted-desc');
    });

    // Add appropriate class to current header
    header.classList.add(isAscending ? 'sorted-asc' : 'sorted-desc');

    // Sort rows
    rows.sort((a, b) => {
        const cellA = a.querySelectorAll('td')[columnIndex];
        const cellB = b.querySelectorAll('td')[columnIndex];

        // Get text content, handling links and nested elements
        let valueA = cellA.textContent.trim();
        let valueB = cellB.textContent.trim();

        // Try to parse as numbers (for stock, min/max, consumption columns)
        const numA = parseFloat(valueA.replace(/[^\d.,-]/g, '').replace(',', '.'));
        const numB = parseFloat(valueB.replace(/[^\d.,-]/g, '').replace(',', '.'));

        let comparison = 0;

        if (!isNaN(numA) && !isNaN(numB)) {
            // Numeric comparison
            comparison = numA - numB;
        } else {
            // String comparison
            comparison = valueA.localeCompare(valueB, 'cs');
        }

        return isAscending ? comparison : -comparison;
    });

    // Reorder rows in DOM
    rows.forEach(row => tbody.appendChild(row));
}
</script>
