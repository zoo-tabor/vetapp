<div class="container">
    <div class="breadcrumb">
        <a href="/">Pracoviště</a> /
        <span><?= htmlspecialchars($workplace['name']) ?></span>
    </div>

    <div class="page-header">
        <div>
            <h1><?= htmlspecialchars($workplace['name']) ?></h1>
            <p>Analýza moči</p>
        </div>
        <div style="display: flex; gap: 10px;">
            <a href="/urineanalysis/workplace/<?= $workplace['id'] ?>/search" class="btn btn-info">
                🔍 Vyhledávání
            </a>
            <a href="/urineanalysis/workplace/<?= $workplace['id'] ?>/graph" class="btn btn-success">
                📊 Vytvořit graf
            </a>
            <?php if ($canEdit): ?>
                <a href="/workplace/<?= $workplace['id'] ?>/animals/create?from=urineanalysis" class="btn btn-primary">
                    + Přidat zvíře
                </a>
                <button type="button" class="btn btn-primary" onclick="openEnclosureModal()">
                    + Přidat výběh
                </button>
            <?php endif; ?>
        </div>
    </div>

    <?php if (empty($animals)): ?>
        <div class="alert alert-info">
            <strong>Žádná zvířata</strong><br>
            V tomto pracovišti nejsou zatím žádná zvířata.
        </div>
    <?php else: ?>
        <div class="animals-table-wrapper">
            <table class="animals-table" id="urine-table">
                <thead>
                    <tr>
                        <th class="sortable" onclick="sortTable(0)">ID <span class="sort-icon">⇅</span></th>
                        <th class="sortable" onclick="sortTable(1)">Jméno <span class="sort-icon">⇅</span></th>
                        <th class="sortable" onclick="sortTable(2)">Druh <span class="sort-icon">⇅</span></th>
                        <th class="sortable" onclick="sortTable(3)">Výběh <span class="sort-icon">⇅</span></th>
                        <th class="sortable" onclick="sortTable(4)">Poslední vyšetření <span class="sort-icon">⇅</span></th>
                        <th>Akce</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($animals as $animal): ?>
                        <tr>
                            <td><strong><?= htmlspecialchars($animal['identifier'] ?? '-') ?></strong></td>
                            <td>
                                <a href="/urineanalysis/animal/<?= $animal['id'] ?>" class="animal-name-link">
                                    <?= htmlspecialchars($animal['name']) ?>
                                </a>
                            </td>
                            <td><?= htmlspecialchars($animal['species']) ?></td>
                            <td><?= htmlspecialchars($animal['enclosure_name'] ?? '-') ?></td>
                            <td>
                                <?php if ($animal['last_test_date']): ?>
                                    <?= date('d.m.Y', strtotime($animal['last_test_date'])) ?>
                                <?php else: ?>
                                    <span class="text-muted">Bez záznamu</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <a href="/urineanalysis/animal/<?= $animal['id'] ?>" class="btn btn-sm btn-primary">
                                    Detail
                                </a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
</div>

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
    font-size: 16px;
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

