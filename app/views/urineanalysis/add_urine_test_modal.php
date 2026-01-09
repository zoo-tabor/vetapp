<!-- Add Urine Test Modal -->
<div id="addTestModal" class="modal">
    <div class="modal-content">
        <div class="modal-header">
            <h2>Přidat vyšetření moči</h2>
            <span class="close" onclick="closeAddTestModal()">&times;</span>
        </div>
        <div class="modal-body">
            <form id="addTestForm" method="POST" action="/urineanalysis/tests/create">
                <input type="hidden" name="animal_id" value="<?= $animal['id'] ?>">

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

                <!-- Chemical Parameters Section -->
                <div class="parameter-section">
                    <h3>Moč chemicky</h3>
                    <div class="parameters-grid">
                        <?php
                        $chemicalParams = [
                            ['name' => 'Glukóza', 'unit' => ''],
                            ['name' => 'Bílkovina', 'unit' => ''],
                            ['name' => 'Bilirubin', 'unit' => ''],
                            ['name' => 'Urobilinogen', 'unit' => ''],
                            ['name' => 'pH', 'unit' => ''],
                            ['name' => 'Krev', 'unit' => ''],
                            ['name' => 'Ketony', 'unit' => ''],
                            ['name' => 'Nitrity', 'unit' => ''],
                            ['name' => 'Leukocyty', 'unit' => ''],
                            ['name' => 'Specifická hustota', 'unit' => 'kg/m3'],
                        ];

                        foreach ($chemicalParams as $param):
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
                                    <?php if ($param['unit']): ?>
                                        <span class="unit-label"><?= htmlspecialchars($param['unit']) ?></span>
                                    <?php endif; ?>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>

                <hr>

                <!-- Sediment Parameters Section -->
                <div class="parameter-section">
                    <h3>Močový sediment</h3>
                    <div class="parameters-grid">
                        <?php
                        $sedimentParams = [
                            ['name' => 'Erytrocyty elementy', 'unit' => ''],
                            ['name' => 'Erytrocyty', 'unit' => '0 (0 - 5), 1 (6 -50), 2 (51 - 100), 3 (100 - 500), 4 ( > 500)'],
                            ['name' => 'Leukocyty elementy', 'unit' => ''],
                            ['name' => 'Leukocyty', 'unit' => '0 (0 - 10), 1 (11 - 50), 2 (51 - 100), 3 (101 - 250), 4 ( > 250)'],
                            ['name' => 'Bakterie', 'unit' => ''],
                            ['name' => 'Drť', 'unit' => ''],
                            ['name' => 'Hlen', 'unit' => ''],
                        ];

                        foreach ($sedimentParams as $param):
                        ?>
                            <div class="parameter-input">
                                <label><?= htmlspecialchars($param['name']) ?></label>
                                <div class="input-with-unit">
                                    <input type="text"
                                           name="params[<?= htmlspecialchars($param['name']) ?>][value]"
                                           class="form-control param-value-input"
                                           placeholder="hodnota"
                                           title="<?= htmlspecialchars($param['unit']) ?>">
                                    <input type="hidden"
                                           name="params[<?= htmlspecialchars($param['name']) ?>][unit]"
                                           value="<?= htmlspecialchars($param['unit']) ?>">
                                    <?php if ($param['unit'] && !str_contains($param['unit'], '(')): ?>
                                        <span class="unit-label"><?= htmlspecialchars($param['unit']) ?></span>
                                    <?php endif; ?>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>

                <hr>

                <!-- Urine Parameters Section -->
                <div class="parameter-section">
                    <h3>Močové parametry</h3>
                    <div class="parameters-grid">
                        <?php
                        $urineParams = [
                            ['name' => 'Albumin - moč', 'unit' => 'mg/l'],
                            ['name' => 'Kreatinin - moč', 'unit' => 'mmol/l'],
                            ['name' => 'Albumin/Kreatinin - moč', 'unit' => 'g/mol'],
                            ['name' => 'Bílkovina - moč', 'unit' => 'g/l'],
                            ['name' => 'Bílkovina/Kreatinin - moč', 'unit' => 'index'],
                        ];

                        foreach ($urineParams as $param):
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
                </div>

                <hr>

                <!-- Custom Parameters Section -->
                <div class="parameter-section">
                    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 15px;">
                        <h3 style="margin: 0;">Další parametry</h3>
                        <button type="button" class="btn btn-add-param" onclick="addCustomParameter()">
                            + Přidat jiný parametr
                        </button>
                    </div>
                    <div id="customParametersContainer" class="parameters-grid">
                        <!-- Custom parameters will be added here dynamically -->
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
    background: linear-gradient(135deg, #f39c12 0%, #e67e22 100%);
    color: white;
    padding: 20px 30px;
    border-radius: 8px 8px 0 0;
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.modal-header h2 {
    margin: 0;
    font-size: 24px;
}

.close {
    color: white;
    font-size: 32px;
    font-weight: bold;
    cursor: pointer;
    line-height: 1;
    transition: color 0.2s;
}

.close:hover {
    color: #ecf0f1;
}

.modal-body {
    padding: 30px;
    overflow-y: auto;
    flex: 1;
}

.modal-footer {
    padding: 20px 30px;
    background: #f8f9fa;
    border-top: 1px solid #dee2e6;
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

.form-control {
    width: 100%;
    padding: 10px;
    border: 1px solid #ddd;
    border-radius: 4px;
    font-size: 14px;
    transition: border-color 0.2s;
}

.form-control:focus {
    outline: none;
    border-color: #f39c12;
}

.parameter-section {
    margin-bottom: 30px;
}

.parameter-section h3 {
    color: #f39c12;
    font-size: 20px;
    margin-bottom: 15px;
    font-weight: 600;
    border-bottom: 2px solid #f39c12;
    padding-bottom: 8px;
}

.parameters-grid {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 15px;
}

@media (max-width: 1200px) {
    .parameters-grid {
        grid-template-columns: repeat(2, 1fr);
    }
}

@media (max-width: 768px) {
    .parameters-grid {
        grid-template-columns: 1fr;
    }
}

.parameter-input {
    display: flex;
    flex-direction: column;
}

.parameter-input label {
    display: block;
    margin-bottom: 6px;
    font-size: 12px;
    font-weight: 600;
    color: #34495e;
    min-height: 32px;
    line-height: 1.3;
}

.input-with-unit {
    position: relative;
    display: flex;
    align-items: center;
    gap: 8px;
}

.param-value-input {
    flex: 1;
    padding: 8px 10px;
    border: 1px solid #ddd;
    border-radius: 4px;
    font-size: 14px;
    min-width: 0;
}

.param-value-input:focus {
    outline: none;
    border-color: #f39c12;
    box-shadow: 0 0 0 2px rgba(243, 156, 18, 0.1);
}

.unit-label {
    font-size: 12px;
    color: #7f8c8d;
    font-weight: 500;
    white-space: nowrap;
    min-width: fit-content;
}

.btn-outline {
    background: white;
    border: 2px solid #95a5a6;
    color: #95a5a6;
    padding: 10px 20px;
    border-radius: 4px;
    font-size: 14px;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.2s;
}

.btn-outline:hover {
    background: #95a5a6;
    color: white;
}

.btn-add-param {
    background: white;
    border: 2px solid #f39c12;
    color: #f39c12;
    padding: 8px 16px;
    border-radius: 4px;
    font-size: 13px;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.2s;
}

.btn-add-param:hover {
    background: #f39c12;
    color: white;
}

.custom-param-row {
    display: flex;
    gap: 8px;
    align-items: flex-start;
}

.custom-param-row .param-name-input,
.custom-param-row .param-unit-input {
    padding: 8px 10px;
    border: 1px solid #ddd;
    border-radius: 4px;
    font-size: 14px;
}

.custom-param-row .param-name-input {
    flex: 2;
}

.custom-param-row .param-unit-input {
    flex: 1;
}

.btn-remove-param {
    background: #e74c3c;
    border: none;
    color: white;
    padding: 8px 12px;
    border-radius: 4px;
    font-size: 12px;
    font-weight: 600;
    cursor: pointer;
    transition: background 0.2s;
    white-space: nowrap;
}

.btn-remove-param:hover {
    background: #c0392b;
}

hr {
    border: none;
    border-top: 1px solid #ecf0f1;
    margin: 25px 0;
}
</style>

<script>
let customParamCounter = 0;

function addCustomParameter() {
    const container = document.getElementById('customParametersContainer');
    const paramId = 'custom_param_' + (++customParamCounter);

    const paramDiv = document.createElement('div');
    paramDiv.className = 'parameter-input';
    paramDiv.id = paramId;
    paramDiv.innerHTML = `
        <label>Vlastní parametr</label>
        <div class="custom-param-row">
            <input type="text"
                   class="param-name-input"
                   placeholder="Název parametru"
                   onchange="updateCustomParamName(this, '${paramId}')">
            <input type="text"
                   class="param-value-input"
                   name="params[${paramId}][value]"
                   placeholder="Hodnota">
            <input type="text"
                   class="param-unit-input"
                   name="params[${paramId}][unit]"
                   placeholder="Jednotka">
            <button type="button" class="btn-remove-param" onclick="removeCustomParameter('${paramId}')">
                Smazat
            </button>
        </div>
    `;

    container.appendChild(paramDiv);
}

function updateCustomParamName(input, paramId) {
    const paramName = input.value.trim();
    if (!paramName) return;

    const paramDiv = document.getElementById(paramId);
    const valueInput = paramDiv.querySelector('.param-value-input');
    const unitInput = paramDiv.querySelector('.param-unit-input');

    // Update the name attributes to use the custom parameter name
    valueInput.name = `params[${paramName}][value]`;
    unitInput.name = `params[${paramName}][unit]`;

    // Update label
    const label = paramDiv.querySelector('label');
    label.textContent = paramName;
}

function removeCustomParameter(paramId) {
    const paramDiv = document.getElementById(paramId);
    if (paramDiv) {
        paramDiv.remove();
    }
}
</script>
