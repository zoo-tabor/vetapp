<div class="container">
    <div class="breadcrumb">
        <a href="/warehouse">Sklad</a> /
        <a href="/warehouse/workplace/<?= $workplace['id'] ?>"><?= htmlspecialchars($workplace['name']) ?></a> /
        <span>Inventura</span>
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
            <h1>📋 Inventura - <?= htmlspecialchars($workplace['name']) ?></h1>
            <p>Fyzická kontrola zásob a úprava stavů</p>
        </div>
        <div style="display: flex; gap: 10px;">
            <a href="/warehouse/workplace/<?= $workplace['id'] ?>" class="btn btn-outline">← Zpět</a>
            <button class="btn btn-primary" onclick="saveAllChanges()">💾 Uložit změny</button>
        </div>
    </div>

    <form id="inventoryForm" method="POST" action="/warehouse/inventory/save">
        <input type="hidden" name="workplace_id" value="<?= $workplace['id'] ?>">

        <!-- Inventory Date -->
        <div class="inventory-date-section">
            <label for="inventory_date" class="date-label">
                <strong>📅 Datum inventury:</strong>
            </label>
            <input
                type="date"
                id="inventory_date"
                name="inventory_date"
                class="date-input"
                value="<?= date('Y-m-d') ?>"
                max="<?= date('Y-m-d') ?>"
                required
            >
            <small style="color: #7f8c8d; margin-left: 10px;">Datum, kdy byla fyzicky provedena inventura</small>
        </div>

        <!-- Tabs for Food and Medicaments -->
        <div class="tabs">
            <button type="button" class="tab active" onclick="switchTab('food')">🌾 Krmiva</button>
            <button type="button" class="tab" onclick="switchTab('medicament')">💊 Léčiva</button>
        </div>

        <!-- Food Items -->
        <div id="food-content" class="tab-content active">
            <h2>Krmiva</h2>
            <?php
            $foodItems = array_filter($items, function($item) { return $item['category'] === 'food'; });
            if (empty($foodItems)):
            ?>
                <div class="alert alert-info">Žádná krmiva v zásobách.</div>
            <?php else: ?>
                <div class="inventory-table-wrapper">
                    <table class="inventory-table" id="food-inventory-table">
                        <thead>
                            <tr>
                                <th onclick="sortInventoryTable('food-inventory-table', 0)" class="sortable" style="width: 50px;">
                                    # <span class="sort-arrow">⇅</span>
                                </th>
                                <th onclick="sortInventoryTable('food-inventory-table', 1)" class="sortable">
                                    Název položky <span class="sort-arrow">⇅</span>
                                </th>
                                <th style="width: 200px;">Nový stav</th>
                                <th onclick="sortInventoryTable('food-inventory-table', 3)" class="sortable" style="width: 200px;">
                                    Aktuální stav <span class="sort-arrow">⇅</span>
                                </th>
                                <th onclick="sortInventoryTable('food-inventory-table', 4)" class="sortable" style="width: 150px;">
                                    Jednotka <span class="sort-arrow">⇅</span>
                                </th>
                                <th onclick="sortInventoryTable('food-inventory-table', 5)" class="sortable" style="width: 120px;">
                                    Změna <span class="sort-arrow">⇅</span>
                                </th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($foodItems as $item): ?>
                                <tr data-item-id="<?= $item['id'] ?>">
                                    <td><?= htmlspecialchars($item['item_code'] ?? $item['id']) ?></td>
                                    <td>
                                        <strong><?= htmlspecialchars($item['name']) ?></strong>
                                        <?php if ($item['storage_location']): ?>
                                            <br><small style="color: #7f8c8d;">📍 <?= htmlspecialchars($item['storage_location']) ?></small>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <input
                                            type="number"
                                            step="0.01"
                                            name="stock[<?= $item['id'] ?>]"
                                            class="stock-input"
                                            value="<?= $item['current_stock'] ?>"
                                            data-original="<?= $item['current_stock'] ?>"
                                            oninput="calculateDifference(this)"
                                        >
                                    </td>
                                    <td class="current-stock"><?= number_format($item['current_stock'], 2, ',', ' ') ?></td>
                                    <td><?= htmlspecialchars($item['unit']) ?></td>
                                    <td class="difference-cell" data-item-id="<?= $item['id'] ?>">-</td>
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
            $medicamentItems = array_filter($items, function($item) { return $item['category'] === 'medicament'; });
            if (empty($medicamentItems)):
            ?>
                <div class="alert alert-info">Žádná léčiva v zásobách.</div>
            <?php else: ?>
                <div class="inventory-table-wrapper">
                    <table class="inventory-table" id="medicament-inventory-table">
                        <thead>
                            <tr>
                                <th onclick="sortInventoryTable('medicament-inventory-table', 0)" class="sortable" style="width: 50px;">
                                    # <span class="sort-arrow">⇅</span>
                                </th>
                                <th onclick="sortInventoryTable('medicament-inventory-table', 1)" class="sortable">
                                    Název položky <span class="sort-arrow">⇅</span>
                                </th>
                                <th style="width: 200px;">Nový stav</th>
                                <th onclick="sortInventoryTable('medicament-inventory-table', 3)" class="sortable" style="width: 200px;">
                                    Aktuální stav <span class="sort-arrow">⇅</span>
                                </th>
                                <th onclick="sortInventoryTable('medicament-inventory-table', 4)" class="sortable" style="width: 150px;">
                                    Jednotka <span class="sort-arrow">⇅</span>
                                </th>
                                <th onclick="sortInventoryTable('medicament-inventory-table', 5)" class="sortable" style="width: 120px;">
                                    Změna <span class="sort-arrow">⇅</span>
                                </th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($medicamentItems as $item): ?>
                                <tr data-item-id="<?= $item['id'] ?>">
                                    <td><?= htmlspecialchars($item['item_code'] ?? $item['id']) ?></td>
                                    <td>
                                        <strong><?= htmlspecialchars($item['name']) ?></strong>
                                        <?php if ($item['storage_location']): ?>
                                            <br><small style="color: #7f8c8d;">📍 <?= htmlspecialchars($item['storage_location']) ?></small>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <input
                                            type="number"
                                            step="0.01"
                                            name="stock[<?= $item['id'] ?>]"
                                            class="stock-input"
                                            value="<?= $item['current_stock'] ?>"
                                            data-original="<?= $item['current_stock'] ?>"
                                            oninput="calculateDifference(this)"
                                        >
                                    </td>
                                    <td class="current-stock"><?= number_format($item['current_stock'], 2, ',', ' ') ?></td>
                                    <td><?= htmlspecialchars($item['unit']) ?></td>
                                    <td class="difference-cell" data-item-id="<?= $item['id'] ?>">-</td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>

        <!-- Bottom Save Button (Mobile) -->
        <div class="bottom-save-section">
            <div class="changes-counter" id="changesCounter" style="display: none;">
                <span class="counter-badge" id="counterBadge">0</span>
                <span class="counter-text">změn</span>
            </div>
            <button type="button" class="btn btn-primary btn-block" onclick="saveAllChanges()">💾 Uložit změny</button>
        </div>
    </form>
