<?php
$layout = 'main';
$hideFooter = true;
?>

<style>
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
}

.breadcrumb {
    margin: 0 0 5px 0;
    font-size: 14px;
    color: #666;
}

.breadcrumb a {
    color: #3498db;
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

/* Search form */
.search-form {
    display: flex;
    gap: 10px;
    align-items: center;
}

.filter-group {
    margin: 0;
}

/* Action buttons - not sticky */
.action-buttons-sticky {
    background: #f5f5f5;
    padding: 10px 20px;
    border-bottom: 1px solid #ddd;
}

.btn-row {
    display: flex;
    gap: 10px;
}

.btn-light-blue {
    background-color: #5dade2;
    color: white;
    border: none;
    padding: 10px 20px;
    cursor: pointer;
    border-radius: 4px;
    font-size: 14px;
    transition: background-color 0.3s;
}

.btn-light-blue:hover {
    background-color: #3498db;
}

.btn-dark-blue {
    background-color: #2874a6;
    color: white;
    border: none;
    padding: 10px 20px;
    cursor: pointer;
    border-radius: 4px;
    font-size: 14px;
    transition: background-color 0.3s;
}

.btn-dark-blue:hover {
    background-color: #1f618d;
}

.btn-green {
    background-color: #27ae60;
    color: white;
    border: none;
    padding: 10px 20px;
    cursor: pointer;
    border-radius: 4px;
    font-size: 14px;
    transition: background-color 0.3s;
}

.btn-green:hover {
    background-color: #229954;
}

.btn-red {
    background-color: #e74c3c;
    color: white;
    border: none;
    padding: 10px 20px;
    cursor: pointer;
    border-radius: 4px;
    font-size: 14px;
    transition: background-color 0.3s;
}

.btn-red:hover {
    background-color: #c0392b;
}

/* Table area */
.table-area {
    padding: 20px;
    overflow-x: auto;
    overflow-y: visible;
}

/* Table headers - sticky */
.examination-history-table thead {
    position: sticky;
    top: 0;
    z-index: 20;
}

.examination-history-table thead th {
    background-color: #34495e;
    color: white;
    padding: 12px 8px;
    text-align: left;
    border: 1px solid #2c3e50;
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
    min-width: 150px !important;
    max-width: 150px !important;
    width: 150px !important;
}

.sticky-col-3 {
    position: sticky !important;
    left: 300px !important;
    z-index: 10 !important;
    background-color: white !important;
    border-right: 2px solid #ddd !important;
    font-size: 14px !important;
    min-width: 150px !important;
    max-width: 150px !important;
    width: 150px !important;
}

.sticky-col-4 {
    position: sticky !important;
    left: 450px !important;
    z-index: 10 !important;
    background-color: white !important;
    border-right: 2px solid #ddd !important;
    font-size: 14px !important;
    min-width: 150px !important;
    max-width: 150px !important;
    width: 150px !important;
}

/* Sticky column headers */
th.sticky-col,
th.sticky-col-2,
th.sticky-col-3,
th.sticky-col-4 {
    z-index: 30 !important;
    background-color: #34495e !important;
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

/* Sortable headers */
.sortable {
    cursor: pointer;
    user-select: none;
}

.sortable:hover {
    background-color: #2c3e50 !important;
}

.sort-icon {
    font-size: 12px;
    margin-left: 5px;
}

/* Editable cell */
.editable-cell {
    cursor: text;
    min-height: 20px;
}

.editable-cell:hover {
    background-color: #f0f8ff !important;
}

.editable-cell.editing {
    padding: 0 !important;
}

.editable-cell input {
    width: 100%;
    height: 100%;
    border: 2px solid #4285f4;
    padding: 6px 8px;
    font-size: 13px;
    box-sizing: border-box;
    outline: none;
}

.editable-cell.saving {
    background-color: #fff3cd !important;
}

.editable-cell.success {
    background-color: #d4edda !important;
}

.editable-cell.error {
    background-color: #f8d7da !important;
}

/* Animal link */
.animal-link {
    color: #2c3e50;
    text-decoration: none;
}

.animal-link:hover {
    text-decoration: underline;
}

.text-muted {
    color: #7f8c8d;
}

/* Exam cell */
.exam-cell {
    font-size: 12px;
    line-height: 1.4;
}

.exam-header-clickable {
    cursor: pointer;
    transition: background-color 0.2s;
    padding: 2px 4px;
    border-radius: 3px;
    display: inline-block;
}

.exam-header-clickable:hover {
    background-color: rgba(52, 152, 219, 0.1);
}

.exam-header-clickable strong {
    text-decoration: underline;
    text-decoration-style: dotted;
}

/* Forms */
.form-control {
    padding: 8px 12px;
    border: 1px solid #ddd;
    border-radius: 4px;
    font-size: 14px;
}

.btn {
    padding: 8px 16px;
    border-radius: 4px;
    font-size: 14px;
    cursor: pointer;
    text-decoration: none;
    display: inline-block;
}

.btn-primary {
    background-color: #3498db;
    color: white;
    border: none;
}

.btn-primary:hover {
    background-color: #2980b9;
}

.btn-outline {
    background-color: white;
    color: #3498db;
    border: 1px solid #3498db;
}

.btn-outline:hover {
    background-color: #ecf0f1;
}

.alert {
    padding: 15px;
    margin: 20px 0;
    border-radius: 4px;
}

.alert-info {
    background-color: #d1ecf1;
    color: #0c5460;
    border: 1px solid #bee5eb;
}

/* Modal styles */
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
    background-color: #fefefe;
    margin: 5% auto;
    padding: 0;
    border: 1px solid #888;
    border-radius: 8px;
    width: 90%;
    max-width: 500px;
    box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
}

.modal-header {
    padding: 20px;
    background-color: #3498db;
    color: white;
    border-radius: 8px 8px 0 0;
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.modal-header h2 {
    margin: 0;
    font-size: 1.5rem;
}

.modal-close {
    color: white;
    font-size: 28px;
    font-weight: bold;
    cursor: pointer;
    line-height: 1;
}

.modal-close:hover,
.modal-close:focus {
    color: #ecf0f1;
}

.modal-content form {
    padding: 20px;
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

.form-actions {
    margin-top: 20px;
    display: flex;
    gap: 10px;
    justify-content: flex-end;
}

.modal-content-wide {
    max-width: 700px;
}

.form-row {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 15px;
    margin-bottom: 15px;
}

.exam-target-tabs {
    display: flex;
    gap: 10px;
    margin-top: 10px;
}

.tab-btn {
    padding: 8px 16px;
    border: 2px solid #3498db;
    background: white;
    color: #3498db;
    cursor: pointer;
    border-radius: 4px;
    font-weight: 600;
    transition: all 0.2s;
}

.tab-btn:hover {
    background: #ecf0f1;
}

.tab-btn.active {
    background: #3498db;
    color: white;
}

.target-selection {
    margin-top: 15px;
}

.checkbox-list {
    max-height: 200px;
    overflow-y: auto;
    border: 1px solid #ddd;
    border-radius: 4px;
    padding: 10px;
    background: #f9f9f9;
}

.checkbox-item {
    display: block;
    padding: 8px;
    margin-bottom: 5px;
    cursor: pointer;
    transition: background 0.2s;
}

.checkbox-item:hover {
    background: #e8f4f8;
}

.checkbox-item input[type="checkbox"] {
    margin-right: 8px;
}

.modal-body {
    padding: 20px;
}

.exam-entry-card {
    border: 1px solid #ddd;
    padding: 15px;
    margin-bottom: 10px;
    border-radius: 5px;
    background-color: #f9f9f9;
    position: relative;
}

.exam-entry-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 10px;
    font-weight: bold;
    color: #2c3e50;
}

.exam-entry-remove {
    background-color: #e74c3c;
    color: white;
    border: none;
    padding: 5px 10px;
    cursor: pointer;
    border-radius: 3px;
    font-size: 12px;
}

.exam-entry-remove:hover {
    background-color: #c0392b;
}

.exam-entry-fields {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 10px;
}

.exam-entry-fields .form-group {
    margin-bottom: 0;
}

.btn-danger {
    background-color: #e74c3c;
    color: white;
    border: none;
    padding: 10px 20px;
    cursor: pointer;
    border-radius: 4px;
}

.btn-danger:hover {
    background-color: #c0392b;
}

/* Exam details styling */
.exam-details {
    font-size: 13px;
    padding: 2px 0;
    color: #2c3e50;
}

.exam-notes {
    font-size: 12px;
    color: #7f8c8d;
    font-style: italic;
    margin-top: 4px;
}

/* Exam cell hover coloring */
.exam-cell.finding-positive:hover {
    background-color: #ffcccc !important;
}

.exam-cell.finding-negative:hover {
    background-color: #ccffcc !important;
}

.exam-cell-empty {
    background-color: #f9f9f9;
}

/* Table layout */
.examination-history-table {
    width: auto;
    min-width: 100%;
    table-layout: auto;
}
</style>

<!-- Sticky page header -->
<div class="page-sticky-header">
    <div class="header-flex">
        <div class="header-left">
            <div class="breadcrumb">
                <a href="/">Pracoviště</a> /
                <a href="/workplace/<?= $workplace['id'] ?>"><?= htmlspecialchars($workplace['name']) ?></a> /
                Přehled zvířat
            </div>
            <h1 class="page-title">Přehled zvířat</h1>
        </div>
        <div class="header-right">
            <form method="GET" class="search-form">
                <div class="filter-group">
                    <input
                        type="text"
                        name="search"
                        placeholder="Hledat (jméno, identifikátor, druh)..."
                        value="<?= htmlspecialchars($filters['search']) ?>"
                        class="form-control"
                    >
                </div>
                <div class="filter-group">
                    <select name="status" class="form-control">
                        <option value="">Všechny stavy</option>
                        <option value="active" <?= $filters['status'] === 'active' ? 'selected' : '' ?>>Aktivní</option>
                        <option value="transferred" <?= $filters['status'] === 'transferred' ? 'selected' : '' ?>>Přesunuté</option>
                        <option value="deceased" <?= $filters['status'] === 'deceased' ? 'selected' : '' ?>>Uhynulé</option>
                        <option value="removed" <?= $filters['status'] === 'removed' ? 'selected' : '' ?>>Vyřazené</option>
                    </select>
                </div>
                <div class="filter-group">
                    <select name="enclosure_id" class="form-control">
                        <option value="">Všechny výběhy</option>
                        <?php foreach ($enclosures as $enclosure): ?>
                            <option value="<?= $enclosure['id'] ?>" <?= $filters['enclosure_id'] == $enclosure['id'] ? 'selected' : '' ?>>
                                <?= htmlspecialchars($enclosure['name']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <button type="submit" class="btn btn-primary">Filtrovat</button>
                <a href="/workplace/<?= $workplace['id'] ?>/animals" class="btn btn-outline">Zrušit</a>
            </form>
        </div>
    </div>
</div>

<!-- Sticky action buttons -->
<div class="action-buttons-sticky">
    <div class="btn-row">
        <?php if ($canEdit): ?>
        <button type="button" class="btn-light-blue" onclick="openAnimalModal()">
            ➕ Přidat zvíře
        </button>
        <button type="button" class="btn-dark-blue" onclick="openEnclosureModal()">
            ➕ Přidat výběh
        </button>
        <button type="button" class="btn-green" onclick="openExaminationModal()">
            ➕ Přidat záznam
        </button>
        <button type="button" class="btn-red" onclick="openDewormingModal()">
            ➕ Aplikace antiparazitika
        </button>
        <?php endif; ?>
        <button type="button" class="btn btn-outline" onclick="openPrintModal()" style="margin-left: auto;">
            🖨️ Tisk historie
        </button>
    </div>
</div>

<!-- Table area -->
<div class="table-area">
    <?php if (empty($animals)): ?>
        <div class="alert alert-info">
            Nebyla nalezena žádná zvířata odpovídající zadaným kritériím.
        </div>
    <?php else: ?>
        <table class="examination-history-table" id="examinationTable">
            <thead>
                <tr>
                    <th class="sticky-col sortable" rowspan="2" data-column="name" data-sort="none">
                        Jméno <span class="sort-icon">⇅</span>
                    </th>
                    <th class="sticky-col-2 sortable" rowspan="2" data-column="species" data-sort="none">
                        Druh <span class="sort-icon">⇅</span>
                    </th>
                    <th class="sticky-col-3 sortable" rowspan="2" data-column="enclosure" data-sort="none">
                        Výběh <span class="sort-icon">⇅</span>
                    </th>
                    <th class="sticky-col-4 sortable" rowspan="2" data-column="next_test" data-sort="none">
                        Další test <span class="sort-icon">⇅</span>
                    </th>
                    <th colspan="100">Historie vyšetření (seřazeno od nejnovějších)</th>
                </tr>
                <tr>
                    <?php
                    // Find maximum number of examination groups
                    $maxExams = 0;
                    foreach ($animals as $animal) {
                        $grouped = [];
                        foreach ($animal['examinations'] ?? [] as $exam) {
                            $key = $exam['examination_date'] . '|' . ($exam['institution'] ?? '');
                            $grouped[$key] = true;
                        }
                        $examCount = count($grouped);
                        if ($examCount > $maxExams) {
                            $maxExams = $examCount;
                        }
                    }

                    // Generate column headers
                    for ($i = 1; $i <= $maxExams; $i++):
                    ?>
                        <th class="exam-col">Vyšetření <?= $i ?></th>
                    <?php endfor; ?>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($animals as $animal): ?>
                <tr>
                    <td class="sticky-col" data-sort-value="<?= htmlspecialchars($animal['name'] ?? $animal['identifier'] ?? '') ?>">
                        <a href="/workplace/<?= $workplace['id'] ?>/animals/<?= $animal['animal_id'] ?>" class="animal-link">
                            <strong><?= htmlspecialchars($animal['name'] ?? '-') ?></strong><br>
                            <small class="text-muted"><?= htmlspecialchars($animal['identifier'] ?? '-') ?></small>
                        </a>
                    </td>
                    <td class="sticky-col-2" data-sort-value="<?= htmlspecialchars($animal['species']) ?>">
                        <?= htmlspecialchars($animal['species']) ?>
                    </td>
                    <td class="sticky-col-3" data-sort-value="<?= htmlspecialchars($animal['enclosure_name'] ?? '') ?>">
                        <?= htmlspecialchars($animal['enclosure_name'] ?? '-') ?>
                    </td>
                    <td class="sticky-col-4 editable-cell"
                        data-animal-id="<?= $animal['animal_id'] ?>"
                        data-sort-value="<?= htmlspecialchars($animal['next_check_date'] ?? '') ?>"
                        onclick="editNextTest(this)"
                        title="Klikněte pro úpravu">
                        <?php if (!empty($animal['next_check_date'])): ?>
                            <?= date('d.m.Y', strtotime($animal['next_check_date'])) ?>
                        <?php else: ?>
                            <span style="color: #999;">např. 15.12.2025</span>
                        <?php endif; ?>
                    </td>

                    <?php
                    // Group examinations by date and institution
                    $examinations = $animal['examinations'] ?? [];
                    $groupedExams = [];
                    foreach ($examinations as $exam) {
                        $key = $exam['examination_date'] . '|' . ($exam['institution'] ?? '');
                        if (!isset($groupedExams[$key])) {
                            $groupedExams[$key] = [
                                'date' => $exam['examination_date'],
                                'institution' => $exam['institution'] ?? '',
                                'notes' => $exam['notes'] ?? '',
                                'exams' => []
                            ];
                        }
                        $groupedExams[$key]['exams'][] = $exam;
                    }

                    // Sort by date descending
                    uasort($groupedExams, function($a, $b) {
                        return strtotime($b['date']) - strtotime($a['date']);
                    });

                    $groupedExams = array_values($groupedExams);

                    // Output exam cells
                    for ($i = 0; $i < $maxExams; $i++):
                        if (isset($groupedExams[$i])):
                            $group = $groupedExams[$i];

                            // Determine cell class based on whether any exam is positive
                            $hasPositive = false;
                            foreach ($group['exams'] as $e) {
                                if ($e['finding_status'] === 'positive') {
                                    $hasPositive = true;
                                    break;
                                }
                            }
                            $cellClass = $hasPositive ? 'finding-positive' : 'finding-negative';

                            // Collect all examination IDs in this group
                            $examIds = array_column($group['exams'], 'id');
                            $examIdsJson = htmlspecialchars(json_encode($examIds));
                            ?>
                            <td class="exam-cell <?= $cellClass ?>">
                                <div class="exam-header exam-header-clickable" onclick="openEditExaminationModal(<?= $examIdsJson ?>)" title="Klikněte pro úpravu nebo smazání">
                                    <strong><?= date('d.m.Y', strtotime($group['date'])) ?><?= $group['institution'] ? ' ' . htmlspecialchars($group['institution']) : '' ?></strong>
                                </div>
                                <?php foreach ($group['exams'] as $exam): ?>
                                    <div class="exam-details">
                                        <?= htmlspecialchars($exam['sample_type']) ?>:
                                        <?php
                                        $parasiteFound = $exam['parasite_found'] ?? '';
                                        $intensity = $exam['intensity'] ?? '';
                                        ?>
                                        <?php if ($exam['finding_status'] === 'positive'): ?>
                                            <?php if ($parasiteFound): ?>
                                                <?= htmlspecialchars($parasiteFound) ?>
                                                <?= $intensity ? ' ' . htmlspecialchars($intensity) : '' ?>
                                            <?php else: ?>
                                                pozitivní<?= $intensity ? ' ' . htmlspecialchars($intensity) : '' ?>
                                            <?php endif; ?>
                                        <?php else: ?>
                                            negativní<?= $intensity && $intensity !== 'neg.' ? ' ' . htmlspecialchars($intensity) : '' ?>
                                        <?php endif; ?>
                                    </div>
                                    <?php if (!empty($exam['deworming_id'])): ?>
                                        <div class="exam-details" style="margin-top: 4px; padding: 4px; background-color: rgba(52, 152, 219, 0.1); border-left: 3px solid #3498db;">
                                            <strong>Aplikace antiparazitika <?= date('d.m.Y', strtotime($exam['deworming_date'])) ?>:</strong><br>
                                            <?= htmlspecialchars($exam['medication'] ?? '-') ?>
                                            <?php if (!empty($exam['dosage'])): ?>
                                                (<?= htmlspecialchars($exam['dosage']) ?>)
                                            <?php endif; ?>
                                            <?php if (!empty($exam['administration_route'])): ?>
                                                <em><?= htmlspecialchars($exam['administration_route']) ?></em>
                                            <?php endif; ?>
                                        </div>
                                    <?php endif; ?>
                                <?php endforeach; ?>
                                <?php if ($group['notes']): ?>
                                    <div class="exam-notes"><?= htmlspecialchars($group['notes']) ?></div>
                                <?php endif; ?>
                            </td>
                        <?php else: ?>
                            <td class="exam-cell exam-cell-empty"></td>
                        <?php endif; ?>
                    <?php endfor; ?>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>
</div>

<script>
// Table sorting
document.addEventListener('DOMContentLoaded', function() {
    const table = document.getElementById('examinationTable');
    if (!table) return;

    const headers = table.querySelectorAll('th.sortable');

    headers.forEach((header, columnIndex) => {
        header.style.cursor = 'pointer';
        header.addEventListener('click', function() {
            sortTable(header);
        });
    });

    function sortTable(header) {
        const tbody = table.querySelector('tbody');
        const rows = Array.from(tbody.querySelectorAll('tr'));
        const column = header.dataset.column;
        const currentSort = header.dataset.sort;

        // Reset all other headers
        headers.forEach(h => {
            if (h !== header) {
                h.dataset.sort = 'none';
                const icon = h.querySelector('.sort-icon');
                if (icon) icon.textContent = '⇅';
            }
        });

        // Determine new sort direction
        let newSort = 'asc';
        if (currentSort === 'none' || currentSort === 'desc') {
            newSort = 'asc';
        } else {
            newSort = 'desc';
        }

        header.dataset.sort = newSort;
        const icon = header.querySelector('.sort-icon');
        if (icon) {
            icon.textContent = newSort === 'asc' ? '↑' : '↓';
        }

        // Sort rows
        rows.sort((a, b) => {
            const aCell = a.querySelector(`td[data-sort-value]`);
            const bCell = b.querySelector(`td[data-sort-value]`);

            if (!aCell || !bCell) return 0;

            const aValue = aCell.dataset.sortValue || '';
            const bValue = bCell.dataset.sortValue || '';

            if (newSort === 'asc') {
                return aValue.localeCompare(bValue);
            } else {
                return bValue.localeCompare(aValue);
            }
        });

        // Reappend rows
        rows.forEach(row => tbody.appendChild(row));
    }
});

// Editable "Další test" cell
let currentEditingCell = null;

function editNextTest(cell) {
    if (cell.classList.contains('editing')) {
        return;
    }

    // Save any other cell being edited
    if (currentEditingCell && currentEditingCell !== cell) {
        finishEditing(currentEditingCell, true);
    }

    currentEditingCell = cell;
    const animalId = cell.dataset.animalId;
    const currentValue = cell.dataset.sortValue || '';

    // Store original value and HTML
    cell.dataset.originalValue = currentValue;
    cell.dataset.originalHtml = cell.innerHTML;

    // Create input element
    const input = document.createElement('input');
    input.type = 'text';
    input.value = currentValue;
    input.placeholder = 'např. 15.12.2025';

    // Mark cell as editing
    cell.classList.add('editing');
    cell.textContent = '';
    cell.appendChild(input);

    // Focus and select
    input.focus();
    input.select();

    // Handle blur (save)
    input.addEventListener('blur', () => {
        finishEditing(cell, true);
    });

    // Handle Enter key (save) and Escape (cancel)
    input.addEventListener('keydown', (e) => {
        if (e.key === 'Enter') {
            e.preventDefault();
            finishEditing(cell, true);
        } else if (e.key === 'Escape') {
            e.preventDefault();
            finishEditing(cell, false);
        }
    });
}

function finishEditing(cell, save) {
    if (!cell.classList.contains('editing')) {
        return;
    }

    const input = cell.querySelector('input');
    const newValue = input ? input.value.trim() : '';
    const originalValue = cell.dataset.originalValue || '';
    const animalId = cell.dataset.animalId;

    // Remove input
    cell.classList.remove('editing');

    if (!save) {
        // Cancelled - restore original HTML
        cell.innerHTML = cell.dataset.originalHtml || '';
        currentEditingCell = null;
        return;
    }

    // Check if value changed
    if (newValue === originalValue) {
        // Restore original HTML
        cell.innerHTML = cell.dataset.originalHtml || '';
        currentEditingCell = null;
        return;
    }

    // Show saving state
    if (newValue) {
        cell.textContent = newValue;
    } else {
        cell.innerHTML = '<span style="color: #999;">např. 15.12.2025</span>';
    }
    cell.classList.add('saving');

    // Save to server
    fetch('/animals/' + animalId + '/update-next-test', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json'
        },
        body: JSON.stringify({ next_test: newValue })
    })
    .then(response => response.json())
    .then(data => {
        cell.classList.remove('saving');
        if (data.success) {
            cell.classList.add('success');
            cell.dataset.sortValue = newValue;
            // Update display
            if (newValue) {
                cell.textContent = newValue;
            } else {
                cell.innerHTML = '<span style="color: #999;">např. 15.12.2025</span>';
            }
            setTimeout(() => {
                cell.classList.remove('success');
            }, 1000);
        } else {
            cell.classList.add('error');
            alert('Chyba při ukládání: ' + (data.error || 'Neznámá chyba'));
            cell.innerHTML = cell.dataset.originalHtml || '';
            setTimeout(() => {
                cell.classList.remove('error');
            }, 2000);
        }
        currentEditingCell = null;
    })
    .catch(error => {
        cell.classList.remove('saving');
        cell.classList.add('error');
        alert('Chyba při komunikaci se serverem: ' + error.message);
        cell.innerHTML = cell.dataset.originalHtml || '';
        setTimeout(() => {
            cell.classList.remove('error');
        }, 2000);
        currentEditingCell = null;
    });
}

// Modal close handlers
let examEntryCounter = 0;

function closeAnimalModal() {
    document.getElementById('animalModal').style.display = 'none';
}

function openEnclosureModal() {
    document.getElementById('enclosureModal').style.display = 'block';
}

function closeEnclosureModal() {
    document.getElementById('enclosureModal').style.display = 'none';
    document.getElementById('enclosureForm').reset();
}

function openExaminationModal() {
    document.getElementById('examinationModal').style.display = 'block';
    // Add first entry if none exist
    if (document.getElementById('examinationEntries').children.length === 0) {
        addExaminationEntry();
    }
}

function closeExaminationModal() {
    document.getElementById('examinationModal').style.display = 'none';
    document.getElementById('examinationForm').reset();
    // Clear all examination entries
    document.getElementById('examinationEntries').innerHTML = '';
    examEntryCounter = 0;
}

function addExaminationEntry() {
    examEntryCounter++;
    const container = document.getElementById('examinationEntries');
    const entryId = 'examEntry' + examEntryCounter;
    const currentCounter = examEntryCounter;

    const entryHtml = `
        <div class="exam-entry-card" id="${entryId}">
            <div class="exam-entry-header">
                <span>Vyšetření #${examEntryCounter}</span>
                <button type="button" class="exam-entry-remove" onclick="removeExaminationEntry('${entryId}')">
                    ✕ Odstranit
                </button>
            </div>
            <div class="exam-entry-fields">
                <div class="form-group">
                    <label>Typ vyšetření: *</label>
                    <select name="examinations[${examEntryCounter}][sample_type]" class="form-control exam-sample-type" required>
                        <option value="">Vyberte typ...</option>
                        <option value="Flotace">Flotace</option>
                        <option value="Larvoskopie">Larvoskopie</option>
                        <option value="Sedimentace">Sedimentace</option>
                        <option value="EPG">EPG</option>
                        <option value="OPG">OPG</option>
                    </select>
                </div>
                <div class="form-group">
                    <label>Intenzita: *</label>
                    <select name="examinations[${examEntryCounter}][intensity]" class="form-control exam-intensity" required data-entry-id="${currentCounter}">
                        <option value="">Vyberte intenzitu...</option>
                        <option value="neg.">neg.</option>
                        <option value="+">+</option>
                        <option value="++">++</option>
                        <option value="+++">+++</option>
                        <option value="++++">++++</option>
                        <option value="+++++">+++++</option>
                        <option value="!!!!!!">!!!!!!</option>
                        <option value="custom">Vlastní číslo (EPG/OPG)</option>
                    </select>
                </div>
            </div>
            <div class="form-group" id="customIntensityGroup${examEntryCounter}" style="display: none; margin-top: 10px;">
                <label>Vlastní hodnota (číslo):</label>
                <input type="number" name="examinations[${examEntryCounter}][custom_intensity]" class="form-control" min="0">
            </div>
            <div class="form-group" style="margin-top: 10px;">
                <label>Nalezený parazit/červ:</label>
                <input type="text" name="examinations[${examEntryCounter}][parasite_found]" class="form-control" placeholder="Např. Ascaris, Toxocara...">
            </div>
        </div>
    `;

    container.insertAdjacentHTML('beforeend', entryHtml);

    const newEntry = document.getElementById(entryId);
    const intensitySelect = newEntry.querySelector('.exam-intensity');
    const customGroup = document.getElementById(`customIntensityGroup${currentCounter}`);

    intensitySelect.addEventListener('change', function() {
        if (this.value === 'custom') {
            customGroup.style.display = 'block';
            customGroup.querySelector('input').required = true;
        } else {
            customGroup.style.display = 'none';
            customGroup.querySelector('input').required = false;
        }
    });
}

function removeExaminationEntry(entryId) {
    const entry = document.getElementById(entryId);
    if (entry) {
        const container = document.getElementById('examinationEntries');
        if (container.children.length > 1) {
            entry.remove();
        } else {
            alert('Musíte mít alespoň jedno vyšetření');
        }
    }
}

// Close modal when clicking outside
window.onclick = function(event) {
    if (event.target.classList.contains('modal')) {
        closeAnimalModal();
        closeEnclosureModal();
        closeExaminationModal();
        closeEditExaminationModal();
    }
}

// Handle enclosure form submission (wrapped in DOMContentLoaded to ensure form exists)
document.addEventListener('DOMContentLoaded', function() {
    const enclosureForm = document.getElementById('enclosureForm');
    if (enclosureForm) {
        enclosureForm.addEventListener('submit', function(e) {
            e.preventDefault();

            const formData = new FormData(this);

            fetch('/workplace/<?= $workplace['id'] ?>/enclosures/create', {
                method: 'POST',
                body: formData
            })
            .then(response => response.text().then(text => {
                try {
                    return JSON.parse(text);
                } catch (e) {
                    console.error('Server returned invalid JSON:', text);
                    throw new Error('Server returned invalid JSON: ' + text.substring(0, 200));
                }
            }))
            .then(data => {
                if (data.success) {
                    closeEnclosureModal();
                    location.reload();
                } else {
                    alert('Chyba při vytváření výběhu: ' + (data.error || 'Neznámá chyba'));
                }
            })
            .catch(error => {
                alert('Chyba při komunikaci se serverem: ' + error.message);
                console.error('Error:', error);
            });
        });
    }
});
</script>

<!-- Enclosure Modal -->
<div id="enclosureModal" class="modal">
    <div class="modal-content">
        <div class="modal-header">
            <h2>Přidat výběh</h2>
            <span class="modal-close" onclick="closeEnclosureModal()">&times;</span>
        </div>
        <form id="enclosureForm">
            <div class="form-group">
                <label for="enclosure_name">Název výběhu: *</label>
                <input type="text" id="enclosure_name" name="name" class="form-control" required>
            </div>

            <div class="form-group">
                <label for="enclosure_description">Popis:</label>
                <textarea id="enclosure_description" name="description" class="form-control" rows="3"></textarea>
            </div>

            <div class="form-actions">
                <button type="submit" class="btn btn-primary">Vytvořit výběh</button>
                <button type="button" class="btn btn-outline" onclick="closeEnclosureModal()">Zrušit</button>
            </div>
        </form>
    </div>
</div>

<!-- Animal Modal -->
<div id="animalModal" class="modal">
    <div class="modal-content">
        <div class="modal-header">
            <h2>Přidat zvíře</h2>
            <span class="modal-close" onclick="closeAnimalModal()">&times;</span>
        </div>
        <div class="modal-body">
            <p>Otevírá se formulář pro přidání zvířete...</p>
        </div>
    </div>
</div>

<script>
// Redirect to animal creation form when modal opens
function openAnimalModal() {
    window.location.href = '/workplace/<?= $workplace['id'] ?>/animals/create';
}
</script>

<!-- Modal for adding animal -->
<div id="animalModal" class="modal">
    <div class="modal-content">
        <div class="modal-header">
            <h2>Přidat zvíře</h2>
            <span class="modal-close" onclick="closeAnimalModal()">&times;</span>
        </div>
        <div class="modal-body">
            <p>Otevírá se formulář pro přidání zvířete...</p>
        </div>
    </div>
</div>

<script>
// Redirect to animal creation form when modal opens
function openAnimalModal() {
    window.location.href = '/workplace/<?= $workplace['id'] ?>/animals/create';
}
</script>

<!-- Modal for adding examination record -->
<div id="examinationModal" class="modal">
    <div class="modal-content modal-content-wide">
        <div class="modal-header">
            <h2>Přidat záznam vyšetření</h2>
            <span class="modal-close" onclick="closeExaminationModal()">&times;</span>
        </div>
        <form id="examinationForm">
            <div class="form-row">
                <div class="form-group">
                    <label for="examination_date">Datum vyšetření: *</label>
                    <input type="date" id="examination_date" name="examination_date" class="form-control" required value="<?= date('Y-m-d') ?>">
                </div>

                <div class="form-group">
                    <label for="institution">Instituce: *</label>
                    <select id="institution" name="institution" class="form-control" required>
                        <option value="">Vyberte instituci...</option>
                        <option value="SVÚ Jihlava">SVÚ Jihlava</option>
                        <option value="SVÚ Praha">SVÚ Praha</option>
                    </select>
                </div>
            </div>

            <div class="form-group">
                <label>Vyšetření: *</label>
                <div id="examinationEntries">
                    <!-- Examination entries will be added here -->
                </div>
                <button type="button" class="btn btn-outline" onclick="addExaminationEntry()" style="margin-top: 10px;">
                    ➕ Přidat typ vyšetření
                </button>
            </div>

            <div class="form-group">
                <label>Vyšetření pro: *</label>
                <div class="exam-target-tabs">
                    <button type="button" class="tab-btn active" onclick="switchExamTarget('animals')">Zvířata</button>
                    <button type="button" class="tab-btn" onclick="switchExamTarget('enclosures')">Výběhy</button>
                </div>
            </div>

            <div id="animalsTarget" class="target-selection">
                <label>Vyberte zvířata: *</label>
                <input type="text" id="animalSearch" class="form-control" placeholder="Hledat zvíře..." onkeyup="filterAnimals()">
                <div class="checkbox-list" id="animalsList">
                    <?php foreach ($animals as $animal): ?>
                        <label class="checkbox-item" data-name="<?= htmlspecialchars(strtolower($animal['name'] ?? $animal['identifier'])) ?>" data-species="<?= htmlspecialchars(strtolower($animal['species'])) ?>">
                            <input type="checkbox" name="animal_ids[]" value="<?= $animal['animal_id'] ?>">
                            <span><?= htmlspecialchars($animal['name'] ?? $animal['identifier']) ?> (<?= htmlspecialchars($animal['species']) ?>)</span>
                        </label>
                    <?php endforeach; ?>
                </div>
            </div>

            <div id="enclosuresTarget" class="target-selection" style="display: none;">
                <label>Vyberte výběhy: *</label>
                <input type="text" id="enclosureSearch" class="form-control" placeholder="Hledat výběh..." onkeyup="filterEnclosures()">
                <div class="checkbox-list" id="enclosuresList">
                    <?php foreach ($enclosures as $enclosure): ?>
                        <label class="checkbox-item" data-name="<?= htmlspecialchars(strtolower($enclosure['name'])) ?>">
                            <input type="checkbox" name="enclosure_ids[]" value="<?= $enclosure['id'] ?>">
                            <span><?= htmlspecialchars($enclosure['name']) ?></span>
                        </label>
                    <?php endforeach; ?>
                </div>
            </div>

            <div class="form-group">
                <label for="examination_notes">Poznámky:</label>
                <textarea id="examination_notes" name="notes" class="form-control" rows="3"></textarea>
            </div>

            <div class="form-actions">
                <button type="submit" class="btn btn-primary">Vytvořit záznam</button>
                <button type="button" class="btn btn-outline" onclick="closeExaminationModal()">Zrušit</button>
            </div>
        </form>
    </div>
</div>

<!-- Edit/Delete Examination Modal -->
<div id="editExaminationModal" class="modal">
    <div class="modal-content">
        <div class="modal-header">
            <h2>Upravit nebo smazat vyšetření</h2>
            <span class="modal-close" onclick="closeEditExaminationModal()">&times;</span>
        </div>
        <div class="modal-body">
            <div id="editExaminationContent">
                <p style="text-align: center; padding: 20px;">Načítání...</p>
            </div>
            <div class="form-actions" style="margin-top: 20px; display: flex; gap: 10px; justify-content: space-between;">
                <button type="button" class="btn btn-danger" onclick="deleteExaminations()" id="deleteBtn">Smazat všechny záznamy</button>
                <div style="display: flex; gap: 10px;">
                    <button type="button" class="btn btn-primary" onclick="saveExaminations()">Uložit změny</button>
                    <button type="button" class="btn btn-outline" onclick="closeEditExaminationModal()">Zavřít</button>
                </div>
            </div>
        </div>
    </div>
</div>


</div> <!-- End table-wrapper -->
<script>
// Tab switching for examination targets
function switchExamTarget(target) {
    const tabs = document.querySelectorAll('.tab-btn');
    tabs.forEach(tab => tab.classList.remove('active'));
    event.target.classList.add('active');

    if (target === 'animals') {
        document.getElementById('animalsTarget').style.display = 'block';
        document.getElementById('enclosuresTarget').style.display = 'none';
    } else {
        document.getElementById('animalsTarget').style.display = 'none';
        document.getElementById('enclosuresTarget').style.display = 'block';
    }
}

// Filter animals by search
function filterAnimals() {
    const searchTerm = document.getElementById('animalSearch').value.toLowerCase();
    const items = document.querySelectorAll('#animalsList .checkbox-item');
    items.forEach(item => {
        const name = item.dataset.name || '';
        const species = item.dataset.species || '';
        if (name.includes(searchTerm) || species.includes(searchTerm)) {
            item.style.display = 'block';
        } else {
            item.style.display = 'none';
        }
    });
}

// Filter enclosures by search
function filterEnclosures() {
    const searchTerm = document.getElementById('enclosureSearch').value.toLowerCase();
    const items = document.querySelectorAll('#enclosuresList .checkbox-item');
    items.forEach(item => {
        const name = item.dataset.name || '';
        if (name.includes(searchTerm)) {
            item.style.display = 'block';
        } else {
            item.style.display = 'none';
        }
    });
}

// Handle examination form submission
document.getElementById('examinationForm').addEventListener('submit', function(e) {
    e.preventDefault();

    const formData = new FormData(this);

    // Handle custom intensity for each examination entry
    const intensitySelects = document.querySelectorAll('.exam-intensity');
    intensitySelects.forEach((select, index) => {
        if (select.value === 'custom') {
            const customInput = select.closest('.exam-entry-card').querySelector('input[name*="[custom_intensity]"]');
            if (customInput && customInput.value) {
                const intensityFieldName = select.name;
                formData.set(intensityFieldName, customInput.value);
            }
        }
    });

    fetch('/workplace/<?= $workplace['id'] ?>/examinations/create', {
        method: 'POST',
        body: formData
    })
    .then(response => response.text().then(text => {
        try {
            return JSON.parse(text);
        } catch (e) {
            throw new Error('Server returned invalid JSON: ' + text.substring(0, 200));
        }
    }))
    .then(data => {
        if (data.success) {
            closeExaminationModal();
            location.reload();
        } else {
            alert('Chyba při vytváření záznamu: ' + (data.error || 'Neznámá chyba'));
        }
    })
    .catch(error => {
        alert('Chyba při komunikaci se serverem: ' + error.message);
        console.error('Error:', error);
    });
});

// Edit/Delete examination functions
let currentExaminationIds = [];

function openEditExaminationModal(examIds) {
    currentExaminationIds = examIds;
    document.getElementById('editExaminationModal').style.display = 'block';

    fetch(`/examinations/details?ids=${examIds.join(',')}`)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                displayExaminationDetails(data.examinations);
            } else {
                alert('Chyba při načítání vyšetření: ' + (data.error || 'Neznámá chyba'));
                closeEditExaminationModal();
            }
        })
        .catch(error => {
            alert('Chyba při komunikaci se serverem: ' + error.message);
            console.error('Error:', error);
            closeEditExaminationModal();
        });
}

