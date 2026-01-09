<?php $layout = 'main'; ?>

<div class="page-header">
    <div class="breadcrumb">
        <a href="/">Pracoviště</a> /
        <a href="/urineanalysis/workplace/<?= $animal['workplace_id'] ?>"><?= htmlspecialchars($animal['workplace_name']) ?></a> /
        <?= htmlspecialchars($animal['name'] ?? $animal['identifier']) ?>
    </div>
    <h1><?= htmlspecialchars($animal['name'] ?? 'Zvíře #' . $animal['identifier']) ?></h1>
</div>

<!-- Základní informace o zvířeti -->
<div class="card">
    <div class="card-header">
        <h2>Základní informace</h2>
        <?php if ($canEdit): ?>
            <button type="button" class="btn btn-sm btn-outline" onclick="toggleEditMode()" id="editBtn">
                Upravit
            </button>
        <?php endif; ?>
    </div>
    <div class="card-body">
        <form id="animalInfoForm" onsubmit="saveAnimalInfo(event)">
            <div class="info-grid">
                <div class="info-item">
                    <label>Druh: *</label>
                    <span class="view-mode"><?= htmlspecialchars($animal['species']) ?></span>
                    <input type="text" name="species" class="form-control edit-mode" value="<?= htmlspecialchars($animal['species']) ?>" required style="display: none;">
                </div>
                <div class="info-item">
                    <label>Pohlaví:</label>
                    <span class="view-mode">
                        <?php
                        $genders = ['male' => 'Samec', 'female' => 'Samice', 'unknown' => 'Neznámé'];
                        echo $genders[$animal['gender']] ?? 'Neznámé';
                        ?>
                    </span>
                    <select name="gender" class="form-control edit-mode" style="display: none;">
                        <option value="unknown" <?= $animal['gender'] === 'unknown' ? 'selected' : '' ?>>Neznámé</option>
                        <option value="male" <?= $animal['gender'] === 'male' ? 'selected' : '' ?>>Samec</option>
                        <option value="female" <?= $animal['gender'] === 'female' ? 'selected' : '' ?>>Samice</option>
                    </select>
                </div>
                <div class="info-item">
                    <label>Datum narození:</label>
                    <span class="view-mode">
                        <?= $animal['birth_date'] ? date('d.m.Y', strtotime($animal['birth_date'])) : '-' ?>
                    </span>
                    <input type="date" name="birth_date" class="form-control edit-mode" value="<?= $animal['birth_date'] ?? '' ?>" style="display: none;">
                </div>
                <div class="info-item view-mode">
                    <label>Věk:</label>
                    <span>
                        <?php
                        if ($animal['birth_date']) {
                            $age = date_diff(date_create($animal['birth_date']), date_create('now'))->y;
                            echo $age . ' let';
                        } else {
                            echo '-';
                        }
                        ?>
                    </span>
                </div>
                <div class="info-item">
                    <label>Další kontrola:</label>
                    <span class="view-mode"><?= htmlspecialchars($animal['next_check_date'] ?? '-') ?></span>
                    <input type="text" name="next_check_date" class="form-control edit-mode" value="<?= htmlspecialchars($animal['next_check_date'] ?? '') ?>" style="display: none;">
                </div>
                <div class="info-item">
                    <label>Aktuální výběh:</label>
                    <span class="view-mode"><?= htmlspecialchars($animal['enclosure_name'] ?? '-') ?></span>
                    <select name="current_enclosure_id" class="form-control edit-mode" style="display: none;">
                        <option value="">Bez výběhu</option>
                        <?php foreach ($enclosures as $enc): ?>
                            <option value="<?= $enc['id'] ?>" <?= $animal['current_enclosure_id'] == $enc['id'] ? 'selected' : '' ?>>
                                <?= htmlspecialchars($enc['name']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="info-item">
                    <label>Stav:</label>
                    <span class="view-mode">
                        <?php
                        $statuses = ['active' => 'Aktivní', 'transferred' => 'Přesunutý', 'deceased' => 'Uhynulý', 'removed' => 'Odchovaný'];
                        echo $statuses[$animal['current_status']] ?? 'Neznámý';
                        ?>
                    </span>
                    <select name="current_status" id="statusSelect" class="form-control edit-mode" style="display: none;" onchange="toggleTransferLocation()">
                        <option value="active" <?= $animal['current_status'] === 'active' ? 'selected' : '' ?>>Aktivní</option>
                        <option value="transferred" <?= $animal['current_status'] === 'transferred' ? 'selected' : '' ?>>Přesunutý</option>
                        <option value="deceased" <?= $animal['current_status'] === 'deceased' ? 'selected' : '' ?>>Uhynulý</option>
                        <option value="removed" <?= $animal['current_status'] === 'removed' ? 'selected' : '' ?>>Odchovaný</option>
                    </select>
                </div>
            </div>

            <div class="info-item full-width edit-mode" id="transferLocationGroup" style="display: none;">
                <label>Kam přesunuto: *</label>
                <select name="transfer_workplace_id" class="form-control" id="transferWorkplaceSelect">
                    <option value="">Vyberte pracoviště...</option>
                    <?php foreach ($transferWorkplaces as $wp): ?>
                        <option value="<?= $wp['id'] ?>">
                            <?= htmlspecialchars($wp['name']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="info-item full-width">
                <label>Poznámky:</label>
                <span class="view-mode"><?= $animal['notes'] ? nl2br(htmlspecialchars($animal['notes'])) : '-' ?></span>
                <textarea name="notes" class="form-control edit-mode" rows="3" style="display: none;"><?= htmlspecialchars($animal['notes'] ?? '') ?></textarea>
            </div>

            <div class="edit-mode" style="display: none; margin-top: 15px;">
                <button type="submit" class="btn btn-primary">Uložit změny</button>
                <button type="button" class="btn btn-outline" onclick="cancelEditMode()">Zrušit</button>
            </div>
        </form>
    </div>
</div>

<!-- Analýza moči -->
<div class="card">
    <div class="card-header">
        <h2>Analýza moči</h2>
        <a href="/urineanalysis/animal/<?= $animal['id'] ?>" class="btn btn-sm btn-primary">Zobrazit detail</a>
    </div>
    <div class="card-body">
        <?php if (empty($urineTests)): ?>
            <p class="text-muted">Zatím nebylo provedeno žádné vyšetření.</p>
        <?php else: ?>
            <table class="test-list-table">
                <thead>
                    <tr>
                        <th>Datum</th>
                        <th>Místo</th>
                        <th>Referenční zdroj</th>
                        <th>Vytvořil</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($urineTests as $test): ?>
                        <tr class="clickable-row" onclick="openUrineDetail()">
                            <td><?= date('d.m.Y', strtotime($test['test_date'])) ?></td>
                            <td><?= htmlspecialchars($test['test_location'] ?? '-') ?></td>
                            <td><?= htmlspecialchars($test['reference_source']) ?></td>
                            <td><?= htmlspecialchars($test['created_by_name'] ?? '-') ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </div>
</div>

<style>
.breadcrumb {
    color: #7f8c8d;
    font-size: 14px;
    margin-bottom: 15px;
}

.breadcrumb a {
    color: #f39c12;
    text-decoration: none;
}

.breadcrumb a:hover {
    text-decoration: underline;
}

.page-header h1 {
    margin: 0;
    color: #2c3e50;
}

.card {
    background: white;
    border-radius: 8px;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
    margin-bottom: 20px;
}

.card-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 20px;
    border-bottom: 1px solid #ecf0f1;
}

.card-header h2 {
    margin: 0;
    color: #2c3e50;
    font-size: 18px;
}

.card-body {
    padding: 20px;
}

.info-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
    gap: 15px;
}

