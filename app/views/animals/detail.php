<?php
$layout = 'main';
$fromAnimals = isset($_GET['from']) && $_GET['from'] === 'animals';
?>

<div class="page-header">
    <div class="breadcrumb <?= $fromAnimals ? 'breadcrumb-animals' : '' ?>">
        <?php if ($fromAnimals): ?>
            <a href="/animals">Seznam zvířat</a> /
            <a href="/animals/workplace/<?= $animal['workplace_id'] ?>"><?= htmlspecialchars($animal['workplace_name']) ?></a> /
            <?= htmlspecialchars($animal['name'] ?? $animal['identifier']) ?>
        <?php else: ?>
            <a href="/">Pracoviště</a> /
            <a href="/workplace/<?= $animal['workplace_id'] ?>"><?= htmlspecialchars($animal['workplace_name']) ?></a> /
            <a href="/workplace/<?= $animal['workplace_id'] ?>/animals">Zvířata</a> /
            <?= htmlspecialchars($animal['name'] ?? $animal['identifier']) ?>
        <?php endif; ?>
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
                    <span class="view-mode">
                        <?= $animal['next_check_date'] ? date('d.m.Y', strtotime($animal['next_check_date'])) : '-' ?>
                    </span>
                    <input type="date" name="next_check_date" class="form-control edit-mode" value="<?= $animal['next_check_date'] ?? '' ?>" style="display: none;">
                </div>
                <div class="info-item">
                    <label>Aktuální výběh:</label>
                    <span class="view-mode"><?= htmlspecialchars($animal['enclosure_name'] ?? '-') ?></span>
                    <select name="current_enclosure_id" class="form-control edit-mode" style="display: none;">
                        <option value="">Žádný výběh</option>
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
                        $statusLabels = [
                            'active' => ['Aktivní', 'success'],
                            'transferred' => ['Přesunuto', 'info'],
                            'deceased' => ['Uhynulo', 'danger'],
                            'removed' => ['Vyřazeno', 'secondary']
                        ];
                        $status = $statusLabels[$animal['current_status']] ?? ['Neznámý', 'secondary'];
                        ?>
                        <span class="badge badge-<?= $status[1] ?>"><?= $status[0] ?></span>
                        <?php if ($animal['current_status'] === 'transferred' && !empty($animal['transfer_location'])): ?>
                            <br><small>Kam: <?= htmlspecialchars($animal['transfer_location']) ?></small>
                        <?php endif; ?>
                    </span>
                    <select name="current_status" id="statusSelect" class="form-control edit-mode" style="display: none;" onchange="toggleTransferLocation()">
                        <option value="active" <?= $animal['current_status'] === 'active' ? 'selected' : '' ?>>Aktivní</option>
                        <option value="transferred" <?= $animal['current_status'] === 'transferred' ? 'selected' : '' ?>>Přesunuto</option>
                        <option value="deceased" <?= $animal['current_status'] === 'deceased' ? 'selected' : '' ?>>Uhynulo</option>
                        <option value="removed" <?= $animal['current_status'] === 'removed' ? 'selected' : '' ?>>Vyřazeno</option>
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
                            <?php if ($wp['name'] === 'Deponace'): ?>
                                (půjčeno mimo organizaci)
                            <?php endif; ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="info-item full-width">
                <label>Poznámky:</label>
                <p class="view-mode"><?= $animal['notes'] ? nl2br(htmlspecialchars($animal['notes'])) : '-' ?></p>
                <textarea name="notes" class="form-control edit-mode" rows="4" style="display: none;"><?= htmlspecialchars($animal['notes'] ?? '') ?></textarea>
            </div>

            <div class="form-actions edit-mode" style="display: none; margin-top: 20px;">
                <button type="submit" class="btn btn-primary">Uložit změny</button>
                <button type="button" class="btn btn-outline" onclick="cancelEdit()">Zrušit</button>
            </div>
        </form>
    </div>
</div>

<script>
let isEditMode = false;

