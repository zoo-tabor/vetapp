<div class="container">
    <div class="page-header">
        <div>
            <h1>Vakcinační plán</h1>
            <p>Přehled vakcinačních plánů podle pracovišť</p>
        </div>
    </div>

    <?php if (empty($workplaces)): ?>
        <div class="alert alert-info">
            <strong>Žádná pracoviště</strong><br>
            Nemáte přiřazena žádná pracoviště. Kontaktujte administrátora.
        </div>
    <?php else: ?>
        <div class="workplaces-grid">
            <?php
            // Map workplace names to emojis
            $workplaceEmojis = [
                'ZOO Tábor' => '🐯',
                'Babice' => '🧑🏻‍🌾',
                'Lipence' => '🐶',
                'Deponace' => '➡️'
            ];

            foreach ($workplaces as $workplace):
                $emoji = $workplaceEmojis[$workplace['name']] ?? '🏢';
            ?>
                <a href="/vaccination-plan/workplace/<?= $workplace['id'] ?>" class="workplace-card">
                    <div class="card-icon"><?= $emoji ?></div>
                    <h3><?= htmlspecialchars($workplace['name']) ?></h3>
                    <div class="workplace-meta">
                        <?php if ($workplace['can_edit']): ?>
                            <span class="badge badge-success">Editace</span>
                        <?php else: ?>
                            <span class="badge badge-info">Čtení</span>
                        <?php endif; ?>
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
    grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
    gap: 20px;
    margin-top: 20px;
}

.workplace-card {
    background: white;
    border-radius: 12px;
    padding: 30px;
    text-decoration: none;
    color: inherit;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
    transition: all 0.3s ease;
    text-align: center;
    border-top: 4px solid #16a085;
}

.workplace-card:hover {
    transform: translateY(-4px);
    box-shadow: 0 4px 16px rgba(22, 160, 133, 0.3);
}

.card-icon {
    font-size: 48px;
    margin-bottom: 15px;
}

.workplace-card h3 {
    margin: 0 0 15px 0;
    color: #16a085;
    font-size: 20px;
    font-weight: 600;
}

.workplace-meta {
    display: flex;
    justify-content: center;
    gap: 8px;
    flex-wrap: wrap;
}

.badge {
    display: inline-block;
    padding: 4px 12px;
    border-radius: 12px;
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