</div>

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

.inventory-date-section {
    background: white;
    padding: 20px;
    border-radius: 8px;
    margin-bottom: 25px;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
    display: flex;
    align-items: center;
    gap: 15px;
}

.date-label {
    margin: 0;
    color: #2c3e50;
    font-size: 16px;
}

.date-input {
    padding: 10px 15px;
    border: 2px solid #27ae60;
    border-radius: 6px;
    font-size: 16px;
    font-weight: 600;
    color: #2c3e50;
    background: white;
    cursor: pointer;
    transition: border-color 0.2s;
}

.date-input:focus {
    outline: none;
    border-color: #229954;
    box-shadow: 0 0 0 3px rgba(39, 174, 96, 0.1);
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
    outline: none;
}

.tab:hover {
    color: #27ae60;
    border-bottom-color: #27ae60;
}

.tab:focus {
    outline: none;
    color: #7f8c8d;
    border-bottom-color: transparent;
}

.tab:active {
    color: #7f8c8d;
    border-bottom-color: transparent;
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

.inventory-table-wrapper {
    background: white;
    border-radius: 8px;
    overflow: hidden;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
}

.inventory-table {
    width: 100%;
    border-collapse: collapse;
}

.inventory-table thead {
    background: linear-gradient(135deg, #27ae60 0%, #229954 100%);
    color: white;
}

.inventory-table th {
    padding: 15px;
    text-align: left;
    font-weight: 600;
    font-size: 14px;
}

.inventory-table td {
    padding: 12px 15px;
    border-bottom: 1px solid #ecf0f1;
}

.inventory-table tbody tr:hover {
    background-color: #f8f9fa;
}

.current-stock {
    font-weight: 600;
    font-size: 16px;
    color: #7f8c8d;
}

.stock-input {
    width: 100%;
    padding: 8px 12px;
    border: 2px solid #e0e0e0;
    border-radius: 6px;
    font-size: 16px;
    font-weight: 600;
    transition: border-color 0.2s;
}

.stock-input:focus {
    outline: none;
    border-color: #27ae60;
}

.stock-input.changed {
    border-color: #f39c12;
    background-color: #fff8e1;
}

.difference-cell {
    font-weight: 600;
    font-size: 15px;
    text-align: center;
}

.difference-cell.positive {
    color: #27ae60;
}

.difference-cell.negative {
    color: #e74c3c;
}

.btn {
    padding: 10px 20px;
    border-radius: 6px;
    border: none;
    cursor: pointer;
    font-size: 14px;
    font-weight: 600;
    transition: all 0.2s;
    text-decoration: none;
    display: inline-block;
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

.btn-outline {
    background: white;
    border: 2px solid #27ae60;
    color: #27ae60;
}

.btn-outline:hover {
    background: #27ae60;
    color: white;
}

.btn-block {
    width: 100%;
    display: block;
}

.bottom-save-section {
    margin-top: 30px;
    padding: 20px;
    background: white;
    border-radius: 8px;
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
    position: sticky;
    bottom: 20px;
    z-index: 100;
    border: 2px solid #27ae60;
    animation: slideUp 0.3s ease-out;
}

.changes-counter {
    text-align: center;
    margin-bottom: 10px;
    padding: 10px;
    background: #f8f9fa;
    border-radius: 6px;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 8px;
}

.counter-badge {
    display: inline-block;
    background: linear-gradient(135deg, #27ae60 0%, #229954 100%);
    color: white;
    padding: 6px 12px;
    border-radius: 20px;
    font-weight: 700;
    font-size: 18px;
    min-width: 40px;
}

.counter-text {
    font-size: 14px;
    color: #7f8c8d;
    font-weight: 600;
}

@keyframes slideUp {
    from {
        opacity: 0;
        transform: translateY(20px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

/* Desktop: hide bottom save button */
@media (min-width: 769px) {
    .bottom-save-section {
        display: none;
    }
}

@media (max-width: 768px) {
    .container {
        padding: 10px;
    }

    .page-header {
        flex-direction: column;
        gap: 15px;
        align-items: stretch;
    }

    .page-header > div:last-child {
        width: 100%;
    }

    .page-header h1 {
        font-size: 24px;
    }

    .page-header p {
        font-size: 14px;
    }

    .inventory-date-section {
        flex-direction: column;
        align-items: flex-start;
        gap: 10px;
        padding: 15px;
    }

    .date-input {
        width: 100%;
        font-size: 16px;
    }

    .tab-content h2 {
        font-size: 20px;
    }

    /* Mobile-optimized table */
    .inventory-table-wrapper {
        overflow-x: auto;
        -webkit-overflow-scrolling: touch;
        position: relative;
    }

    .inventory-table-wrapper::after {
        content: '← Přejeďte prstem →';
        position: absolute;
        top: 10px;
        right: 10px;
        background: rgba(39, 174, 96, 0.9);
        color: white;
        padding: 8px 12px;
        border-radius: 4px;
        font-size: 11px;
        font-weight: 600;
        pointer-events: none;
        opacity: 0;
        animation: fadeInOut 4s ease-in-out;
        z-index: 5;
    }

    @keyframes fadeInOut {
        0% { opacity: 0; }
        20% { opacity: 1; }
        80% { opacity: 1; }
        100% { opacity: 0; }
    }

    .inventory-table {
        font-size: 13px;
        min-width: 600px;
    }

    .inventory-table th,
    .inventory-table td {
        padding: 10px 8px;
    }

    .inventory-table th:first-child,
    .inventory-table td:first-child {
        position: sticky;
        left: 0;
        background: white;
        z-index: 10;
    }

    .inventory-table thead th:first-child {
        background: #27ae60;
        z-index: 11;
    }

    .stock-input {
        padding: 8px 10px;
        font-size: 16px;
        width: 100%;
        min-width: 80px;
    }

    .difference-cell {
        font-size: 14px;
        white-space: nowrap;
    }

    .btn {
        padding: 12px 16px;
        font-size: 15px;
        white-space: nowrap;
    }

    .btn-outline {
        flex: 1;
        min-width: 0;
    }

    .btn-primary {
        flex: 1;
        min-width: 0;
    }

    .page-header > div:last-child {
        flex-wrap: wrap;
    }

    .bottom-save-section {
        margin-top: 20px;
        padding: 15px;
        bottom: 10px;
    }

    .bottom-save-section .btn {
        padding: 15px;
        font-size: 16px;
        font-weight: 700;
    }
}

/* Extra small devices (phones in portrait) */
@media (max-width: 480px) {
    .container {
        padding: 5px;
    }

    .page-header h1 {
        font-size: 20px;
    }

    .breadcrumb {
        font-size: 12px;
    }

    .inventory-date-section {
        padding: 12px;
    }

    .date-label {
        font-size: 14px;
    }

    .date-input {
        padding: 10px 12px;
        font-size: 16px;
    }

    .tab {
        padding: 10px 16px;
        font-size: 14px;
    }

    .inventory-table {
        font-size: 12px;
    }

    .inventory-table th,
    .inventory-table td {
        padding: 8px 6px;
    }

    .stock-input {
        padding: 8px;
        font-size: 16px;
    }
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
function switchTab(category) {
    // Update content visibility
    document.querySelectorAll('.tab-content').forEach(content => {
        content.classList.remove('active');
    });
    document.getElementById(category + '-content').classList.add('active');

    // Remove focus from clicked button
    event.target.blur();
}

function calculateDifference(input) {
    const newValue = parseFloat(input.value) || 0;
    const originalValue = parseFloat(input.dataset.original) || 0;
    const difference = newValue - originalValue;

    const row = input.closest('tr');
    const itemId = row.dataset.itemId;
    const differenceCell = document.querySelector(`.difference-cell[data-item-id="${itemId}"]`);

    // Update input styling
    if (Math.abs(difference) > 0.001) {
        input.classList.add('changed');
    } else {
        input.classList.remove('changed');
    }

    // Update difference cell
    if (Math.abs(difference) < 0.001) {
        differenceCell.textContent = '-';
        differenceCell.className = 'difference-cell';
    } else {
        const sign = difference > 0 ? '+' : '';
        differenceCell.textContent = sign + difference.toFixed(2);
        differenceCell.className = 'difference-cell ' + (difference > 0 ? 'positive' : 'negative');
    }

    // Update changes counter
    updateChangesCounter();
}

function updateChangesCounter() {
    const changedInputs = document.querySelectorAll('.stock-input.changed');
    const counter = document.getElementById('changesCounter');
    const badge = document.getElementById('counterBadge');

    if (changedInputs.length > 0) {
        counter.style.display = 'flex';
        badge.textContent = changedInputs.length;
    } else {
        counter.style.display = 'none';
    }
}

function saveAllChanges() {
    // Check if date is filled
    const dateInput = document.getElementById('inventory_date');
    if (!dateInput.value) {
        alert('Prosím vyplňte datum inventury.');
        dateInput.focus();
        return;
    }

    // Check if any changes were made
    const changedInputs = document.querySelectorAll('.stock-input.changed');

    if (changedInputs.length === 0) {
        alert('Nebyly provedeny žádné změny.');
        return;
    }

    // Confirm before saving with date
    const formattedDate = new Date(dateInput.value).toLocaleDateString('cs-CZ');
    const confirmed = confirm(`Uložit inventuru k datu ${formattedDate}?\nBude změněno ${changedInputs.length} položek.`);

    if (confirmed) {
        document.getElementById('inventoryForm').submit();
    }
}

function sortInventoryTable(tableId, columnIndex) {
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

        // Try to parse as numbers (for stock, change columns)
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

// Allow Enter key to move to next input
document.addEventListener('DOMContentLoaded', function() {
    const inputs = document.querySelectorAll('.stock-input');

    inputs.forEach((input, index) => {
        input.addEventListener('keydown', function(e) {
            if (e.key === 'Enter') {
                e.preventDefault();
                const nextInput = inputs[index + 1];
                if (nextInput) {
                    nextInput.focus();
                    nextInput.select();
                } else {
                    // Last input - blur to close keyboard on mobile
                    this.blur();
                }
            }
        });

        // Select all on focus for easy editing
        input.addEventListener('focus', function() {
            // Small delay for better mobile experience
            setTimeout(() => {
                this.select();
            }, 50);
        });

        // Prevent scrolling when input is focused (better mobile UX)
        input.addEventListener('focus', function() {
            const scrollY = window.scrollY;
            setTimeout(() => {
                window.scrollTo(0, scrollY);
            }, 0);
        }, { passive: true });
    });

    // Add haptic feedback on mobile when changing values
    inputs.forEach(input => {
        input.addEventListener('input', function() {
            if ('vibrate' in navigator && this.classList.contains('changed')) {
                navigator.vibrate(10);
            }
        });
    });
});
</script>