function escapeHtml(text) {
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}

function displayExaminationDetails(examinations) {
    const container = document.getElementById('editExaminationContent');
    let html = '';

    examinations.forEach((exam, index) => {
        html += `
            <div class="exam-entry-card" style="margin-bottom: 15px;" data-exam-id="${exam.id}">
                <div class="exam-entry-header" style="margin-bottom: 15px;">
                    <span>Vyšetření #${index + 1}</span>
                </div>
                <div class="form-group" style="margin-bottom: 10px;">
                    <label><strong>Typ vyšetření: *</strong></label>
                    <select class="form-control" data-field="sample_type" required>
                        <option value="">Vyberte typ...</option>
                        <option value="Flotace" ${exam.sample_type === 'Flotace' ? 'selected' : ''}>Flotace</option>
                        <option value="Larvoskopie" ${exam.sample_type === 'Larvoskopie' ? 'selected' : ''}>Larvoskopie</option>
                        <option value="Sedimentace" ${exam.sample_type === 'Sedimentace' ? 'selected' : ''}>Sedimentace</option>
                        <option value="EPG" ${exam.sample_type === 'EPG' ? 'selected' : ''}>EPG</option>
                        <option value="OPG" ${exam.sample_type === 'OPG' ? 'selected' : ''}>OPG</option>
                    </select>
                </div>
                <div class="form-group" style="margin-bottom: 10px;">
                    <label><strong>Datum: *</strong></label>
                    <input type="date" class="form-control" data-field="examination_date" value="${exam.examination_date}" required>
                </div>
                <div class="form-group" style="margin-bottom: 10px;">
                    <label><strong>Instituce: *</strong></label>
                    <input type="text" class="form-control" data-field="institution" value="${escapeHtml(exam.institution || '')}" required>
                </div>
                <div class="form-group" style="margin-bottom: 10px;">
                    <label><strong>Nalezený parazit:</strong></label>
                    <input type="text" class="form-control" data-field="parasite_found" value="${escapeHtml(exam.parasite_found || '')}">
                </div>
                <div class="form-group" style="margin-bottom: 10px;">
                    <label><strong>Intenzita: *</strong></label>
                    <select class="form-control" data-field="intensity" required>
                        <option value="">Vyberte intenzitu...</option>
                        <option value="neg." ${exam.intensity === 'neg.' ? 'selected' : ''}>neg.</option>
                        <option value="+" ${exam.intensity === '+' ? 'selected' : ''}>+</option>
                        <option value="++" ${exam.intensity === '++' ? 'selected' : ''}>++</option>
                        <option value="+++" ${exam.intensity === '+++' ? 'selected' : ''}>+++</option>
                        <option value="++++" ${exam.intensity === '++++' ? 'selected' : ''}>++++</option>
                        <option value="+++++" ${exam.intensity === '+++++' ? 'selected' : ''}>+++++</option>
                        <option value="!!!!!!" ${exam.intensity === '!!!!!!' ? 'selected' : ''}>!!!!!!</option>
                        <option value="custom">Vlastní číslo</option>
                    </select>
                </div>
                <div class="form-group" style="margin-bottom: 10px;">
                    <label><strong>Poznámky:</strong></label>
                    <textarea class="form-control" data-field="notes" rows="2">${escapeHtml(exam.notes || '')}</textarea>
                </div>
            </div>
        `;
    });

    container.innerHTML = html;
}

