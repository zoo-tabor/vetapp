<div class="container-wide">
    <div class="page-header">
        <div>
            <h1>Vakcinační plán <?= htmlspecialchars($year) ?> - <?= htmlspecialchars($workplace['name']) ?></h1>
            <p class="text-muted">Plán vakcinací pro rok <?= htmlspecialchars($year) ?></p>
        </div>
        <div style="display: flex; gap: 10px; align-items: center;">
            <!-- Year selector -->
            <select id="yearSelector" onchange="window.location.href='/vaccination-plan/planning-grid/<?= $workplace['id'] ?>?year=' + this.value" class="form-control" style="width: 120px;">
                <?php for ($y = date('Y') - 2; $y <= date('Y') + 3; $y++): ?>
                    <option value="<?= $y ?>" <?= $y == $year ? 'selected' : '' ?>><?= $y ?></option>
                <?php endfor; ?>
            </select>

            <a href="/vaccination-plan/workplace/<?= $workplace['id'] ?>" class="btn btn-secondary">
                ← Zpět na přehled
            </a>

            <button onclick="window.print()" class="btn btn-outline">
                🖨️ Tisknout
            </button>
        </div>
    </div>

    <!-- Legend -->
    <div class="vaccination-legend" style="margin-bottom: 20px; padding: 15px; background: #f8f9fa; border-radius: 8px;">
        <h3 style="margin: 0 0 10px 0; font-size: 16px;">Legenda vakcín:</h3>
        <div style="display: flex; flex-wrap: wrap; gap: 15px;">
            <?php foreach ($vaccineColors as $vc): ?>
                <div style="display: flex; align-items: center; gap: 8px;">
                    <div style="width: 30px; height: 30px; background-color: <?= htmlspecialchars($vc['color_hex']) ?>; border-radius: 4px; border: 1px solid #ddd;"></div>
                    <span><strong><?= htmlspecialchars($vc['abbreviation']) ?></strong> - <?= htmlspecialchars($vc['vaccine_type']) ?></span>
                </div>
            <?php endforeach; ?>
        </div>
    </div>

    <!-- Status Legend -->
    <div class="status-legend" style="margin-bottom: 20px; padding: 15px; background: #f8f9fa; border-radius: 8px;">
        <h3 style="margin: 0 0 10px 0; font-size: 16px;">Legenda stavů:</h3>
        <div style="display: flex; flex-wrap: wrap; gap: 15px;">
            <div><span style="color: #000; font-weight: 600;">●</span> Naplánováno</div>
            <div><span style="color: #dc3545; font-weight: 600;">●</span> Po termínu</div>
            <div><span style="color: #28a745; font-weight: 600;">●</span> Dokončeno</div>
        </div>
    </div>

    <!-- Planning Grid Table -->
    <div class="planning-grid-wrapper" style="overflow-x: auto;">
        <table class="planning-grid-table">
            <thead>
                <tr>
                    <th rowspan="2" style="min-width: 150px; vertical-align: middle;">Kategorie / Zvíře</th>
                    <th colspan="12" style="text-align: center;">Měsíce</th>
                    <th rowspan="2" style="min-width: 100px; vertical-align: middle;">Akce</th>
                </tr>
                <tr>
                    <?php
                    $months = ['I', 'II', 'III', 'IV', 'V', 'VI', 'VII', 'VIII', 'IX', 'X', 'XI', 'XII'];
                    foreach ($months as $month):
                    ?>
                        <th style="min-width: 80px;"><?= $month ?></th>
                    <?php endforeach; ?>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($animalsByCategory)): ?>
                    <tr>
                        <td colspan="14" style="text-align: center; padding: 40px;">
                            Žádná zvířata nenalezena pro tento pracovní prostor.
                        </td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($animalsByCategory as $category => $animals): ?>
                        <!-- Category Header Row -->
                        <tr class="category-header">
                            <td colspan="14" style="background: #2c3e50; color: white; font-weight: bold; padding: 10px;">
                                <?= htmlspecialchars($category) ?>
                            </td>
                        </tr>

                        <!-- Animal Rows -->
                        <?php foreach ($animals as $animal): ?>
                            <tr class="animal-row">
                                <td class="animal-name" style="font-weight: 600; padding: 12px;">
                                    <?= htmlspecialchars($animal['animal_name']) ?>
                                    <?php if ($animal['animal_identifier']): ?>
                                        <span style="color: #666; font-size: 12px;">(<?= htmlspecialchars($animal['animal_identifier']) ?>)</span>
                                    <?php endif; ?>
                                </td>

                                <!-- Month Cells -->
                                <?php for ($month = 1; $month <= 12; $month++): ?>
                                    <td class="month-cell" data-animal-id="<?= $animal['animal_id'] ?>" data-month="<?= $month ?>">
                                        <?php
                                        // Find vaccinations for this month
                                        $monthVaccinations = array_filter($animal['vaccinations'], function($v) use ($month) {
                                            if (!empty($v['planned_date'])) {
                                                return (int)date('n', strtotime($v['planned_date'])) === $month;
                                            }
                                            return (int)$v['month_planned'] === $month;
                                        });
                                        ?>

                                        <?php if (!empty($monthVaccinations)): ?>
                                            <div class="vaccination-cell-content">
                                                <?php foreach ($monthVaccinations as $vac): ?>
                                                    <?php
                                                    // Determine status color
                                                    $statusColor = '#000'; // planned
                                                    if ($vac['status'] === 'overdue') {
                                                        $statusColor = '#dc3545';
                                                    } elseif ($vac['status'] === 'completed') {
                                                        $statusColor = '#28a745';
                                                    }

                                                    // Get vaccine color
                                                    $bgColor = $vac['vaccine_color'] ?? '#e0e0e0';
                                                    $abbr = $vac['vaccine_abbr'] ?? substr($vac['vaccine_name'], 0, 3);
                                                    ?>
                                                    <div class="vaccination-badge"
                                                         style="background-color: <?= htmlspecialchars($bgColor) ?>;
                                                                color: <?= htmlspecialchars($statusColor) ?>;
                                                                padding: 4px 8px;
                                                                border-radius: 4px;
                                                                margin: 2px;
                                                                font-size: 11px;
                                                                font-weight: 600;
                                                                cursor: pointer;
                                                                display: inline-block;"
                                                         onclick="viewVaccination(<?= $vac['id'] ?>)"
                                                         title="<?= htmlspecialchars($vac['vaccine_name']) ?> - <?= htmlspecialchars($vac['status']) ?>">
                                                        <?= htmlspecialchars($abbr) ?>
                                                    </div>
                                                <?php endforeach; ?>
                                            </div>
                                        <?php endif; ?>

                                        <?php if ($canEdit): ?>
                                            <button class="add-vaccination-btn"
                                                    onclick="openAddVaccinationModal(<?= $animal['animal_id'] ?>, <?= $month ?>, '<?= htmlspecialchars($animal['animal_name']) ?>')"
                                                    style="width: 100%; padding: 4px; border: 1px dashed #ccc; background: transparent; cursor: pointer; font-size: 16px; color: #666;"
                                                    title="Přidat vakcinaci">
                                                +
                                            </button>
                                        <?php endif; ?>
                                    </td>
                                <?php endfor; ?>

                                <!-- Actions -->
                                <td style="text-align: center;">
                                    <a href="/animals/detail/<?= $animal['animal_id'] ?>"
                                       class="btn btn-sm btn-outline"
                                       title="Zobrazit detail zvířete">
                                        👁️
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Add Vaccination Modal -->
<div id="addVaccinationModal" class="modal" style="display: none;">
    <div class="modal-content" style="max-width: 600px;">
        <div class="modal-header">
            <h2>Přidat vakcinaci</h2>
            <button onclick="closeAddVaccinationModal()" class="close-btn">&times;</button>
        </div>
        <form method="POST" action="/vaccination-plan/save" id="addVaccinationForm">
            <input type="hidden" name="animal_id" id="modal_animal_id">
            <input type="hidden" name="month_planned" id="modal_month_planned">
            <input type="hidden" name="animal_category" id="modal_animal_category">

            <div class="form-group">
                <label><strong>Zvíře:</strong> <span id="modal_animal_name"></span></label>
            </div>

            <div class="form-group">
                <label><strong>Měsíc:</strong> <span id="modal_month_display"></span></label>
            </div>

            <div class="form-group">
                <label for="vaccine_id">Vakcína ze skladu:</label>
                <select name="vaccine_id" id="vaccine_id" class="form-control" onchange="updateVaccineName()">
                    <option value="">-- Vyberte vakcínu --</option>
                    <?php foreach ($vaccines as $vaccine): ?>
                        <option value="<?= $vaccine['id'] ?>" data-name="<?= htmlspecialchars($vaccine['name']) ?>">
                            <?= htmlspecialchars($vaccine['name']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="form-group">
                <label for="vaccine_name">Název vakcíny: *</label>
                <input type="text" name="vaccine_name" id="vaccine_name" class="form-control" required>
                <small class="text-muted">Pokud vybíráte ze skladu, název se doplní automaticky. Můžete jej upravit.</small>
            </div>

            <div class="form-group">
                <label for="planned_date">Plánované datum: *</label>
                <input type="date" name="planned_date" id="planned_date" class="form-control" required>
            </div>

            <div class="form-group">
                <label for="vaccination_interval_days">Interval vakcinace (dny):</label>
                <select name="vaccination_interval_days" id="vaccination_interval_days" class="form-control">
                    <option value="">-- Nevyplněno --</option>
                    <option value="365">Roční (365 dní)</option>
                    <option value="730">Dvouleté (730 dní)</option>
                    <option value="1095">Tříleté (1095 dní)</option>
                    <option value="custom">Vlastní</option>
                </select>
                <input type="number" name="vaccination_interval_days_custom" id="vaccination_interval_days_custom"
                       class="form-control" placeholder="Vlastní interval (dny)" style="margin-top: 8px; display: none;">
            </div>

            <div class="form-group">
                <label>
                    <input type="checkbox" name="requires_booster" id="requires_booster" onchange="toggleBoosterDays()">
                    Vyžaduje přeočkování (booster)
                </label>
            </div>

            <div class="form-group" id="booster_days_group" style="display: none;">
                <label for="booster_days">Dny do boosteru:</label>
                <input type="number" name="booster_days" id="booster_days" class="form-control" value="14" min="1">
                <small class="text-muted">Typicky 14 dní po primární vakcinaci</small>
            </div>

            <div class="form-group">
                <label for="notes">Poznámky:</label>
                <textarea name="notes" id="notes" class="form-control" rows="3"></textarea>
            </div>

            <div class="form-actions">
                <button type="button" onclick="closeAddVaccinationModal()" class="btn btn-secondary">Zrušit</button>
                <button type="submit" class="btn btn-primary">Uložit vakcinaci</button>
            </div>
        </form>
    </div>
</div>

<style>
.planning-grid-table {
    width: 100%;
    border-collapse: collapse;
    background: white;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
}

.planning-grid-table th {
    background: linear-gradient(135deg, #3498db 0%, #2980b9 100%);
    color: white;
    padding: 12px;
    text-align: center;
    font-weight: 600;
    border: 1px solid #2980b9;
}

.planning-grid-table td {
    border: 1px solid #ddd;
    padding: 8px;
    vertical-align: top;
    min-height: 50px;
}

.category-header td {
    background: #2c3e50 !important;
    color: white !important;
}

.animal-row:hover {
    background: #f8f9fa;
}

.animal-name {
    position: sticky;
    left: 0;
    background: white;
    z-index: 10;
}

.month-cell {
    position: relative;
    min-width: 80px;
    text-align: center;
}

.add-vaccination-btn:hover {
    background: #f0f0f0 !important;
    border-color: #999 !important;
}

.vaccination-cell-content {
    display: flex;
    flex-wrap: wrap;
    gap: 4px;
    justify-content: center;
    margin-bottom: 4px;
}

/* Modal Styles */
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
    padding: 0;
    max-height: 90vh;
    overflow-y: auto;
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.3);
}

