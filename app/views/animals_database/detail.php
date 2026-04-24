<div class="container">
    <div class="page-header">
        <div>
            <div class="breadcrumb">
                <a href="/animals">Seznam zvířat</a> /
                <a href="/animals/workplace/<?= $animal['workplace_id'] ?>"><?= htmlspecialchars($animal['workplace_name'] ?? 'Pracoviště') ?></a> /
                <?= htmlspecialchars($animal['name'] ?: $animal['species']) ?>
            </div>
            <h1><?= htmlspecialchars($animal['name'] ?: $animal['species']) ?></h1>
            <p class="animal-id">ID: <?= htmlspecialchars($animal['identifier']) ?></p>
        </div>
        <div class="header-actions">
            <a href="/animals/workplace/<?= $animal['workplace_id'] ?>" class="btn btn-secondary">
                ← Zpět
            </a>
        </div>
    </div>

    <!-- Basic Information Card -->
    <div class="info-card">
        <div class="card-header-row">
            <h2>Základní informace</h2>
            <?php if ($canEdit): ?>
                <a href="/workplace/<?= $animal['workplace_id'] ?>/animals/<?= $animal['id'] ?>?from=animals" class="btn btn-sm btn-primary">
                    Upravit
                </a>
            <?php endif; ?>
        </div>
        <div class="info-grid">
            <div class="info-item">
                <span class="label">Druh:</span>
                <span class="value"><?= htmlspecialchars($animal['species']) ?></span>
            </div>
            <div class="info-item">
                <span class="label">Plemeno:</span>
                <span class="value"><?= htmlspecialchars($animal['breed'] ?: '-') ?></span>
            </div>
            <div class="info-item">
                <span class="label">Hmotnost:</span>
                <span class="value"><?= $animal['weight'] !== null ? htmlspecialchars($animal['weight']) . ' kg' : '-' ?></span>
            </div>
            <div class="info-item">
                <span class="label">Datum narození:</span>
                <span class="value">
                    <?= $animal['birth_date'] ? date('d.m.Y', strtotime($animal['birth_date'])) : '-' ?>
                </span>
            </div>
            <div class="info-item">
                <span class="label">Pohlaví:</span>
                <span class="value">
                    <?php
                    $genderLabels = [
                        'male' => '♂ Samec',
                        'female' => '♀ Samice',
                        'unknown' => '? Neznámé'
                    ];
                    echo $genderLabels[$animal['gender']] ?? '-';
                    ?>
                </span>
            </div>
            <div class="info-item">
                <span class="label">Status:</span>
                <span class="value">
                    <?php
                    $statusLabels = [
                        'active' => '✓ Aktivní',
                        'transferred' => '→ Přeloženo',
                        'deceased' => '† Uhynulo',
                        'removed' => '✗ Odstraněno'
                    ];
                    echo $statusLabels[$animal['current_status']] ?? $animal['current_status'];
                    ?>
                </span>
            </div>
            <?php if ($animal['notes']): ?>
                <div class="info-item full-width">
                    <span class="label">Poznámky:</span>
                    <span class="value"><?= nl2br(htmlspecialchars($animal['notes'])) ?></span>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Weight History Card -->
    <?php if ($canEdit || !empty($weightHistory)): ?>
    <div class="info-card" id="weightCard">
        <div class="card-header-row">
            <h2>Hmotnost</h2>
            <?php if ($canEdit): ?>
                <button type="button" class="btn btn-sm btn-primary" onclick="toggleWeightForm()" id="weightToggleBtn">
                    + Přidat měření
                </button>
            <?php endif; ?>
        </div>

        <!-- Current weight -->
        <div style="margin-bottom: 16px;">
            <span style="font-size: 1.4em; font-weight: 700; color: #2c3e50;" id="currentWeightDisplay">
                <?= $animal['weight'] !== null ? htmlspecialchars($animal['weight']) . ' kg' : '—' ?>
            </span>
            <?php if (!empty($weightHistory)): ?>
                <span style="color: #888; font-size: 0.9em; margin-left: 8px;">
                    (poslední měření: <?= date('d.m.Y', strtotime($weightHistory[0]['measured_date'])) ?>)
                </span>
            <?php endif; ?>
        </div>

        <!-- Add measurement form -->
        <?php if ($canEdit): ?>
        <div id="weightForm" style="display: none; background: #f8f9fa; border-radius: 8px; padding: 16px; margin-bottom: 20px;">
            <div style="display: grid; grid-template-columns: 1fr 1fr 2fr auto; gap: 12px; align-items: end;">
                <div>
                    <label style="display: block; font-size: 0.85em; color: #666; margin-bottom: 4px;">Hmotnost (kg) *</label>
                    <input type="number" id="weightInput" step="0.01" min="0.01" placeholder="0.00" class="btn" style="width: 100%; padding: 8px; border: 1px solid #ddd; border-radius: 6px;">
                </div>
                <div>
                    <label style="display: block; font-size: 0.85em; color: #666; margin-bottom: 4px;">Datum *</label>
                    <input type="date" id="weightDate" value="<?= date('Y-m-d') ?>" class="btn" style="width: 100%; padding: 8px; border: 1px solid #ddd; border-radius: 6px;">
                </div>
                <div>
                    <label style="display: block; font-size: 0.85em; color: #666; margin-bottom: 4px;">Poznámka</label>
                    <input type="text" id="weightNotes" placeholder="Volitelná poznámka" class="btn" style="width: 100%; padding: 8px; border: 1px solid #ddd; border-radius: 6px;">
                </div>
                <div>
                    <button type="button" class="btn btn-primary" onclick="saveWeight()" id="weightSaveBtn">
                        Uložit
                    </button>
                </div>
            </div>
            <div id="weightError" style="color: #c0392b; font-size: 0.85em; margin-top: 8px; display: none;"></div>
        </div>
        <?php endif; ?>

        <!-- History table -->
        <?php if (!empty($weightHistory)): ?>
        <table style="width: 100%; border-collapse: collapse; font-size: 0.9em;" id="weightHistoryTable">
            <thead>
                <tr style="border-bottom: 2px solid #e0e0e0;">
                    <th style="text-align: left; padding: 8px 12px; color: #666; font-weight: 600;">Datum</th>
                    <th style="text-align: right; padding: 8px 12px; color: #666; font-weight: 600;">Hmotnost</th>
                    <th style="text-align: left; padding: 8px 12px; color: #666; font-weight: 600;">Poznámka</th>
                    <th style="text-align: left; padding: 8px 12px; color: #666; font-weight: 600;">Zaznamenal</th>
                </tr>
            </thead>
            <tbody id="weightHistoryBody">
                <?php foreach ($weightHistory as $i => $entry): ?>
                <tr style="border-bottom: 1px solid #f0f0f0; <?= $i === 0 ? 'font-weight: 600;' : '' ?>">
                    <td style="padding: 8px 12px;"><?= date('d.m.Y', strtotime($entry['measured_date'])) ?></td>
                    <td style="padding: 8px 12px; text-align: right;"><?= htmlspecialchars($entry['weight']) ?> kg</td>
                    <td style="padding: 8px 12px; color: #555;"><?= htmlspecialchars($entry['notes'] ?? '—') ?></td>
                    <td style="padding: 8px 12px; color: #888;"><?= htmlspecialchars($entry['created_by_name'] ?? '—') ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        <?php else: ?>
            <p style="color: #888; margin: 0;">Zatím žádná měření.</p>
        <?php endif; ?>
    </div>
    <?php endif; ?>

    <!-- Quick Links to Other Sections -->
    <div class="section-links">
        <h2>Přejít na data v jiných sekcích</h2>
        <div class="links-grid">
            <a href="/workplace/<?= $animal['workplace_id'] ?>/animals/<?= $animal['id'] ?>" class="section-link parasitology">
                <div class="link-icon">🦠</div>
                <h3>Parazitologie</h3>
                <p>Kompletní záznamy vyšetření</p>
            </a>
            <a href="/biochemistry/animal/<?= $animal['id'] ?>" class="section-link biochemistry">
                <div class="link-icon">🧪</div>
                <h3>Biochemie a hematologie</h3>
                <p>Výsledky testů a grafy</p>
            </a>
            <a href="/urineanalysis/animal/<?= $animal['id'] ?>" class="section-link urine">
                <div class="link-icon">🧫</div>
                <h3>Analýza moči</h3>
                <p>Výsledky rozborů moči</p>
            </a>
        </div>
    </div>

    <!-- Preview Data from Other Sections -->
    <div class="preview-sections">
        <!-- Parasitology Preview -->
        <div class="preview-card">
            <div class="preview-header">
                <h3>🦠 Poslední vyšetření (Parazitologie)</h3>
                <a href="/workplace/<?= $animal['workplace_id'] ?>/animals/<?= $animal['id'] ?>" class="view-all">
                    Zobrazit vše →
                </a>
            </div>
            <?php if (empty($examinations)): ?>
                <p class="no-data">Zatím žádná vyšetření</p>
            <?php else: ?>
                <div class="preview-list">
                    <?php foreach (array_slice($examinations, 0, 3) as $exam): ?>
                        <div class="preview-item">
                            <div class="preview-date">
                                <?= date('d.m.Y', strtotime($exam['examination_date'])) ?>
                            </div>
                            <div class="preview-content">
                                <strong><?= htmlspecialchars($exam['sample_type']) ?></strong>
                                - Nález:
                                <span class="finding-<?= $exam['finding_status'] ?>">
                                    <?= $exam['finding_status'] === 'positive' ? 'Pozitivní' : 'Negativní' ?>
                                </span>
                                <?php if ($exam['parasite_found']): ?>
                                    <br><small><?= htmlspecialchars($exam['parasite_found']) ?></small>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>

        <!-- Biochemistry Preview -->
        <div class="preview-card">
            <div class="preview-header">
                <h3>🧪 Poslední testy (Biochemie)</h3>
                <a href="/biochemistry/animal/<?= $animal['id'] ?>" class="view-all">
                    Zobrazit vše →
                </a>
            </div>
            <?php if (empty($biochemistryTests)): ?>
                <p class="no-data">Zatím žádné testy</p>
            <?php else: ?>
                <div class="preview-list">
                    <?php foreach (array_slice($biochemistryTests, 0, 3) as $test): ?>
                        <div class="preview-item">
                            <div class="preview-date">
                                <?= date('d.m.Y', strtotime($test['test_date'])) ?>
                            </div>
                            <div class="preview-content">
                                <strong><?= htmlspecialchars($test['test_location']) ?></strong>
                                <br><small><?= $test['result_count'] ?> parametrů měřeno</small>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>

        <!-- Hematology Preview -->
        <div class="preview-card">
            <div class="preview-header">
                <h3>🩸 Poslední testy (Hematologie)</h3>
                <a href="/biochemistry/animal/<?= $animal['id'] ?>" class="view-all">
                    Zobrazit vše →
                </a>
            </div>
            <?php if (empty($hematologyTests)): ?>
                <p class="no-data">Zatím žádné testy</p>
            <?php else: ?>
                <div class="preview-list">
                    <?php foreach (array_slice($hematologyTests, 0, 3) as $test): ?>
                        <div class="preview-item">
                            <div class="preview-date">
                                <?= date('d.m.Y', strtotime($test['test_date'])) ?>
                            </div>
                            <div class="preview-content">
                                <strong><?= htmlspecialchars($test['test_location']) ?></strong>
                                <br><small><?= $test['result_count'] ?> parametrů měřeno</small>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>

        <!-- Urine Analysis Preview -->
        <div class="preview-card">
            <div class="preview-header">
                <h3>🧫 Poslední testy (Analýza moči)</h3>
                <a href="/urineanalysis/animal/<?= $animal['id'] ?>" class="view-all">
                    Zobrazit vše →
                </a>
            </div>
            <?php if (empty($urineTests)): ?>
                <p class="no-data">Zatím žádné testy</p>
            <?php else: ?>
                <div class="preview-list">
                    <?php foreach (array_slice($urineTests, 0, 3) as $test): ?>
                        <div class="preview-item">
                            <div class="preview-date">
                                <?= date('d.m.Y', strtotime($test['test_date'])) ?>
                            </div>
                            <div class="preview-content">
                                <strong><?= htmlspecialchars($test['test_location']) ?></strong>
                                <br><small><?= $test['result_count'] ?> parametrů měřeno</small>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
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
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    margin-bottom: 30px;
}

