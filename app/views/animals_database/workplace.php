<div class="container">
    <div class="page-header">
        <div>
            <h1><?= htmlspecialchars($workplace['name']) ?></h1>
            <p>Seznam zvířat a výběhů na pracovišti</p>
        </div>
        <div class="header-actions">
            <?php if ($canEdit): ?>
                <a href="/workplace/<?= $workplace['id'] ?>/animals/create" class="btn btn-primary" id="addAnimalBtn">
                    + Přidat zvíře
                </a>
                <button class="btn btn-primary" id="addEnclosureBtn" onclick="showAddEnclosureModal()" style="display: none;">
                    + Přidat výběh
                </button>
            <?php endif; ?>
            <a href="/animals" class="btn btn-secondary">← Zpět</a>
        </div>
    </div>

    <!-- Tabs for Animals, My Animals, and Enclosures -->
    <div class="tabs-container">
        <div class="tabs">
            <button class="tab active" onclick="switchTab('animals')">🦁 Zvířata</button>
            <button class="tab" onclick="switchTab('myanimals')">👤 Moje zvířata</button>
            <button class="tab" onclick="switchTab('enclosures')">🏠 Výběhy</button>
        </div>
        <div class="view-switcher">
            <button class="view-btn active" onclick="switchView('grid')" title="Zobrazit mřížku">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="currentColor">
                    <rect x="3" y="3" width="7" height="7" rx="1"/>
                    <rect x="14" y="3" width="7" height="7" rx="1"/>
                    <rect x="3" y="14" width="7" height="7" rx="1"/>
                    <rect x="14" y="14" width="7" height="7" rx="1"/>
                </svg>
            </button>
            <button class="view-btn" onclick="switchView('list')" title="Zobrazit seznam">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="currentColor">
                    <rect x="3" y="4" width="18" height="3" rx="1"/>
                    <rect x="3" y="10.5" width="18" height="3" rx="1"/>
                    <rect x="3" y="17" width="18" height="3" rx="1"/>
                </svg>
            </button>
        </div>
    </div>

    <!-- Animals Tab Content -->
    <div id="animals-content" class="tab-content active">
        <?php if (empty($animalsBySpecies)): ?>
            <div class="alert alert-info">
                <strong>Žádná zvířata</strong><br>
                Na tomto pracovišti nejsou zatím žádná aktivní zvířata.
            </div>
        <?php else: ?>
            <!-- Search bar -->
            <div class="search-bar">
                <input type="text" id="searchAnimalsInput" class="form-control" placeholder="Vyhledat zvíře...">
            </div>

            <!-- Animals grouped by species -->
            <?php foreach ($animalsBySpecies as $species => $animals): ?>
                <div class="species-group">
                    <div class="species-header">
                        <h2><?= htmlspecialchars($species) ?></h2>
                        <span class="species-count"><?= count($animals) ?> zvířat</span>
                    </div>

                    <div class="animals-grid">
                        <?php foreach ($animals as $animal): ?>
                            <a href="/animals/detail/<?= $animal['id'] ?>" class="animal-card"
                               data-search="<?= strtolower(htmlspecialchars($animal['name'] . ' ' . $animal['identifier'] . ' ' . $animal['species'])) ?>">
                                <div class="animal-card-header">
                                    <h3 class="animal-name">
                                        <?= htmlspecialchars($animal['name'] ?: 'Bez jména') ?>
                                    </h3>
                                    <span class="animal-id">
                                        <?= htmlspecialchars($animal['identifier'] ?: '-') ?>
                                    </span>
                                </div>
                                <div class="animal-card-body">
                                    <div class="animal-info-row">
                                        <span class="label">Druh:</span>
                                        <span class="value"><?= htmlspecialchars($animal['species']) ?></span>
                                    </div>
                                    <?php if ($animal['birth_date']): ?>
                                        <div class="animal-info-row">
                                            <span class="label">Datum narození:</span>
                                            <span class="value"><?= date('d.m.Y', strtotime($animal['birth_date'])) ?></span>
                                        </div>
                                    <?php endif; ?>
                                    <div class="animal-info-row">
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
                                    <?php if ($animal['notes']): ?>
                                        <div class="animal-info-row animal-notes">
                                            <span class="label">Poznámky:</span>
                                            <span class="value"><?= htmlspecialchars($animal['notes']) ?></span>
                                        </div>
                                    <?php endif; ?>
                                </div>
                                <div class="animal-card-footer">
                                    <span class="view-detail">Zobrazit detail →</span>
                                </div>
                            </a>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php endforeach; ?>

            <div class="no-results" id="noAnimalsResults" style="display: none;">
                <p>Nenalezena žádná zvířata odpovídající vyhledávání.</p>
            </div>
        <?php endif; ?>

        <!-- Deceased Animals Section -->
        <?php if (!empty($deceasedAnimalsBySpecies)): ?>
            <div class="deceased-section">
                <div class="deceased-section-header">
                    <h2>† Uhynulá zvířata</h2>
                </div>

                <?php foreach ($deceasedAnimalsBySpecies as $species => $animals): ?>
                    <div class="species-group deceased">
                        <div class="species-header deceased">
                            <h2><?= htmlspecialchars($species) ?></h2>
                            <span class="species-count deceased"><?= count($animals) ?> zvířat</span>
                        </div>

                        <div class="animals-grid">
                            <?php foreach ($animals as $animal): ?>
                                <a href="/animals/detail/<?= $animal['id'] ?>" class="animal-card deceased"
                                   data-search="<?= strtolower(htmlspecialchars($animal['name'] . ' ' . $animal['identifier'] . ' ' . $animal['species'])) ?>">
                                    <div class="animal-card-header">
                                        <h3 class="animal-name">
                                            <?= htmlspecialchars($animal['name'] ?: 'Bez jména') ?>
                                        </h3>
                                        <span class="animal-id">
                                            <?= htmlspecialchars($animal['identifier'] ?: '-') ?>
                                        </span>
                                    </div>
                                    <div class="animal-card-body">
                                        <div class="animal-info-row">
                                            <span class="label">Druh:</span>
                                            <span class="value"><?= htmlspecialchars($animal['species']) ?></span>
                                        </div>
                                        <?php if ($animal['birth_date']): ?>
                                            <div class="animal-info-row">
                                                <span class="label">Datum narození:</span>
                                                <span class="value"><?= date('d.m.Y', strtotime($animal['birth_date'])) ?></span>
                                            </div>
                                        <?php endif; ?>
                                        <?php if (!empty($animal['death_date'])): ?>
                                            <div class="animal-info-row">
                                                <span class="label">Datum úmrtí:</span>
                                                <span class="value"><?= date('d.m.Y', strtotime($animal['death_date'])) ?></span>
                                            </div>
                                        <?php endif; ?>
                                        <div class="animal-info-row">
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
                                        <?php if ($animal['notes']): ?>
                                            <div class="animal-info-row animal-notes">
                                                <span class="label">Poznámky:</span>
                                                <span class="value"><?= htmlspecialchars($animal['notes']) ?></span>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                    <div class="animal-card-footer">
                                        <span class="view-detail">Zobrazit detail →</span>
                                    </div>
                                </a>
                            <?php endforeach; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

        <!-- Transferred / Removed Animals Section -->
        <?php if (!empty($otherAnimalsBySpecies)): ?>
            <div class="other-section">
                <div class="other-section-header">
                    <h2>Přesunutá a vyřazená zvířata</h2>
                </div>

                <?php foreach ($otherAnimalsBySpecies as $species => $animals): ?>
                    <div class="species-group other">
                        <div class="species-header other">
                            <h2><?= htmlspecialchars($species) ?></h2>
                            <span class="species-count other"><?= count($animals) ?> zvířat</span>
                        </div>

                        <div class="animals-grid">
                            <?php foreach ($animals as $animal): ?>
                                <?php $isTransferred = $animal['current_status'] === 'transferred'; ?>
                                <a href="/animals/detail/<?= $animal['id'] ?>" class="animal-card other"
                                   data-search="<?= strtolower(htmlspecialchars($animal['name'] . ' ' . $animal['identifier'] . ' ' . $animal['species'] . ' ' . ($animal['transfer_location'] ?? ''))) ?>">
                                    <div class="animal-card-header">
                                        <h3 class="animal-name">
                                            <?= htmlspecialchars($animal['name'] ?: 'Bez jména') ?>
                                        </h3>
                                        <span class="animal-id">
                                            <?= htmlspecialchars($animal['identifier'] ?: '-') ?>
                                        </span>
                                    </div>
                                    <div class="animal-card-body">
                                        <div class="animal-info-row">
                                            <span class="label">Stav:</span>
                                            <span class="value">
                                                <?php if ($isTransferred): ?>
                                                    → Přesunuto<?= !empty($animal['transfer_location']) ? ' (' . htmlspecialchars($animal['transfer_location']) . ')' : '' ?>
                                                <?php else: ?>
                                                    ✗ Vyřazeno
                                                <?php endif; ?>
                                            </span>
                                        </div>
                                        <div class="animal-info-row">
                                            <span class="label">Druh:</span>
                                            <span class="value"><?= htmlspecialchars($animal['species']) ?></span>
                                        </div>
                                        <?php if ($animal['birth_date']): ?>
                                            <div class="animal-info-row">
                                                <span class="label">Datum narození:</span>
                                                <span class="value"><?= date('d.m.Y', strtotime($animal['birth_date'])) ?></span>
                                            </div>
                                        <?php endif; ?>
                                        <?php if (!empty($animal['death_date'])): ?>
                                            <div class="animal-info-row">
                                                <span class="label">Datum úmrtí:</span>
                                                <span class="value"><?= date('d.m.Y', strtotime($animal['death_date'])) ?></span>
                                            </div>
                                        <?php endif; ?>
                                        <div class="animal-info-row">
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
                                        <?php if ($animal['notes']): ?>
                                            <div class="animal-info-row animal-notes">
                                                <span class="label">Poznámky:</span>
                                                <span class="value"><?= htmlspecialchars($animal['notes']) ?></span>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                    <div class="animal-card-footer">
                                        <span class="view-detail">Zobrazit detail →</span>
                                    </div>
                                </a>
                            <?php endforeach; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>

    <!-- My Animals Tab Content -->
    <div id="myanimals-content" class="tab-content">
        <?php if (empty($myAnimalsBySpecies)): ?>
            <div class="alert alert-info">
                <strong>Žádná přiřazená zvířata</strong><br>
                Nemáte přiřazena žádná zvířata k péči.
            </div>
        <?php else: ?>
            <!-- Search bar -->
            <div class="search-bar">
                <input type="text" id="searchMyAnimalsInput" class="form-control" placeholder="Vyhledat zvíře...">
            </div>

            <!-- My Animals grouped by species -->
            <?php foreach ($myAnimalsBySpecies as $species => $animals): ?>
                <div class="species-group my-animals-group">
                    <div class="species-header">
                        <h2><?= htmlspecialchars($species) ?></h2>
                        <span class="species-count"><?= count($animals) ?> zvířat</span>
                    </div>

                    <div class="animals-grid">
                        <?php foreach ($animals as $animal): ?>
                            <a href="/animals/detail/<?= $animal['id'] ?>" class="animal-card"
                               data-search="<?= strtolower(htmlspecialchars($animal['name'] . ' ' . $animal['identifier'] . ' ' . $animal['species'])) ?>">
                                <div class="animal-card-header">
                                    <h3 class="animal-name">
                                        <?= htmlspecialchars($animal['name'] ?: 'Bez jména') ?>
                                    </h3>
                                    <span class="animal-id">
                                        <?= htmlspecialchars($animal['identifier'] ?: '-') ?>
                                    </span>
                                </div>
                                <div class="animal-card-body">
                                    <div class="animal-info-row">
                                        <span class="label">Druh:</span>
                                        <span class="value"><?= htmlspecialchars($animal['species']) ?></span>
                                    </div>
                                    <?php if ($animal['birth_date']): ?>
                                        <div class="animal-info-row">
                                            <span class="label">Datum narození:</span>
                                            <span class="value"><?= date('d.m.Y', strtotime($animal['birth_date'])) ?></span>
                                        </div>
                                    <?php endif; ?>
                                    <div class="animal-info-row">
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
                                    <?php if ($animal['notes']): ?>
                                        <div class="animal-info-row animal-notes">
                                            <span class="label">Poznámky:</span>
                                            <span class="value"><?= htmlspecialchars($animal['notes']) ?></span>
                                        </div>
                                    <?php endif; ?>
                                </div>
                                <div class="animal-card-footer">
                                    <span class="view-detail">Zobrazit detail →</span>
                                </div>
                            </a>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php endforeach; ?>

            <div class="no-results" id="noMyAnimalsResults" style="display: none;">
                <p>Nenalezena žádná zvířata odpovídající vyhledávání.</p>
            </div>
        <?php endif; ?>
    </div>

    <!-- Enclosures Tab Content -->
    <div id="enclosures-content" class="tab-content">
        <?php if (empty($enclosures)): ?>
            <div class="alert alert-info">
                <strong>Žádné výběhy</strong><br>
                Na tomto pracovišti nejsou zatím žádné aktivní výběhy.
            </div>
        <?php else: ?>
            <!-- Search bar -->
            <div class="search-bar">
                <input type="text" id="searchEnclosuresInput" class="form-control" placeholder="Vyhledat výběh...">
            </div>

            <!-- Enclosures grid -->
            <div class="enclosures-grid">
                <?php foreach ($enclosures as $enclosure): ?>
                    <div class="enclosure-card"
                         data-search="<?= strtolower(htmlspecialchars($enclosure['name'] . ' ' . ($enclosure['code'] ?? ''))) ?>">
                        <div class="enclosure-card-header">
                            <h3 class="enclosure-name">
                                <?= htmlspecialchars($enclosure['name']) ?>
                            </h3>
                            <?php if ($enclosure['code']): ?>
                                <span class="enclosure-code">
                                    Kód: <?= htmlspecialchars($enclosure['code']) ?>
                                </span>
                            <?php endif; ?>
                        </div>
                        <div class="enclosure-card-body">
                            <div class="enclosure-info-row">
                                <span class="label">Typ vzorkování:</span>
                                <span class="value">
                                    <?= $enclosure['sample_type'] === 'individual' ? 'Individuální' : 'Smíšený' ?>
                                </span>
                            </div>
                            <?php if ($enclosure['notes']): ?>
                                <div class="enclosure-info-row">
                                    <span class="label">Poznámky:</span>
                                    <span class="value"><?= htmlspecialchars($enclosure['notes']) ?></span>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>

            <div class="no-results" id="noEnclosuresResults" style="display: none;">
                <p>Nenalezeny žádné výběhy odpovídající vyhledávání.</p>
            </div>
        <?php endif; ?>
    </div>
