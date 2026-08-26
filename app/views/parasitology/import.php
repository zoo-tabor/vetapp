<?php $layout = 'main'; ?>

<div class="pgi-wrap">
    <div class="page-header">
        <div class="breadcrumb">
            <a href="/">Pracoviště</a> /
            <a href="/workplace/<?= (int)$workplace['id'] ?>/animals"><?= htmlspecialchars($workplace['name']) ?></a> /
            Import parazitologie
        </div>
        <h1>Import parazitologie (.xlsx)</h1>
        <p class="subtitle">Import protokolů SVÚ Jihlava. Popisy vzorků se před uložením ručně spárují na zvířata nebo skupiny.</p>
    </div>

    <?php if (isset($_SESSION['error'])): ?>
        <div class="alert alert-error"><?= htmlspecialchars($_SESSION['error']); unset($_SESSION['error']); ?></div>
    <?php endif; ?>
    <?php if (isset($_SESSION['success'])): ?>
        <div class="alert alert-success">
            <?= htmlspecialchars($_SESSION['success']); unset($_SESSION['success']); ?>
            <div style="margin-top:10px;">
                <a href="/workplace/<?= (int)$workplace['id'] ?>/animals" class="btn btn-primary btn-sm">Zobrazit zvířata</a>
            </div>
        </div>
    <?php endif; ?>

    <div class="pgi-card">
        <h2>Nahrát soubor</h2>
        <form action="/workplace/<?= (int)$workplace['id'] ?>/parasitology-import/upload" method="POST" enctype="multipart/form-data">
            <div class="form-group">
                <label for="import_file">Vyberte protokol SVÚ (.xlsx)</label>
                <input type="file" id="import_file" name="import_file" accept=".xlsx" required class="form-control">
                <small class="form-text">Přijímá pouze soubory .xlsx (protokol z laboratoře SVÚ Jihlava).</small>
            </div>
            <button type="submit" class="btn btn-primary">Nahrát a zobrazit náhled</button>
        </form>
    </div>

    <div class="pgi-card">
        <h2>Jak to funguje</h2>
        <ul>
            <li>Ze souboru se načtou vzorky (číslo PR + popis) a jejich parazitologické výsledky.</li>
            <li>V náhledu <strong>u každého vzorku ručně potvrdíš párování</strong> na zvíře nebo na parazitologickou skupinu (návrh se předvyplní z minula, ale vždy je vidět ke kontrole).</li>
            <li>Popis, který je skupinový vzorek (např. „vodní svět", „lama Navara, Chico"), napáruj na skupinu — vyšetření se uloží jako <strong>jeden skupinový záznam</strong> pro všechny členy.</li>
            <li>Skupiny s členy vytvoříš ve
                <a href="/workplace/<?= (int)$workplace['id'] ?>/parasitology-groups">správě skupin</a>
                (ideálně před importem).</li>
            <li>Každý řádek výsledku (metoda + nález + intenzita) = jedno vyšetření. Duplicitní záznamy se při opakovaném importu přeskočí.</li>
        </ul>
    </div>

    <a href="/workplace/<?= (int)$workplace['id'] ?>/animals" class="btn btn-outline">← Zpět na zvířata</a>
</div>

<style>
.pgi-wrap { max-width: 900px; margin: 0 auto; padding: 20px; }
.pgi-wrap .page-header h1 { margin: 6px 0; color: #2c3e50; }
.pgi-wrap .subtitle { color: #7f8c8d; margin: 4px 0 12px; }
.pgi-wrap .breadcrumb { color: #7f8c8d; font-size: 14px; margin-bottom: 6px; }
.pgi-wrap .breadcrumb a { color: #2c3e50; text-decoration: none; }
.pgi-card { background:#fff; border-radius:10px; padding:18px 20px; margin-bottom:18px; box-shadow:0 2px 8px rgba(0,0,0,.08); }
.pgi-card h2 { margin-top: 0; color:#2c3e50; font-size: 18px; }
.pgi-card ul { margin: 6px 0; padding-left: 20px; line-height: 1.6; }
.alert-error { background:#f8d7da; border:1px solid #f5c6cb; color:#721c24; padding:14px; border-radius:8px; margin-bottom:16px; }
.alert-success { background:#d4edda; border:1px solid #c3e6cb; color:#155724; padding:14px; border-radius:8px; margin-bottom:16px; }
</style>
