<div class="container">
    <div class="page-header">
        <div>
            <h1>Sklad</h1>
            <p>Správa zásob krmiv a léčiv</p>
        </div>
    </div>

    <?php if (empty($workplaces)): ?>
        <div class="alert alert-info">
            <strong>Žádná pracoviště</strong><br>
            Nemáte přiřazena žádná pracoviště. Kontaktujte administrátora.
        </div>
    <?php else: ?>
        <!-- Central Warehouse Card -->
        <div class="section-title">
            <h2>Centrální sklad</h2>
            <p class="subtitle">Společné zásoby pro všechna pracoviště</p>
        </div>

        <div class="workplaces-grid" style="margin-bottom: 40px;">
            <a href="/warehouse/central" class="workplace-card central-warehouse">
                <div class="card-icon">🏭</div>
                <h3>Centrální sklad</h3>
                <div class="workplace-meta">
                    <span class="badge badge-primary">Hlavní sklad</span>
                </div>
            </a>
        </div>

        <!-- Workplace Warehouses -->
        <div class="section-title">
            <h2>Sklady pracovišť</h2>
            <p class="subtitle">Zásoby jednotlivých pracovišť</p>
        </div>

        <div class="workplaces-grid">
            <?php foreach ($workplaces as $workplace): ?>
                <a href="/warehouse/workplace/<?= $workplace['id'] ?>" class="workplace-card">
                    <div class="card-icon">📦</div>
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

.section-title {
    margin: 30px 0 20px 0;
}

.section-title h2 {
    margin: 0 0 5px 0;
    color: #2c3e50;
    font-size: 20px;
}

.subtitle {
    margin: 0;
    color: #7f8c8d;
    font-size: 14px;
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
    border-top: 4px solid #27ae60;
}

.workplace-card:hover {
    transform: translateY(-4px);
    box-shadow: 0 4px 16px rgba(39, 174, 96, 0.3);
}

.workplace-card.central-warehouse {
    border-top-color: #3498db;
    background: linear-gradient(135deg, #ffffff 0%, #ecf0f1 100%);
}

.workplace-card.central-warehouse:hover {
    box-shadow: 0 4px 16px rgba(52, 152, 219, 0.3);
}

.card-icon {
    font-size: 48px;
    margin-bottom: 15px;
}

.workplace-card h3 {
    margin: 0 0 15px 0;
    color: #27ae60;
    font-size: 20px;
    font-weight: 600;
}

.workplace-card.central-warehouse h3 {
    color: #3498db;
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

.badge-primary {
    background-color: #cce5ff;
    color: #004085;
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
</style>