</div>

<!-- Enclosure Modal -->
<div id="enclosureModal" class="modal">
    <div class="modal-content">
        <div class="modal-header">
            <h2>Přidat výběh</h2>
            <span class="modal-close" onclick="closeAddEnclosureModal()">&times;</span>
        </div>
        <form id="enclosureForm">
            <div class="form-group">
                <label for="enclosure_name">Název výběhu: *</label>
                <input type="text" id="enclosure_name" name="name" class="form-control" required>
            </div>

            <div class="form-group">
                <label for="enclosure_code">Kód:</label>
                <input type="text" id="enclosure_code" name="code" class="form-control">
            </div>

            <div class="form-group">
                <label for="enclosure_sample_type">Typ vzorkování: *</label>
                <select id="enclosure_sample_type" name="sample_type" class="form-control" required>
                    <option value="individual">Individuální</option>
                    <option value="mixed">Smíšený</option>
                </select>
            </div>

            <div class="form-group">
                <label for="enclosure_notes">Poznámky:</label>
                <textarea id="enclosure_notes" name="notes" class="form-control" rows="3"></textarea>
            </div>

            <div class="form-actions">
                <button type="submit" class="btn btn-primary">Vytvořit výběh</button>
                <button type="button" class="btn btn-outline" onclick="closeAddEnclosureModal()">Zrušit</button>
            </div>
        </form>
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

