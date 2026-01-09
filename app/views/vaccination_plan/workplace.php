<div class="container">
    <div class="breadcrumb">
        <a href="/vaccination-plan">Vakcinační plán</a> /
        <span><?= htmlspecialchars($workplace['name']) ?></span>
    </div>

    <div class="page-header">
        <div>
            <h1><?= htmlspecialchars($workplace['name']) ?></h1>
            <p>Plánování a sledování vakcinací</p>
        </div>
        <div style="display: flex; gap: 10px;">
            <a href="/vaccination-plan/planning-grid/<?= $workplace['id'] ?>" class="btn btn-primary">
                📋 Plánovací mřížka
            </a>
            <a href="/vaccination-plan" class="btn btn-secondary">← Zpět</a>
        </div>
    </div>

    <!-- Statistics Cards -->
    <div class="stats-grid">
        <div class="stat-card planned">
            <div class="stat-icon">📅</div>
            <div class="stat-content">
                <div class="stat-value"><?= $stats['planned_count'] ?? 0 ?></div>
                <div class="stat-label">Naplánováno</div>
            </div>
        </div>
        <div class="stat-card overdue">
            <div class="stat-icon">⚠️</div>
            <div class="stat-content">
                <div class="stat-value"><?= $stats['overdue_count'] ?? 0 ?></div>
                <div class="stat-label">Po termínu</div>
            </div>
        </div>
        <div class="stat-card completed">
            <div class="stat-icon">✅</div>
            <div class="stat-content">
                <div class="stat-value"><?= $stats['completed_count'] ?? 0 ?></div>
                <div class="stat-label">Dokončeno</div>
            </div>
        </div>
    </div>

    <!-- Overdue Vaccinations -->
    <?php if (!empty($overduePlans)): ?>
        <div class="section overdue-section">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
                <h2 style="margin: 0;">⚠️ Vakcinace po termínu</h2>
                <?php if ($canEdit): ?>
                    <button onclick="batchCompleteOverdue()" class="btn btn-success" id="batchCompleteOverdueBtn" style="display: none;">
                        ✓ Hromadně dokončit vybrané
                    </button>
                <?php endif; ?>
            </div>
            <div class="plans-table-wrapper">
                <form id="overdueForm" method="POST" action="/vaccination-plan/batch-mark-completed">
                    <table class="plans-table">
                        <thead>
                            <tr>
                                <?php if ($canEdit): ?>
                                    <th style="width: 40px;">
                                        <input type="checkbox" id="selectAllOverdue" onchange="toggleAllOverdue()">
                                    </th>
                                <?php endif; ?>
                                <th>Zvíře</th>
                                <th>Druh</th>
                                <th>Vakcína</th>
                                <th>Plánovaný termín</th>
                                <th>Poznámky</th>
                                <?php if ($canEdit): ?>
                                    <th>Akce</th>
                                <?php endif; ?>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($overduePlans as $plan): ?>
                                <tr>
                                    <?php if ($canEdit): ?>
                                        <td>
                                            <input type="checkbox" class="plan-checkbox-overdue" name="plan_ids[]" value="<?= $plan['id'] ?>" onchange="updateOverdueBatchButton()">
                                        </td>
                                    <?php endif; ?>
                                    <td>
                                        <strong><?= htmlspecialchars($plan['animal_name']) ?></strong>
                                        <br>
                                        <small>ID: <?= htmlspecialchars($plan['animal_identifier']) ?></small>
                                    </td>
                                    <td><?= htmlspecialchars($plan['animal_species']) ?></td>
                                    <td><strong><?= htmlspecialchars($plan['vaccine_name']) ?></strong></td>
                                    <td>
                                        <span class="date-overdue"><?= date('d.m.Y', strtotime($plan['planned_date'])) ?></span>
                                        <br>
                                        <small class="overdue-label"><?= floor((strtotime('now') - strtotime($plan['planned_date'])) / 86400) ?> dní po termínu</small>
                                    </td>
                                    <td><?= htmlspecialchars($plan['notes'] ?: '-') ?></td>
                                    <?php if ($canEdit): ?>
                                        <td>
                                            <button type="button" onclick="markSingleCompleted(<?= $plan['id'] ?>)" class="btn btn-sm btn-success">Dokončit</button>
                                        </td>
                                    <?php endif; ?>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </form>
            </div>
        </div>
    <?php endif; ?>

    <!-- Upcoming Vaccinations -->
    <?php if (!empty($upcomingPlans)): ?>
        <div class="section">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
                <h2 style="margin: 0;">📅 Nadcházející vakcinace</h2>
                <?php if ($canEdit): ?>
                    <button onclick="batchCompleteUpcoming()" class="btn btn-success" id="batchCompleteUpcomingBtn" style="display: none;">
                        ✓ Hromadně dokončit vybrané
                    </button>
                <?php endif; ?>
            </div>
            <div class="plans-table-wrapper">
                <form id="upcomingForm" method="POST" action="/vaccination-plan/batch-mark-completed">
                    <table class="plans-table">
                        <thead>
                            <tr>
                                <?php if ($canEdit): ?>
                                    <th style="width: 40px;">
                                        <input type="checkbox" id="selectAllUpcoming" onchange="toggleAllUpcoming()">
                                    </th>
                                <?php endif; ?>
                                <th>Zvíře</th>
                                <th>Druh</th>
                                <th>Vakcína</th>
                                <th>Plánovaný termín</th>
                                <th>Poznámky</th>
                                <?php if ($canEdit): ?>
                                    <th>Akce</th>
                                <?php endif; ?>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($upcomingPlans as $plan): ?>
                                <tr>
                                    <?php if ($canEdit): ?>
                                        <td>
                                            <input type="checkbox" class="plan-checkbox-upcoming" name="plan_ids[]" value="<?= $plan['id'] ?>" onchange="updateUpcomingBatchButton()">
                                        </td>
                                    <?php endif; ?>
                                    <td>
                                        <strong><?= htmlspecialchars($plan['animal_name']) ?></strong>
                                        <br>
                                        <small>ID: <?= htmlspecialchars($plan['animal_identifier']) ?></small>
                                    </td>
                                    <td><?= htmlspecialchars($plan['animal_species']) ?></td>
                                    <td><strong><?= htmlspecialchars($plan['vaccine_name']) ?></strong></td>
                                    <td>
                                        <?= date('d.m.Y', strtotime($plan['planned_date'])) ?>
                                        <br>
                                        <small><?= floor((strtotime($plan['planned_date']) - strtotime('now')) / 86400) ?> dní</small>
                                    </td>
                                    <td><?= htmlspecialchars($plan['notes'] ?: '-') ?></td>
                                    <?php if ($canEdit): ?>
                                        <td>
                                            <button type="button" onclick="markSingleCompleted(<?= $plan['id'] ?>)" class="btn btn-sm btn-success">Dokončit</button>
                                        </td>
                                    <?php endif; ?>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </form>
            </div>
        </div>
    <?php endif; ?>

    <!-- Completed Vaccinations (Last 10) -->
    <?php if (!empty($completedPlans)): ?>
        <div class="section">
            <h2>✅ Nedávno dokončené vakcinace</h2>
            <div class="plans-table-wrapper">
                <table class="plans-table">
                    <thead>
                        <tr>
                            <th>Zvíře</th>
                            <th>Druh</th>
                            <th>Vakcína</th>
                            <th>Datum provedení</th>
                            <th>Provedl</th>
                            <th>Poznámky</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach (array_slice($completedPlans, 0, 10) as $plan): ?>
                            <tr>
                                <td>
                                    <strong><?= htmlspecialchars($plan['animal_name']) ?></strong>
                                    <br>
                                    <small>ID: <?= htmlspecialchars($plan['animal_identifier']) ?></small>
                                </td>
                                <td><?= htmlspecialchars($plan['animal_species']) ?></td>
                                <td><strong><?= htmlspecialchars($plan['vaccine_name']) ?></strong></td>
                                <td><?= $plan['administered_date'] ? date('d.m.Y', strtotime($plan['administered_date'])) : '-' ?></td>
                                <td><?= htmlspecialchars($plan['administered_by_name'] ?: '-') ?></td>
                                <td><?= htmlspecialchars($plan['notes'] ?: '-') ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    <?php endif; ?>

    <?php if (empty($overduePlans) && empty($upcomingPlans) && empty($completedPlans)): ?>
        <div class="alert alert-info">
            <strong>Žádné vakcinační plány</strong><br>
            V tomto pracovišti nejsou zatím žádné vakcinační plány.
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
    color: #16a085;
    text-decoration: none;
}

