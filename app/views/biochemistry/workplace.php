<div class="container">
    <div class="breadcrumb">
        <a href="/">Pracoviště</a> /
        <span><?= htmlspecialchars($workplace['name']) ?></span>
    </div>

    <div class="page-header">
        <div>
            <h1><?= htmlspecialchars($workplace['name']) ?></h1>
            <p>Biochemie a hematologie</p>
        </div>
        <div style="display: flex; gap: 10px;">
            <a href="/biochemistry/workplace/<?= $workplace['id'] ?>/advanced-search" class="btn btn-info">
                🔍 Vyhledávání
            </a>
            <a href="/biochemistry/workplace/<?= $workplace['id'] ?>/graph" class="btn btn-success">
                📊 Vytvořit graf
            </a>
            <?php if ($canEdit): ?>
                <a href="/workplace/<?= $workplace['id'] ?>/animals/create?from=biochemistry" class="btn btn-primary">
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
            <table class="animals-table" id="biochem-table">
                <thead>
                    <tr>
                        <th class="sortable" onclick="sortTable(0)">ID <span class="sort-icon">⇅</span></th>
                        <th class="sortable" onclick="sortTable(1)">Jméno <span class="sort-icon">⇅</span></th>
                        <th class="sortable" onclick="sortTable(2)">Druh <span class="sort-icon">⇅</span></th>
                        <th class="sortable" onclick="sortTable(3)">Výběh <span class="sort-icon">⇅</span></th>
                        <th class="sortable" onclick="sortTable(4)">Poslední biochemie <span class="sort-icon">⇅</span></th>
                        <th class="sortable" onclick="sortTable(5)">Poslední hematologie <span class="sort-icon">⇅</span></th>
                        <th>Akce</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($animals as $animal): ?>
                        <tr>
                            <td><strong><?= htmlspecialchars($animal['identifier'] ?? '-') ?></strong></td>
                            <td>
                                <a href="/biochemistry/animal/<?= $animal['id'] ?>" style="color: #2c3e50; text-decoration: none; font-weight: 500;">
                                    <?= htmlspecialchars($animal['name']) ?>
                                </a>
                            </td>
                            <td><?= htmlspecialchars($animal['species']) ?></td>
                            <td><?= htmlspecialchars($animal['enclosure_name'] ?? '-') ?></td>
                            <td>
                                <span class="test-date">
                                    <?php if ($animal['last_biochemistry']): ?>
                                        <?= date('d.m.Y', strtotime($animal['last_biochemistry'])) ?>
                                    <?php else: ?>
                                        <span class="text-muted">Bez záznamu</span>
                                    <?php endif; ?>
                                </span>
                            </td>
                            <td>
                                <span class="test-date">
                                    <?php if ($animal['last_hematology']): ?>
                                        <?= date('d.m.Y', strtotime($animal['last_hematology'])) ?>
                                    <?php else: ?>
                                        <span class="text-muted">Bez záznamu</span>
                                    <?php endif; ?>
                                </span>
                            </td>
                            <td>
                                <a href="/biochemistry/animal/<?= $animal['id'] ?>" class="btn btn-sm btn-primary">
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
    color: #667eea;
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
    margin: 0 0 5px 0;
    color: #2c3e50;
}

.page-header p {
    margin: 0;
    color: #7f8c8d;
    font-size: 14px;
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
    background: linear-gradient(135deg, #c0392b 0%, #a93226 100%);
    color: white;
}

.animals-table th {
    padding: 15px;
    text-align: left;
    font-weight: 600;
    font-size: 14px;
}

.animals-table th.sortable {
    cursor: pointer;
    user-select: none;
    transition: background-color 0.2s;
}

.animals-table th.sortable:hover {
    background: linear-gradient(135deg, #a93226 0%, #8f2a20 100%);
}

.animals-table th.sortable .sort-icon {
    margin-left: 5px;
    opacity: 0.6;
    font-size: 0.9em;
}

.animals-table th.sortable[data-sort="asc"] .sort-icon::before {
    content: "▲";
    opacity: 1;
}

.animals-table th.sortable[data-sort="desc"] .sort-icon::before {
    content: "▼";
    opacity: 1;
}

.animals-table td {
    padding: 12px 15px;
    border-bottom: 1px solid #f0f0f0;
}

.animals-table tbody tr:hover {
    background-color: #f8f9fa;
}

.animals-table tbody td a:hover {
    color: #27ae60 !important;
    text-decoration: underline !important;
}

.animals-table tbody tr:last-child td {
    border-bottom: none;
}

.test-date {
    font-size: 13px;
}

.text-muted {
    color: #999;
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

.btn-success {
    background: linear-gradient(135deg, #27ae60 0%, #229954 100%);
    color: white;
    padding: 10px 20px;
    border-radius: 4px;
    font-size: 14px;
    text-decoration: none;
    display: inline-block;
    transition: all 0.2s;
    border: none;
    cursor: pointer;
}

.btn-success:hover {
    background: linear-gradient(135deg, #229954 0%, #1e8449 100%);
    transform: translateY(-1px);
    box-shadow: 0 4px 12px rgba(39, 174, 96, 0.3);
}

.btn-info {
    background: linear-gradient(135deg, #3498db 0%, #2980b9 100%);
    color: white;
    padding: 10px 20px;
    border-radius: 4px;
    font-size: 14px;
    text-decoration: none;
    display: inline-block;
    transition: all 0.2s;
    border: none;
    cursor: pointer;
}

.btn-info:hover {
    background: linear-gradient(135deg, #2980b9 0%, #21618c 100%);
    transform: translateY(-1px);
    box-shadow: 0 4px 12px rgba(52, 152, 219, 0.3);
}
</style>

<script>
function sortTable(columnIndex) {
    const table = document.getElementById('biochem-table');
    const tbody = table.querySelector('tbody');
    const rows = Array.from(tbody.querySelectorAll('tr'));
    const th = table.querySelectorAll('th.sortable')[columnIndex];
    const currentSort = th.getAttribute('data-sort');

    // Clear all sort indicators
    table.querySelectorAll('th.sortable').forEach(header => {
        header.removeAttribute('data-sort');
    });

    // Determine new sort direction
    const newSort = currentSort === 'asc' ? 'desc' : 'asc';
    th.setAttribute('data-sort', newSort);

    // Sort rows
    rows.sort((a, b) => {
        const aValue = a.cells[columnIndex].textContent.trim();
        const bValue = b.cells[columnIndex].textContent.trim();

        // Handle empty values
        if (aValue === '-' || aValue === 'Bez záznamu') return 1;
        if (bValue === '-' || bValue === 'Bez záznamu') return -1;

        // Compare values
        const comparison = aValue.localeCompare(bValue, 'cs', { numeric: true, sensitivity: 'base' });
        return newSort === 'asc' ? comparison : -comparison;
    });

    // Reappend sorted rows
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
    background: linear-gradient(135deg, #c0392b 0%, #a93226 100%);
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
    border-color: #c0392b;
    box-shadow: 0 0 0 0.2rem rgba(192, 57, 43, 0.25);
}

.form-actions {
    display: flex;
    gap: 10px;
    margin-top: 20px;
}

.btn-outline {
    background: white;
    border: 2px solid #c0392b;
    color: #c0392b;
}

.btn-outline:hover {
    background: #c0392b;
    color: white;
}
</style>
