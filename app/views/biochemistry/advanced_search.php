<div class="container">
    <div class="page-header">
        <div class="breadcrumb">
            <a href="/">Pracoviště</a> /
            <a href="/biochemistry/workplace/<?= $workplace['id'] ?>">
                <?= htmlspecialchars($workplace['name']) ?>
            </a> /
            <span>Vyhledávání</span>
        </div>

        <h1>Pokročilé vyhledávání</h1>
        <p class="subtitle">
            <strong><?= htmlspecialchars($workplace['name']) ?></strong>
        </p>
    </div>

    <div class="search-container">
        <!-- Search by Animal Name/ID -->
        <div class="search-section">
            <h2>1. Vyhledávání zvířete podle jména nebo ID</h2>
            <p class="instruction">Zadejte jméno nebo ID zvířete</p>

            <div class="search-input-wrapper">
                <input type="text"
                       id="animalSearch"
                       class="search-input"
                       placeholder="Zadejte jméno nebo ID zvířete">
                <button type="button" class="btn btn-primary" onclick="searchAnimal()">
                    🔍 Vyhledat zvíře
                </button>
            </div>

            <div id="animalResults" class="results-container"></div>
        </div>

        <!-- Search by Parameter Values -->
        <div class="search-section">
            <h2>2. Vyhledávání podle hodnot parametrů</h2>
            <p class="instruction">Najít zvířata s hodnotami mimo referenční rozsah</p>

            <div class="param-search-form">
                <!-- Parameter Selection -->
                <div class="form-group">
                    <label for="paramType">Typ parametru:</label>
                    <select id="paramType" class="form-control" onchange="updateParameterList()">
                        <option value="">-- Vyberte typ --</option>
                        <option value="biochemistry">Biochemie</option>
                        <option value="hematology">Hematologie</option>
                    </select>
                </div>

                <div class="form-group">
                    <label for="paramName">Parametr:</label>
                    <select id="paramName" class="form-control" disabled>
                        <option value="">-- Nejprve vyberte typ --</option>
                    </select>
                </div>

                <!-- Direction Selection -->
                <div class="form-group">
                    <label for="direction">Směr odchylky:</label>
                    <select id="direction" class="form-control">
                        <option value="">-- Vyberte směr --</option>
                        <option value="elevated">Zvýšené hodnoty (nad referenční rozsah)</option>
                        <option value="decreased">Snížené hodnoty (pod referenční rozsah)</option>
                        <option value="both">Obojí (mimo referenční rozsah)</option>
                    </select>
                </div>

                <!-- Reference Source Selection -->
                <div class="form-group">
                    <label for="refSource">Referenční zdroj:</label>
                    <select id="refSource" class="form-control">
                        <option value="">-- Všechny zdroje --</option>
                        <?php foreach ($referenceSources as $source): ?>
                            <option value="<?= htmlspecialchars($source) ?>">
                                <?= htmlspecialchars($source) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <button type="button" class="btn btn-primary" onclick="searchByParameter()">
                    🔍 Vyhledat podle parametru
                </button>
            </div>

            <div id="paramResults" class="results-container"></div>
        </div>

        <div class="form-actions">
            <a href="/biochemistry/workplace/<?= $workplace['id'] ?>" class="btn btn-outline">
                ← Zpět
            </a>
        </div>
    </div>
</div>

<style>
.container {
    max-width: 1400px;
    margin: 0 auto;
    padding: 20px;
}

.page-header {
    margin-bottom: 30px;
}