.info-item {
    display: flex;
    flex-direction: column;
}

.info-item.full-width {
    grid-column: 1 / -1;
}

.info-item label {
    font-weight: 600;
    color: #7f8c8d;
    margin-bottom: 5px;
    font-size: 14px;
}

.info-item span {
    color: #2c3e50;
}

.form-control {
    width: 100%;
    padding: 8px;
    border: 1px solid #d0d0d0;
    border-radius: 4px;
    font-size: 14px;
}

.form-control:focus {
    outline: none;
    border-color: #f39c12;
}

.test-list-table {
    width: 100%;
    border-collapse: collapse;
}

.test-list-table thead {
    background: #f8f9fa;
}

.test-list-table th {
    padding: 12px;
    text-align: left;
    font-weight: 600;
    color: #2c3e50;
    border-bottom: 2px solid #ecf0f1;
}

.test-list-table td {
    padding: 12px;
    border-bottom: 1px solid #f0f0f0;
}

.test-list-table tbody tr:hover {
    background-color: #f8f9fa;
}

.clickable-row {
    cursor: pointer;
    transition: background-color 0.2s;
}

.clickable-row:hover {
    background-color: #fef5e7 !important;
}

.text-muted {
    color: #7f8c8d;
}

.btn {
    padding: 8px 16px;
    border-radius: 4px;
    text-decoration: none;
    font-size: 14px;
    font-weight: 600;
    cursor: pointer;
    border: none;
    transition: all 0.2s;
    display: inline-block;
}

.btn-primary {
    background: linear-gradient(135deg, #f39c12 0%, #e67e22 100%);
    color: white;
}

.btn-primary:hover {
    background: linear-gradient(135deg, #e67e22 0%, #d35400 100%);
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

.btn-sm {
    padding: 6px 12px;
    font-size: 13px;
}
</style>

<script>
function toggleEditMode() {
    document.querySelectorAll('.view-mode').forEach(el => el.style.display = 'none');
    document.querySelectorAll('.edit-mode').forEach(el => el.style.display = '');
    document.getElementById('editBtn').style.display = 'none';
    toggleTransferLocation();
}

function cancelEditMode() {
    document.querySelectorAll('.view-mode').forEach(el => el.style.display = '');
    document.querySelectorAll('.edit-mode').forEach(el => el.style.display = 'none');
    document.getElementById('editBtn').style.display = '';
}

function toggleTransferLocation() {
    const statusSelect = document.getElementById('statusSelect');
    const transferGroup = document.getElementById('transferLocationGroup');

    if (statusSelect && transferGroup) {
        if (statusSelect.value === 'transferred') {
            transferGroup.style.display = '';
        } else {
            transferGroup.style.display = 'none';
        }
    }
}

async function saveAnimalInfo(event) {
    event.preventDefault();

    const formData = new FormData(event.target);
    const data = Object.fromEntries(formData.entries());

    // Validate transfer workplace if status is transferred
    if (data.current_status === 'transferred' && !data.transfer_workplace_id) {
        alert('Musíte vybrat, kam bylo zvíře přesunuto');
        return;
    }

    try {
        const response = await fetch('/animals/<?= $animal['id'] ?>/update', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify(data)
        });

        if (response.ok) {
            location.reload();
        } else {
            alert('Chyba při ukládání změn');
        }
    } catch (error) {
        console.error('Error:', error);
        alert('Chyba při ukládání změn');
    }
}

function openUrineDetail() {
    window.location.href = `/urineanalysis/animal/<?= $animal['id'] ?>`;
}
</script>
