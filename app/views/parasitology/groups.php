<?php $layout = 'main'; ?>

<div class="pg-wrap">
    <div class="page-header">
        <div class="breadcrumb">
            <a href="/">Pracoviště</a> /
            <a href="/workplace/<?= (int)$workplace['id'] ?>/animals"><?= htmlspecialchars($workplace['name']) ?></a> /
            Parazitologické skupiny
        </div>
        <h1>Parazitologické skupiny</h1>
        <p class="subtitle">
            Skupiny slouží pro společné (skupinové) vzorky a aplikaci antiparazitik.
            Zvíře může být nejvýše v&nbsp;jedné skupině – přidáním do skupiny se z&nbsp;té
            předchozí automaticky přesune.
        </p>
        <a href="/workplace/<?= (int)$workplace['id'] ?>/animals" class="btn btn-outline">← Zpět na zvířata</a>
    </div>

    <?php if ($canEdit): ?>
    <div class="pg-card pg-create">
        <h3>➕ Nová skupina</h3>
        <div class="pg-create-row">
            <input type="text" id="newGroupName" class="form-control" placeholder="Název skupiny (např. Vodní svět, Lamy)">
            <input type="text" id="newGroupNotes" class="form-control" placeholder="Poznámka (nepovinné)">
            <button type="button" class="btn btn-primary" onclick="pgCreateGroup()">Vytvořit</button>
        </div>
    </div>
    <?php endif; ?>

    <?php if (empty($groups)): ?>
        <div class="alert alert-info">
            Zatím zde nejsou žádné skupiny.<?= $canEdit ? ' Vytvořte první skupinu výše.' : '' ?>
        </div>
    <?php else: ?>
        <?php foreach ($groups as $group): ?>
            <?php $members = $membersByGroup[$group['id']] ?? []; ?>
            <div class="pg-card pg-group" data-group-id="<?= (int)$group['id'] ?>">
                <div class="pg-group-head">
                    <div>
                        <h3 class="pg-group-name"><?= htmlspecialchars($group['name']) ?>
                            <span class="badge badge-info"><?= count($members) ?> zvířat</span>
                        </h3>
                        <?php if (!empty($group['notes'])): ?>
                            <p class="pg-group-notes"><?= htmlspecialchars($group['notes']) ?></p>
                        <?php endif; ?>
                    </div>
                    <?php if ($canEdit): ?>
                    <div class="pg-group-actions">
                        <button type="button" class="btn btn-sm btn-outline"
                            onclick="pgRenameGroup(<?= (int)$group['id'] ?>, <?= htmlspecialchars(json_encode($group['name']), ENT_QUOTES) ?>, <?= htmlspecialchars(json_encode($group['notes'] ?? ''), ENT_QUOTES) ?>)">
                            ✏️ Upravit
                        </button>
                        <button type="button" class="btn btn-sm btn-danger"
                            onclick="pgDeleteGroup(<?= (int)$group['id'] ?>, <?= htmlspecialchars(json_encode($group['name']), ENT_QUOTES) ?>)">
                            🗑️ Smazat
                        </button>
                    </div>
                    <?php endif; ?>
                </div>

                <div class="pg-members">
                    <?php if (empty($members)): ?>
                        <span class="text-muted">Skupina zatím nemá žádné členy.</span>
                    <?php else: ?>
                        <?php foreach ($members as $m): ?>
                            <span class="pg-chip">
                                <span class="pg-chip-name">
                                    <?= htmlspecialchars($m['name'] ?: ('#' . $m['identifier'])) ?>
                                    <?php if (!empty($m['identifier'])): ?>
                                        <small>(<?= htmlspecialchars($m['identifier']) ?>)</small>
                                    <?php endif; ?>
                                </span>
                                <?php if ($canEdit): ?>
                                    <button type="button" class="pg-chip-x" title="Odebrat ze skupiny"
                                        onclick="pgRemoveMember(<?= (int)$group['id'] ?>, <?= (int)$m['id'] ?>)">✕</button>
                                <?php endif; ?>
                            </span>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>

                <?php if ($canEdit): ?>
                <div class="pg-add">
                    <select multiple size="6" class="form-control pg-add-select" id="pgAdd<?= (int)$group['id'] ?>">
                        <?php foreach ($animals as $a): ?>
                            <?php
                                $inGroup = $groupMap[$a['id']] ?? null;
                                // Nezobrazovat zvířata, která už jsou v TÉTO skupině.
                                if ($inGroup && (int)$inGroup['id'] === (int)$group['id']) {
                                    continue;
                                }
                                $label = ($a['name'] ?: ('#' . $a['identifier']));
                                if (!empty($a['species'])) $label .= ' · ' . $a['species'];
                                if (!empty($a['identifier'])) $label .= ' · ' . $a['identifier'];
                                if ($inGroup) $label .= '  → ' . $inGroup['name'];
                            ?>
                            <option value="<?= (int)$a['id'] ?>"><?= htmlspecialchars($label) ?></option>
                        <?php endforeach; ?>
                    </select>
                    <button type="button" class="btn btn-sm btn-success" onclick="pgAddMembers(<?= (int)$group['id'] ?>)">
                        Přidat vybraná zvířata
                    </button>
                    <small class="text-muted">Šipka „→" značí, že zvíře je nyní v jiné skupině (přidáním se přesune sem).</small>
                </div>
                <?php endif; ?>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>
