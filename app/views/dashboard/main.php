<?php $layout = 'main'; ?>

<div class="container">
    <div class="page-header">
        <div>
            <h1>Přehled pracovišť</h1>
            <p>Vyberte pracoviště pro zobrazení detailů a práci s daty</p>
        </div>
    </div>

    <?php if (empty($workplaces)): ?>
        <div class="alert alert-info">
            <strong>Nemáte přístup k žádnému pracovišti.</strong><br>
            Kontaktujte administrátora pro přidělení oprávnění.
        </div>
    <?php else: ?>
        <div class="workplaces-grid">
            <?php
            // Map workplace names to codes
            $workplaceCodes = [
                'ZOO Tábor' => 'ZOO',
                'Babice' => 'BAB',
                'Lipence' => 'LIP',
                'Deponace' => 'DEP'
            ];

            foreach ($workplaces as $workplace):
                $code = $workplaceCodes[$workplace['name']] ?? substr(strtoupper($workplace['name']), 0, 3);
            ?>
                <a href="/workplace/<?= $workplace['id'] ?>" class="workplace-card">
                    <div class="card-header">
                        <span class="workplace-code"><?= $code ?></span>
                    </div>
                    <div class="card-body">
                        <h3><?= htmlspecialchars($workplace['name']) ?></h3>
                        <p class="workplace-subtitle">
                            <?php if ($workplace['name'] == 'ZOO Tábor'): ?>
                                Hlavní pracoviště - Zoologická zahrada Praha
                            <?php elseif ($workplace['name'] == 'Babice'): ?>
                                První deponované pracoviště
                            <?php elseif ($workplace['name'] == 'Lipence'): ?>
                                Druhé deponované pracoviště
                            <?php else: ?>
                                Zvířata darovaná nebo zapůjčená mimo organizaci
                            <?php endif; ?>
                        </p>
                        <div class="workplace-meta">
                            <?php if ($workplace['can_edit']): ?>
                                <span class="badge badge-success">Editace</span>
                            <?php else: ?>
                                <span class="badge badge-info">Čtení</span>
                            <?php endif; ?>
                        </div>
                    </div>
                    <div class="card-footer">
                        <button class="btn-open">Otevřít pracoviště</button>
                    </div>
                </a>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
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

.workplaces-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
    gap: 24px;
    margin-top: 20px;
}

.workplace-card {
    background: white;
    border-radius: 16px;
    overflow: hidden;
    text-decoration: none;
    color: inherit;
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
    transition: all 0.3s ease;
    display: flex;
    flex-direction: column;
}

.workplace-card:hover {
    transform: translateY(-8px);
    box-shadow: 0 8px 24px rgba(0, 0, 0, 0.15);
}

.card-header {
    padding: 40px 24px;
    color: white;
    position: relative;
    display: flex;
    justify-content: center;
    align-items: center;
    background: linear-gradient(135deg, #5e72e4 0%, #825ee4 100%);
}

.workplace-code {
    font-size: 32px;
    font-weight: 700;
    letter-spacing: 2px;
}

.card-body {
    padding: 24px;
    flex: 1;
}

.workplace-card h3 {
    margin: 0 0 10px 0;
    color: #2c3e50;
    font-size: 22px;
    font-weight: 600;
}

.workplace-subtitle {
    margin: 0 0 16px 0;
    color: #7f8c8d;
    font-size: 14px;
    line-height: 1.5;
}

.workplace-meta {
    display: flex;
    gap: 8px;
    flex-wrap: wrap;
}

.badge {
    display: inline-block;
    padding: 5px 14px;
    border-radius: 20px;
    font-size: 12px;
    font-weight: 600;
}

.badge-success {
    background-color: #d4edda;
    color: #155724;
}

.badge-info {
    background-color: #d1ecf1;
    color: #0c5460;
}

.card-footer {
    padding: 16px 24px;
    background: #f8f9fa;
    border-top: 1px solid #ecf0f1;
}

.btn-open {
    width: 100%;
    padding: 12px;
    background: linear-gradient(135deg, #5e72e4 0%, #825ee4 100%);
    color: white;
    border: none;
    border-radius: 8px;
    font-size: 14px;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.2s ease;
}

.btn-open:hover {
    background: linear-gradient(135deg, #4c63d2 0%, #7046d2 100%);
    transform: translateY(-2px);
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

@media (max-width: 768px) {
    .workplaces-grid {
        grid-template-columns: 1fr;
    }
}
</style>