function closeEditExaminationModal() {
    document.getElementById('editExaminationModal').style.display = 'none';
    currentExaminationIds = [];
}

function saveExaminations() {
    const container = document.getElementById('editExaminationContent');
    const examCards = container.querySelectorAll('.exam-entry-card');
    const updates = [];

    examCards.forEach(card => {
        const examId = card.dataset.examId;
        const sampleType = card.querySelector('[data-field="sample_type"]').value;
        const examinationDate = card.querySelector('[data-field="examination_date"]').value;
        const institution = card.querySelector('[data-field="institution"]').value;
        const parasiteFound = card.querySelector('[data-field="parasite_found"]').value;
        const intensity = card.querySelector('[data-field="intensity"]').value;
        const notes = card.querySelector('[data-field="notes"]').value;

        if (!sampleType || !examinationDate || !institution || !intensity) {
            alert('Vyplňte všechna povinná pole');
            return;
        }

        updates.push({
            id: examId,
            sample_type: sampleType,
            examination_date: examinationDate,
            institution: institution,
            parasite_found: parasiteFound || null,
            intensity: intensity,
            notes: notes || null
        });
    });

    if (updates.length === 0) return;

    fetch('/examinations/update', {
        method: 'POST',
        headers: {'Content-Type': 'application/json'},
        body: JSON.stringify({examinations: updates})
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            closeEditExaminationModal();
            location.reload();
        } else {
            alert('Chyba: ' + (data.error || 'Neznámá chyba'));
        }
    })
    .catch(error => {
        alert('Chyba: ' + error.message);
    });
}