.page-header h1 {
    margin: 0 0 10px 0;
    color: #2c3e50;
}

.page-header p {
    margin: 0;
    color: #7f8c8d;
    font-size: 16px;
}

.header-actions {
    display: flex;
    gap: 10px;
}

/* Tabs Container and View Switcher */
.tabs-container {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 30px;
    border-bottom: 2px solid #ecf0f1;
}

/* Tabs */
.tabs {
    display: flex;
    gap: 10px;
}

.tab {
    background: none;
    border: none;
    padding: 12px 24px;
    font-size: 16px;
    font-weight: 600;
    color: #7f8c8d;
    cursor: pointer;
    border-bottom: 3px solid transparent;
    transition: all 0.3s;
    margin-bottom: -2px;
}

.tab:hover {
    color: #8e44ad;
}

.tab.active {
    color: #8e44ad;
    border-bottom-color: #8e44ad;
}

/* View Switcher */
.view-switcher {
    display: flex;
    gap: 8px;
    padding: 0 10px 10px 10px;
}

.view-btn {
    background: none;
    border: 2px solid #ddd;
    border-radius: 6px;
    width: 36px;
    height: 36px;
    display: flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    transition: all 0.3s;
    color: #7f8c8d;
}

.view-btn:hover {
    border-color: #8e44ad;
    color: #8e44ad;
}