.breadcrumb {
    margin-bottom: 15px;
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

.search-section {
    margin-bottom: 40px;
    padding-bottom: 30px;
    border-bottom: 2px solid #f0f0f0;
}

.search-section:last-of-type {
    border-bottom: none;
}

.search-section h2 {
    margin: 0 0 10px 0;
    color: #c0392b;
    font-size: 20px;
}

.instruction {
    color: #7f8c8d;
    font-size: 14px;
    margin: 0 0 20px 0;
}

.search-input-wrapper {
    display: flex;
    gap: 15px;
    align-items: center;
    margin-bottom: 20px;
}

.search-input {
    flex: 1;
    padding: 12px 15px;
    border: 2px solid #e0e0e0;
    border-radius: 6px;
    font-size: 15px;
    font-weight: 500;
    color: #2c3e50;
    transition: all 0.2s;
    background: #f8f9fa;
}

.search-input:focus {
    outline: none;
    border-color: #c0392b;
    background: white;
}

.param-search-form {
    display: flex;
    flex-direction: column;
    gap: 15px;
    max-width: 600px;
}

.form-group {
    display: flex;
    flex-direction: column;
    gap: 8px;
}

.form-group label {
    font-weight: 600;
    color: #2c3e50;
    font-size: 14px;
}

.form-control {
    padding: 10px 15px;
    border: 2px solid #e0e0e0;
    border-radius: 6px;
    font-size: 14px;
    color: #2c3e50;
    background: #f8f9fa;
    transition: all 0.2s;
}

.form-control:focus {
    outline: none;
    border-color: #c0392b;
    background: white;
}

.form-control:disabled {
    opacity: 0.6;
    cursor: not-allowed;
}

.results-container {
    margin-top: 20px;
}

.results-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 15px;
}

.results-header h3 {
    margin: 0;
    color: #2c3e50;
    font-size: 18px;
}

.results-count {
    background: #c0392b;
    color: white;
    padding: 4px 12px;
    border-radius: 12px;
    font-size: 13px;
    font-weight: 600;
}

.results-table {
    width: 100%;
    border-collapse: collapse;
    background: white;
    border-radius: 8px;
    overflow: hidden;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
}

