<?php $layout = 'main'; ?>

<div class="pg-wrap" data-wp="<?= (int)$workplace['id'] ?>">
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

    <?php if ($canEdit): ?>
    <!-- Sdílený našeptávač zvířat pracoviště pro všechny skupiny -->
    <datalist id="pgAnimalsList">
        <?php foreach ($animals as $a): ?>
            <?php
                $name  = ($a['name'] !== '' && $a['name'] !== null) ? $a['name'] : '(bez jména)';
                $label = $name
                    . (!empty($a['identifier']) ? ' (' . $a['identifier'] . ')' : '')
                    . (!empty($a['species']) ? ' · ' . $a['species'] : '');
            ?>
            <option data-id="<?= (int)$a['id'] ?>"
                    data-name="<?= htmlspecialchars($name) ?>"
                    data-ident="<?= htmlspecialchars($a['identifier'] ?? '') ?>"
                    value="<?= htmlspecialchars($label) ?>"></option>
        <?php endforeach; ?>
    </datalist>
    <?php endif; ?>

    <?php if (empty($groups)): ?>
        <div class="alert alert-info" id="pgEmpty">
            Zatím zde nejsou žádné skupiny.<?= $canEdit ? ' Vytvořte první skupinu výše.' : '' ?>
        </div>
    <?php endif; ?>

    <div id="pgGroups">
        <?php foreach ($groups as $group): ?>
            <?php $members = $membersByGroup[$group['id']] ?? []; ?>
            <div class="pg-card pg-group" data-group-id="<?= (int)$group['id'] ?>">
                <div class="pg-group-head">
                    <div>
                        <h3 class="pg-group-name">
                            <span class="pg-group-title"><?= htmlspecialchars($group['name']) ?></span>
                            <span class="badge badge-info pg-count"><?= count($members) ?> zvířat</span>
                        </h3>
                        <p class="pg-group-notes"><?= htmlspecialchars($group['notes'] ?? '') ?></p>
                    </div>
                    <?php if ($canEdit): ?>
                    <div class="pg-group-actions">
                        <button type="button" class="btn btn-sm btn-outline"
                            onclick="pgRenameGroup(<?= (int)$group['id'] ?>)">✏️ Upravit</button>
                        <button type="button" class="btn btn-sm btn-danger"
                            onclick="pgDeleteGroup(<?= (int)$group['id'] ?>)">🗑️ Smazat</button>
                    </div>
                    <?php endif; ?>
                </div>

                <div class="pg-members">
                    <span class="pg-empty-note"<?= empty($members) ? '' : ' style="display:none"' ?>>
                        Skupina zatím nemá žádné členy.
                    </span>
                    <?php foreach ($members as $m): ?>
                        <span class="pg-chip" data-animal-id="<?= (int)$m['id'] ?>">
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
                </div>

                <?php if ($canEdit): ?>
                <div class="pg-add">
                    <div class="pg-add-row">
                        <input type="text" class="form-control pg-animal-search" list="pgAnimalsList"
                               placeholder="Hledat zvíře (jméno, ID, druh)…" autocomplete="off"
                               onkeydown="if(event.key==='Enter'){event.preventDefault();pgAddMember(<?= (int)$group['id'] ?>,this);}">
                        <button type="button" class="btn btn-sm btn-success"
                            onclick="pgAddMember(<?= (int)$group['id'] ?>, this.previousElementSibling)">Přidat</button>
                    </div>
                    <small class="text-muted">Začni psát a vyber zvíře z nabídky. Pokud je v jiné skupině, přesune se sem.</small>
                </div>
                <?php endif; ?>
            </div>
        <?php endforeach; ?>
    </div>
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
.pg-group-notes:empty { display: none; }
.pg-group-actions { display: flex; gap: 8px; flex-shrink: 0; }
.pg-members { display: flex; flex-wrap: wrap; gap: 8px; margin: 14px 0; align-items: center; }
.pg-empty-note { color: #95a5a6; font-size: 14px; }
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
.pg-add { border-top: 1px dashed #e0e0e0; padding-top: 12px; }
.pg-add-row { display: flex; gap: 8px; align-items: center; max-width: 620px; }
.pg-add-row .pg-animal-search { flex: 1 1 auto; }
.badge-info { background: #d1ecf1; color: #0c5460; font-size: 12px; padding: 2px 8px; border-radius: 10px; white-space: nowrap; }
.alert-info { background: #d1ecf1; border: 1px solid #bee5eb; color: #0c5460; padding: 16px; border-radius: 8px; }
</style>

<script>
(function () {
    var WP = document.querySelector('.pg-wrap').getAttribute('data-wp');
    var BASE = '/workplace/' + WP + '/parasitology-groups';

    function esc(s) {
        return String(s == null ? '' : s)
            .replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;');
    }

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

    function groupEl(groupId) {
        return document.querySelector('.pg-group[data-group-id="' + groupId + '"]');
    }

    function refreshCount(gEl) {
        var n = gEl.querySelectorAll('.pg-members .pg-chip').length;
        var badge = gEl.querySelector('.pg-count');
        if (badge) badge.textContent = n + ' zvířat';
        var note = gEl.querySelector('.pg-empty-note');
        if (note) note.style.display = n === 0 ? '' : 'none';
    }

    function buildChip(groupId, animalId, name, ident) {
        var chip = document.createElement('span');
        chip.className = 'pg-chip';
        chip.setAttribute('data-animal-id', animalId);
        var identHtml = ident ? ' <small>(' + esc(ident) + ')</small>' : '';
        chip.innerHTML =
            '<span class="pg-chip-name">' + esc(name) + identHtml + '</span>' +
            '<button type="button" class="pg-chip-x" title="Odebrat ze skupiny" ' +
            'onclick="pgRemoveMember(' + groupId + ',' + animalId + ')">✕</button>';
        return chip;
    }

    window.pgCreateGroup = function () {
        var name = (document.getElementById('newGroupName').value || '').trim();
        var notes = (document.getElementById('newGroupNotes').value || '').trim();
        if (!name) { alert('Zadejte název skupiny.'); return; }
        pgPost(BASE + '/create', { name: name, notes: notes })
            .then(function () { location.reload(); })
            .catch(function (e) { alert(e.message); });
    };

    window.pgRenameGroup = function (groupId) {
        var gEl = groupEl(groupId);
        var currentName = gEl.querySelector('.pg-group-title').textContent.trim();
        var currentNotes = gEl.querySelector('.pg-group-notes').textContent.trim();
        var name = prompt('Název skupiny:', currentName);
        if (name === null) return;
        name = name.trim();
        if (!name) { alert('Název nesmí být prázdný.'); return; }
        var notes = prompt('Poznámka (nepovinné):', currentNotes);
        if (notes === null) notes = currentNotes;
        pgPost(BASE + '/' + groupId + '/rename', { name: name, notes: notes })
            .then(function () {
                gEl.querySelector('.pg-group-title').textContent = name;
                gEl.querySelector('.pg-group-notes').textContent = notes;
            })
            .catch(function (e) { alert(e.message); });
    };

    window.pgDeleteGroup = function (groupId) {
        var gEl = groupEl(groupId);
        var name = gEl.querySelector('.pg-group-title').textContent.trim();
        if (!confirm('Opravdu smazat skupinu „' + name + '"? Členství zvířat se zruší (zvířata ani jejich vyšetření se nemažou).')) return;
        pgPost(BASE + '/' + groupId + '/delete', {})
            .then(function () { gEl.remove(); })
            .catch(function (e) { alert(e.message); });
    };

    window.pgAddMember = function (groupId, input) {
        var value = (input.value || '').trim();
        if (!value) { input.focus(); return; }

        var list = document.getElementById('pgAnimalsList');
        var opt = null;
        for (var i = 0; i < list.options.length; i++) {
            if (list.options[i].value === value) { opt = list.options[i]; break; }
        }
        if (!opt) {
            alert('Vyber zvíře z nabídky – začni psát a vyber položku ze seznamu.');
            input.focus();
            return;
        }
        var animalId = parseInt(opt.getAttribute('data-id'), 10);
        var name = opt.getAttribute('data-name');
        var ident = opt.getAttribute('data-ident');

        var gEl = groupEl(groupId);
        // Už je v této skupině? Nic nedělat.
        if (gEl.querySelector('.pg-members .pg-chip[data-animal-id="' + animalId + '"]')) {
            input.value = ''; input.focus();
            return;
        }

        pgPost(BASE + '/' + groupId + '/members/add', { animal_ids: [animalId] })
            .then(function () {
                // Přesun z jiné skupiny: odebrat případný chip odjinud a přepočítat.
                document.querySelectorAll('.pg-members .pg-chip[data-animal-id="' + animalId + '"]').forEach(function (c) {
                    var otherGroup = c.closest('.pg-group');
                    c.remove();
                    if (otherGroup && otherGroup !== gEl) refreshCount(otherGroup);
                });
                gEl.querySelector('.pg-members').appendChild(buildChip(groupId, animalId, name, ident));
                refreshCount(gEl);
                input.value = '';
                input.focus();
            })
            .catch(function (e) { alert(e.message); });
    };

    window.pgRemoveMember = function (groupId, animalId) {
        pgPost(BASE + '/' + groupId + '/members/remove', { animal_id: animalId })
            .then(function () {
                var gEl = groupEl(groupId);
                var chip = gEl.querySelector('.pg-members .pg-chip[data-animal-id="' + animalId + '"]');
                if (chip) chip.remove();
                refreshCount(gEl);
            })
            .catch(function (e) { alert(e.message); });
    };
})();
</script>