function deleteExaminations() {
    if (!confirm('Opravdu chcete smazat všechny záznamy z tohoto vyšetření?')) return;

    fetch('/examinations/delete', {
        method: 'POST',
        headers: {'Content-Type': 'application/json'},
        body: JSON.stringify({examination_ids: currentExaminationIds})
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            closeEditExaminationModal();
            location.reload();
        } else {
            alert('Chyba: ' + (data.error || 'Neznámá chyba'));
        }
    })
    .catch(error => {
        alert('Chyba: ' + error.message);
    });
}
</script>

<!-- Print History Modal -->
<div id="printModal" class="modal">
    <div class="modal-content modal-content-wide">
        <div class="modal-header">
            <h2>Tisk historie vyšetření</h2>
            <span class="modal-close" onclick="closePrintModal()">&times;</span>
        </div>
        <form id="printForm">
            <div class="form-group">
                <label>Počet vyšetření k vytištění (1-10): *</label>
                <input type="number" id="print_count" name="print_count" class="form-control" min="1" max="10" value="5" required>
            </div>

            <div class="form-group">
                <label>Tisknout pro: *</label>
                <div class="exam-target-tabs">
                    <button type="button" class="tab-btn active" onclick="switchPrintTarget('animals')">Jednotlivá zvířata</button>
                    <button type="button" class="tab-btn" onclick="switchPrintTarget('enclosures')">Celé výběhy</button>
                </div>
            </div>

            <div id="printAnimalsTarget" class="target-selection">
                <label>Vyberte zvířata: *</label>
                <input type="text" id="printAnimalSearch" class="form-control" placeholder="Hledat zvíře..." onkeyup="filterPrintAnimals()">
                <div class="checkbox-list" id="printAnimalsList">
                    <?php foreach ($animals as $animal): ?>
                        <label class="checkbox-item" data-name="<?= htmlspecialchars(strtolower($animal['name'] ?? $animal['identifier'])) ?>" data-species="<?= htmlspecialchars(strtolower($animal['species'])) ?>">
                            <input type="checkbox" name="animal_ids[]" value="<?= $animal['animal_id'] ?>">
                            <span><?= htmlspecialchars($animal['name'] ?? $animal['identifier']) ?> (<?= htmlspecialchars($animal['species']) ?>)</span>
                        </label>
                    <?php endforeach; ?>
                </div>
            </div>

            <div id="printEnclosuresTarget" class="target-selection" style="display: none;">
                <label>Vyberte výběhy: *</label>
                <input type="text" id="printEnclosureSearch" class="form-control" placeholder="Hledat výběh..." onkeyup="filterPrintEnclosures()">
                <div class="checkbox-list" id="printEnclosuresList">
                    <?php foreach ($enclosures as $enclosure): ?>
                        <label class="checkbox-item" data-name="<?= htmlspecialchars(strtolower($enclosure['name'])) ?>">
                            <input type="checkbox" name="enclosure_ids[]" value="<?= $enclosure['id'] ?>">
                            <span><?= htmlspecialchars($enclosure['name']) ?></span>
                        </label>
                    <?php endforeach; ?>
                </div>
            </div>

            <div class="form-actions">
                <button type="button" class="btn btn-primary" onclick="generatePrint()">Generovat tisk</button>
                <button type="button" class="btn btn-outline" onclick="closePrintModal()">Zrušit</button>
            </div>
        </form>
    </div>
