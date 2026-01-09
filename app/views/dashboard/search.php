<div class="page-header">
    <div class="breadcrumb">
        <a href="/">Pracoviště</a> /
        <a href="/workplace/<?= $workplace['id'] ?>">
            <?= htmlspecialchars($workplace['name']) ?>
        </a> /
        <span>Vyhledávání</span>
    </div>
    <h1>Pokročilé vyhledávání</h1>
    <p class="subtitle"><?= htmlspecialchars($workplace['name']) ?></p>
</div>

<div class="search-container">
    <div class="search-tabs">
        <button class="search-tab active" data-tab="parasites">
            🦠 Paraziti
        </button>
        <button class="search-tab" data-tab="drugs">
            💊 Antiparazitika
        </button>
    </div>

    <!-- Parasite Search Tab -->
    <div id="parasites-tab" class="tab-content active">
        <div class="search-section">
            <h2>Vyhledávání podle parazitů</h2>
            <p class="instruction">Vyberte parazita a zobrazte všechny nálezy napříč zvířaty</p>

            <div class="search-form">
                <div class="form-group">
                    <label for="parasite-select">Vyberte parazita:</label>
                    <select id="parasite-select" class="form-control">
                        <option value="">-- Vyberte parazita --</option>
                        <?php foreach ($parasites as $parasite): ?>
                            <option value="<?= htmlspecialchars($parasite) ?>">
                                <?= htmlspecialchars($parasite) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <button onclick="searchParasite()" class="btn btn-primary">
                    🔍 Vyhledat
                </button>
            </div>

            <div id="parasite-results" class="results-container" style="display: none;">
                <!-- Results will be loaded here via JavaScript -->
            </div>
        </div>
    </div>

    <!-- Drug Search Tab -->
    <div id="drugs-tab" class="tab-content">
        <div class="search-section">
            <h2>Vyhledávání podle antiparazitik</h2>
            <p class="instruction">Vyberte preparát a zobrazte historii použití</p>

            <div class="search-form">
                <div class="form-group">
                    <label for="drug-select">Vyberte antiparazitikum:</label>
                    <select id="drug-select" class="form-control">
                        <option value="">-- Vyberte preparát --</option>
                        <?php foreach ($drugs as $drug): ?>
                            <option value="<?= htmlspecialchars($drug) ?>">
                                <?= htmlspecialchars($drug) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <button onclick="searchDrug()" class="btn btn-primary">
                    🔍 Vyhledat
                </button>
            </div>

            <div id="drug-results" class="results-container" style="display: none;">
                <!-- Results will be loaded here via JavaScript -->
            </div>
        </div>
    </div>

    <div class="form-actions">
        <a href="/workplace/<?= $workplace['id'] ?>" class="btn btn-outline">
            ← Zpět na pracoviště
        </a>
    </div>
</div>

<style>
.page-header {
    margin-bottom: 30px;
}

.breadcrumb {
    margin-bottom: 15px;
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

.page-header h1 {
    margin: 0 0 10px 0;
    color: #2c3e50;
}

.subtitle {
    color: #7f8c8d;
    font-size: 14px;
    margin: 0;
}

.search-container {
    background: white;
    border-radius: 8px;
    padding: 30px;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
}

.search-tabs {
    display: flex;
    gap: 10px;
    margin-bottom: 30px;
    border-bottom: 2px solid #e0e0e0;
}

.search-tab {
    background: none;
    border: none;
    padding: 12px 24px;
    font-size: 16px;
    font-weight: 600;
    color: #7f8c8d;
    cursor: pointer;
    border-bottom: 3px solid transparent;
    transition: all 0.2s;
}

.search-tab:hover {
    color: #667eea;
}

.search-tab.active {
    color: #667eea;
    border-bottom-color: #667eea;
}

.tab-content {
    display: none;
}

.tab-content.active {
    display: block;
}

.search-section h2 {
    margin: 0 0 10px 0;
    color: #667eea;
    font-size: 20px;
}

.instruction {
    color: #7f8c8d;
    font-size: 14px;
    margin: 0 0 25px 0;
}

.search-form {
    display: flex;
    gap: 15px;
    align-items: end;
    margin-bottom: 30px;
}

.form-group {
    flex: 1;
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
    border: 2px solid #e0e0e0;
    border-radius: 4px;
    font-size: 15px;
    transition: border-color 0.2s;
}

.form-control:focus {
    outline: none;
    border-color: #667eea;
}

.results-container {
    margin-top: 30px;
    padding-top: 30px;
    border-top: 2px solid #f0f0f0;
}

.results-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 20px;
}

.results-header h3 {
    margin: 0;
    color: #2c3e50;
}

