<!-- Add Item Modal -->
<div id="addItemModal" class="modal">
    <div class="modal-content">
        <div class="modal-header">
            <h2>Přidat novou položku</h2>
            <span class="close" onclick="closeAddItemModal()">&times;</span>
        </div>
        <div class="modal-body">
            <form method="POST" action="/warehouse/items/create">
                <input type="hidden" name="workplace_id" value="<?= $workplace['id'] ?>">
                <input type="hidden" name="redirect" value="<?= $_SERVER['REQUEST_URI'] ?>">

                <div class="form-group">
                    <label for="category">Kategorie *</label>
                    <select id="category" name="category" class="form-control" required>
                        <option value="food">🌾 Krmivo</option>
                        <option value="medicament">💊 Léčivo</option>
                    </select>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="item_code">Číslo položky *</label>
                        <input type="text" id="item_code" name="item_code" class="form-control" placeholder="např. 1, 2, 3..." required>
                        <small class="form-help">Unikátní číslo pro snadnou identifikaci</small>
                    </div>
                    <div class="form-group">
                        <label for="unit">Jednotka *</label>
                        <input type="text" id="unit" name="unit" class="form-control" placeholder="kg, L, ks, balení..." required>
                    </div>
                </div>

                <div class="form-group">
                    <label for="name">Název položky *</label>
                    <input type="text" id="name" name="name" class="form-control" required>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="current_stock">Aktuální stav skladu *</label>
                        <input type="number" step="0.01" id="current_stock" name="current_stock" class="form-control" value="0" required>
                    </div>
                    <div class="form-group">
                        <label for="min_stock_level">Minimální stav (pro upozornění)</label>
                        <input type="number" step="0.01" id="min_stock_level" name="min_stock_level" class="form-control">
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="max_stock_level">Maximální stav (cílový)</label>
                        <input type="number" step="0.01" id="max_stock_level" name="max_stock_level" class="form-control">
                    </div>
                    <div class="form-group">
                        <label for="supplier">Dodavatel</label>
                        <input type="text" id="supplier" name="supplier" class="form-control">
                    </div>
                </div>

                <div class="form-group">
                    <label for="storage_location">Místo uložení</label>
                    <input type="text" id="storage_location" name="storage_location" class="form-control" placeholder="např. Regál A, police 3">
                </div>

                <div class="form-group">
                    <label for="notes">Poznámky</label>
                    <textarea id="notes" name="notes" class="form-control" rows="3"></textarea>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-outline" onclick="closeAddItemModal()">Zrušit</button>
                    <button type="submit" class="btn btn-primary">Přidat položku</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Stock Movement Modal -->
<div id="movementModal" class="modal">
    <div class="modal-content">
        <div class="modal-header">
            <h2 id="movementModalTitle">Pohyb zásob</h2>
            <span class="close" onclick="closeMovementModal()">&times;</span>
        </div>
        <div class="modal-body">
            <form method="POST" action="/warehouse/movements/add">
                <input type="hidden" id="movement_item_id" name="item_id">
                <input type="hidden" id="movement_type" name="movement_type">
                <input type="hidden" name="redirect" value="<?= $_SERVER['REQUEST_URI'] ?>">

                <div class="form-group">
                    <label>Položka</label>
                    <p id="movement_item_name" style="font-weight: 600; font-size: 16px; margin: 5px 0;"></p>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="movement_quantity">Množství *</label>
                        <input type="number" step="0.01" id="movement_quantity" name="quantity" class="form-control" required>
                    </div>
                    <div class="form-group">
                        <label for="movement_date">Datum *</label>
                        <input type="date" id="movement_date" name="movement_date" class="form-control" value="<?= date('Y-m-d') ?>" required>
                    </div>
                </div>

                <div class="form-group">
                    <label for="reference_document">Číslo dokladu</label>
                    <input type="text" id="reference_document" name="reference_document" class="form-control" placeholder="Číslo faktury, dodacího listu...">
                </div>

                <!-- Batch fields — visible only for 'in' movements -->
                <div id="batchFields" style="display:none;">
                    <div class="form-row">
                        <div class="form-group">
                            <label for="batch_number">Číslo šarže / LOT</label>
                            <input type="text" id="batch_number" name="batch_number" class="form-control" placeholder="Č. šarže...">
                        </div>
                        <div class="form-group">
                            <label for="expiration_date">Datum expirace</label>
                            <input type="date" id="expiration_date" name="expiration_date" class="form-control">
                        </div>
                    </div>
                </div>

                <div class="form-group">
                    <label for="movement_notes">Poznámka</label>
                    <textarea id="movement_notes" name="notes" class="form-control" rows="3" placeholder="Důvod pohybu (např. Týdenní krmení, Dodávka od dodavatele XY...)"></textarea>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-outline" onclick="closeMovementModal()">Zrušit</button>
                    <button type="submit" class="btn btn-primary">Zaznamenat pohyb</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Set Consumption Modal -->
