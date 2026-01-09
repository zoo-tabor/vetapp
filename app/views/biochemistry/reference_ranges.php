<div class="container">
    <div class="breadcrumb">
        <a href="/">Pracoviště</a> /
        <span>Správa referenčních hodnot</span>
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
            <h1>Správa referenčních hodnot</h1>
            <p>Definice referenčních rozsahů pro biochemii a hematologii</p>
        </div>
    </div>

    <!-- Species selection -->
    <div class="filter-section">
        <div class="filter-group">
            <label for="speciesFilter">Druh zvířete:</label>
            <select id="speciesFilter" class="form-control" onchange="filterBySpecies()">
                <option value="">Vyberte druh...</option>
                <?php foreach ($species as $s): ?>
                    <option value="<?= htmlspecialchars($s) ?>" <?= $selectedSpecies === $s ? 'selected' : '' ?>>
                        <?= htmlspecialchars($s) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="filter-group">
            <label for="testTypeFilter">Typ testu:</label>
            <select id="testTypeFilter" class="form-control" onchange="filterByTestType()">
                <option value="biochemistry" <?= $testType === 'biochemistry' ? 'selected' : '' ?>>Biochemie</option>
                <option value="hematology" <?= $testType === 'hematology' ? 'selected' : '' ?>>Hematologie</option>
            </select>
        </div>
    </div>

    <?php if (!$selectedSpecies): ?>
        <div class="alert alert-info">
            <strong>Vyberte druh zvířete</strong><br>
            Pro zobrazení a úpravu referenčních hodnot vyberte druh zvířete ze seznamu výše.
        </div>
    <?php else: ?>
        <div class="ranges-section">
            <h2>Referenční hodnoty pro: <?= htmlspecialchars($selectedSpecies) ?> - <?= $testType === 'biochemistry' ? 'Biochemie' : 'Hematologie' ?></h2>

            <!-- Tabs for sources -->
            <div class="source-tabs">
                <?php foreach ($sources as $source): ?>
                    <button class="source-tab <?= $activeSource === $source ? 'active' : '' ?>"
                            onclick="switchSource('<?= htmlspecialchars($source) ?>')">
                        <?= htmlspecialchars($source) ?>
                    </button>
                <?php endforeach; ?>
                <button class="source-tab add-source-btn" onclick="openAddSourceModal()" type="button">
                    <svg width="16" height="16" viewBox="0 0 16 16" fill="currentColor" style="vertical-align: middle; margin-right: 4px;">
                        <path d="M8 0a1 1 0 0 1 1 1v6h6a1 1 0 1 1 0 2H9v6a1 1 0 1 1-2 0V9H1a1 1 0 0 1 0-2h6V1a1 1 0 0 1 1-1z"/>
                    </svg>
                    Přidat zdroj
                </button>
            </div>

            <!-- Parameters table -->
            <div class="parameters-table-wrapper">
                <form method="POST" action="/biochemistry/reference-ranges/save">
                    <input type="hidden" name="species" value="<?= htmlspecialchars($selectedSpecies) ?>">
                    <input type="hidden" name="test_type" value="<?= htmlspecialchars($testType) ?>">
                    <input type="hidden" name="source" value="<?= htmlspecialchars($activeSource) ?>">

                    <table class="parameters-table">
                        <thead>
                            <tr>
                                <th>Parametr</th>
                                <th>Minimální hodnota</th>
                                <th>Maximální hodnota</th>
                                <th>Jednotka</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $parameters = $testType === 'biochemistry' ? $biochemParams : $hematoParams;
                            foreach ($parameters as $param):
                                $existingRange = null;
                                foreach ($ranges as $range) {
                                    if ($range['parameter_name'] === $param['name'] && $range['source'] === $activeSource) {
                                        $existingRange = $range;
                                        break;
                                    }
                                }
                            ?>
                                <tr>
                                    <td><strong><?= htmlspecialchars($param['name']) ?></strong></td>
                                    <td>
                                        <input type="text"
                                               name="params[<?= htmlspecialchars($param['name']) ?>][min]"
                                               value="<?= $existingRange ? htmlspecialchars($existingRange['min_value']) : '' ?>"
                                               class="form-control reference-value-input"
                                               placeholder="Min">
                                    </td>
                                    <td>
                                        <input type="text"
                                               name="params[<?= htmlspecialchars($param['name']) ?>][max]"
                                               value="<?= $existingRange ? htmlspecialchars($existingRange['max_value']) : '' ?>"
                                               class="form-control reference-value-input"
                                               placeholder="Max">
                                    </td>
                                    <td>
                                        <input type="text"
                                               name="params[<?= htmlspecialchars($param['name']) ?>][unit]"
                                               value="<?= $existingRange ? htmlspecialchars($existingRange['unit']) : htmlspecialchars($param['unit']) ?>"
                                               class="form-control"
                                               placeholder="Jednotka">
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                            <!-- Custom parameters will be added here -->
                            <tbody id="customParametersBody"></tbody>
                        </tbody>
                    </table>

                    <button type="button" class="btn btn-outline" onclick="addCustomParameter()" style="margin-top: 15px;">
                        + Přidat jiný parametr
                    </button>

                    <div class="form-actions">
                        <button type="submit" class="btn btn-primary">Uložit referenční hodnoty</button>
                    </div>
                </form>
            </div>
        </div>
    <?php endif; ?>
