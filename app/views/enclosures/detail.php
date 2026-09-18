<div class="container">
    <div class="page-header">
        <div>
            <div class="breadcrumb">
                <a href="/animals">Seznam zvířat</a> /
                <a href="/animals/workplace/<?= $enclosure['workplace_id'] ?>"><?= htmlspecialchars($workplace['name'] ?? 'Pracoviště') ?></a> /
                <?= htmlspecialchars($enclosure['name']) ?>
            </div>
            <h1><?= htmlspecialchars($enclosure['name']) ?></h1>
            <?php if (!empty($enclosure['code'])): ?>
                <p class="enclosure-code">Kód: <?= htmlspecialchars($enclosure['code']) ?></p>
            <?php endif; ?>
        </div>
        <div class="header-actions">
            <a href="/animals/workplace/<?= $enclosure['workplace_id'] ?>" class="btn btn-secondary">← Zpět</a>
        </div>
    </div>

    <!-- Základní informace -->
    <div class="info-card">
        <div class="card-header-row">
            <h2>Základní informace</h2>
            <?php if ($canEdit): ?>
                <div>
                    <button type="button" class="btn btn-sm btn-primary" id="editBtn" onclick="toggleEdit(true)">Upravit</button>
                    <button type="button" class="btn btn-sm btn-danger" onclick="deleteEnclosure()">Smazat</button>
                </div>
            <?php endif; ?>
        </div>

        <form id="enclosureForm">
            <div class="info-grid">
                <div class="info-item">
                    <span class="label">Název:</span>
                    <span class="value view-mode"><?= htmlspecialchars($enclosure['name']) ?></span>
                    <input type="text" name="name" class="form-control edit-mode" value="<?= htmlspecialchars($enclosure['name']) ?>" required style="display:none;">
                </div>
                <div class="info-item">
                    <span class="label">Kód:</span>
                    <span class="value view-mode"><?= htmlspecialchars($enclosure['code'] ?? '') ?: '—' ?></span>
                    <input type="text" name="code" class="form-control edit-mode" value="<?= htmlspecialchars($enclosure['code'] ?? '') ?>" style="display:none;">
                </div>
                <div class="info-item">
                    <span class="label">Typ vzorkování:</span>
                    <span class="value view-mode"><?= ($enclosure['sample_type'] ?? 'individual') === 'individual' ? 'Individuální' : 'Smíšený' ?></span>
                    <select name="sample_type" class="form-control edit-mode" style="display:none;">
                        <option value="individual" <?= ($enclosure['sample_type'] ?? 'individual') === 'individual' ? 'selected' : '' ?>>Individuální</option>
                        <option value="mixed" <?= ($enclosure['sample_type'] ?? '') === 'mixed' ? 'selected' : '' ?>>Smíšený</option>
                    </select>
                </div>
                <div class="info-item full-width">
                    <span class="label">Poznámky:</span>
                    <span class="value view-mode"><?= nl2br(htmlspecialchars($enclosure['notes'] ?? '')) ?: '—' ?></span>
                    <textarea name="notes" class="form-control edit-mode" rows="3" style="display:none;"><?= htmlspecialchars($enclosure['notes'] ?? '') ?></textarea>
                </div>
            </div>
            <?php if ($canEdit): ?>
                <div class="edit-actions edit-mode" style="display:none; margin-top:16px;">
                    <button type="submit" class="btn btn-primary">Uložit změny</button>
                    <button type="button" class="btn btn-outline" onclick="toggleEdit(false)">Zrušit</button>
                </div>
            <?php endif; ?>
        </form>
    </div>

    <!-- Zvířata ve výběhu -->
    <div class="info-card">
        <div class="card-header-row">
            <h2>Zvířata ve výběhu <span class="count-badge"><?= count($animals) ?></span></h2>
        </div>

        <?php if (empty($animals)): ?>
            <p class="muted">V tomto výběhu nejsou žádná aktivní zvířata.</p>
        <?php else: ?>
            <div class="animals-grid">
                <?php foreach ($animals as $animal): ?>
                    <a href="/animals/detail/<?= $animal['id'] ?>" class="animal-card">
                        <div class="animal-card-header">
                            <h3 class="animal-name"><?= htmlspecialchars($animal['name'] ?: 'Bez jména') ?></h3>
                            <span class="animal-id"><?= htmlspecialchars($animal['identifier'] ?: '-') ?></span>
                        </div>
                        <div class="animal-info-row">
                            <span class="label">Druh:</span>
                            <span class="value"><?= htmlspecialchars($animal['species']) ?></span>
                        </div>
                        <div class="animal-info-row">
                            <span class="label">Pohlaví:</span>
                            <span class="value">
                                <?php
                                $g = ['male' => '♂ Samec', 'female' => '♀ Samice', 'unknown' => '? Neznámé'];
                                echo $g[$animal['gender']] ?? '-';
                                ?>
                            </span>
                        </div>
                        <div class="animal-card-footer"><span class="view-detail">Detail →</span></div>
                    </a>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</div>

