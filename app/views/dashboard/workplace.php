<?php $layout = 'main'; ?>

<div class="page-header">
    <div class="breadcrumb">
        <a href="/">Pracoviště</a> / <?= htmlspecialchars($workplace['name']) ?>
    </div>
    <h1><?= htmlspecialchars($workplace['name']) ?></h1>
</div>

<div class="stats-grid">
    <div class="stat-card">
        <div class="stat-value"><?= $stats['total_animals'] ?? 0 ?></div>
        <div class="stat-label">Celkem zvířat</div>
    </div>
    <div class="stat-card">
        <div class="stat-value"><?= $stats['active_animals'] ?? 0 ?></div>
        <div class="stat-label">Aktivních zvířat</div>
    </div>
    <div class="stat-card">
        <div class="stat-value"><?= $stats['total_examinations'] ?? 0 ?></div>
        <div class="stat-label">Vyšetření celkem</div>
    </div>
    <div class="stat-card">
        <div class="stat-value"><?= $stats['recent_examinations'] ?? 0 ?></div>
        <div class="stat-label">Vyšetření (30 dní)</div>
    </div>
</div>

<div class="action-cards">
    <div class="action-card">
        <h3>📋 Přehled zvířat</h3>
        <p>Zobrazit seznam všech zvířat v tomto pracovišti</p>
        <a href="/workplace/<?= $workplace['id'] ?>/animals" class="btn btn-primary">
            Zobrazit zvířata
        </a>
    </div>
    
    <?php if ($canEdit): ?>
    <div class="action-card">
        <h3>➕ Přidat zvíře</h3>
        <p>Zaregistrovat nové zvíře do evidence</p>
        <a href="/workplace/<?= $workplace['id'] ?>/animals/create" class="btn btn-success">
            Přidat zvíře
        </a>
    </div>
    <?php endif; ?>
    
    <div class="action-card">
        <h3>🔍 Vyhledávání</h3>
        <p>Pokročilé vyhledávání podle parazitů a dalších kritérií</p>
        <a href="/workplace/<?= $workplace['id'] ?>/search" class="btn btn-outline">
            Vyhledávat
        </a>
    </div>
</div>

<?php if (!empty($enclosures)): ?>
<div class="section">
    <h2 style="margin-left: 20px;">Výběhy</h2>
    <div class="table-responsive">
        <table class="table">
            <thead>
                <tr>
                    <th>Název</th>
                    <th>Kód</th>
                    <th>Poznámky</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($enclosures as $enclosure): ?>
                <tr>
                    <td><?= htmlspecialchars($enclosure['name']) ?></td>
                    <td><?= htmlspecialchars($enclosure['code'] ?? '-') ?></td>
                    <td><?= htmlspecialchars($enclosure['notes'] ?? '-') ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>
<?php endif; ?>