.breadcrumb {
    color: #7f8c8d;
    font-size: 14px;
    margin-bottom: 10px;
}

.breadcrumb a {
    color: #8e44ad;
    text-decoration: none;
}

.breadcrumb a:hover {
    text-decoration: underline;
}

.page-header h1 {
    margin: 0 0 5px 0;
    color: #2c3e50;
}

.animal-id {
    margin: 0;
    color: #7f8c8d;
    font-family: 'Courier New', monospace;
    font-size: 14px;
}

.header-actions {
    display: flex;
    gap: 10px;
}

.info-card {
    background: white;
    border-radius: 12px;
    padding: 30px;
    margin-bottom: 30px;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
}

.card-header-row {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 20px;
}

.info-card h2 {
    margin: 0;
    color: #2c3e50;
    font-size: 22px;
}

.info-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
    gap: 20px;
}

.info-item {
    display: flex;
    flex-direction: column;
    gap: 5px;
}

.info-item.full-width {
    grid-column: 1 / -1;
}

.info-item .label {
    color: #7f8c8d;
    font-size: 14px;
    font-weight: 600;
}

.info-item .value {
    color: #2c3e50;
    font-size: 16px;
}

.section-links {
    margin-bottom: 40px;
}

.section-links h2 {
    margin: 0 0 20px 0;
    color: #2c3e50;
}