</div>

<script>
function openPrintModal() {
    document.getElementById('printModal').style.display = 'block';
}

function closePrintModal() {
    document.getElementById('printModal').style.display = 'none';
    document.getElementById('printForm').reset();
}

function switchPrintTarget(target) {
    const tabs = document.querySelectorAll('#printModal .tab-btn');
    tabs.forEach(tab => tab.classList.remove('active'));
    event.target.classList.add('active');

    if (target === 'animals') {
        document.getElementById('printAnimalsTarget').style.display = 'block';
        document.getElementById('printEnclosuresTarget').style.display = 'none';
    } else {
        document.getElementById('printAnimalsTarget').style.display = 'none';
        document.getElementById('printEnclosuresTarget').style.display = 'block';
    }
}

function filterPrintAnimals() {
    const searchTerm = document.getElementById('printAnimalSearch').value.toLowerCase();
    const items = document.querySelectorAll('#printAnimalsList .checkbox-item');
    items.forEach(item => {
        const name = item.dataset.name || '';
        const species = item.dataset.species || '';
        if (name.includes(searchTerm) || species.includes(searchTerm)) {
            item.style.display = 'block';
        } else {
            item.style.display = 'none';
        }
    });
}

function filterPrintEnclosures() {
    const searchTerm = document.getElementById('printEnclosureSearch').value.toLowerCase();
    const items = document.querySelectorAll('#printEnclosuresList .checkbox-item');
    items.forEach(item => {
        const name = item.dataset.name || '';
        if (name.includes(searchTerm)) {
            item.style.display = 'block';
        } else {
            item.style.display = 'none';
        }
    });
}