.view-btn.active {
    background: #8e44ad;
    border-color: #8e44ad;
    color: white;
}

.tab-content {
    display: none;
}

.tab-content.active {
    display: block;
}

.search-bar {
    margin-bottom: 30px;
}

.form-control {
    width: 100%;
    max-width: 500px;
    padding: 12px 16px;
    border: 2px solid #ddd;
    border-radius: 8px;
    font-size: 16px;
    transition: border-color 0.3s;
}

.form-control:focus {
    outline: none;
    border-color: #8e44ad;
}

/* Animals Section */
.species-group {
    margin-bottom: 40px;
}

.species-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 20px;
    padding-bottom: 10px;
    border-bottom: 3px solid #8e44ad;
}

.species-header h2 {
    margin: 0;
    color: #2c3e50;
    font-size: 24px;
}

.species-count {
    background: #8e44ad;
    color: white;
    padding: 6px 14px;
    border-radius: 20px;
    font-size: 14px;
    font-weight: 600;
}

/* Deceased Animals Section */
.deceased-section {
    margin-top: 60px;
    padding-top: 40px;
    border-top: 3px solid #95a5a6;
}

.deceased-section-header {
    margin-bottom: 30px;
}

.deceased-section-header h2 {
    color: #7f8c8d;
    font-size: 28px;
    font-weight: 700;
}

