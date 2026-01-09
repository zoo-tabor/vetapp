<div class="container">
    <div class="page-header">
        <div>
            <h1>Centrální databáze zvířat</h1>
            <p>Všechna zvířata ze všech pracovišť</p>
        </div>
        <a href="/animals" class="btn btn-secondary">← Zpět</a>
    </div>

    <?php if (empty($animals)): ?>
        <div class="alert alert-info">
            <strong>Žádná zvířata</strong><br>
            V databázi nejsou zatím žádná aktivní zvířata.
        </div>
    <?php else: ?>
        <!-- Filter controls -->
        <div class="filter-controls">
            <div class="filter-group">
                <label for="workplaceFilter">Filtr pracoviště:</label>
                <select id="workplaceFilter" class="form-control">
                    <option value="">Všechna pracoviště</option>
                    <?php foreach ($workplaces as $workplace): ?>
                        <option value="<?= $workplace['id'] ?>"><?= htmlspecialchars($workplace['name']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="filter-group">
                <label for="searchInput">Vyhledat:</label>
                <input type="text" id="searchInput" class="form-control" placeholder="Jméno, ID, druh...">
            </div>
        </div>

        <!-- Animals table -->
        <div class="table-responsive">
            <table class="animals-table" id="animalsTable">
                <thead>
                    <tr>
                        <th class="sortable" onclick="sortTable(0)">Pracoviště <span class="sort-icon">⇅</span></th>
                        <th class="sortable" onclick="sortTable(1)">Jméno <span class="sort-icon">⇅</span></th>
                        <th class="sortable" onclick="sortTable(2)">ID zvířete <span class="sort-icon">⇅</span></th>
                        <th class="sortable" onclick="sortTable(3)">Druh <span class="sort-icon">⇅</span></th>
                        <th class="sortable" onclick="sortTable(4)">Datum narození <span class="sort-icon">⇅</span></th>
                        <th class="sortable" onclick="sortTable(5)">Pohlaví <span class="sort-icon">⇅</span></th>
                        <th>Akce</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    // Map workplace names to badge colors
                    $workplaceBadges = [
                        'ZOO Tábor' => 'badge-zoo-tabor',
                        'Babice' => 'badge-babice',
                        'Lipence' => 'badge-lipence'
                    ];

                    foreach ($animals as $animal):
                        $badgeClass = $workplaceBadges[$animal['workplace_name']] ?? 'badge-workplace';
                    ?>
                        <tr data-workplace-id="<?= $animal['workplace_id'] ?>"
                            data-search="<?= strtolower(htmlspecialchars($animal['name'] . ' ' . $animal['identifier'] . ' ' . $animal['species'] . ' ' . $animal['workplace_name'])) ?>">
                            <td>
                                <span class="badge <?= $badgeClass ?>"><?= htmlspecialchars($animal['workplace_name']) ?></span>
                            </td>
                            <td class="animal-name">
                                <?= htmlspecialchars($animal['name'] ?: '-') ?>
                            </td>
                            <td class="animal-id">
                                <?= htmlspecialchars($animal['identifier'] ?: '-') ?>
                            </td>
                            <td><?= htmlspecialchars($animal['species']) ?></td>
                            <td>
                                <?= $animal['birth_date'] ? date('d.m.Y', strtotime($animal['birth_date'])) : '-' ?>
                            </td>
                            <td>
                                <?php
                                $genderLabels = [
                                    'male' => '♂ Samec',
                                    'female' => '♀ Samice',
                                    'unknown' => '? Neznámé'
                                ];
                                echo $genderLabels[$animal['gender']] ?? '-';
                                ?>
                            </td>
                            <td>
                                <a href="/animals/detail/<?= $animal['id'] ?>" class="btn btn-sm btn-primary">
                                    Detail
                                </a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <div class="table-footer">
            <p>Celkem: <strong id="totalCount"><?= count($animals) ?></strong> zvířat</p>
        </div>

        <!-- Deceased Animals Section -->
        <?php if (!empty($deceasedAnimals)): ?>
            <div class="deceased-section">
                <h2>† Uhynulá zvířata</h2>

                <div class="table-responsive">
                    <table class="animals-table deceased-table" id="deceasedAnimalsTable">
                        <thead>
                            <tr>
                                <th class="sortable" onclick="sortDeceasedTable(0)">Pracoviště <span class="sort-icon">⇅</span></th>
                                <th class="sortable" onclick="sortDeceasedTable(1)">Jméno <span class="sort-icon">⇅</span></th>
                                <th class="sortable" onclick="sortDeceasedTable(2)">ID zvířete <span class="sort-icon">⇅</span></th>
                                <th class="sortable" onclick="sortDeceasedTable(3)">Druh <span class="sort-icon">⇅</span></th>
                                <th class="sortable" onclick="sortDeceasedTable(4)">Datum narození <span class="sort-icon">⇅</span></th>
                                <th class="sortable" onclick="sortDeceasedTable(5)">Pohlaví <span class="sort-icon">⇅</span></th>
                                <th>Akce</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            // Map workplace names to badge colors
                            $workplaceBadges = [
                                'ZOO Tábor' => 'badge-zoo-tabor',
                                'Babice' => 'badge-babice',
                                'Lipence' => 'badge-lipence'
                            ];

                            foreach ($deceasedAnimals as $animal):
                                $badgeClass = $workplaceBadges[$animal['workplace_name']] ?? 'badge-workplace';
                            ?>
                                <tr data-workplace-id="<?= $animal['workplace_id'] ?>"
                                    data-search="<?= strtolower(htmlspecialchars($animal['name'] . ' ' . $animal['identifier'] . ' ' . $animal['species'] . ' ' . $animal['workplace_name'])) ?>"
                                    class="deceased-row">
                                    <td>
                                        <span class="badge <?= $badgeClass ?>"><?= htmlspecialchars($animal['workplace_name']) ?></span>
                                    </td>
                                    <td class="animal-name">
                                        <?= htmlspecialchars($animal['name'] ?: '-') ?>
                                    </td>
                                    <td class="animal-id">
                                        <?= htmlspecialchars($animal['identifier'] ?: '-') ?>
                                    </td>
                                    <td><?= htmlspecialchars($animal['species']) ?></td>
                                    <td>
                                        <?= $animal['birth_date'] ? date('d.m.Y', strtotime($animal['birth_date'])) : '-' ?>
                                    </td>
                                    <td>
                                        <?php
                                        $genderLabels = [
                                            'male' => '♂ Samec',
                                            'female' => '♀ Samice',
                                            'unknown' => '? Neznámé'
                                        ];
                                        echo $genderLabels[$animal['gender']] ?? '-';
                                        ?>
                                    </td>
                                    <td>
                                        <a href="/animals/detail/<?= $animal['id'] ?>" class="btn btn-sm btn-secondary">
                                            Detail
                                        </a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>

                <div class="table-footer">
                    <p>Celkem: <strong id="deceasedTotalCount"><?= count($deceasedAnimals) ?></strong> uhynulých zvířat</p>
                </div>
            </div>
        <?php endif; ?>
    <?php endif; ?>
</div>

<style>
.container {
    max-width: 1400px;
    margin: 0 auto;
    padding: 20px;
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

.filter-controls {
    background: white;
    padding: 20px;
    border-radius: 8px;
    margin-bottom: 20px;
    display: flex;
    gap: 20px;
    flex-wrap: wrap;
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
}

.filter-group {
    flex: 1;
    min-width: 200px;
}

.filter-group label {
    display: block;
    margin-bottom: 5px;
    font-weight: 600;
    color: #2c3e50;
}

.form-control {
    width: 100%;
    padding: 8px 12px;
    border: 1px solid #ddd;
    border-radius: 4px;
    font-size: 14px;
}

.table-responsive {
    background: white;
    border-radius: 8px;
    overflow-x: auto;
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
}

.animals-table {
    width: 100%;
    border-collapse: collapse;
}

.animals-table thead {
    background: #8e44ad;
    color: white;
}

.animals-table th {
    padding: 15px;
    text-align: left;
    font-weight: 600;
}

.animals-table th.sortable {
    cursor: pointer;
    user-select: none;
    transition: background-color 0.2s;
}

.animals-table th.sortable:hover {
    background-color: #7d3c98;
}

.sort-icon {
    font-size: 12px;
    margin-left: 5px;
    opacity: 0.5;
}

.animals-table th.sorted {
    background-color: #8e44ad;
}

.animals-table th.sorted .sort-icon {
    opacity: 1;
}

.animals-table tbody tr {
    border-bottom: 1px solid #ecf0f1;
    transition: background-color 0.2s;
}

.animals-table tbody tr:hover {
    background-color: #f8f9fa;
}

.animals-table td {
    padding: 12px 15px;
}

.animal-name {
    font-weight: 600;
    color: #2c3e50;
}

.animal-id {
    font-family: 'Courier New', monospace;
    color: #7f8c8d;
}

.badge {
    display: inline-block;
    padding: 4px 12px;
    border-radius: 12px;
    font-size: 12px;
    font-weight: 600;
}

.badge-workplace {
    background-color: #e8daef;
    color: #6c3483;
}

.badge-zoo-tabor {
    background-color: #ffe0b2;
    color: #e65100;
}

.badge-babice {
    background-color: #c8e6c9;
    color: #1b5e20;
}

.badge-lipence {
    background-color: #d7ccc8;
    color: #4e342e;
}

.deceased-section {
    margin-top: 60px;
    padding-top: 40px;
    border-top: 3px solid #95a5a6;
}

.deceased-section h2 {
    color: #7f8c8d;
    font-size: 28px;
    font-weight: 700;
    margin-bottom: 20px;
}

.deceased-table thead {
    background: #95a5a6 !important;
}

.deceased-table th.sortable:hover {
    background-color: #7f8c8d !important;
}

.deceased-row {
    opacity: 0.85;
}

.deceased-row:hover {
    background-color: #ecf0f1 !important;
}

.btn {
    padding: 8px 16px;
    border-radius: 4px;
    text-decoration: none;
    display: inline-block;
    font-weight: 600;
    border: none;
    cursor: pointer;
    transition: all 0.3s;
}

.btn-primary {
    background-color: #8e44ad;
    color: white;
}

.btn-primary:hover {
    background-color: #7d3c98;
}

.btn-secondary {
    background-color: #95a5a6;
    color: white;
}

.btn-secondary:hover {
    background-color: #7f8c8d;
}

.btn-sm {
    padding: 6px 12px;
    font-size: 14px;
}

.table-footer {
    background: white;
    padding: 15px 20px;
    border-radius: 0 0 8px 8px;
    margin-top: -8px;
}

.alert {
    padding: 20px;
    border-radius: 8px;
    margin-bottom: 20px;
}

.alert-info {
    background-color: #d1ecf1;
    border: 1px solid #bee5eb;
    color: #0c5460;
}

@media (max-width: 768px) {
    .filter-controls {
        flex-direction: column;
    }

    .filter-group {
        width: 100%;
    }

    .animals-table {
        font-size: 14px;
    }

    .animals-table th,
    .animals-table td {
        padding: 10px;
    }
}
</style>

<script>
let sortDirection = {};

function sortTable(columnIndex) {
    const table = document.getElementById('animalsTable');
    const tbody = table.querySelector('tbody');
    const rows = Array.from(tbody.querySelectorAll('tr'));

    // Initialize sort direction for this column
    if (!sortDirection[columnIndex]) {
        sortDirection[columnIndex] = 'asc';
    } else {
        sortDirection[columnIndex] = sortDirection[columnIndex] === 'asc' ? 'desc' : 'asc';
    }

    const direction = sortDirection[columnIndex];

    // Update header styling
    const headers = table.querySelectorAll('th.sortable');
    headers.forEach((header, index) => {
        if (index === columnIndex) {
            header.classList.add('sorted');
            const icon = header.querySelector('.sort-icon');
            icon.textContent = direction === 'asc' ? '↑' : '↓';
        } else {
            header.classList.remove('sorted');
            const icon = header.querySelector('.sort-icon');
            icon.textContent = '⇅';
        }
    });

    // Sort rows
    rows.sort((a, b) => {
        let aValue = a.cells[columnIndex].textContent.trim();
        let bValue = b.cells[columnIndex].textContent.trim();

        // Handle dates (format: dd.mm.yyyy)
        if (columnIndex === 4) { // Birth date column
            const parseDate = (str) => {
                if (str === '-') return new Date(0);
                const parts = str.split('.');
                return new Date(parts[2], parts[1] - 1, parts[0]);
            };
            aValue = parseDate(aValue);
            bValue = parseDate(bValue);
            return direction === 'asc' ? aValue - bValue : bValue - aValue;
        }

        // String comparison
        const comparison = aValue.localeCompare(bValue, 'cs', { sensitivity: 'base' });
        return direction === 'asc' ? comparison : -comparison;
    });

    // Reorder table
    rows.forEach(row => tbody.appendChild(row));
}

document.addEventListener('DOMContentLoaded', function() {
    const workplaceFilter = document.getElementById('workplaceFilter');
    const searchInput = document.getElementById('searchInput');
    const table = document.getElementById('animalsTable');
    const tbody = table.querySelector('tbody');
    const totalCount = document.getElementById('totalCount');

    function filterTable() {
        const workplaceId = workplaceFilter.value;
        const searchTerm = searchInput.value.toLowerCase();
        let visibleCount = 0;

        const rows = tbody.querySelectorAll('tr');
        rows.forEach(row => {
            const rowWorkplaceId = row.getAttribute('data-workplace-id');
            const searchData = row.getAttribute('data-search');

            let show = true;

            // Filter by workplace
            if (workplaceId && rowWorkplaceId !== workplaceId) {
                show = false;
            }

            // Filter by search term
            if (searchTerm && !searchData.includes(searchTerm)) {
                show = false;
            }

            if (show) {
                row.style.display = '';
                visibleCount++;
            } else {
                row.style.display = 'none';
            }
        });

        totalCount.textContent = visibleCount;
    }

    workplaceFilter.addEventListener('change', filterTable);
    searchInput.addEventListener('input', filterTable);

    // Also filter deceased animals table
    const deceasedTable = document.getElementById('deceasedAnimalsTable');
    if (deceasedTable) {
        const deceasedTbody = deceasedTable.querySelector('tbody');
        const deceasedTotalCount = document.getElementById('deceasedTotalCount');

        function filterDeceasedTable() {
            const workplaceId = workplaceFilter.value;
            const searchTerm = searchInput.value.toLowerCase();
            let visibleCount = 0;

            const rows = deceasedTbody.querySelectorAll('tr');
            rows.forEach(row => {
                const rowWorkplaceId = row.getAttribute('data-workplace-id');
                const searchData = row.getAttribute('data-search');

                let show = true;

                // Filter by workplace
                if (workplaceId && rowWorkplaceId !== workplaceId) {
                    show = false;
                }

                // Filter by search term
                if (searchTerm && !searchData.includes(searchTerm)) {
                    show = false;
                }

                if (show) {
                    row.style.display = '';
                    visibleCount++;
                } else {
                    row.style.display = 'none';
                }
            });

            deceasedTotalCount.textContent = visibleCount;
        }

        workplaceFilter.addEventListener('change', filterDeceasedTable);
        searchInput.addEventListener('input', filterDeceasedTable);
    }
});

// Sort function for deceased animals table
let sortDirectionDeceased = {};

function sortDeceasedTable(columnIndex) {
    const table = document.getElementById('deceasedAnimalsTable');
    const tbody = table.querySelector('tbody');
    const rows = Array.from(tbody.querySelectorAll('tr'));

    // Initialize sort direction for this column
    if (!sortDirectionDeceased[columnIndex]) {
        sortDirectionDeceased[columnIndex] = 'asc';
    } else {
        sortDirectionDeceased[columnIndex] = sortDirectionDeceased[columnIndex] === 'asc' ? 'desc' : 'asc';
    }

    const direction = sortDirectionDeceased[columnIndex];

    // Update header styling
    const headers = table.querySelectorAll('th.sortable');
    headers.forEach((header, index) => {
        if (index === columnIndex) {
            header.classList.add('sorted');
            const icon = header.querySelector('.sort-icon');
            icon.textContent = direction === 'asc' ? '↑' : '↓';
        } else {
            header.classList.remove('sorted');
            const icon = header.querySelector('.sort-icon');
            icon.textContent = '⇅';
        }
    });

    // Sort rows
    rows.sort((a, b) => {
        let aValue = a.cells[columnIndex].textContent.trim();
        let bValue = b.cells[columnIndex].textContent.trim();

        // Handle dates (format: dd.mm.yyyy)
        if (columnIndex === 4) { // Birth date column
            const parseDate = (str) => {
                if (str === '-') return new Date(0);
                const parts = str.split('.');
                return new Date(parts[2], parts[1] - 1, parts[0]);
            };
            aValue = parseDate(aValue);
            bValue = parseDate(bValue);
            return direction === 'asc' ? aValue - bValue : bValue - aValue;
        }

        // String comparison
        const comparison = aValue.localeCompare(bValue, 'cs', { sensitivity: 'base' });
        return direction === 'asc' ? comparison : -comparison;
    });

    // Reorder table
    rows.forEach(row => tbody.appendChild(row));
}
</script>