function toggleEditMode() {
    isEditMode = !isEditMode;
    const viewElements = document.querySelectorAll('.view-mode');
    const editElements = document.querySelectorAll('.edit-mode');
    const editBtn = document.getElementById('editBtn');

    viewElements.forEach(el => el.style.display = isEditMode ? 'none' : '');
    editElements.forEach(el => el.style.display = isEditMode ? '' : 'none');

    if (isEditMode) {
        editBtn.textContent = 'Zrušit';
        editBtn.classList.remove('btn-outline');
        editBtn.classList.add('btn-secondary');
        // Check if we need to show transfer location on edit mode entry
        toggleTransferLocation();
    } else {
        editBtn.textContent = 'Upravit';
        editBtn.classList.remove('btn-secondary');
        editBtn.classList.add('btn-outline');
    }
}

function toggleTransferLocation() {
    const statusSelect = document.getElementById('statusSelect');
    const transferLocationGroup = document.getElementById('transferLocationGroup');
    const transferWorkplaceSelect = document.getElementById('transferWorkplaceSelect');

    if (statusSelect && statusSelect.value === 'transferred') {
        transferLocationGroup.style.display = '';
        transferWorkplaceSelect.required = true;
    } else {
        transferLocationGroup.style.display = 'none';
        transferWorkplaceSelect.required = false;
        transferWorkplaceSelect.value = '';
    }
}

function cancelEdit() {
    // Reset form to original values
    document.getElementById('animalInfoForm').reset();
    toggleEditMode();
}

function saveAnimalInfo(event) {
    event.preventDefault();

    const formData = new FormData(event.target);
    const data = Object.fromEntries(formData.entries());

    // Validate transfer workplace if status is transferred
    if (data.current_status === 'transferred' && !data.transfer_workplace_id) {
        alert('Musíte vybrat, kam bylo zvíře přesunuto');
        return;
    }

    fetch('/animals/<?= $animal['id'] ?>/update', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json'
        },
        body: JSON.stringify(data)
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Redirect to the new workplace's animal list if transferred
            const statusSelect = document.getElementById('statusSelect');
            if (statusSelect && statusSelect.value === 'transferred') {
                const transferWorkplaceSelect = document.getElementById('transferWorkplaceSelect');
                if (transferWorkplaceSelect && transferWorkplaceSelect.value) {
                    window.location.href = '/workplace/' + transferWorkplaceSelect.value + '/animals';
                    return;
                }
            }
            location.reload();
        } else {
            alert('Chyba při ukládání: ' + (data.error || 'Neznámá chyba'));
        }
    })
    .catch(error => {
        alert('Chyba při komunikaci se serverem: ' + error.message);
        console.error('Error:', error);
    });
}
</script>

