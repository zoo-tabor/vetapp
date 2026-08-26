<?php $layout = 'main'; ?>

<div class="pgi-wrap" data-wp="<?= (int)$workplace['id'] ?>">
    <div class="page-header">
        <div class="breadcrumb">
            <a href="/">Pracoviště</a> /
            <a href="/workplace/<?= (int)$workplace['id'] ?>/animals"><?= htmlspecialchars($workplace['name']) ?></a> /
            <a href="/workplace/<?= (int)$workplace['id'] ?>/parasitology-import">Import parazitologie</a> /
            Náhled
        </div>
        <h1>Náhled importu</h1>
        <p class="subtitle">
            Soubor: <strong><?= htmlspecialchars($filename) ?></strong>
            &nbsp;·&nbsp; Datum odběru: <strong><?= htmlspecialchars($parsed['date'] ?? '—') ?></strong>
            <?php if (!empty($parsed['protocol'])): ?>&nbsp;·&nbsp; Protokol: <strong><?= htmlspecialchars($parsed['protocol']) ?></strong><?php endif; ?>
            &nbsp;·&nbsp; Vzorků: <strong><?= count($parsed['samples']) ?></strong>
        </p>
    </div>

    <?php if (isset($_SESSION['error'])): ?>
        <div class="alert alert-error"><?= htmlspecialchars($_SESSION['error']); unset($_SESSION['error']); ?></div>
    <?php endif; ?>

    <div class="pgi-note">
        Zkontroluj <strong>každý</strong> řádek. Napiš do pole zvíře nebo skupinu a vyber z nabídky.
        Skupinové vzorky (více zvířat / výběh) párуj na <strong>skupinu</strong>.
        Co nechceš importovat, zaškrtni <em>Přeskočit</em>.
        Skupiny s členy založíš ve <a href="/workplace/<?= (int)$workplace['id'] ?>/parasitology-groups" target="_blank">správě skupin</a>.
    </div>

    <datalist id="pairTargets">
        <?php foreach ($animalLabels as $aid => $label): ?>
            <option data-type="animal" data-id="<?= (int)$aid ?>" value="<?= htmlspecialchars($label) ?>"></option>
        <?php endforeach; ?>
        <?php foreach ($groupLabels as $gid => $label): ?>
            <option data-type="group" data-id="<?= (int)$gid ?>" value="<?= htmlspecialchars($label) ?>"></option>
        <?php endforeach; ?>
    </datalist>

    <form action="/workplace/<?= (int)$workplace['id'] ?>/parasitology-import/execute" method="POST" id="importForm">
        <div class="pgi-table-wrap">
            <table class="pgi-table">
                <thead>
                    <tr>
                        <th style="width:32%;">Vzorek</th>
                        <th style="width:34%;">Výsledky</th>
                        <th style="width:34%;">Napárovat na</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($parsed['samples'] as $i => $s): ?>
                        <tr>
                            <td>
                                <div class="pgi-desc"><?= htmlspecialchars($s['description'] ?: '(bez popisu)') ?></div>
                                <div class="pgi-meta">
                                    <?= htmlspecialchars($s['pr']) ?>
                                    <?php if (!empty($s['zoo_no'])): ?> · <?= htmlspecialchars($s['zoo_no']) ?><?php endif; ?>
                                    <?php if (!empty($s['material'])): ?> · <?= htmlspecialchars($s['material']) ?><?php endif; ?>
                                </div>
                            </td>
                            <td>
                                <?php foreach ($s['results'] as $r): ?>
                                    <?php $neg = (mb_stripos($r['finding'], 'negativní') !== false); ?>
                                    <div class="pgi-res <?= $neg ? 'neg' : 'pos' ?>">
                                        <span class="pgi-method"><?= htmlspecialchars($r['method']) ?>:</span>
                                        <?= htmlspecialchars($r['finding']) ?>
                                        <?php if (!empty($r['note'])): ?><span class="pgi-note-val"><?= htmlspecialchars($r['note']) ?></span><?php endif; ?>
                                    </div>
                                <?php endforeach; ?>
                            </td>
                            <td>
                                <input type="text" class="form-control pair-input" list="pairTargets"
                                       data-idx="<?= $i ?>" autocomplete="off"
                                       placeholder="Zvíře nebo skupina…"
                                       value="<?= htmlspecialchars($s['suggestion'] ?? '') ?>">
                                <label class="pgi-skip"><input type="checkbox" class="pair-skip" data-idx="<?= $i ?>"> Přeskočit</label>
                                <input type="hidden" name="pair_type[<?= $i ?>]" value="">
                                <input type="hidden" name="pair_id[<?= $i ?>]" value="">
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <div class="pgi-actions">
            <button type="submit" class="btn btn-primary">Importovat</button>
            <a href="/workplace/<?= (int)$workplace['id'] ?>/parasitology-import" class="btn btn-outline">Zrušit</a>
        </div>
    </form>