.breadcrumb a:hover {
    color: #138d75;
    text-decoration: underline;
}

.page-header {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
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

.stats-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 20px;
    margin-bottom: 40px;
}

.stat-card {
    background: white;
    border-radius: 12px;
    padding: 20px;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
    display: flex;
    align-items: center;
    gap: 15px;
}

.stat-card.planned {
    border-left: 4px solid #3498db;
}

.stat-card.overdue {
    border-left: 4px solid #e74c3c;
}

.stat-card.completed {
    border-left: 4px solid #27ae60;
}

.stat-icon {
    font-size: 36px;
}

.stat-value {
    font-size: 32px;
    font-weight: 700;
    color: #2c3e50;
}

.stat-label {
    color: #7f8c8d;
    font-size: 14px;
}

.section {
    margin-bottom: 40px;
}

.section h2 {
    margin: 0 0 20px 0;
    color: #2c3e50;
    font-size: 22px;
}

.overdue-section h2 {
    color: #e74c3c;
}

.plans-table-wrapper {
    background: white;
    border-radius: 8px;
    overflow: hidden;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
}

.plans-table {
    width: 100%;
    border-collapse: collapse;
}

.plans-table thead {
    background: linear-gradient(135deg, #16a085 0%, #138d75 100%);
    color: white;
}

.overdue-section .plans-table thead {
    background: linear-gradient(135deg, #e74c3c 0%, #c0392b 100%);
}

.plans-table th {
    padding: 15px;
    text-align: left;
    font-weight: 600;
    font-size: 14px;
}

.plans-table td {
    padding: 12px 15px;
    border-bottom: 1px solid #ecf0f1;
}

.plans-table tbody tr:hover {
    background: #f8f9fa;
}

.plans-table tbody tr:last-child td {
    border-bottom: none;
}

.date-overdue {
    color: #e74c3c;
    font-weight: 600;
}

.overdue-label {
    color: #e74c3c;
    font-style: italic;
}

.btn {
    padding: 8px 16px;
    border-radius: 4px;
    text-decoration: none;
    display: inline-block;
    font-weight: 600;
    border: none;
    cursor: pointer;
    transition: all 0.3s;
}

.btn-secondary {
    background-color: #95a5a6;
    color: white;
}

.btn-secondary:hover {
    background-color: #7f8c8d;
}

.btn-success {
    background: linear-gradient(135deg, #27ae60 0%, #229954 100%);
    color: white;
}

.btn-success:hover {
    background: linear-gradient(135deg, #229954 0%, #1e8449 100%);
}

.btn-sm {
    padding: 6px 12px;
    font-size: 13px;
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

.btn-primary {
    background: linear-gradient(135deg, #3498db 0%, #2980b9 100%);
    color: white;
}

.btn-primary:hover {
    background: linear-gradient(135deg, #2980b9 0%, #21618c 100%);
}

/* Modal styles */
.modal {
    display: none;
    position: fixed;
    z-index: 1000;
    left: 0;
    top: 0;
    width: 100%;
    height: 100%;
    background-color: rgba(0, 0, 0, 0.5);
}

.modal-content {
    background-color: white;
    margin: 10% auto;
    padding: 0;
    border-radius: 8px;
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.3);
    width: 90%;
    max-width: 500px;
}

.modal-header {
    padding: 20px;
    background: linear-gradient(135deg, #3498db 0%, #2980b9 100%);
    color: white;
    border-radius: 8px 8px 0 0;
}

.modal-header h3 {
    margin: 0;
}

.modal-body {
    padding: 20px;
}

.modal-footer {
    padding: 15px 20px;
    border-top: 1px solid #ecf0f1;
    display: flex;
    justify-content: flex-end;
    gap: 10px;
}

.form-group {
    margin-bottom: 15px;
}

.form-group label {
    display: block;
    margin-bottom: 6px;
    font-weight: 600;
    color: #2c3e50;
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

textarea.form-control {
    resize: vertical;
    min-height: 80px;
}
</style>

<!-- Completion Modal -->
<div id="completionModal" class="modal">
    <div class="modal-content">
        <div class="modal-header">
            <h3>Dokončit vakcinaci</h3>
        </div>
        <form id="completionForm" method="POST">
            <div class="modal-body">
                <div class="form-group">
                    <label for="administered_date">Datum provedení: *</label>
                    <input type="date" name="administered_date" id="administered_date" class="form-control" required value="<?= date('Y-m-d') ?>">
                </div>
                <div class="form-group">
                    <label for="completion_notes">Poznámky:</label>
                    <textarea name="completion_notes" id="completion_notes" class="form-control" placeholder="Nepovinné..."></textarea>
                </div>
                <div id="batchCountInfo" style="display: none; background: #e8f5e9; padding: 10px; border-radius: 4px; margin-top: 10px;">
                    <strong>Hromadné dokončení:</strong> <span id="batchCount">0</span> vakcinací
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" onclick="closeCompletionModal()" class="btn btn-secondary">Zrušit</button>
                <button type="submit" class="btn btn-success">Uložit</button>
            </div>
        </form>
    </div>
</div>

<script>
// Toggle all overdue checkboxes
function toggleAllOverdue() {
    const selectAll = document.getElementById('selectAllOverdue');
    const checkboxes = document.querySelectorAll('.plan-checkbox-overdue');
    checkboxes.forEach(cb => cb.checked = selectAll.checked);
    updateOverdueBatchButton();
}

// Toggle all upcoming checkboxes
function toggleAllUpcoming() {
    const selectAll = document.getElementById('selectAllUpcoming');
    const checkboxes = document.querySelectorAll('.plan-checkbox-upcoming');
    checkboxes.forEach(cb => cb.checked = selectAll.checked);
    updateUpcomingBatchButton();
}

// Update overdue batch button visibility
function updateOverdueBatchButton() {
    const checkboxes = document.querySelectorAll('.plan-checkbox-overdue:checked');
    const button = document.getElementById('batchCompleteOverdueBtn');
    button.style.display = checkboxes.length > 0 ? 'inline-block' : 'none';
}

// Update upcoming batch button visibility
function updateUpcomingBatchButton() {
    const checkboxes = document.querySelectorAll('.plan-checkbox-upcoming:checked');
    const button = document.getElementById('batchCompleteUpcomingBtn');
    button.style.display = checkboxes.length > 0 ? 'inline-block' : 'none';
}

// Mark single vaccination as completed
function markSingleCompleted(planId) {
    const form = document.getElementById('completionForm');
    form.action = '/vaccination-plan/mark-completed/' + planId;

    document.getElementById('batchCountInfo').style.display = 'none';
    document.getElementById('completionModal').style.display = 'block';
}

// Batch complete overdue vaccinations
function batchCompleteOverdue() {
    const checkboxes = document.querySelectorAll('.plan-checkbox-overdue:checked');
    if (checkboxes.length === 0) {
        alert('Vyberte alespoň jednu vakcinaci k dokončení.');
        return;
    }

    const form = document.getElementById('completionForm');
    form.action = '/vaccination-plan/batch-mark-completed';

    // Add hidden inputs for selected plan IDs
    // First remove any existing hidden inputs
    document.querySelectorAll('#completionForm input[name="plan_ids[]"]').forEach(input => input.remove());

    checkboxes.forEach(cb => {
        const input = document.createElement('input');
        input.type = 'hidden';
        input.name = 'plan_ids[]';
        input.value = cb.value;
        form.appendChild(input);
    });

    document.getElementById('batchCount').textContent = checkboxes.length;
    document.getElementById('batchCountInfo').style.display = 'block';
    document.getElementById('completionModal').style.display = 'block';
}

// Batch complete upcoming vaccinations
function batchCompleteUpcoming() {
    const checkboxes = document.querySelectorAll('.plan-checkbox-upcoming:checked');
    if (checkboxes.length === 0) {
        alert('Vyberte alespoň jednu vakcinaci k dokončení.');
        return;
    }

    const form = document.getElementById('completionForm');
    form.action = '/vaccination-plan/batch-mark-completed';

    // Add hidden inputs for selected plan IDs
    // First remove any existing hidden inputs
    document.querySelectorAll('#completionForm input[name="plan_ids[]"]').forEach(input => input.remove());

    checkboxes.forEach(cb => {
        const input = document.createElement('input');
        input.type = 'hidden';
        input.name = 'plan_ids[]';
        input.value = cb.value;
        form.appendChild(input);
    });

    document.getElementById('batchCount').textContent = checkboxes.length;
    document.getElementById('batchCountInfo').style.display = 'block';
    document.getElementById('completionModal').style.display = 'block';
}

// Close completion modal
function closeCompletionModal() {
    document.getElementById('completionModal').style.display = 'none';
    document.getElementById('completionForm').reset();
    // Remove hidden plan_ids inputs
    document.querySelectorAll('#completionForm input[name="plan_ids[]"]').forEach(input => input.remove());
}

// Close modal when clicking outside
window.onclick = function(event) {
    const modal = document.getElementById('completionModal');
    if (event.target === modal) {
        closeCompletionModal();
    }
}
</script>