.results-count {
    color: #7f8c8d;
    font-size: 14px;
}

.results-table {
    width: 100%;
    border-collapse: collapse;
    margin-top: 15px;
}

.results-table thead {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
}

.results-table th {
    padding: 12px;
    text-align: left;
    font-weight: 600;
    font-size: 14px;
    cursor: pointer;
    user-select: none;
    position: relative;
    transition: background-color 0.2s;
}

.results-table th:hover {
    background: rgba(255, 255, 255, 0.1);
}

.results-table th::after {
    content: '⇅';
    margin-left: 8px;
    opacity: 0.3;
    font-size: 12px;
}

.results-table th.sort-asc::after {
    content: '↑';
    opacity: 1;
}

.results-table th.sort-desc::after {
    content: '↓';
    opacity: 1;
}

.results-table td {
    padding: 12px;
    border-bottom: 1px solid #f0f0f0;
}

.results-table td:first-child {
    white-space: nowrap;
}

.results-table tbody tr:hover {
    background-color: #f8f9fa;
}

.no-results {
    text-align: center;
    padding: 40px;
    color: #7f8c8d;
}

.form-actions {
    margin-top: 30px;
    padding-top: 20px;
    border-top: 2px solid #f0f0f0;
}

.btn {
    padding: 12px 24px;
    border-radius: 4px;
    border: none;
    cursor: pointer;
    font-size: 15px;
    font-weight: 600;
    transition: all 0.2s;
    text-decoration: none;
    display: inline-block;
}

.btn-primary {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
}

