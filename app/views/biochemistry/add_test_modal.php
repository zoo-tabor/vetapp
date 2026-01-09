<!-- Add Test Modal -->
<div id="addTestModal" class="modal">
    <div class="modal-content">
        <div class="modal-header">
            <h2 id="modalTitle">Přidat nový test</h2>
            <span class="close" onclick="closeAddTestModal()">&times;</span>
        </div>
        <div class="modal-body">
            <form id="addTestForm" method="POST" action="/biochemistry/tests/create">
                <input type="hidden" name="animal_id" value="<?= $animal['id'] ?>">
                <input type="hidden" name="test_type" id="testType">

                <div class="form-row">
                    <div class="form-group">
                        <label for="test_date">Datum testu *</label>
                        <input type="date" id="test_date" name="test_date" class="form-control" required value="<?= date('Y-m-d') ?>">
                    </div>
                    <div class="form-group">
                        <label for="test_location">Místo testu</label>
                        <input type="text" id="test_location" name="test_location" class="form-control" placeholder="např. Laboklin Praha">
                    </div>
                </div>

                <div class="form-group">
                    <label for="reference_source">Referenční zdroj *</label>
                    <select id="reference_source" name="reference_source" class="form-control" required>
                        <option value="Idexx">Idexx</option>
                        <option value="Laboklin">Laboklin</option>
                        <option value="Synlab">Synlab</option>
                        <option value="ZIMS">ZIMS</option>
                    </select>
                </div>

                <div class="form-group">
                    <label for="notes">Poznámky</label>
                    <textarea id="notes" name="notes" class="form-control" rows="3"></textarea>
                </div>

                <hr>

                <h3>Hodnoty testů</h3>
                <p class="text-muted">Vyplňte pouze hodnoty, které byly testovány</p>

                <!-- Biochemistry Parameters -->
                <div id="biochemParameters" style="display: none;">
                    <div class="parameters-grid">
                        <?php
                        $biochemParams = [
                            ['name' => 'Amyláza', 'unit' => 'U/L'],
                            ['name' => 'Lipáza', 'unit' => 'U/L'],
                            ['name' => 'Glukóza', 'unit' => 'mmol/L'],
                            ['name' => 'Fruktozamin', 'unit' => 'µmol/L'],
                            ['name' => 'Triacylglyceridy', 'unit' => 'mmol/L'],
                            ['name' => 'Cholesterol', 'unit' => 'mmol/L'],
                            ['name' => 'Bilirubin celkový', 'unit' => 'µmol/L'],
                            ['name' => 'ALP', 'unit' => 'U/L'],
                            ['name' => 'GLDH', 'unit' => 'U/L'],
                            ['name' => 'y-GT', 'unit' => 'U/L'],
                            ['name' => 'ALT', 'unit' => 'U/L'],
                            ['name' => 'AST', 'unit' => 'U/L'],
                            ['name' => 'CK (Kreatinkináza)', 'unit' => 'U/L'],
                            ['name' => 'Celková bílkovina', 'unit' => 'g/L'],
                            ['name' => 'Albumin', 'unit' => 'g/L'],
                            ['name' => 'Globuliny', 'unit' => 'g/L'],
                            ['name' => 'A/G poměr', 'unit' => ''],
                            ['name' => 'SDMA', 'unit' => 'µg/dL'],
                            ['name' => 'Močovina', 'unit' => 'mmol/L'],
                            ['name' => 'Kreatinin', 'unit' => 'µmol/L'],
                            ['name' => 'Fosfor', 'unit' => 'mmol/L'],
                            ['name' => 'Hořčík', 'unit' => 'mmol/L'],
                            ['name' => 'Vápník', 'unit' => 'mmol/L'],
                            ['name' => 'Chloridy', 'unit' => 'mmol/L'],
                            ['name' => 'Sodík', 'unit' => 'mmol/L'],
                            ['name' => 'Draslík', 'unit' => 'mmol/L'],
                            ['name' => 'Na-/K-kvocient', 'unit' => ''],
                            ['name' => 'Železo', 'unit' => 'µmol/L'],
                            ['name' => 'T4', 'unit' => 'nmol/L'],
                            ['name' => 'FT4', 'unit' => 'pmol/L'],
                            ['name' => 'TSH', 'unit' => 'ng/mL'],
                        ];

                        foreach ($biochemParams as $param):
                        ?>
                            <div class="parameter-input">
                                <label><?= htmlspecialchars($param['name']) ?></label>
                                <div class="input-with-unit">
                                    <input type="text"
                                           name="params[<?= htmlspecialchars($param['name']) ?>][value]"
                                           class="form-control param-value-input"
                                           placeholder="hodnota">
                                    <input type="hidden"
                                           name="params[<?= htmlspecialchars($param['name']) ?>][unit]"
                                           value="<?= htmlspecialchars($param['unit']) ?>">
                                    <span class="unit-label"><?= htmlspecialchars($param['unit']) ?></span>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>

                    <!-- Custom parameter addition -->
                    <div class="custom-parameter-section">
                        <button type="button" class="btn btn-sm btn-outline" onclick="addCustomParameter('biochemistry')">
                            + Přidat jiný parametr
                        </button>
                        <div id="customBiochemParameters"></div>
                    </div>
                </div>

                <!-- Hematology Parameters -->
                <div id="hematoParameters" style="display: none;">
                    <div class="parameters-grid">
                        <?php
                        $hematoParams = [
                            ['name' => 'Erytrocyty', 'unit' => '10^12/L'],
                            ['name' => 'Hematokrit', 'unit' => '%'],
                            ['name' => 'Hemoglobin', 'unit' => 'g/L'],
                            ['name' => 'Hypochromazie', 'unit' => '%'],
                            ['name' => 'Anizocytoza', 'unit' => '%'],
                            ['name' => 'MCHC', 'unit' => 'g/L'],
                            ['name' => 'MCH', 'unit' => 'pg'],
                            ['name' => 'MCV', 'unit' => 'fL'],
                            ['name' => 'Retikulocyty', 'unit' => '%'],
                            ['name' => 'IRF', 'unit' => '%'],
                            ['name' => 'Ret-He', 'unit' => 'pg'],
                            ['name' => 'Leukocyty', 'unit' => '10^9/L'],
                            ['name' => 'Neutrofily', 'unit' => '%'],
                            ['name' => 'Lymfocyty', 'unit' => '%'],
                            ['name' => 'Monocyty', 'unit' => '%'],
                            ['name' => 'Eozinofily', 'unit' => '%'],
                            ['name' => 'Bazofily', 'unit' => '%'],
                            ['name' => 'Tyčky', 'unit' => '%'],
                            ['name' => 'Neutrofily - absolutní', 'unit' => '10^9/L'],
                            ['name' => 'Lymfocyty - absolutní', 'unit' => '10^9/L'],
                            ['name' => 'Monocyty - absolutní', 'unit' => '10^9/L'],
                            ['name' => 'Eozinofily - absolutní', 'unit' => '10^9/L'],
                            ['name' => 'Bazofily - absolutní', 'unit' => '10^9/L'],
                            ['name' => 'Tyčky - absolutní', 'unit' => '10^9/L'],
                            ['name' => 'Trombocyty', 'unit' => '10^9/L'],
                        ];

                        foreach ($hematoParams as $param):
                        ?>
                            <div class="parameter-input">
                                <label><?= htmlspecialchars($param['name']) ?></label>
                                <div class="input-with-unit">
                                    <input type="text"
                                           name="params[<?= htmlspecialchars($param['name']) ?>][value]"
                                           class="form-control param-value-input"
                                           placeholder="hodnota">
                                    <input type="hidden"
                                           name="params[<?= htmlspecialchars($param['name']) ?>][unit]"
                                           value="<?= htmlspecialchars($param['unit']) ?>">
                                    <span class="unit-label"><?= htmlspecialchars($param['unit']) ?></span>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>

                    <!-- Custom parameter addition -->
                    <div class="custom-parameter-section">
                        <button type="button" class="btn btn-sm btn-outline" onclick="addCustomParameter('hematology')">
                            + Přidat jiný parametr
                        </button>
                        <div id="customHematoParameters"></div>
                    </div>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-outline" onclick="closeAddTestModal()">Zrušit</button>
                    <button type="submit" class="btn btn-primary">Uložit test</button>
                </div>
            </form>
        </div>
    </div>