function generatePrint() {
    const printCount = document.getElementById('print_count').value;
    const isAnimals = document.getElementById('printAnimalsTarget').style.display !== 'none';

    let selectedIds = [];
    if (isAnimals) {
        const checkboxes = document.querySelectorAll('#printAnimalsList input[type="checkbox"]:checked');
        selectedIds = Array.from(checkboxes).map(cb => cb.value);
        if (selectedIds.length === 0) {
            alert('Vyberte alespoň jedno zvíře');
            return;
        }
    } else {
        const checkboxes = document.querySelectorAll('#printEnclosuresList input[type="checkbox"]:checked');
        selectedIds = Array.from(checkboxes).map(cb => cb.value);
        if (selectedIds.length === 0) {
            alert('Vyberte alespoň jeden výběh');
            return;
        }
    }

    // Build URL with parameters
    const params = new URLSearchParams({
        workplace_id: <?= $workplace['id'] ?>,
        print_count: printCount,
        type: isAnimals ? 'animals' : 'enclosures',
        ids: selectedIds.join(',')
    });

    // Open print preview in new window
    window.open('/print/history?' + params.toString(), '_blank');
}

// Close modal when clicking outside
window.addEventListener('click', function(event) {
    const printModal = document.getElementById('printModal');
    if (event.target === printModal) {
        closePrintModal();
    }
    const dewormingModal = document.getElementById('dewormingModal');
    if (event.target === dewormingModal) {
        closeDewormingModal();
    }
});