.links-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
    gap: 20px;
}

.section-link {
    background: white;
    border-radius: 12px;
    padding: 25px;
    text-decoration: none;
    color: inherit;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
    transition: all 0.3s ease;
    text-align: center;
}

.section-link:hover {
    transform: translateY(-4px);
    box-shadow: 0 4px 16px rgba(0, 0, 0, 0.2);
}

.section-link.parasitology {
    border-top: 4px solid #667eea;
}

.section-link.biochemistry {
    border-top: 4px solid #c0392b;
}

.section-link.urine {
    border-top: 4px solid #f39c12;
}

.link-icon {
    font-size: 48px;
    margin-bottom: 15px;
}

.section-link h3 {
    margin: 0 0 8px 0;
    color: #2c3e50;
    font-size: 18px;
}

.section-link p {
    margin: 0;
    color: #7f8c8d;
    font-size: 14px;
}

.preview-sections {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
    gap: 20px;
}

.preview-card {
    background: white;
    border-radius: 12px;
    padding: 20px;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
}

.preview-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 15px;
    padding-bottom: 10px;
    border-bottom: 2px solid #ecf0f1;
}

.preview-header h3 {
    margin: 0;
    color: #2c3e50;
    font-size: 16px;
}

.view-all {
    color: #8e44ad;
    text-decoration: none;
    font-size: 14px;
    font-weight: 600;
}