</div>

<style>
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
    background-color: white;
    margin: 2% auto;
    padding: 0;
    border-radius: 8px;
    width: 90%;
    max-width: 1200px;
    max-height: 90vh;
    overflow: hidden;
    display: flex;
    flex-direction: column;
}

.modal-header {
    padding: 20px 30px;
    border-bottom: 2px solid #f0f0f0;
    display: flex;
    justify-content: space-between;
    align-items: center;
    background: linear-gradient(135deg, #c0392b 0%, #a93226 100%);
    color: white;
}

.modal-header h2 {
    margin: 0;
    font-size: 22px;
}

.close {
    color: white;
    font-size: 32px;
    font-weight: bold;
    cursor: pointer;
    line-height: 1;
}

.close:hover {
    opacity: 0.8;
}

.modal-body {
    padding: 30px;
    overflow-y: auto;
    flex: 1;
}

.modal-footer {
    padding: 20px 30px;
    border-top: 2px solid #f0f0f0;
    display: flex;
    justify-content: flex-end;
    gap: 10px;
}

.form-row {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 20px;
    margin-bottom: 20px;
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

.parameters-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
    gap: 15px;
    margin-bottom: 20px;
}

.parameter-input label {
    display: block;
    margin-bottom: 5px;
    font-size: 13px;
    font-weight: 500;
    color: #555;
}

.input-with-unit {
    display: flex;
    align-items: center;
    gap: 8px;
}

.input-with-unit input[type="number"] {
    flex: 1;
}

.unit-label {
    font-size: 12px;
    color: #7f8c8d;
    min-width: 60px;
    font-weight: 500;
}

.custom-parameter-section {
    margin-top: 20px;
    padding-top: 20px;
    border-top: 2px dashed #e0e0e0;
}

.custom-parameter-row {
    display: grid;
    grid-template-columns: 2fr 2fr 1fr auto;
    gap: 10px;
    margin-top: 10px;
    align-items: end;
}

.text-muted {
    color: #7f8c8d;
    font-size: 14px;
    margin-bottom: 15px;
}

hr {
    border: none;
    border-top: 2px solid #f0f0f0;
    margin: 25px 0;
}
</style>

<script>
let customParamCounter = 0;

function showAddTestModal(testType) {
    const modal = document.getElementById('addTestModal');
    const title = document.getElementById('modalTitle');
    const testTypeInput = document.getElementById('testType');
    const biochemParams = document.getElementById('biochemParameters');
    const hematoParams = document.getElementById('hematoParameters');

    testTypeInput.value = testType;

    if (testType === 'biochemistry') {
        title.textContent = 'Přidat biochemický test';
        biochemParams.style.display = 'block';
        hematoParams.style.display = 'none';
    } else {
        title.textContent = 'Přidat hematologický test';
        biochemParams.style.display = 'none';
        hematoParams.style.display = 'block';
    }

    modal.style.display = 'block';
}

function closeAddTestModal() {
    document.getElementById('addTestModal').style.display = 'none';
    document.getElementById('addTestForm').reset();
    document.getElementById('customBiochemParameters').innerHTML = '';
    document.getElementById('customHematoParameters').innerHTML = '';
    customParamCounter = 0;
}

function addCustomParameter(testType) {
    customParamCounter++;
    const containerId = testType === 'biochemistry' ? 'customBiochemParameters' : 'customHematoParameters';
    const container = document.getElementById(containerId);

    const row = document.createElement('div');
    row.className = 'custom-parameter-row';
    row.id = `customParam${customParamCounter}`;
    row.innerHTML = `
        <div class="form-group" style="margin: 0;">
            <label>Název parametru</label>
            <input type="text"
                   name="custom_params[${customParamCounter}][name]"
                   class="form-control"
                   placeholder="např. Speciální test"
                   required>
        </div>
        <div class="form-group" style="margin: 0;">
            <label>Hodnota</label>
            <input type="text"
                   name="custom_params[${customParamCounter}][value]"
                   class="form-control param-value-input"
                   placeholder="hodnota"
                   required>
        </div>
        <div class="form-group" style="margin: 0;">
            <label>Jednotka</label>
            <input type="text"
                   name="custom_params[${customParamCounter}][unit]"
                   class="form-control"
                   placeholder="např. mg/L"
                   required>
        </div>
        <div class="form-group" style="margin: 0;">
            <label>&nbsp;</label>
            <button type="button" class="btn btn-sm btn-danger" onclick="removeCustomParameter(${customParamCounter})">
                ✕
            </button>
        </div>
    `;

    container.appendChild(row);
}

function removeCustomParameter(id) {
    const element = document.getElementById(`customParam${id}`);
    if (element) {
        element.remove();
    }
}

// Close modal when clicking outside
window.onclick = function(event) {
    const modal = document.getElementById('addTestModal');
    if (event.target === modal) {
        closeAddTestModal();
    }
}

// Convert commas to dots on form submission (only for numeric values)
document.addEventListener('DOMContentLoaded', function() {
    const testForm = document.getElementById('addTestForm');
    if (testForm) {
        testForm.addEventListener('submit', function(e) {
            // Convert all commas to dots in parameter value inputs (but only for numeric-like values)
            const valueInputs = this.querySelectorAll('.param-value-input');
            valueInputs.forEach(input => {
                if (input.value && input.value.includes(',')) {
                    // Only replace commas if the value looks numeric
                    // Don't replace for text values like "neg.", "pozitivní", etc.
                    const trimmedValue = input.value.trim().toLowerCase();
                    if (!trimmedValue.includes('neg') && !trimmedValue.includes('poz')) {
                        input.value = input.value.replace(/,/g, '.');
                    }
                }
            });
        });
    }
});
</script>