// Deworming modal functions
function openDewormingModal() {
    document.getElementById('dewormingModal').style.display = 'block';
}

function closeDewormingModal() {
    document.getElementById('dewormingModal').style.display = 'none';
    document.getElementById('dewormingForm').reset();
    // Clear examination dropdown
    const examSelect = document.getElementById('related_examination_id');
    examSelect.innerHTML = '<option value="">Žádné</option>';
}

function filterDewormingAnimals() {
    const searchTerm = document.getElementById('dewormingAnimalSearch').value.toLowerCase();
    const items = document.querySelectorAll('#dewormingAnimalsList .checkbox-item');
    items.forEach(item => {
        const name = item.dataset.name || '';
        const species = item.dataset.species || '';
        if (name.includes(searchTerm) || species.includes(searchTerm)) {
            item.style.display = 'block';
        } else {
            item.style.display = 'none';
        }
    });
}

// Update related examinations dropdown based on selected animals
function updateRelatedExaminations() {
    const checkboxes = document.querySelectorAll('#dewormingAnimalsList input[type="checkbox"]:checked');
    const selectedAnimalIds = Array.from(checkboxes).map(cb => cb.value);
    const examSelect = document.getElementById('related_examination_id');

    // Clear current options
    examSelect.innerHTML = '<option value="">Žádné</option>';

    // If no animals selected, keep empty
    if (selectedAnimalIds.length === 0) {
        return;
    }

    // Fetch examinations for selected animals
    fetch('/examinations/by-animals?animal_ids=' + selectedAnimalIds.join(','))
        .then(response => response.json())
        .then(data => {
            if (data.success && data.examinations) {
                // Group examinations by date and institution to avoid duplicates
                const grouped = {};
                data.examinations.forEach(exam => {
                    const key = exam.examination_date + '|' + (exam.institution || '');
                    if (!grouped[key]) {
                        grouped[key] = exam;
                    }
                });

                // Add options to dropdown
                Object.values(grouped).forEach(exam => {
                    const option = document.createElement('option');
                    option.value = exam.id;
                    const date = new Date(exam.examination_date);
                    const dateStr = date.toLocaleDateString('cs-CZ');
                    option.textContent = `${dateStr} - ${exam.institution || '-'} - ${exam.sample_type || '-'} - ${exam.finding_status || '-'}`;
                    examSelect.appendChild(option);
                });
            }
        })
        .catch(error => {
            console.error('Error loading examinations:', error);
        });
}