.results-table thead {
    background: linear-gradient(135deg, #c0392b 0%, #a93226 100%);
    color: white;
}

.results-table th {
    padding: 12px 15px;
    text-align: left;
    font-weight: 600;
    font-size: 14px;
}

.results-table td {
    padding: 12px 15px;
    border-bottom: 1px solid #f0f0f0;
}

.results-table tbody tr:hover {
    background-color: #f8f9fa;
    cursor: pointer;
}

.results-table tbody tr:last-child td {
    border-bottom: none;
}

.no-results {
    text-align: center;
    padding: 40px;
    color: #7f8c8d;
    font-style: italic;
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
    background: linear-gradient(135deg, #c0392b 0%, #a93226 100%);
    color: white;
}

.btn-primary:hover {
    background: linear-gradient(135deg, #a93226 0%, #8f2a20 100%);
    transform: translateY(-1px);
    box-shadow: 0 4px 12px rgba(192, 57, 43, 0.3);
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

.form-actions {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-top: 30px;
    padding-top: 20px;
    border-top: 2px solid #f0f0f0;
}

.value-cell {
    font-weight: 600;
}

.value-high {
    color: #e74c3c;
}

.value-low {
    color: #3498db;
}

.deviation {
    font-size: 12px;
    color: #7f8c8d;
}
</style>

<script>
const workplaceId = <?= $workplace['id'] ?>;
const biochemParams = <?= json_encode($biochemParams) ?>;
const hematoParams = <?= json_encode($hematoParams) ?>;

// Update parameter list based on selected type
function updateParameterList() {
    const paramType = document.getElementById('paramType').value;
    const paramSelect = document.getElementById('paramName');

    paramSelect.innerHTML = '<option value="">-- Vyberte parametr --</option>';
    paramSelect.disabled = !paramType;

    if (paramType === 'biochemistry') {
        biochemParams.forEach(param => {
            const option = document.createElement('option');
            option.value = param.parameter_name;
            option.textContent = `${param.parameter_name} (${param.unit})`;
            option.dataset.unit = param.unit;
            paramSelect.appendChild(option);
        });
    } else if (paramType === 'hematology') {
        hematoParams.forEach(param => {
            const option = document.createElement('option');
            option.value = param.parameter_name;
            option.textContent = `${param.parameter_name} (${param.unit})`;
            option.dataset.unit = param.unit;
            paramSelect.appendChild(option);
        });
    }
}

// Search animal by name or ID
async function searchAnimal() {
    const query = document.getElementById('animalSearch').value.trim();
    const resultsDiv = document.getElementById('animalResults');

    if (!query) {
        alert('Zadejte jméno nebo ID zvířete');
        return;
    }

    resultsDiv.innerHTML = '<p class="no-results">Vyhledávání...</p>';

    try {
        const response = await fetch(`/api/biochemistry/search-animal?workplace_id=${workplaceId}&query=${encodeURIComponent(query)}`);
        const data = await response.json();

        console.log('API Response:', data);

        if (data.results && data.results.length > 0) {
            let html = `
                <div class="results-header">
                    <h3>Nalezená zvířata</h3>
                    <span class="results-count">${data.results.length} ${data.results.length === 1 ? 'zvíře' : data.results.length < 5 ? 'zvířata' : 'zvířat'}</span>
                </div>
                <table class="results-table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Jméno</th>
                            <th>Druh</th>
                            <th>Výběh</th>
                            <th>Akce</th>
                        </tr>
                    </thead>
                    <tbody>
            `;

            data.results.forEach(animal => {
                html += `
                    <tr onclick="window.location.href='/biochemistry/animal/${animal.id}'">
                        <td><strong>${animal.identifier || '-'}</strong></td>
                        <td>${animal.name}</td>
                        <td>${animal.species || '-'}</td>
                        <td>${animal.enclosure_name || '-'}</td>
                        <td><a href="/biochemistry/animal/${animal.id}" class="btn btn-sm btn-primary">Detail</a></td>
                    </tr>
                `;
            });

            html += `
                    </tbody>
                </table>
            `;

            resultsDiv.innerHTML = html;
        } else {
            resultsDiv.innerHTML = '<p class="no-results">Žádná zvířata nenalezena</p>';
        }
    } catch (error) {
        console.error('Error:', error);
        resultsDiv.innerHTML = '<p class="no-results">Chyba při vyhledávání</p>';
    }
}

// Search by parameter values
async function searchByParameter() {
    const paramType = document.getElementById('paramType').value;
    const paramName = document.getElementById('paramName').value;
    const direction = document.getElementById('direction').value;
    const refSource = document.getElementById('refSource').value;
    const resultsDiv = document.getElementById('paramResults');

    if (!paramType || !paramName || !direction) {
        alert('Vyplňte všechna povinná pole (typ parametru, parametr a směr odchylky)');
        return;
    }

    resultsDiv.innerHTML = '<p class="no-results">Vyhledávání...</p>';

    try {
        const params = new URLSearchParams({
            workplace_id: workplaceId,
            param_type: paramType,
            param_name: paramName,
            direction: direction
        });

        if (refSource) {
            params.append('ref_source', refSource);
        }

        const response = await fetch(`/api/biochemistry/search-parameter?${params.toString()}`);
        const data = await response.json();

        console.log('Parameter Search Response:', data);

        if (data.results && data.results.length > 0) {
            let html = `
                <div class="results-header">
                    <h3>Zvířata s abnormálními hodnotami: ${paramName}</h3>
                    <span class="results-count">${data.results.length} ${data.results.length === 1 ? 'nález' : data.results.length < 5 ? 'nálezy' : 'nálezů'}</span>
                </div>
                <table class="results-table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Jméno</th>
                            <th>Druh</th>
                            <th>Hodnota</th>
                            <th>Referenční rozsah</th>
                            <th>Odchylka</th>
                            <th>Datum testu</th>
                        </tr>
                    </thead>
                    <tbody>
            `;

            data.results.forEach(result => {
                const valueClass = result.status === 'high' ? 'value-high' : 'value-low';
                const arrow = result.status === 'high' ? '↑' : '↓';

                html += `
                    <tr onclick="window.location.href='/biochemistry/animal/${result.animal_id}'">
                        <td><strong>${result.identifier || '-'}</strong></td>
                        <td>${result.animal_name}</td>
                        <td>${result.species || '-'}</td>
                        <td class="value-cell ${valueClass}">${result.value} ${result.unit}</td>
                        <td>${result.min_value} - ${result.max_value} ${result.unit}</td>
                        <td class="value-cell ${valueClass}">${arrow} ${result.deviation}%</td>
                        <td>${new Date(result.test_date).toLocaleDateString('cs-CZ')}</td>
                    </tr>
                `;
            });

            html += `
                    </tbody>
                </table>
            `;

            resultsDiv.innerHTML = html;
        } else {
            resultsDiv.innerHTML = '<p class="no-results">Žádné výsledky nenalezeny</p>';
        }
    } catch (error) {
        console.error('Error:', error);
        resultsDiv.innerHTML = '<p class="no-results">Chyba při vyhledávání</p>';
    }
}

// Allow Enter key to trigger search
document.getElementById('animalSearch').addEventListener('keypress', function(e) {
    if (e.key === 'Enter') {
        searchAnimal();
    }
});
</script>