.btn-primary:hover {
    background: linear-gradient(135deg, #764ba2 0%, #667eea 100%);
    transform: translateY(-1px);
    box-shadow: 0 4px 12px rgba(102, 126, 234, 0.3);
}

.btn-outline {
    background: white;
    border: 2px solid #667eea;
    color: #667eea;
}

.btn-outline:hover {
    background: #667eea;
    color: white;
}

.loading {
    text-align: center;
    padding: 40px;
    color: #7f8c8d;
}
</style>

<script>
// Tab switching
document.querySelectorAll('.search-tab').forEach(tab => {
    tab.addEventListener('click', function() {
        // Update active tab
        document.querySelectorAll('.search-tab').forEach(t => t.classList.remove('active'));
        this.classList.add('active');

        // Show corresponding content
        const tabName = this.dataset.tab;
        document.querySelectorAll('.tab-content').forEach(content => {
            content.classList.remove('active');
        });
        document.getElementById(`${tabName}-tab`).classList.add('active');
    });
});

// Search for parasite
async function searchParasite() {
    const parasite = document.getElementById('parasite-select').value;
    if (!parasite) {
        alert('Prosím vyberte parazita');
        return;
    }

    const resultsContainer = document.getElementById('parasite-results');
    resultsContainer.style.display = 'block';
    resultsContainer.innerHTML = '<div class="loading">Načítám...</div>';

    try {
        const response = await fetch(`/api/search/parasites?workplace_id=<?= $workplace['id'] ?>&parasite=${encodeURIComponent(parasite)}`);
        const data = await response.json();

        if (data.results && data.results.length > 0) {
            let html = `
                <div class="results-header">
                    <h3>Výsledky vyhledávání: ${parasite}</h3>
                    <span class="results-count">${data.results.length} ${data.results.length === 1 ? 'nález' : data.results.length < 5 ? 'nálezy' : 'nálezů'}</span>
                </div>
                <table class="results-table">
                    <thead>
                        <tr>
                            <th style="width: 110px; white-space: nowrap;">Datum</th>
                            <th>Zvíře</th>
                            <th>Druh</th>
                            <th>Typ vzorku</th>
                            <th style="width: 80px;">Intenzita</th>
                            <th>Poznámky</th>
                        </tr>
                    </thead>
                    <tbody>
            `;

            data.results.forEach(result => {
                const date = new Date(result.examination_date);
                const formattedDate = date.toLocaleDateString('cs-CZ');

                html += `
                    <tr>
                        <td>${formattedDate}</td>
                        <td><strong>${result.animal_name}</strong></td>
                        <td>${result.species || '-'}</td>
                        <td>${result.sample_type || '-'}</td>
                        <td>${result.intensity || '-'}</td>
                        <td>${result.notes || '-'}</td>
                    </tr>
                `;
            });

            html += `
                    </tbody>
                </table>
            `;

            resultsContainer.innerHTML = html;
            initTableSorting('parasite-results');
        } else {
            resultsContainer.innerHTML = '<div class="no-results">Žádné výsledky nenalezeny</div>';
        }
    } catch (error) {
        resultsContainer.innerHTML = '<div class="no-results">Chyba při načítání dat</div>';
        console.error('Error:', error);
    }
}

// Search for drug
async function searchDrug() {
    const drug = document.getElementById('drug-select').value;
    if (!drug) {
        alert('Prosím vyberte antiparazitikum');
        return;
    }

    const resultsContainer = document.getElementById('drug-results');
    resultsContainer.style.display = 'block';
    resultsContainer.innerHTML = '<div class="loading">Načítám...</div>';

    try {
        const response = await fetch(`/api/search/drugs?workplace_id=<?= $workplace['id'] ?>&drug=${encodeURIComponent(drug)}`);
        const data = await response.json();

        if (data.results && data.results.length > 0) {
            let html = `
                <div class="results-header">
                    <h3>Výsledky vyhledávání: ${drug}</h3>
                    <span class="results-count">${data.results.length} ${data.results.length === 1 ? 'aplikace' : data.results.length < 5 ? 'aplikace' : 'aplikací'}</span>
                </div>
                <table class="results-table">
                    <thead>
                        <tr>
                            <th>Datum</th>
                            <th>Zvíře</th>
                            <th>Druh</th>
                            <th>Dávka</th>
                            <th>Způsob podání</th>
                            <th>Poznámky</th>
                        </tr>
                    </thead>
                    <tbody>
            `;

            data.results.forEach(result => {
                const date = new Date(result.deworming_date);
                const formattedDate = date.toLocaleDateString('cs-CZ');

                html += `
                    <tr>
                        <td>${formattedDate}</td>
                        <td><strong>${result.animal_name}</strong></td>
                        <td>${result.species || '-'}</td>
                        <td>${result.dosage || '-'}</td>
                        <td>${result.administration_route || '-'}</td>
                        <td>${result.notes || '-'}</td>
                    </tr>
                `;
            });

            html += `
                    </tbody>
                </table>
            `;

            resultsContainer.innerHTML = html;
            initTableSorting('drug-results');
        } else {
            resultsContainer.innerHTML = '<div class="no-results">Žádné výsledky nenalezeny</div>';
        }
    } catch (error) {
        resultsContainer.innerHTML = '<div class="no-results">Chyba při načítání dat</div>';
        console.error('Error:', error);
    }
}

// Initialize table sorting functionality
function initTableSorting(containerId) {
    const container = document.getElementById(containerId);
    const table = container.querySelector('.results-table');
    if (!table) return;

    const headers = table.querySelectorAll('th');
    headers.forEach((header, index) => {
        header.addEventListener('click', () => {
            sortTable(table, index, header);
        });
    });
}

function sortTable(table, columnIndex, header) {
    const tbody = table.querySelector('tbody');
    const rows = Array.from(tbody.querySelectorAll('tr'));

    // Determine sort direction
    const isAscending = header.classList.contains('sort-asc');
    const isDescending = header.classList.contains('sort-desc');

    // Remove sort classes from all headers
    table.querySelectorAll('th').forEach(th => {
        th.classList.remove('sort-asc', 'sort-desc');
    });

    // Set new sort direction
    let sortDirection = 'asc';
    if (!isAscending && !isDescending) {
        sortDirection = 'asc';
    } else if (isAscending) {
        sortDirection = 'desc';
    } else {
        sortDirection = 'asc';
    }

    header.classList.add(sortDirection === 'asc' ? 'sort-asc' : 'sort-desc');

    // Sort rows
    rows.sort((a, b) => {
        const aValue = a.cells[columnIndex].textContent.trim();
        const bValue = b.cells[columnIndex].textContent.trim();

        // Try to parse as date (DD.MM.YYYY format)
        const dateRegex = /^(\d{1,2})\.(\d{1,2})\.(\d{4})$/;
        const aDateMatch = aValue.match(dateRegex);
        const bDateMatch = bValue.match(dateRegex);

        if (aDateMatch && bDateMatch) {
            // Compare as dates
            const aDate = new Date(aDateMatch[3], aDateMatch[2] - 1, aDateMatch[1]);
            const bDate = new Date(bDateMatch[3], bDateMatch[2] - 1, bDateMatch[1]);
            return sortDirection === 'asc' ? aDate - bDate : bDate - aDate;
        }

        // Try to parse as number
        const aNum = parseFloat(aValue);
        const bNum = parseFloat(bValue);
        if (!isNaN(aNum) && !isNaN(bNum)) {
            return sortDirection === 'asc' ? aNum - bNum : bNum - aNum;
        }

        // Compare as strings
        const comparison = aValue.localeCompare(bValue, 'cs');
        return sortDirection === 'asc' ? comparison : -comparison;
    });

    // Reattach sorted rows
    rows.forEach(row => tbody.appendChild(row));
}
</script>
