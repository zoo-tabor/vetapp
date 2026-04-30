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

    <!-- Protection Card -->
    <div class="info-card" id="protectionCard">
        <div class="card-header-row">
            <h2>Ochrana</h2>
            <?php if ($canEdit): ?>
                <button type="button" class="btn btn-sm btn-primary" onclick="toggleProtectionEdit()" id="protectionEditBtn">Upravit</button>
            <?php endif; ?>
        </div>

        <div id="protectionDisplay">
            <div class="info-grid">
                <div class="info-item">
                    <span class="label">Registrační číslo:</span>
                    <span class="value"><?= htmlspecialchars($animal['registration_number'] ?? '') ?: '—' ?></span>
                </div>
                <div class="info-item">
                    <span class="label">CITES kategorie:</span>
                    <span class="value"><?= htmlspecialchars($animal['cites_category'] ?? '') ?: '—' ?></span>
                </div>
                <div class="info-item">
                    <span class="label">Nařízení EU 338/97:</span>
                    <span class="value"><?= htmlspecialchars($animal['eu_regulation'] ?? '') ?: '—' ?></span>
                </div>
                <?php
                $protBoolFields = [
                    'law_critically_endangered' => 'Kriticky ohrožený (114/1992)',
                    'law_strongly_endangered'   => 'Silně ohrožený',
                    'law_endangered'            => 'Ohrožený',
                    'commercial_exception'      => 'Výjimka z obch. zákazu',
                    'requires_ku_registration'  => 'Evidence KÚ - nutná',
                    'ku_registration_done'      => 'Evidence KÚ - provedena',
                    'exception_required'        => 'Výjimka - nutná',
                    'deviation_required'        => 'Odchylka - nutná',
                ];
                foreach ($protBoolFields as $field => $label): ?>
                <div class="info-item">
                    <span class="label"><?= $label ?>:</span>
                    <span class="value">
                        <?= !empty($animal[$field])
                            ? '<span style="color:#27ae60;font-weight:600">✓ Ano</span>'
                            : '<span style="color:#bdc3c7">✗ Ne</span>' ?>
                    </span>
                </div>
                <?php endforeach; ?>
                <div class="info-item">
                    <span class="label">Výjimka - udělena:</span>
                    <span class="value"><?= htmlspecialchars($animal['exception_granted'] ?? '') ?: '—' ?></span>
                </div>
                <div class="info-item">
                    <span class="label">Odchylka - nastavena:</span>
                    <span class="value"><?= htmlspecialchars($animal['deviation_set'] ?? '') ?: '—' ?></span>
                </div>
            </div>
        </div>

        <?php if ($canEdit): ?>
        <div id="protectionForm" style="display:none;">
            <div class="info-grid" style="margin-bottom:20px;">
                <div class="info-item">
                    <label class="label" for="prot_registration_number">Registrační číslo:</label>
                    <input type="text" id="prot_registration_number" value="<?= htmlspecialchars($animal['registration_number'] ?? '') ?>" class="form-input" placeholder="Volitelné">
                </div>
                <div class="info-item">
                    <label class="label" for="prot_cites_category">CITES kategorie:</label>
                    <input type="text" id="prot_cites_category" value="<?= htmlspecialchars($animal['cites_category'] ?? '') ?>" class="form-input" placeholder="Např. A, B, C">
                </div>
                <div class="info-item">
                    <label class="label" for="prot_eu_regulation">Nařízení EU 338/97:</label>
                    <input type="text" id="prot_eu_regulation" value="<?= htmlspecialchars($animal['eu_regulation'] ?? '') ?>" class="form-input" placeholder="Např. I, II, III">
                </div>
                <?php foreach ($protBoolFields as $field => $label): ?>
                <div class="info-item" style="flex-direction:row;align-items:center;gap:10px;padding-top:6px;">
                    <input type="checkbox" id="prot_<?= $field ?>" <?= !empty($animal[$field]) ? 'checked' : '' ?> style="width:18px;height:18px;cursor:pointer;flex-shrink:0;">
                    <label class="label" for="prot_<?= $field ?>" style="cursor:pointer;margin:0;"><?= $label ?></label>
                </div>
                <?php endforeach; ?>
                <div class="info-item">
                    <label class="label" for="prot_exception_granted">Výjimka - udělena:</label>
                    <select id="prot_exception_granted" class="form-input">
                        <option value="">—</option>
                        <option value="UDĚLENA" <?= ($animal['exception_granted'] ?? '') === 'UDĚLENA' ? 'selected' : '' ?>>UDĚLENA</option>
                        <option value="NAHRAZENA" <?= ($animal['exception_granted'] ?? '') === 'NAHRAZENA' ? 'selected' : '' ?>>NAHRAZENA</option>
                    </select>
                </div>
                <div class="info-item">
                    <label class="label" for="prot_deviation_set">Odchylka - nastavena:</label>
                    <select id="prot_deviation_set" class="form-input">
                        <option value="">—</option>
                        <option value="UDĚLENA" <?= ($animal['deviation_set'] ?? '') === 'UDĚLENA' ? 'selected' : '' ?>>UDĚLENA</option>
                        <option value="NAHRAZENA" <?= ($animal['deviation_set'] ?? '') === 'NAHRAZENA' ? 'selected' : '' ?>>NAHRAZENA</option>
                    </select>
                </div>
            </div>
            <div style="display:flex;gap:10px;">
                <button type="button" class="btn btn-primary" onclick="saveProtection()" id="protectionSaveBtn">Uložit</button>
                <button type="button" class="btn btn-secondary" onclick="toggleProtectionEdit()">Zrušit</button>
            </div>
            <div id="protectionError" style="color:#c0392b;font-size:0.85em;margin-top:8px;display:none;"></div>
        </div>
        <?php endif; ?>
    </div>

    <!-- Notes Card -->
    <div class="info-card" id="notesCard">
        <div class="card-header-row">
            <h2>Poznámky</h2>
            <?php if ($canEdit): ?>
                <button type="button" class="btn btn-sm btn-primary" onclick="toggleNotesEdit()" id="notesEditBtn">Upravit</button>
            <?php endif; ?>
        </div>
        <div id="notesDisplay">
            <?php if ($animal['notes']): ?>
                <p id="notesText" style="white-space:pre-wrap;color:#2c3e50;line-height:1.6;margin:0;"><?= htmlspecialchars($animal['notes']) ?></p>
            <?php else: ?>
                <p id="notesText" style="color:#95a5a6;font-style:italic;margin:0;">Žádné poznámky</p>
            <?php endif; ?>
        </div>
        <?php if ($canEdit): ?>
        <div id="notesForm" style="display:none;">
            <textarea id="notesInput" rows="5" class="form-input" style="width:100%;resize:vertical;" placeholder="Poznámky k zvířeti..."><?= htmlspecialchars($animal['notes'] ?? '') ?></textarea>
            <div style="display:flex;gap:10px;margin-top:10px;">
                <button type="button" class="btn btn-primary" onclick="saveNotes()" id="notesSaveBtn">Uložit</button>
                <button type="button" class="btn btn-secondary" onclick="toggleNotesEdit()">Zrušit</button>
            </div>
            <div id="notesError" style="color:#c0392b;font-size:0.85em;margin-top:8px;display:none;"></div>
        </div>
        <?php endif; ?>
    </div>

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
            <a href="/vaccination-plan/workplace/<?= $animal['workplace_id'] ?>" class="section-link vaccination">
                <div class="link-icon">💉</div>
                <h3>Vakcinační plán</h3>
                <p>Plánované a provedené vakcinace</p>
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

        <!-- Vaccination Plan Preview -->
        <div class="preview-card">
            <div class="preview-header">
                <h3>💉 Vakcinační plán</h3>
                <a href="/vaccination-plan/workplace/<?= $animal['workplace_id'] ?>" class="view-all">
                    Zobrazit vše →
                </a>
            </div>
            <?php if (empty($vaccinationUpcoming) && empty($vaccinationCompleted)): ?>
                <p class="no-data">Zatím žádné vakcinace</p>
            <?php else: ?>
                <div class="preview-list">
                    <?php foreach ($vaccinationUpcoming as $vacc): ?>
                        <div class="preview-item">
                            <div class="preview-date" style="color: <?= $vacc['status'] === 'overdue' ? '#e74c3c' : '#2c3e50' ?>;">
                                <?= date('d.m.Y', strtotime($vacc['planned_date'])) ?>
                            </div>
                            <div class="preview-content">
                                <strong><?= htmlspecialchars($vacc['vaccine_name']) ?></strong>
                                <br><small style="color: <?= $vacc['status'] === 'overdue' ? '#e74c3c' : '#27ae60' ?>;">
                                    <?= $vacc['status'] === 'overdue' ? '⚠️ Po termínu' : '📅 Plánováno' ?>
                                </small>
                            </div>
                        </div>
                    <?php endforeach; ?>
                    <?php foreach ($vaccinationCompleted as $vacc): ?>
                        <div class="preview-item">
                            <div class="preview-date">
                                <?= date('d.m.Y', strtotime($vacc['administered_date'])) ?>
                            </div>
                            <div class="preview-content">
                                <strong><?= htmlspecialchars($vacc['vaccine_name']) ?></strong>
                                <br><small style="color: #27ae60;">✅ Provedeno<?= $vacc['administered_by_name'] ? ' – ' . htmlspecialchars($vacc['administered_by_name']) : '' ?></small>
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