</div>

<!-- Add Source Modal -->
<div id="addSourceModal" class="modal" style="display: none;">
    <div class="modal-content">
        <div class="modal-header">
            <h3>Přidat nový referenční zdroj</h3>
            <button class="modal-close" onclick="closeAddSourceModal()">&times;</button>
        </div>
        <form id="addSourceForm" onsubmit="submitAddSource(event)">
            <div class="modal-body">
                <div class="form-group">
                    <label for="newSourceName">Název zdroje:</label>
                    <input type="text" id="newSourceName" name="source_name" class="form-control" required
                           placeholder="např. CVBD">
                    <small class="form-help">Zadejte krátký název pro nový referenční zdroj</small>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline" onclick="closeAddSourceModal()">Zrušit</button>
                <button type="submit" class="btn btn-primary">Přidat zdroj</button>
            </div>
        </form>
    </div>
</div>

<style>
.breadcrumb {
    margin-bottom: 20px;
    color: #7f8c8d;
    font-size: 14px;
}

.breadcrumb a {
    color: #c0392b;
    text-decoration: none;
}

.breadcrumb a:hover {
    text-decoration: underline;
}

.page-header {
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

.filter-section {
    background: white;
    padding: 20px;
    border-radius: 8px;
    margin-bottom: 30px;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 20px;
}

.filter-group label {
    display: block;
    margin-bottom: 8px;
    font-weight: 600;
    color: #2c3e50;
}

.ranges-section {
    background: white;
    padding: 30px;
    border-radius: 8px;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
}

.ranges-section h2 {
    margin: 0 0 20px 0;
    color: #c0392b;
    font-size: 20px;
}

.source-tabs {
    display: flex;
    gap: 10px;
    margin-bottom: 20px;
    border-bottom: 2px solid #e0e0e0;
}

.source-tab {
    background: none;
    border: none;
    padding: 12px 24px;
    font-size: 15px;
    font-weight: 600;
    color: #7f8c8d;
    cursor: pointer;
    border-bottom: 3px solid transparent;
    transition: all 0.2s;
}

.source-tab:hover {
    color: #c0392b;
}

.source-tab.active {
    color: #c0392b;
    border-bottom-color: #c0392b;
}

.source-tab.add-source-btn {
    background: #ecf0f1;
    border-radius: 4px;
    margin-bottom: 2px;
    color: #2c3e50;
    border-bottom: none !important;
}

.source-tab.add-source-btn:hover {
    background: #d5dbdb;
    color: #2c3e50;
}

.parameters-table-wrapper {
    overflow-x: auto;
}

.parameters-table {
    width: 100%;
    border-collapse: collapse;
}

.parameters-table thead {
    background: linear-gradient(135deg, #c0392b 0%, #a93226 100%);
    color: white;
}

.parameters-table th {
    padding: 12px;
    text-align: left;
    font-weight: 600;
    font-size: 14px;
}

.parameters-table td {
    padding: 10px 12px;
    border-bottom: 1px solid #f0f0f0;
}

.parameters-table tbody tr:hover {
    background-color: #f8f9fa;
}

.parameters-table input.form-control {
    width: 100%;
    max-width: 150px;
}

.form-actions {
    margin-top: 20px;
    padding-top: 20px;
    border-top: 2px solid #e0e0e0;
    display: flex;
    justify-content: flex-end;
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

.btn-outline {
    background: white;
    border: 2px solid #c0392b;
    color: #c0392b;
    padding: 10px 20px;
    border-radius: 4px;
    font-size: 14px;
    cursor: pointer;
    transition: all 0.2s;
}

.btn-outline:hover {
    background: #c0392b;
    color: white;
}

.custom-param-row {
    background-color: #fff8f0;
}

.custom-param-row td {
    position: relative;
}

.remove-param-btn {
    background: #e74c3c;
    color: white;
    border: none;
    padding: 5px 10px;
    border-radius: 4px;
    cursor: pointer;
    font-size: 12px;
    margin-left: 10px;
}

.remove-param-btn:hover {
    background: #c0392b;
}

/* Modal styles */
.modal {
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: rgba(0, 0, 0, 0.5);
    z-index: 1000;
    display: flex;
    align-items: center;
    justify-content: center;
}

.modal-content {
    background: white;
    border-radius: 8px;
    width: 90%;
    max-width: 500px;
    box-shadow: 0 4px 20px rgba(0, 0, 0, 0.3);
}

.modal-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 20px 25px;
    border-bottom: 2px solid #ecf0f1;
}

.modal-header h3 {
    margin: 0;
    color: #2c3e50;
    font-size: 20px;
}

.modal-close {
    background: none;
    border: none;
    font-size: 28px;
    color: #7f8c8d;
    cursor: pointer;
    line-height: 1;
    padding: 0;
    width: 30px;
    height: 30px;
    display: flex;
    align-items: center;
    justify-content: center;
}

.modal-close:hover {
    color: #c0392b;
}

.modal-body {
    padding: 25px;
}

.modal-footer {
    display: flex;
    justify-content: flex-end;
    gap: 10px;
    padding: 20px 25px;
    border-top: 2px solid #ecf0f1;
}

.form-help {
    display: block;
    margin-top: 5px;
    color: #7f8c8d;
    font-size: 13px;
}
</style>

<script>
let customParamCount = 0;

function addCustomParameter() {
    customParamCount++;
    const tbody = document.getElementById('customParametersBody');
    const row = document.createElement('tr');
    row.className = 'custom-param-row';
    row.id = `custom-param-${customParamCount}`;
    row.dataset.index = customParamCount;

    row.innerHTML = `
        <td>
            <input type="text"
                   id="custom-param-name-${customParamCount}"
                   class="form-control custom-param-name"
                   placeholder="Název parametru"
                   data-index="${customParamCount}"
                   onblur="updateCustomParamNames()"
                   required
                   style="max-width: 200px;">
        </td>
        <td>
            <input type="text"
                   id="custom-param-min-${customParamCount}"
                   class="form-control reference-value-input"
                   placeholder="Min">
        </td>
        <td>
            <input type="text"
                   id="custom-param-max-${customParamCount}"
                   class="form-control reference-value-input"
                   placeholder="Max">
        </td>
        <td>
            <input type="text"
                   id="custom-param-unit-${customParamCount}"
                   class="form-control"
                   placeholder="Jednotka">
            <button type="button" class="remove-param-btn" onclick="removeCustomParameter('custom-param-${customParamCount}')">
                Odebrat
            </button>
        </td>
    `;

    tbody.appendChild(row);
}

function updateCustomParamNames() {
    // Remove old hidden inputs for custom params
    const oldInputs = document.querySelectorAll('input[data-custom-param-hidden]');
    oldInputs.forEach(input => input.remove());

    // Create new hidden inputs with proper naming based on parameter names
    const customRows = document.querySelectorAll('.custom-param-row');
    customRows.forEach(row => {
        const index = row.dataset.index;
        const nameInput = document.getElementById(`custom-param-name-${index}`);
        const minInput = document.getElementById(`custom-param-min-${index}`);
        const maxInput = document.getElementById(`custom-param-max-${index}`);
        const unitInput = document.getElementById(`custom-param-unit-${index}`);

        const paramName = nameInput.value.trim();
        if (paramName) {
            // Create hidden inputs with proper naming structure
            const form = nameInput.closest('form');

            const minHidden = document.createElement('input');
            minHidden.type = 'hidden';
            minHidden.name = `params[${paramName}][min]`;
            minHidden.value = minInput.value;
            minHidden.dataset.customParamHidden = 'true';
            form.appendChild(minHidden);

            const maxHidden = document.createElement('input');
            maxHidden.type = 'hidden';
            maxHidden.name = `params[${paramName}][max]`;
            maxHidden.value = maxInput.value;
            maxHidden.dataset.customParamHidden = 'true';
            form.appendChild(maxHidden);

            const unitHidden = document.createElement('input');
            unitHidden.type = 'hidden';
            unitHidden.name = `params[${paramName}][unit]`;
            unitHidden.value = unitInput.value;
            unitHidden.dataset.customParamHidden = 'true';
            form.appendChild(unitHidden);

            // Update the visible inputs when values change
            minInput.addEventListener('input', () => minHidden.value = minInput.value);
            maxInput.addEventListener('input', () => maxHidden.value = maxInput.value);
            unitInput.addEventListener('input', () => unitHidden.value = unitInput.value);
        }
    });
}

function removeCustomParameter(rowId) {
    const row = document.getElementById(rowId);
    if (row) {
        row.remove();
    }
}

function filterBySpecies() {
    const species = document.getElementById('speciesFilter').value;
    const testType = document.getElementById('testTypeFilter').value;
    if (species) {
        window.location.href = `/biochemistry/reference-ranges?species=${encodeURIComponent(species)}&test_type=${testType}&source=Idexx`;
    }
}

function filterByTestType() {
    const species = document.getElementById('speciesFilter').value;
    const testType = document.getElementById('testTypeFilter').value;
    if (species) {
        window.location.href = `/biochemistry/reference-ranges?species=${encodeURIComponent(species)}&test_type=${testType}&source=Idexx`;
    }
}

function switchSource(source) {
    const species = document.getElementById('speciesFilter').value;
    const testType = document.getElementById('testTypeFilter').value;
    window.location.href = `/biochemistry/reference-ranges?species=${encodeURIComponent(species)}&test_type=${testType}&source=${source}`;
}

// Add form submit handler to update custom param names and convert commas before submission
document.addEventListener('DOMContentLoaded', function() {
    const form = document.querySelector('form[action="/biochemistry/reference-ranges/save"]');
    if (form) {
        form.addEventListener('submit', function(e) {
            // First, convert all commas to dots in reference value inputs (for Czech keyboard)
            const valueInputs = this.querySelectorAll('.reference-value-input');
            valueInputs.forEach(input => {
                if (input.value && input.value.includes(',')) {
                    input.value = input.value.replace(/,/g, '.');
                }
            });

            // Then update custom param names
            updateCustomParamNames();
        });
    }
});

// Modal functions for adding new source
function openAddSourceModal() {
    document.getElementById('addSourceModal').style.display = 'flex';
    document.getElementById('newSourceName').focus();
}

function closeAddSourceModal() {
    document.getElementById('addSourceModal').style.display = 'none';
    document.getElementById('addSourceForm').reset();
}

async function submitAddSource(event) {
    event.preventDefault();

    const sourceName = document.getElementById('newSourceName').value.trim();

    if (!sourceName) {
        alert('Zadejte název zdroje');
        return;
    }

    try {
        const response = await fetch('/biochemistry/reference-ranges/add-source', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({
                source_name: sourceName
            })
        });

        const data = await response.json();

        if (data.success) {
            // Reload page to show new source
            window.location.reload();
        } else {
            alert(data.error || 'Chyba při přidávání zdroje');
        }
    } catch (error) {
        console.error('Error:', error);
        alert('Chyba při komunikaci se serverem');
    }
}

// Close modal on escape key
document.addEventListener('keydown', function(event) {
    if (event.key === 'Escape') {
        closeAddSourceModal();
    }
});

// Close modal when clicking outside
document.getElementById('addSourceModal')?.addEventListener('click', function(event) {
    if (event.target === this) {
        closeAddSourceModal();
    }
});
</script>