</div>

<style>
.pgi-wrap { max-width: 1150px; margin: 0 auto; padding: 20px; }
.pgi-wrap .page-header h1 { margin: 6px 0; color:#2c3e50; }
.pgi-wrap .subtitle { color:#7f8c8d; margin:4px 0 12px; }
.pgi-wrap .breadcrumb { color:#7f8c8d; font-size:14px; margin-bottom:6px; }
.pgi-wrap .breadcrumb a { color:#2c3e50; text-decoration:none; }
.pgi-note { background:#eef5fb; border:1px solid #d3e3f1; color:#2c3e50; padding:12px 14px; border-radius:8px; margin-bottom:16px; font-size:14px; }
.pgi-table-wrap { overflow-x:auto; background:#fff; border-radius:10px; box-shadow:0 2px 8px rgba(0,0,0,.08); }
.pgi-table { width:100%; border-collapse:collapse; }
.pgi-table th, .pgi-table td { padding:12px 14px; border-bottom:1px solid #eef0f3; vertical-align:top; text-align:left; }
.pgi-table th { background:#f7f9fb; color:#2c3e50; font-size:13px; text-transform:uppercase; letter-spacing:.03em; }
.pgi-desc { font-weight:600; color:#2c3e50; }
.pgi-meta { color:#95a5a6; font-size:12px; margin-top:2px; }
.pgi-res { font-size:13px; margin:2px 0; }
.pgi-res.pos { color:#a93226; }
.pgi-res.neg { color:#5b6b7a; }
.pgi-method { color:#7f8c8d; }
.pgi-note-val { color:#2c3e50; font-weight:600; margin-left:4px; }
.pair-input { width:100%; }
.pair-input.pair-error { border-color:#e74c3c; box-shadow:0 0 0 2px rgba(231,76,60,.15); }
.pgi-skip { display:inline-flex; align-items:center; gap:5px; margin-top:6px; font-size:13px; color:#7f8c8d; }
.pgi-actions { margin-top:18px; display:flex; gap:10px; }
.alert-error { background:#f8d7da; border:1px solid #f5c6cb; color:#721c24; padding:14px; border-radius:8px; margin-bottom:16px; }
</style>

<script>
(function () {
    var form = document.getElementById('importForm');
    var list = document.getElementById('pairTargets');

    // Zaškrtnutí "Přeskočit" zašedne pole.
    document.querySelectorAll('.pair-skip').forEach(function (cb) {
        cb.addEventListener('change', function () {
            var idx = cb.getAttribute('data-idx');
            var inp = document.querySelector('.pair-input[data-idx="' + idx + '"]');
            inp.disabled = cb.checked;
            if (cb.checked) inp.classList.remove('pair-error');
        });
    });

    function resolveOption(val) {
        for (var i = 0; i < list.options.length; i++) {
            if (list.options[i].value === val) return list.options[i];
        }
        return null;
    }

    form.addEventListener('submit', function (e) {
        var inputs = document.querySelectorAll('.pair-input');
        var unresolved = [];
        var paired = 0;

        inputs.forEach(function (inp) {
            var idx = inp.getAttribute('data-idx');
            var skip = document.querySelector('.pair-skip[data-idx="' + idx + '"]').checked;
            var tHidden = document.querySelector('input[name="pair_type[' + idx + ']"]');
            var iHidden = document.querySelector('input[name="pair_id[' + idx + ']"]');
            var val = (inp.value || '').trim();

            inp.classList.remove('pair-error');

            if (skip || val === '') {
                tHidden.value = 'skip';
                iHidden.value = '';
                return;
            }
            var opt = resolveOption(val);
            if (!opt) {
                unresolved.push(idx);
                tHidden.value = '';
                iHidden.value = '';
                inp.classList.add('pair-error');
                return;
            }
            tHidden.value = opt.getAttribute('data-type');
            iHidden.value = opt.getAttribute('data-id');
            paired++;
        });

        if (unresolved.length) {
            e.preventDefault();
            alert('Některé vzorky nejsou správně spárované (' + unresolved.length + '). Vyber zvíře/skupinu z nabídky, nebo zaškrtni Přeskočit.');
            return;
        }
        if (paired === 0) {
            if (!confirm('Není spárovaný žádný vzorek – nic se nenaimportuje. Pokračovat?')) {
                e.preventDefault();
            }
        }
    });
})();
</script>