.modal-header {
    padding: 20px;
    border-bottom: 1px solid #ddd;
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.modal-header h2 {
    margin: 0;
    font-size: 20px;
}

.close-btn {
    background: none;
    border: none;
    font-size: 28px;
    cursor: pointer;
    color: #666;
    padding: 0;
    width: 30px;
    height: 30px;
}

.close-btn:hover {
    color: #000;
}

.modal form {
    padding: 20px;
}

.form-group {
    margin-bottom: 16px;
}

.form-group label {
    display: block;
    margin-bottom: 6px;
    font-weight: 600;
}

.form-control {
    width: 100%;
    padding: 10px;
    border: 1px solid #ddd;
    border-radius: 4px;
    font-size: 14px;
}

.form-control:focus {
    outline: none;
    border-color: #3498db;
    box-shadow: 0 0 0 3px rgba(52, 152, 219, 0.1);
}

.form-actions {
    display: flex;
    gap: 10px;
    justify-content: flex-end;
    padding-top: 16px;
    border-top: 1px solid #ddd;
}

.text-muted {
    color: #666;
    font-size: 12px;
}

/* Print Styles */
@media print {
    .page-header button,
    .page-header select,
    .page-header a,
    .add-vaccination-btn,
    .vaccination-legend,
    .status-legend {
        display: none !important;
    }

    .planning-grid-table {
        page-break-inside: avoid;
    }

    .category-header {
        page-break-after: avoid;
    }
}

/* Mobile Responsive */
@media (max-width: 768px) {
    .planning-grid-wrapper {
        overflow-x: scroll;
    }

    .planning-grid-table {
        min-width: 900px;
    }

    .page-header {
        flex-direction: column;
        gap: 15px;
    }

    .page-header > div {
        width: 100%;
    }
}
</style>

<script>
// Update vaccine name when selecting from warehouse
function updateVaccineName() {
    const select = document.getElementById('vaccine_id');
    const selectedOption = select.options[select.selectedIndex];
    const vaccineName = selectedOption.getAttribute('data-name');

    if (vaccineName) {
        document.getElementById('vaccine_name').value = vaccineName;
    }
}

// Toggle booster days input
function toggleBoosterDays() {
    const checkbox = document.getElementById('requires_booster');
    const group = document.getElementById('booster_days_group');

    if (checkbox.checked) {
        group.style.display = 'block';
    } else {
        group.style.display = 'none';
    }
}

// Handle custom interval input
document.addEventListener('DOMContentLoaded', function() {
    const intervalSelect = document.getElementById('vaccination_interval_days');
    const customInput = document.getElementById('vaccination_interval_days_custom');

    if (intervalSelect) {
        intervalSelect.addEventListener('change', function() {
            if (this.value === 'custom') {
                customInput.style.display = 'block';
                customInput.required = true;
            } else {
                customInput.style.display = 'none';
                customInput.required = false;
            }
        });
    }

    // Handle form submission for custom interval
    const form = document.getElementById('addVaccinationForm');
    if (form) {
        form.addEventListener('submit', function(e) {
            const intervalSelect = document.getElementById('vaccination_interval_days');
            const customInput = document.getElementById('vaccination_interval_days_custom');

            if (intervalSelect.value === 'custom' && customInput.value) {
                // Create hidden input with custom value
                const hiddenInput = document.createElement('input');
                hiddenInput.type = 'hidden';
                hiddenInput.name = 'vaccination_interval_days';
                hiddenInput.value = customInput.value;
                form.appendChild(hiddenInput);

                // Remove the select to avoid conflict
                intervalSelect.name = '';
            }
        });
    }
});

// Open add vaccination modal
function openAddVaccinationModal(animalId, month, animalName) {
    const modal = document.getElementById('addVaccinationModal');
    const monthNames = ['Leden', 'Únor', 'Březen', 'Duben', 'Květen', 'Červen',
                       'Červenec', 'Srpen', 'Září', 'Říjen', 'Listopad', 'Prosinec'];

    // Set form values
    document.getElementById('modal_animal_id').value = animalId;
    document.getElementById('modal_month_planned').value = month;
    document.getElementById('modal_animal_name').textContent = animalName;
    document.getElementById('modal_month_display').textContent = monthNames[month - 1];

    // Set default date to first day of selected month
    const year = <?= $year ?>;
    const defaultDate = new Date(year, month - 1, 1);
    const dateStr = defaultDate.toISOString().split('T')[0];
    document.getElementById('planned_date').value = dateStr;

    // Show modal
    modal.style.display = 'flex';
}

// Close add vaccination modal
function closeAddVaccinationModal() {
    const modal = document.getElementById('addVaccinationModal');
    modal.style.display = 'none';

    // Reset form
    document.getElementById('addVaccinationForm').reset();
    document.getElementById('booster_days_group').style.display = 'none';
    document.getElementById('vaccination_interval_days_custom').style.display = 'none';
}

// View vaccination details
function viewVaccination(vaccinationId) {
    // TODO: Implement vaccination detail view modal
    alert('Zobrazení detailu vakcinace #' + vaccinationId + ' bude implementováno.');
}

// Close modal when clicking outside
window.onclick = function(event) {
    const modal = document.getElementById('addVaccinationModal');
    if (event.target === modal) {
        closeAddVaccinationModal();
    }
}
</script>