</div>

<style>
.pg-wrap { max-width: 1100px; margin: 0 auto; padding: 20px; }
.pg-wrap .page-header h1 { margin: 6px 0; color: #2c3e50; }
.pg-wrap .subtitle { color: #7f8c8d; margin: 4px 0 12px; max-width: 760px; }
.pg-wrap .breadcrumb { color: #7f8c8d; font-size: 14px; margin-bottom: 6px; }
.pg-wrap .breadcrumb a { color: #2c3e50; text-decoration: none; }
.pg-card {
    background: #fff; border-radius: 10px; padding: 18px 20px; margin-bottom: 18px;
    box-shadow: 0 2px 8px rgba(0,0,0,.08);
}
.pg-create-row { display: flex; gap: 10px; flex-wrap: wrap; align-items: center; margin-top: 8px; }
.pg-create-row .form-control { flex: 1 1 220px; }
.pg-group { border-left: 4px solid #2c3e50; }
.pg-group-head { display: flex; justify-content: space-between; align-items: flex-start; gap: 12px; }
.pg-group-name { margin: 0 0 4px; color: #2c3e50; display: flex; align-items: center; gap: 10px; }
.pg-group-notes { margin: 0; color: #7f8c8d; font-size: 14px; }
.pg-group-actions { display: flex; gap: 8px; flex-shrink: 0; }
.pg-members { display: flex; flex-wrap: wrap; gap: 8px; margin: 14px 0; }
.pg-chip {
    display: inline-flex; align-items: center; gap: 6px; background: #eef1f5;
    border: 1px solid #d7dde5; border-radius: 16px; padding: 4px 10px; font-size: 14px;
}
.pg-chip-name small { color: #7f8c8d; }
.pg-chip-x {
    border: none; background: transparent; color: #c0392b; cursor: pointer;
    font-size: 13px; line-height: 1; padding: 0 2px;
}
.pg-chip-x:hover { color: #a93226; }
.pg-add { display: flex; flex-direction: column; gap: 8px; border-top: 1px dashed #e0e0e0; padding-top: 12px; }
.pg-add-select { max-width: 620px; }
.badge-info { background: #d1ecf1; color: #0c5460; font-size: 12px; padding: 2px 8px; border-radius: 10px; }
.alert-info { background: #d1ecf1; border: 1px solid #bee5eb; color: #0c5460; padding: 16px; border-radius: 8px; }
</style>

<script>
(function () {
    var WP = <?= (int)$workplace['id'] ?>;
    var BASE = '/workplace/' + WP + '/parasitology-groups';

    async function pgPost(url, body) {
        var res = await fetch(url, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(body || {})
        });
        var data = {};
        try { data = await res.json(); } catch (e) {}
        if (!res.ok || !data.success) {
            throw new Error(data.error || ('Chyba serveru (' + res.status + ')'));
        }
        return data;
    }

    window.pgCreateGroup = function () {
        var name = (document.getElementById('newGroupName').value || '').trim();
        var notes = (document.getElementById('newGroupNotes').value || '').trim();
        if (!name) { alert('Zadejte název skupiny.'); return; }
        pgPost(BASE + '/create', { name: name, notes: notes })
            .then(function () { location.reload(); })
            .catch(function (e) { alert(e.message); });
    };

    window.pgRenameGroup = function (groupId, currentName, currentNotes) {
        var name = prompt('Název skupiny:', currentName);
        if (name === null) return;
        name = name.trim();
        if (!name) { alert('Název nesmí být prázdný.'); return; }
        var notes = prompt('Poznámka (nepovinné):', currentNotes || '');
        if (notes === null) notes = currentNotes || '';
        pgPost(BASE + '/' + groupId + '/rename', { name: name, notes: notes })
            .then(function () { location.reload(); })
            .catch(function (e) { alert(e.message); });
    };

    window.pgDeleteGroup = function (groupId, name) {
        if (!confirm('Opravdu smazat skupinu „' + name + '"? Členství zvířat ve skupině se zruší (samotná zvířata ani jejich vyšetření zůstanou).')) return;
        pgPost(BASE + '/' + groupId + '/delete', {})
            .then(function () { location.reload(); })
            .catch(function (e) { alert(e.message); });
    };

    window.pgAddMembers = function (groupId) {
        var sel = document.getElementById('pgAdd' + groupId);
        var ids = Array.from(sel.selectedOptions).map(function (o) { return parseInt(o.value, 10); });
        if (!ids.length) { alert('Vyberte alespoň jedno zvíře.'); return; }
        pgPost(BASE + '/' + groupId + '/members/add', { animal_ids: ids })
            .then(function () { location.reload(); })
            .catch(function (e) { alert(e.message); });
    };

    window.pgRemoveMember = function (groupId, animalId) {
        pgPost(BASE + '/' + groupId + '/members/remove', { animal_id: animalId })
            .then(function () { location.reload(); })
            .catch(function (e) { alert(e.message); });
    };
})();
</script>