.species-header.deceased {
    border-bottom: 3px solid #95a5a6;
}

.species-header.deceased h2 {
    color: #7f8c8d;
}

.species-count.deceased {
    background: #95a5a6;
}

.animal-card.deceased {
    border-left: 4px solid #95a5a6;
    opacity: 0.85;
}

.animal-card.deceased:hover {
    box-shadow: 0 4px 16px rgba(149, 165, 166, 0.3);
}

/* Transferred / Removed Animals Section */
.other-section {
    margin-top: 60px;
    padding-top: 40px;
    border-top: 3px solid #d4ac0d;
}

.other-section-header {
    margin-bottom: 30px;
}

.other-section-header h2 {
    color: #b7950b;
    font-size: 28px;
    font-weight: 700;
}

.species-header.other {
    border-bottom: 3px solid #d4ac0d;
}

.species-header.other h2 {
    color: #b7950b;
}

.species-count.other {
    background: #d4ac0d;
}

.animal-card.other {
    border-left: 4px solid #d4ac0d;
    opacity: 0.92;
}

.animal-card.other:hover {
    box-shadow: 0 4px 16px rgba(212, 172, 13, 0.3);
}

.animals-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
    gap: 20px;
}

/* List View for Animals */
.animals-grid.list-view {
    display: flex;
    flex-direction: column;
    gap: 12px;
}

.animals-grid.list-view .animal-card {
    display: grid;
    grid-template-columns: 1fr 2fr 1fr auto;
    align-items: center;
    gap: 20px;
    padding: 16px 20px;
    border-radius: 8px;
}

.animals-grid.list-view .animal-card:hover {
    transform: translateY(0);
}

.animals-grid.list-view .animal-card-header {
    margin-bottom: 0;
}

.animals-grid.list-view .animal-name {
    font-size: 18px;
    margin-bottom: 2px;
}

.animals-grid.list-view .animal-id {
    font-size: 13px;
}

.animals-grid.list-view .animal-card-body {
    display: grid;
    grid-template-columns: repeat(2, 1fr);
    gap: 10px 20px;
}

.animals-grid.list-view .animal-info-row {
    border-bottom: none;
    padding: 0;
    display: flex;
    flex-direction: column;
    align-items: flex-start;
}

.animals-grid.list-view .animal-info-row .label {
    font-size: 12px;
    margin-bottom: 2px;
}

.animals-grid.list-view .animal-info-row .value {
    font-size: 14px;
}

.animals-grid.list-view .animal-card-footer {
    margin-top: 0;
    padding-top: 0;
    border-top: none;
    text-align: center;
}

.animals-grid.list-view .view-detail {
    font-size: 13px;
}

.animal-card {
    background: white;
    border-radius: 12px;
    padding: 20px;
    text-decoration: none;
    color: inherit;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
    transition: all 0.3s ease;
    border-left: 4px solid #8e44ad;
    display: flex;
    flex-direction: column;
}

.animal-card:hover {
    transform: translateY(-4px);
    box-shadow: 0 4px 16px rgba(142, 68, 173, 0.3);
}

.animal-card-header {
    margin-bottom: 15px;
}