<?php if (!$fromAnimals): ?>
<!-- Parazitologická vyšetření -->
<div class="card">
    <div class="card-header">
        <h2>Parazitologická vyšetření</h2>
        <?php if ($canEdit): ?>
            <a href="/workplace/<?= $animal['workplace_id'] ?>/animals" class="btn btn-sm btn-primary">
                ➕ Přidat vyšetření
            </a>
        <?php endif; ?>
    </div>
    <div class="card-body">
        <?php if (empty($examinations)): ?>
            <p class="text-muted">Zatím nebylo provedeno žádné vyšetření.</p>
        <?php else:
            // Group examinations by date + institution
            $groupedExams = [];
            foreach ($examinations as $exam) {
                $key = $exam['examination_date'] . '|' . ($exam['institution'] ?? '');
                if (!isset($groupedExams[$key])) {
                    $groupedExams[$key] = [
                        'date' => $exam['examination_date'],
                        'institution' => $exam['institution'] ?? '',
                        'next_check_date' => $exam['next_check_date'] ?? null,
                        'exams' => []
                    ];
                }
                $groupedExams[$key]['exams'][] = $exam;
            }
        ?>
            <div class="table-responsive">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Datum</th>
                            <th>Typ vzorku</th>
                            <th>Nález</th>
                            <th>Aplikace antiparazitika</th>
                            <th>Akce</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($groupedExams as $group):
                            // Determine if any exam in group is positive
                            $hasPositive = false;
                            foreach ($group['exams'] as $e) {
                                if ($e['finding_status'] === 'positive') {
                                    $hasPositive = true;
                                    break;
                                }
                            }
                        ?>
                        <tr>
                            <td>
                                <?= date('d.m.Y', strtotime($group['date'])) ?>
                                <?php if ($group['institution']): ?>
                                    <br><small style="color: #666;"><?= htmlspecialchars($group['institution']) ?></small>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php
                                $types = [];
                                foreach ($group['exams'] as $e) {
                                    $types[] = $e['sample_type'] === 'individual' ? 'Individuální' : 'Směsný';
                                }
                                echo implode('<br>', array_unique($types));
                                ?>
                            </td>
                            <td>
                                <?php foreach ($group['exams'] as $e): ?>
                                    <?php if ($e['finding_status'] === 'positive'): ?>
                                        <span class="badge badge-warning">Pozitivní</span>
                                    <?php else: ?>
                                        <span class="badge badge-success">Negativní</span>
                                    <?php endif; ?>
                                    <br>
                                <?php endforeach; ?>
                            </td>
                            <td>
                                <?php
                                // Show deworming from any exam in the group that has one
                                $dewormingShown = false;
                                foreach ($group['exams'] as $e) {
                                    if (!empty($e['deworming_id'])) {
                                        ?>
                                        <div style="font-size: 0.9em;">
                                            <strong><?= date('d.m.Y', strtotime($e['deworming_date'])) ?></strong><br>
                                            <?= htmlspecialchars($e['medication'] ?? '-') ?>
                                            <?php if (!empty($e['dosage'])): ?>
                                                (<?= htmlspecialchars($e['dosage']) ?>)
                                            <?php endif; ?>
                                            <?php if (!empty($e['administration_route'])): ?>
                                                <br><em><?= htmlspecialchars($e['administration_route']) ?></em>
                                            <?php endif; ?>
                                        </div>
                                        <?php
                                        $dewormingShown = true;
                                        break;
                                    }
                                }
                                if (!$dewormingShown) {
                                    echo '<span style="color: #999;">-</span>';
                                }
                                ?>
                            </td>
                            <td>
                                <button type="button" class="btn btn-sm btn-outline" onclick='openExaminationModal(<?= json_encode($group) ?>)'>Detail</button>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>
</div>

<!-- Odčervení -->
<div class="card">
    <div class="card-header">
        <h2>Historie odčervení</h2>
        <?php if ($canEdit): ?>
            <button type="button" class="btn btn-sm btn-primary" onclick="openDewormingModal()">
                ➕ Aplikace antiparazitika
            </button>
        <?php endif; ?>
    </div>
    <div class="card-body">
        <?php if (empty($dewormings)): ?>
            <p class="text-muted">Zatím nebylo provedeno žádné odčervení.</p>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Datum</th>
                            <th>Přípravek</th>
                            <th>Dávka</th>
                            <th>Důvod</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($dewormings as $dew): ?>
                        <tr>
                            <td><?= date('d.m.Y', strtotime($dew['deworming_date'])) ?></td>
                            <td><?= htmlspecialchars($dew['medication'] ?? '-') ?></td>
                            <td><?= htmlspecialchars($dew['dosage'] ?? '-') ?></td>
                            <td><?= htmlspecialchars($dew['reason'] ?? '-') ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>
</div>

<!-- Plánované kontroly -->
<?php if (!empty($scheduledChecks)): ?>
<div class="card">
    <div class="card-header">
        <h2>Plánované kontroly</h2>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table">
                <thead>
                    <tr>
                        <th>Datum</th>
                        <th>Důvod</th>
                        <th>Stav</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($scheduledChecks as $check): ?>
                    <tr>
                        <td><?= date('d.m.Y', strtotime($check['scheduled_date'])) ?></td>
                        <td><?= htmlspecialchars($check['reason'] ?? '-') ?></td>
                        <td>
                            <span class="badge badge-info">Naplánováno</span>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
<?php endif; ?>