<div id="consumptionModal" class="modal">
    <div class="modal-content">
        <div class="modal-header">
            <h2>Nastavit týdenní spotřebu</h2>
            <span class="close" onclick="closeConsumptionModal()">&times;</span>
        </div>
        <div class="modal-body">
            <form method="POST" action="/warehouse/consumption/set">
                <input type="hidden" id="consumption_item_id" name="item_id">
                <input type="hidden" name="redirect" value="<?= $_SERVER['REQUEST_URI'] ?>">

                <div class="form-group">
                    <label>Položka</label>
                    <p id="consumption_item_name" style="font-weight: 600; font-size: 16px; margin: 5px 0;"></p>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="weekly_consumption">Týdenní spotřeba *</label>
                        <input type="number" step="0.01" id="weekly_consumption" name="weekly_consumption" class="form-control" required>
                        <small class="form-help">Odhadovaná spotřeba za týden</small>
                    </div>
                    <div class="form-group">
                        <label for="desired_weeks_stock">Počet týdnů zásob *</label>
                        <input type="number" id="desired_weeks_stock" name="desired_weeks_stock" class="form-control" value="4" required>
                        <small class="form-help">Kolik týdnů dopředu chcete mít zásoby</small>
                    </div>
                </div>

                <div class="form-group">
                    <label for="consumption_notes">Poznámky</label>
                    <textarea id="consumption_notes" name="notes" class="form-control" rows="2"></textarea>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-outline" onclick="closeConsumptionModal()">Zrušit</button>
                    <button type="submit" class="btn btn-primary">Uložit spotřebu</button>
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
    margin: 5% auto;
    padding: 0;
    border-radius: 8px;
    width: 90%;
    max-width: 600px;
}

.modal-header {
    padding: 20px 30px;
    border-bottom: 2px solid #f0f0f0;
    display: flex;
    justify-content: space-between;
    align-items: center;
    background: linear-gradient(135deg, #27ae60 0%, #229954 100%);
    color: white;
    border-radius: 8px 8px 0 0;
}

.modal-header h2 {
    margin: 0;
    font-size: 20px;
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

.form-control {
    width: 100%;
    padding: 10px 12px;
    border: 1px solid #ddd;
    border-radius: 4px;
    font-size: 14px;
}

.form-control:focus {
    outline: none;
    border-color: #27ae60;
    box-shadow: 0 0 0 3px rgba(39, 174, 96, 0.1);
}

.form-help {
    display: block;
    margin-top: 5px;
    color: #7f8c8d;
    font-size: 12px;
}

textarea.form-control {
    resize: vertical;
}
</style>

<script>
function showAddItemModal() {
    document.getElementById('addItemModal').style.display = 'block';
}

function closeAddItemModal() {
    document.getElementById('addItemModal').style.display = 'none';
    document.querySelector('#addItemModal form').reset();
}

function showMovementModal(itemId, itemName, type) {
    document.getElementById('movement_item_id').value = itemId;
    document.getElementById('movement_type').value = type;
    document.getElementById('movement_item_name').textContent = itemName;

    const title = type === 'in' ? 'Příjem zásob - ' + itemName : 'Výdej zásob - ' + itemName;
    document.getElementById('movementModalTitle').textContent = title;

    const batchFields = document.getElementById('batchFields');
    if (type === 'in') {
        batchFields.style.display = 'block';
    } else {
        batchFields.style.display = 'none';
        document.getElementById('batch_number').value = '';
        document.getElementById('expiration_date').value = '';
    }

    document.getElementById('movementModal').style.display = 'block';
}

function closeMovementModal() {
    document.getElementById('movementModal').style.display = 'none';
    document.querySelector('#movementModal form').reset();
}

function showConsumptionModal(itemId, itemName) {
    document.getElementById('consumption_item_id').value = itemId;
    document.getElementById('consumption_item_name').textContent = itemName;
    document.getElementById('consumptionModal').style.display = 'block';
}

function closeConsumptionModal() {
    document.getElementById('consumptionModal').style.display = 'none';
    document.querySelector('#consumptionModal form').reset();
}

// Close modals when clicking outside
window.onclick = function(event) {
    if (event.target.classList.contains('modal')) {
        event.target.style.display = 'none';
    }
}
</script>