.animal-name {
    margin: 0 0 5px 0;
    color: #2c3e50;
    font-size: 20px;
    font-weight: 700;
}

.animal-id {
    font-family: 'Courier New', monospace;
    color: #7f8c8d;
    font-size: 14px;
}

.animal-card-body {
    flex: 1;
}

.animal-info-row {
    display: flex;
    justify-content: space-between;
    padding: 8px 0;
    border-bottom: 1px solid #ecf0f1;
}

.animal-info-row:last-child {
    border-bottom: none;
}

.animal-info-row .label {
    color: #7f8c8d;
    font-size: 14px;
}

.animal-info-row .value {
    color: #2c3e50;
    font-weight: 600;
    font-size: 14px;
}

.animal-info-row.animal-notes {
    flex-direction: column;
    align-items: flex-start;
}

.animal-info-row.animal-notes .value {
    margin-top: 5px;
    font-weight: normal;
    line-height: 1.5;
}

.animal-card-footer {
    margin-top: 15px;
    padding-top: 15px;
    border-top: 1px solid #ecf0f1;
    text-align: right;
}

.view-detail {
    color: #8e44ad;
    font-weight: 600;
    font-size: 14px;
}

/* Enclosures Section */
.enclosures-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(350px, 1fr));
    gap: 20px;
}

/* List View for Enclosures */
.enclosures-grid.list-view {
    display: flex;
    flex-direction: column;
    gap: 12px;
}

.enclosures-grid.list-view .enclosure-card {
    display: grid;
    grid-template-columns: 1fr 2fr;
    align-items: center;
    gap: 30px;
    padding: 16px 20px;
    border-radius: 8px;
}

.enclosures-grid.list-view .enclosure-card-header {
    margin-bottom: 0;
    padding-bottom: 0;
    border-bottom: none;
}

.enclosures-grid.list-view .enclosure-name {
    font-size: 18px;
    margin-bottom: 2px;
}

.enclosures-grid.list-view .enclosure-code {
    font-size: 12px;
}

.enclosures-grid.list-view .enclosure-card-body {
    flex-direction: row;
    gap: 20px;
}

.enclosures-grid.list-view .enclosure-info-row {
    flex-direction: column;
    align-items: flex-start;
}

.enclosures-grid.list-view .enclosure-info-row .label {
    font-size: 12px;
    margin-bottom: 2px;
}

.enclosures-grid.list-view .enclosure-info-row .value {
    font-size: 14px;
    text-align: left;
}

.enclosure-card {
    background: white;
    border-radius: 12px;
    padding: 20px;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
    border-left: 4px solid #8e44ad;
}

.enclosure-card-header {
    margin-bottom: 15px;
    padding-bottom: 10px;
    border-bottom: 2px solid #ecf0f1;
}

.enclosure-name {
    margin: 0 0 5px 0;
    color: #2c3e50;
    font-size: 20px;
    font-weight: 700;
}

.enclosure-code {
    font-family: 'Courier New', monospace;
    color: #7f8c8d;
    font-size: 13px;
}

.enclosure-card-body {
    display: flex;
    flex-direction: column;
    gap: 10px;
}

.enclosure-info-row {
    display: flex;
    justify-content: space-between;
    gap: 15px;
}

.enclosure-info-row .label {
    color: #7f8c8d;
    font-size: 14px;
    flex-shrink: 0;
}

.enclosure-info-row .value {
    color: #2c3e50;
    font-weight: 600;
    font-size: 14px;
    text-align: right;
}

/* Common */
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

.btn-primary {
    background-color: #8e44ad;
    color: white;
    border: none;
}

.btn-primary:hover {
    background-color: #7d3c98;
}

button.btn-primary {
    font-family: inherit;
    font-size: inherit;
}

.btn-outline {
    background-color: white;
    color: #8e44ad;
    border: 2px solid #8e44ad;
}

.btn-outline:hover {
    background-color: #8e44ad;
    color: white;
}

/* Modal */
.modal {
    display: none;
    position: fixed;
    z-index: 1000;
    left: 0;
    top: 0;
    width: 100%;
    height: 100%;
    background-color: rgba(0, 0, 0, 0.5);
    overflow: auto;
}

.modal-content {
    background-color: white;
    margin: 5% auto;
    padding: 0;
    border-radius: 12px;
    width: 90%;
    max-width: 600px;
    box-shadow: 0 4px 20px rgba(0, 0, 0, 0.3);
}

.modal-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 20px 30px;
    border-bottom: 2px solid #ecf0f1;
}