<style>
.container { max-width: 1200px; margin: 0 auto; padding: 20px; }
.page-header { display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 24px; }
.page-header h1 { margin: 0 0 6px 0; color: #2c3e50; }
.breadcrumb { font-size: 14px; color: #7f8c8d; margin-bottom: 8px; }
.breadcrumb a { color: #8e44ad; text-decoration: none; }
.enclosure-code { margin: 0; color: #7f8c8d; font-family: 'Courier New', monospace; }
.header-actions { display: flex; gap: 10px; }

.info-card { background: #fff; border-radius: 12px; padding: 24px; box-shadow: 0 2px 8px rgba(0,0,0,0.08); margin-bottom: 24px; }
.card-header-row { display: flex; justify-content: space-between; align-items: center; margin-bottom: 16px; }
.card-header-row h2 { margin: 0; font-size: 20px; color: #2c3e50; }
.count-badge { background: #8e44ad; color: #fff; border-radius: 20px; padding: 2px 12px; font-size: 14px; margin-left: 6px; }

.info-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 16px; }
.info-item { display: flex; flex-direction: column; }
.info-item.full-width { grid-column: 1 / -1; }
.info-item .label { color: #7f8c8d; font-size: 13px; margin-bottom: 4px; }
.info-item .value { color: #2c3e50; font-weight: 600; }
.form-control { width: 100%; padding: 8px 12px; border: 2px solid #ddd; border-radius: 6px; font-size: 15px; font-family: inherit; }
.form-control:focus { outline: none; border-color: #8e44ad; }

.btn { padding: 8px 16px; border-radius: 6px; text-decoration: none; display: inline-block; font-weight: 600; border: none; cursor: pointer; font-family: inherit; font-size: 14px; }
.btn-sm { padding: 6px 12px; font-size: 13px; }
.btn-primary { background: #8e44ad; color: #fff; }
.btn-primary:hover { background: #7d3c98; }
.btn-secondary { background: #95a5a6; color: #fff; }
.btn-secondary:hover { background: #7f8c8d; }
.btn-outline { background: #fff; color: #8e44ad; border: 2px solid #8e44ad; }
.btn-danger { background: #c0392b; color: #fff; }
.btn-danger:hover { background: #a93226; }

.animals-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(240px, 1fr)); gap: 16px; }
.animal-card { background: #fff; border: 1px solid #ecf0f1; border-left: 4px solid #8e44ad; border-radius: 10px; padding: 16px; text-decoration: none; color: inherit; transition: all .2s; display: flex; flex-direction: column; }
.animal-card:hover { transform: translateY(-3px); box-shadow: 0 4px 14px rgba(142,68,173,0.25); }
.animal-card-header { margin-bottom: 10px; }
.animal-name { margin: 0; font-size: 17px; color: #2c3e50; }
.animal-id { font-family: 'Courier New', monospace; color: #7f8c8d; font-size: 13px; }
.animal-info-row { display: flex; justify-content: space-between; padding: 4px 0; font-size: 14px; }
.animal-info-row .label { color: #7f8c8d; }
.animal-info-row .value { color: #2c3e50; font-weight: 600; }
.animal-card-footer { margin-top: 10px; text-align: right; }
.view-detail { color: #8e44ad; font-weight: 600; font-size: 13px; }
.muted { color: #7f8c8d; }

@media (max-width: 768px) { .info-grid { grid-template-columns: 1fr; } }
</style>

<script>
function toggleEdit(on) {
    document.querySelectorAll('.view-mode').forEach(el => el.style.display = on ? 'none' : '');
    document.querySelectorAll('.edit-mode').forEach(el => el.style.display = on ? '' : 'none');
    var eb = document.getElementById('editBtn');
    if (eb) eb.style.display = on ? 'none' : '';
}

document.getElementById('enclosureForm').addEventListener('submit', function(e) {
    e.preventDefault();
    var fd = new FormData(this);
    fetch('/enclosures/<?= $enclosure['id'] ?>/update', { method: 'POST', body: fd })
        .then(r => r.text().then(t => { try { return JSON.parse(t); } catch (_) { throw new Error(t.substring(0,200)); } }))
        .then(d => {
            if (d.success) { location.reload(); }
            else { alert('Chyba při ukládání: ' + (d.error || 'Neznámá chyba')); }
        })
        .catch(err => alert('Chyba komunikace se serverem: ' + err.message));
});

function deleteEnclosure() {
    if (!confirm('Opravdu chcete smazat tento výběh?')) return;
    fetch('/enclosures/<?= $enclosure['id'] ?>/delete', { method: 'POST' })
        .then(r => r.text().then(t => { try { return JSON.parse(t); } catch (_) { throw new Error(t.substring(0,200)); } }))
        .then(d => {
            if (d.success) { window.location.href = '/animals/workplace/<?= $enclosure['workplace_id'] ?>'; }
            else { alert('Chyba při mazání: ' + (d.error || 'Neznámá chyba')); }
        })
        .catch(err => alert('Chyba komunikace se serverem: ' + err.message));
}
</script>