.btn-primary {
    background: linear-gradient(135deg, #f39c12 0%, #e67e22 100%);
    color: white;
}

.btn-primary:hover {
    background: linear-gradient(135deg, #e67e22 0%, #d35400 100%);
}

.btn-success {
    background: linear-gradient(135deg, #27ae60 0%, #229954 100%);
    color: white;
}

.btn-success:hover {
    background: linear-gradient(135deg, #229954 0%, #1e8449 100%);
}

.btn-info {
    background: linear-gradient(135deg, #3498db 0%, #2980b9 100%);
    color: white;
}

.btn-info:hover {
    background: linear-gradient(135deg, #2980b9 0%, #21618c 100%);
}

.btn-sm {
    padding: 6px 12px;
    font-size: 13px;
}

.animals-table-wrapper {
    background: white;
    border-radius: 8px;
    overflow: hidden;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
}

.animals-table {
    width: 100%;
    border-collapse: collapse;
}

.animals-table thead {
    background: linear-gradient(135deg, #f39c12 0%, #e67e22 100%);
    color: white;
}

.animals-table th {
    padding: 15px;
    text-align: left;
    font-weight: 600;
    font-size: 14px;
    cursor: pointer;
    user-select: none;
}

.animals-table th.sortable:hover {
    background: rgba(0, 0, 0, 0.1);
}

.animals-table td {
    padding: 12px 15px;
    border-bottom: 1px solid #ecf0f1;
}

.animals-table tbody tr:hover {
    background: #fef5e7;
}

.animals-table tbody tr:last-child td {
    border-bottom: none;
}

.sort-icon {
    margin-left: 5px;
    opacity: 0.5;
}

.text-muted {
    color: #95a5a6;
    font-style: italic;
}

.alert {
    padding: 20px;
    border-radius: 8px;
    margin-bottom: 20px;
}

.alert-info {
    background-color: #fef5e7;
    border: 1px solid #f39c12;
    color: #7f6007;
}

.animal-name-link {
    color: #2c3e50;
    text-decoration: none;
    font-weight: 500;
    transition: color 0.2s;
}

.animal-name-link:hover {
    color: #f39c12;
    text-decoration: underline;
}
</style>

<script>
function sortTable(columnIndex) {
    const table = document.getElementById('urine-table');
    const tbody = table.querySelector('tbody');
    const rows = Array.from(tbody.querySelectorAll('tr'));

    // Determine sort direction
    const isAscending = table.dataset.sortColumn == columnIndex && table.dataset.sortDirection === 'asc';
    table.dataset.sortColumn = columnIndex;
    table.dataset.sortDirection = isAscending ? 'desc' : 'asc';

    // Sort rows
    rows.sort((a, b) => {
        const aVal = a.cells[columnIndex].textContent.trim();
        const bVal = b.cells[columnIndex].textContent.trim();

        // Try to parse as number
        const aNum = parseFloat(aVal.replace(/[^\d.-]/g, ''));
        const bNum = parseFloat(bVal.replace(/[^\d.-]/g, ''));

        if (!isNaN(aNum) && !isNaN(bNum)) {
            return isAscending ? bNum - aNum : aNum - bNum;
        }

        // String comparison
        return isAscending ? bVal.localeCompare(aVal) : aVal.localeCompare(bVal);
    });

    // Re-append sorted rows
    rows.forEach(row => tbody.appendChild(row));
}

// Enclosure Modal Functions
function openEnclosureModal() {
    document.getElementById('enclosureModal').style.display = 'block';
}

function closeEnclosureModal() {
    document.getElementById('enclosureModal').style.display = 'none';
    document.getElementById('enclosureForm').reset();
}

// Handle enclosure form submission
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

// Close modal when clicking outside
window.onclick = function(event) {
    const modal = document.getElementById('enclosureModal');
    if (event.target == modal) {
        closeEnclosureModal();
    }
}
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

<style>
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
    background-color: #fff;
    margin: 5% auto;
    padding: 0;
    border-radius: 8px;
    width: 90%;
    max-width: 600px;
    box-shadow: 0 4px 16px rgba(0, 0, 0, 0.2);
}

.modal-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 20px;
    background: linear-gradient(135deg, #f39c12 0%, #e67e22 100%);
    color: white;
    border-radius: 8px 8px 0 0;
}

.modal-header h2 {
    margin: 0;
    font-size: 20px;
}

.modal-close {
    color: white;
    font-size: 28px;
    font-weight: bold;
    cursor: pointer;
    line-height: 1;
}

.modal-close:hover {
    opacity: 0.8;
}

.modal form {
    padding: 20px;
}

.form-group {
    margin-bottom: 20px;
}

.form-group label {
    display: block;
    margin-bottom: 8px;
    font-weight: 600;
    color: #2c3e50;
}

.form-control {
    width: 100%;
    padding: 10px;
    border: 1px solid #ddd;
    border-radius: 4px;
    font-size: 14px;
    box-sizing: border-box;
}

.form-control:focus {
    outline: none;
    border-color: #f39c12;
    box-shadow: 0 0 0 0.2rem rgba(243, 156, 18, 0.25);
}

.form-actions {
    display: flex;
    gap: 10px;
    margin-top: 20px;
}

.btn-outline {
    background: white;
    border: 2px solid #f39c12;
    color: #f39c12;
}

.btn-outline:hover {
    background: #f39c12;
    color: white;
}
</style>