.modal-header h2 {
    margin: 0;
    color: #2c3e50;
}

.modal-close {
    color: #7f8c8d;
    font-size: 28px;
    font-weight: bold;
    cursor: pointer;
    transition: color 0.3s;
}

.modal-close:hover {
    color: #2c3e50;
}

.modal-content form {
    padding: 30px;
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

.form-group .form-control {
    max-width: 100%;
}

.form-group select.form-control,
.form-group textarea.form-control {
    max-width: 100%;
}

.form-actions {
    display: flex;
    gap: 10px;
    justify-content: flex-end;
    margin-top: 30px;
    padding-top: 20px;
    border-top: 2px solid #ecf0f1;
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

.no-results {
    text-align: center;
    padding: 40px;
    color: #7f8c8d;
}

@media (max-width: 768px) {
    .animals-grid,
    .enclosures-grid {
        grid-template-columns: 1fr;
    }

    .species-header {
        flex-direction: column;
        align-items: flex-start;
        gap: 10px;
    }

    .tabs {
        overflow-x: auto;
    }
}
</style>

<script>
// Store current view preference
let currentView = localStorage.getItem('workplaceView') || 'grid';

function switchTab(tabName) {
    // Hide all tab contents
    const tabContents = document.querySelectorAll('.tab-content');
    tabContents.forEach(content => {
        content.classList.remove('active');
    });

    // Remove active class from all tabs
    const tabs = document.querySelectorAll('.tab');
    tabs.forEach(tab => {
        tab.classList.remove('active');
    });

    // Show selected tab content
    document.getElementById(tabName + '-content').classList.add('active');

    // Mark selected tab as active
    event.target.classList.add('active');

    // Toggle action buttons
    const addAnimalBtn = document.getElementById('addAnimalBtn');
    const addEnclosureBtn = document.getElementById('addEnclosureBtn');

    if (tabName === 'animals') {
        if (addAnimalBtn) addAnimalBtn.style.display = '';
        if (addEnclosureBtn) addEnclosureBtn.style.display = 'none';
    } else if (tabName === 'myanimals') {
        if (addAnimalBtn) addAnimalBtn.style.display = 'none';
        if (addEnclosureBtn) addEnclosureBtn.style.display = 'none';
    } else if (tabName === 'enclosures') {
        if (addAnimalBtn) addAnimalBtn.style.display = 'none';
        if (addEnclosureBtn) addEnclosureBtn.style.display = '';
    }

    // Apply current view to the newly shown tab
    applyViewToCurrentTab();
}

function switchView(viewType) {
    currentView = viewType;
    localStorage.setItem('workplaceView', viewType);

    // Update active state on view buttons
    const viewBtns = document.querySelectorAll('.view-btn');
    viewBtns.forEach(btn => {
        btn.classList.remove('active');
    });
    event.target.closest('.view-btn').classList.add('active');

    // Apply view to current tab
    applyViewToCurrentTab();
}

function applyViewToCurrentTab() {
    // Find all grids in all tabs
    const animalsGrids = document.querySelectorAll('.animals-grid');
    const enclosuresGrids = document.querySelectorAll('.enclosures-grid');

    // Apply view to animals grids (both Animals and My Animals tabs)
    animalsGrids.forEach(grid => {
        if (currentView === 'list') {
            grid.classList.add('list-view');
        } else {
            grid.classList.remove('list-view');
        }
    });

    // Apply view to enclosures grid
    enclosuresGrids.forEach(grid => {
        if (currentView === 'list') {
            grid.classList.add('list-view');
        } else {
            grid.classList.remove('list-view');
        }
    });
}

function showAddEnclosureModal() {
    document.getElementById('enclosureModal').style.display = 'block';
}

function closeAddEnclosureModal() {
    document.getElementById('enclosureModal').style.display = 'none';
    document.getElementById('enclosureForm').reset();
}

// Close modal when clicking outside
window.onclick = function(event) {
    const modal = document.getElementById('enclosureModal');
    if (event.target === modal) {
        closeAddEnclosureModal();
    }
}

document.addEventListener('DOMContentLoaded', function() {
    // Apply saved view preference on page load
    applyViewToCurrentTab();

    // Set active state on correct view button
    const viewBtns = document.querySelectorAll('.view-btn');
    viewBtns.forEach((btn, index) => {
        if ((currentView === 'grid' && index === 0) || (currentView === 'list' && index === 1)) {
            btn.classList.add('active');
        } else {
            btn.classList.remove('active');
        }
    });

    // Animals search
    const searchAnimalsInput = document.getElementById('searchAnimalsInput');
    if (searchAnimalsInput) {
        const animalCards = document.querySelectorAll('.animal-card');
        const speciesGroups = document.querySelectorAll('.species-group');
        const noAnimalsResults = document.getElementById('noAnimalsResults');

        searchAnimalsInput.addEventListener('input', function() {
            const searchTerm = this.value.toLowerCase();
            let hasVisibleResults = false;

            speciesGroups.forEach(group => {
                const cards = group.querySelectorAll('.animal-card');
                let groupHasVisible = false;

                cards.forEach(card => {
                    const searchData = card.getAttribute('data-search');

                    if (!searchTerm || searchData.includes(searchTerm)) {
                        card.style.display = '';
                        groupHasVisible = true;
                        hasVisibleResults = true;
                    } else {
                        card.style.display = 'none';
                    }
                });

                // Hide species group if no animals are visible
                if (groupHasVisible) {
                    group.style.display = '';
                } else {
                    group.style.display = 'none';
                }
            });

            // Show/hide no results message
            if (hasVisibleResults) {
                noAnimalsResults.style.display = 'none';
            } else {
                noAnimalsResults.style.display = 'block';
            }
        });
    }

    // My Animals search
    const searchMyAnimalsInput = document.getElementById('searchMyAnimalsInput');
    if (searchMyAnimalsInput) {
        const mySpeciesGroups = document.querySelectorAll('.my-animals-group');
        const noMyAnimalsResults = document.getElementById('noMyAnimalsResults');

        searchMyAnimalsInput.addEventListener('input', function() {
            const searchTerm = this.value.toLowerCase();
            let hasVisibleResults = false;

            mySpeciesGroups.forEach(group => {
                const cards = group.querySelectorAll('.animal-card');
                let groupHasVisible = false;

                cards.forEach(card => {
                    const searchData = card.getAttribute('data-search');

                    if (!searchTerm || searchData.includes(searchTerm)) {
                        card.style.display = '';
                        groupHasVisible = true;
                        hasVisibleResults = true;
                    } else {
                        card.style.display = 'none';
                    }
                });

                // Hide species group if no animals are visible
                if (groupHasVisible) {
                    group.style.display = '';
                } else {
                    group.style.display = 'none';
                }
            });

            // Show/hide no results message
            if (hasVisibleResults) {
                noMyAnimalsResults.style.display = 'none';
            } else {
                noMyAnimalsResults.style.display = 'block';
            }
        });
    }

    // Enclosures search
    const searchEnclosuresInput = document.getElementById('searchEnclosuresInput');
    if (searchEnclosuresInput) {
        const enclosureCards = document.querySelectorAll('.enclosure-card');
        const noEnclosuresResults = document.getElementById('noEnclosuresResults');

        searchEnclosuresInput.addEventListener('input', function() {
            const searchTerm = this.value.toLowerCase();
            let hasVisibleResults = false;

            enclosureCards.forEach(card => {
                const searchData = card.getAttribute('data-search');

                if (!searchTerm || searchData.includes(searchTerm)) {
                    card.style.display = '';
                    hasVisibleResults = true;
                } else {
                    card.style.display = 'none';
                }
            });

            // Show/hide no results message
            if (hasVisibleResults) {
                noEnclosuresResults.style.display = 'none';
            } else {
                noEnclosuresResults.style.display = 'block';
            }
        });
    }

    // Enclosure form submission
    const enclosureForm = document.getElementById('enclosureForm');
    if (enclosureForm) {
        enclosureForm.addEventListener('submit', function(e) {
            e.preventDefault();

            const formData = new FormData(this);

            fetch('/workplace/<?= $workplace['id'] ?>/enclosures/create', {
                method: 'POST',
                body: formData
            })
            .then(response => response.text().then(text => {
                try {
                    return JSON.parse(text);
                } catch (e) {
                    console.error('Server returned invalid JSON:', text);
                    throw new Error('Server returned invalid JSON: ' + text.substring(0, 200));
                }
            }))
            .then(data => {
                if (data.success) {
                    closeAddEnclosureModal();
                    location.reload();
                } else {
                    alert('Chyba při vytváření výběhu: ' + (data.error || 'Neznámá chyba'));
                }
            })
            .catch(error => {
                alert('Chyba při komunikaci se serverem: ' + error.message);
                console.error('Error:', error);
            });
        });
    }
});
</script>