<!-- Deworming Modal -->
<div id="dewormingModal" class="modal" style="display: none;">
    <div class="modal-content">
        <div class="modal-header">
            <h2>Přidat odčervení</h2>
            <span class="modal-close" onclick="closeDewormingModal()">&times;</span>
        </div>
        <form id="dewormingForm" onsubmit="submitDewormingForm(event)">
            <input type="hidden" name="animal_id" value="<?= $animal['id'] ?>">

            <div class="form-group">
                <label for="deworming_date">Datum odčervení: *</label>
                <input type="date" id="deworming_date" name="deworming_date" class="form-control" required value="<?= date('Y-m-d') ?>">
            </div>

            <div class="form-group">
                <label for="medication">Přípravek:</label>
                <input type="text" id="medication" name="medication" class="form-control" placeholder="Např. Ivermectin, Fenbendazol...">
            </div>

            <div class="form-group">
                <label for="dosage">Dávka:</label>
                <input type="text" id="dosage" name="dosage" class="form-control" placeholder="Např. 5ml, 2 tablety...">
            </div>

            <div class="form-group">
                <label for="administration_route">Forma podání:</label>
                <select id="administration_route" name="administration_route" class="form-control">
                    <option value="">Vyberte formu podání...</option>
                    <option value="p.o.">p.o. (per os - ústy)</option>
                    <option value="s.c.">s.c. (subkutánně - pod kůži)</option>
                    <option value="i.m.">i.m. (intramuskulárně - do svalu)</option>
                </select>
            </div>

            <div class="form-group">
                <label for="reason">Důvod:</label>
                <select id="reason" name="reason" class="form-control">
                    <option value="">Vyberte důvod...</option>
                    <option value="Odčervení">Odčervení</option>
                    <option value="Preventivní">Preventivní</option>
                </select>
            </div>

            <div class="form-group">
                <label for="related_examination_id">Související vyšetření:</label>
                <select id="related_examination_id" name="related_examination_id" class="form-control">
                    <option value="">Žádné</option>
                    <?php foreach ($examinations as $exam): ?>
                        <option value="<?= $exam['id'] ?>">
                            <?= date('d.m.Y', strtotime($exam['examination_date'])) ?> -
                            <?= htmlspecialchars($exam['sample_type']) ?> -
                            <?= htmlspecialchars($exam['finding_status']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="form-group">
                <label for="deworming_notes">Poznámky:</label>
                <textarea id="deworming_notes" name="notes" class="form-control" rows="3"></textarea>
            </div>

            <div class="form-actions">
                <button type="submit" class="btn btn-primary">Přidat odčervení</button>
                <button type="button" class="btn btn-outline" onclick="closeDewormingModal()">Zrušit</button>
            </div>
        </form>
    </div>
</div>

<!-- Examination Detail Modal -->
<div id="examinationModal" class="modal" style="display: none;">
    <div class="modal-content" style="max-width: 800px;">
        <div class="modal-header">
            <h2 id="examinationModalTitle">Detail vyšetření</h2>
            <span class="modal-close" onclick="closeExaminationModal()">&times;</span>
        </div>
        <div style="padding: 20px;">
            <div class="form-group">
                <label><strong>Datum vyšetření:</strong></label>
                <p id="examDate"></p>
            </div>

            <div class="form-group">
                <label><strong>Instituce:</strong></label>
                <p id="examInstitution"></p>
            </div>

            <div class="form-group">
                <label><strong>Termín dalšího vyšetření:</strong></label>
                <p id="examNextCheck"></p>
            </div>

            <h3 style="margin-top: 20px; margin-bottom: 15px; border-bottom: 2px solid #3498db; padding-bottom: 5px;">Provedená vyšetření</h3>
            <div class="table-responsive">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Typ vzorku</th>
                            <th>Výsledek</th>
                            <th>Nalezený parazit</th>
                            <th>Intenzita</th>
                            <th>Výběh</th>
                            <th>Poznámky</th>
                        </tr>
                    </thead>
                    <tbody id="examDetailsTable">
                        <!-- Will be populated by JavaScript -->
                    </tbody>
                </table>
            </div>

            <div id="examNotesSection" style="margin-top: 20px; display: none;">
                <label><strong>Poznámky:</strong></label>
                <p id="examNotes" style="background-color: #f9f9f9; padding: 10px; border-radius: 4px; border: 1px solid #ddd;"></p>
            </div>

            <div class="form-actions">
                <button type="button" class="btn btn-outline" onclick="closeExaminationModal()">Zavřít</button>
            </div>
        </div>
    </div>
</div>

<style>
<?php if ($fromAnimals): ?>
/* Purple breadcrumb for animals section */
.breadcrumb a {
    color: #8e44ad;
    text-decoration: none;
}

.breadcrumb a:hover {
    color: #7d3c98;
    text-decoration: underline;
}
<?php endif; ?>

.modal {
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
    background-color: #fefefe;
    margin: 5% auto;
    padding: 0;
    border: 1px solid #888;
    border-radius: 8px;
    width: 90%;
    max-width: 600px;
    box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
}

.modal-header {
    padding: 20px;
    background-color: #3498db;
    color: white;
    border-radius: 8px 8px 0 0;
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.modal-header h2 {
    margin: 0;
    font-size: 1.5rem;
}

.modal-close {
    color: white;
    font-size: 28px;
    font-weight: bold;
    cursor: pointer;
    line-height: 1;
}

.modal-close:hover {
    color: #ecf0f1;
}

.modal-content form {
    padding: 20px;
}

.form-group {
    margin-bottom: 15px;
}

.form-group label {
    display: block;
    margin-bottom: 5px;
    font-weight: 600;
    color: #2c3e50;
}

.form-actions {
    margin-top: 20px;
    display: flex;
    gap: 10px;
    justify-content: flex-end;
}
</style>

<script>
function openDewormingModal() {
    document.getElementById('dewormingModal').style.display = 'block';
}

function closeDewormingModal() {
    document.getElementById('dewormingModal').style.display = 'none';
    document.getElementById('dewormingForm').reset();
}

function submitDewormingForm(event) {
    event.preventDefault();

    const formData = new FormData(event.target);

    fetch('/dewormings/create', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            closeDewormingModal();
            location.reload();
        } else {
            alert('Chyba při vytváření záznamu: ' + (data.error || 'Neznámá chyba'));
        }
    })
    .catch(error => {
        alert('Chyba při komunikaci se serverem: ' + error.message);
        console.error('Error:', error);
    });
}