// Handle deworming form submission
document.addEventListener('DOMContentLoaded', function() {
    const dewormingForm = document.getElementById('dewormingForm');
    if (dewormingForm) {
        dewormingForm.addEventListener('submit', function(e) {
            e.preventDefault();

            const formData = new FormData(this);

            // Check if at least one animal is selected
            const checkboxes = document.querySelectorAll('#dewormingAnimalsList input[type="checkbox"]:checked');
            if (checkboxes.length === 0) {
                alert('Vyberte alespoň jedno zvíře');
                return;
            }

            fetch('/dewormings/create', {
                method: 'POST',
                body: formData
            })
            .then(response => response.text().then(text => {
                try {
                    return JSON.parse(text);
                } catch (e) {
                    throw new Error('Server returned invalid JSON: ' + text.substring(0, 200));
                }
            }))
            .then(data => {
                if (data.success) {
                    closeDewormingModal();
                    location.reload();
                } else {
                    alert('Chyba při vytváření odčervení: ' + (data.error || 'Neznámá chyba'));
                }
            })
            .catch(error => {
                alert('Chyba při komunikaci se serverem: ' + error.message);
                console.error('Error:', error);
            });
        });
    }
});
</script>

<!-- Deworming Modal -->
<div id="dewormingModal" class="modal">
    <div class="modal-content modal-content-wide">
        <div class="modal-header">
            <h2>Aplikace antiparazitika</h2>
            <span class="modal-close" onclick="closeDewormingModal()">&times;</span>
        </div>
        <form id="dewormingForm">
            <div class="form-row">
                <div class="form-group">
                    <label for="deworming_date">Datum odčervení: *</label>
                    <input type="date" id="deworming_date" name="deworming_date" class="form-control" required value="<?= date('Y-m-d') ?>">
                </div>

                <div class="form-group">
                    <label for="medication">Přípravek:</label>
                    <input type="text" id="medication" name="medication" class="form-control" placeholder="Např. Ivermectin, Fenbendazol...">
                </div>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label for="dosage">Dávka:</label>
                    <input type="text" id="dosage" name="dosage" class="form-control" placeholder="Např. 5ml, 2 tablety...">
                </div>

                <div class="form-group">
                    <label for="administration_route">Forma podání:</label>
                    <select id="administration_route" name="administration_route" class="form-control">
                        <option value="">Vyberte formu podání...</option>
                        <option value="p.o.">p.o. (per os - ústy)</option>
                        <option value="s.c.">s.c. (subkutánně - pod kůži)</option>
                        <option value="i.m.">i.m. (intramuskulárně - do svalu)</option>
                    </select>
                </div>
            </div>

            <div class="form-group">
                <label for="reason">Důvod:</label>
                <select id="reason" name="reason" class="form-control">
                    <option value="">Vyberte důvod...</option>
                    <option value="odčervení">Odčervení</option>
                    <option value="preventivní">Preventivní</option>
                </select>
            </div>

            <div class="form-group">
                <label>Vyberte zvířata: *</label>
                <input type="text" id="dewormingAnimalSearch" class="form-control" placeholder="Hledat zvíře..." onkeyup="filterDewormingAnimals()">
                <div class="checkbox-list" id="dewormingAnimalsList">
                    <?php foreach ($animals as $animal): ?>
                        <label class="checkbox-item" data-name="<?= htmlspecialchars(strtolower($animal['name'] ?? $animal['identifier'])) ?>" data-species="<?= htmlspecialchars(strtolower($animal['species'])) ?>">
                            <input type="checkbox" name="animal_ids[]" value="<?= $animal['animal_id'] ?>" onchange="updateRelatedExaminations()">
                            <span><?= htmlspecialchars($animal['name'] ?? $animal['identifier']) ?> (<?= htmlspecialchars($animal['species']) ?>)</span>
                        </label>
                    <?php endforeach; ?>
                </div>
            </div>

            <div class="form-group">
                <label for="related_examination_id">Související vyšetření:</label>
                <select id="related_examination_id" name="related_examination_id" class="form-control">
                    <option value="">Žádné</option>
                </select>
                <small class="form-text" style="color: #7f8c8d; font-size: 12px; margin-top: 5px; display: block;">Vyberte vyšetření spojené s touto aplikací antiparazitika</small>
            </div>

            <div class="form-group">
                <label for="deworming_notes">Poznámky:</label>
                <textarea id="deworming_notes" name="notes" class="form-control" rows="3"></textarea>
            </div>

            <div class="form-actions">
                <button type="submit" class="btn btn-primary">Přidat odčervení</button>
                <button type="button" class="btn btn-outline" onclick="closeDewormingModal()">Zrušit</button>
            </div>
        </form>
    </div>
</div>