.form-input {
    padding: 8px 10px;
    border: 1px solid #ddd;
    border-radius: 6px;
    font-size: 14px;
    width: 100%;
    box-sizing: border-box;
    background: #fff;
}

.form-input:focus {
    outline: none;
    border-color: #8e44ad;
    box-shadow: 0 0 0 2px rgba(142, 68, 173, 0.15);
}

#protectionCard {
    border-top: 4px solid #16a085;
}

#notesCard {
    border-top: 4px solid #f39c12;
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

.section-link.vaccination {
    border-top: 4px solid #27ae60;
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
function toggleProtectionEdit() {
    const display = document.getElementById('protectionDisplay');
    const form    = document.getElementById('protectionForm');
    const btn     = document.getElementById('protectionEditBtn');
    const editing = form.style.display !== 'none';
    display.style.display = editing ? '' : 'none';
    form.style.display    = editing ? 'none' : '';
    btn.textContent = editing ? 'Upravit' : '✕ Zrušit';
}

function saveProtection() {
    const errEl   = document.getElementById('protectionError');
    const saveBtn = document.getElementById('protectionSaveBtn');
    errEl.style.display = 'none';
    saveBtn.disabled = true;
    saveBtn.textContent = 'Ukládám…';

    const data = {
        registration_number:       document.getElementById('prot_registration_number').value.trim(),
        cites_category:            document.getElementById('prot_cites_category').value.trim(),
        eu_regulation:             document.getElementById('prot_eu_regulation').value.trim(),
        law_critically_endangered: document.getElementById('prot_law_critically_endangered').checked,
        law_strongly_endangered:   document.getElementById('prot_law_strongly_endangered').checked,
        law_endangered:            document.getElementById('prot_law_endangered').checked,
        commercial_exception:      document.getElementById('prot_commercial_exception').checked,
        requires_ku_registration:  document.getElementById('prot_requires_ku_registration').checked,
        ku_registration_done:      document.getElementById('prot_ku_registration_done').checked,
        exception_required:        document.getElementById('prot_exception_required').checked,
        exception_granted:         document.getElementById('prot_exception_granted').value,
        deviation_required:        document.getElementById('prot_deviation_required').checked,
        deviation_set:             document.getElementById('prot_deviation_set').value,
    };

    fetch('/animals/<?= $animal['id'] ?>/protection', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(data)
    })
    .then(r => r.json())
    .then(result => {
        if (result.success) {
            location.reload();
        } else {
            errEl.textContent = result.error || 'Neznámá chyba';
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

function toggleNotesEdit() {
    const display = document.getElementById('notesDisplay');
    const form    = document.getElementById('notesForm');
    const btn     = document.getElementById('notesEditBtn');
    const editing = form.style.display !== 'none';
    display.style.display = editing ? '' : 'none';
    form.style.display    = editing ? 'none' : '';
    btn.textContent = editing ? 'Upravit' : '✕ Zrušit';
    if (!editing) document.getElementById('notesInput').focus();
}

function saveNotes() {
    const errEl   = document.getElementById('notesError');
    const saveBtn = document.getElementById('notesSaveBtn');
    errEl.style.display = 'none';
    saveBtn.disabled = true;
    saveBtn.textContent = 'Ukládám…';

    const notes = document.getElementById('notesInput').value.trim();

    fetch('/animals/<?= $animal['id'] ?>/notes', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ notes })
    })
    .then(r => r.json())
    .then(result => {
        if (result.success) {
            const el = document.getElementById('notesText');
            if (notes) {
                el.textContent  = notes;
                el.style.color  = '#2c3e50';
                el.style.fontStyle = '';
            } else {
                el.textContent  = 'Žádné poznámky';
                el.style.color  = '#95a5a6';
                el.style.fontStyle = 'italic';
            }
            toggleNotesEdit();
        } else {
            errEl.textContent = result.error || 'Neznámá chyba';
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