// Close modal when clicking outside
window.onclick = function(event) {
    const dewormingModal = document.getElementById('dewormingModal');
    const examinationModal = document.getElementById('examinationModal');

    if (event.target === dewormingModal) {
        closeDewormingModal();
    }
    if (event.target === examinationModal) {
        closeExaminationModal();
    }
}

// Examination detail modal functions
function openExaminationModal(group) {
    // Populate date
    const date = new Date(group.date);
    document.getElementById('examDate').textContent = date.toLocaleDateString('cs-CZ');

    // Populate institution
    document.getElementById('examInstitution').textContent = group.institution || '-';

    // Populate next check date
    if (group.next_check_date) {
        const nextDate = new Date(group.next_check_date);
        document.getElementById('examNextCheck').textContent = nextDate.toLocaleDateString('cs-CZ');
    } else {
        document.getElementById('examNextCheck').textContent = '-';
    }

    // Populate examinations table
    const tableBody = document.getElementById('examDetailsTable');
    tableBody.innerHTML = '';

    group.exams.forEach(exam => {
        const row = document.createElement('tr');

        // Sample type
        const sampleTypeCell = document.createElement('td');
        sampleTypeCell.textContent = exam.sample_type || '-';
        row.appendChild(sampleTypeCell);

        // Finding status
        const statusCell = document.createElement('td');
        if (exam.finding_status === 'positive') {
            statusCell.innerHTML = '<span class="badge badge-warning">Pozitivní</span>';
        } else {
            statusCell.innerHTML = '<span class="badge badge-success">Negativní</span>';
        }
        row.appendChild(statusCell);

        // Parasite found
        const parasiteCell = document.createElement('td');
        parasiteCell.textContent = exam.parasite_found || '-';
        row.appendChild(parasiteCell);

        // Intensity
        const intensityCell = document.createElement('td');
        intensityCell.textContent = exam.intensity || '-';
        row.appendChild(intensityCell);

        // Enclosure
        const enclosureCell = document.createElement('td');
        enclosureCell.textContent = exam.enclosure_name || '-';
        row.appendChild(enclosureCell);

        // Notes
        const notesCell = document.createElement('td');
        notesCell.textContent = exam.notes || '-';
        row.appendChild(notesCell);

        tableBody.appendChild(row);
    });

    // Show the modal
    document.getElementById('examinationModal').style.display = 'block';
}

function closeExaminationModal() {
    document.getElementById('examinationModal').style.display = 'none';
}
</script>
<?php endif; ?>