.view-all:hover {
    color: #7d3c98;
}

.preview-list {
    display: flex;
    flex-direction: column;
    gap: 15px;
}

.preview-item {
    display: flex;
    gap: 15px;
    padding: 12px;
    background: #f8f9fa;
    border-radius: 8px;
}

.preview-date {
    flex-shrink: 0;
    font-weight: 600;
    color: #8e44ad;
}

.preview-content {
    flex: 1;
    color: #2c3e50;
    font-size: 14px;
}

.finding-positive {
    color: #e74c3c;
    font-weight: 600;
}

.finding-negative {
    color: #27ae60;
    font-weight: 600;
}

.no-data {
    color: #7f8c8d;
    font-style: italic;
    text-align: center;
    padding: 20px;
}

.btn {
    padding: 10px 20px;
    border-radius: 6px;
    text-decoration: none;
    display: inline-block;
    font-weight: 600;
    border: none;
    cursor: pointer;
    transition: all 0.3s;
}

.btn-primary {
    background-color: #8e44ad;
    color: white;
}

.btn-primary:hover {
    background-color: #7d3c98;
}

.btn-secondary {
    background-color: #95a5a6;
    color: white;
}

.btn-secondary:hover {
    background-color: #7f8c8d;
}

.btn-sm {
    padding: 8px 16px;
    font-size: 14px;
}

@media (max-width: 768px) {
    .page-header {
        flex-direction: column;
        gap: 15px;
    }

    .header-actions {
        width: 100%;
        flex-direction: column;
    }

    .links-grid,
    .preview-sections {
        grid-template-columns: 1fr;
    }
}
</style>

<script>
function toggleWeightForm() {
    const form = document.getElementById('weightForm');
    const btn  = document.getElementById('weightToggleBtn');
    const visible = form.style.display !== 'none';
    form.style.display = visible ? 'none' : '';
    btn.textContent = visible ? '+ Přidat měření' : '✕ Zrušit';
    if (!visible) document.getElementById('weightInput').focus();
}

function saveWeight() {
    const weight = parseFloat(document.getElementById('weightInput').value);
    const date   = document.getElementById('weightDate').value;
    const notes  = document.getElementById('weightNotes').value.trim();
    const errEl  = document.getElementById('weightError');
    const saveBtn = document.getElementById('weightSaveBtn');

    errEl.style.display = 'none';

    if (!weight || weight <= 0) {
        errEl.textContent = 'Zadejte platnou hmotnost.';
        errEl.style.display = '';
        return;
    }
    if (!date) {
        errEl.textContent = 'Zadejte datum měření.';
        errEl.style.display = '';
        return;
    }

    saveBtn.disabled = true;
    saveBtn.textContent = 'Ukládám…';

    fetch('/animals/<?= $animal['id'] ?>/weight', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ weight, measured_date: date, notes })
    })
    .then(r => r.json())
    .then(data => {
        if (data.success) {
            const e = data.entry;
            const formattedDate = new Date(e.measured_date + 'T00:00:00').toLocaleDateString('cs-CZ', {day:'2-digit',month:'2-digit',year:'numeric'});

            document.getElementById('currentWeightDisplay').textContent = e.weight + ' kg';

            const tbody = document.getElementById('weightHistoryBody');
            if (tbody) {
                const prevFirst = tbody.querySelector('tr');
                if (prevFirst) prevFirst.style.fontWeight = '';
                const tr = document.createElement('tr');
                tr.style.cssText = 'border-bottom: 1px solid #f0f0f0; font-weight: 600;';
                tr.innerHTML = `<td style="padding:8px 12px">${formattedDate}</td>
                    <td style="padding:8px 12px;text-align:right">${e.weight} kg</td>
                    <td style="padding:8px 12px;color:#555">${e.notes || '—'}</td>
                    <td style="padding:8px 12px;color:#888">${e.created_by_name || '—'}</td>`;
                tbody.insertBefore(tr, tbody.firstChild);
            } else {
                location.reload();
                return;
            }

            document.getElementById('weightInput').value = '';
            document.getElementById('weightNotes').value = '';
            toggleWeightForm();
        } else {
            errEl.textContent = data.error || 'Neznámá chyba';
            errEl.style.display = '';
        }
    })
    .catch(err => {
        errEl.textContent = 'Chyba komunikace: ' + err.message;
        errEl.style.display = '';
    })
    .finally(() => {
        saveBtn.disabled = false;
        saveBtn.textContent = 'Uložit';
    });
}
</script>
