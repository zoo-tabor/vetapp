<div class="container">
    <div class="page-header">
        <div>
            <h1>Analýza moči</h1>
            <p>Správa vyšetření moči zvířat</p>
        </div>
        <?php if (Auth::isAdmin()): ?>
            <div>
                <a href="/urineanalysis/reference-ranges" class="btn btn-primary">
                    Správa referenčních hodnot
                </a>
            </div>
        <?php endif; ?>
    </div>

    <?php if (empty($workplaces)): ?>
        <div class="alert alert-info">
            <strong>Žádná pracoviště</strong><br>
            Nemáte přiřazena žádná pracoviště. Kontaktujte administrátora.
        </div>
    <?php else: ?>
        <div class="workplaces-grid">
            <?php foreach ($workplaces as $workplace): ?>
                <a href="/urineanalysis/workplace/<?= $workplace['id'] ?>" class="workplace-card">
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
    border-top: 4px solid #f39c12;
}

.workplace-card:hover {
    transform: translateY(-4px);
    box-shadow: 0 4px 16px rgba(243, 156, 18, 0.3);
}

.workplace-card h3 {
    margin: 0 0 15px 0;
    color: #f39c12;
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

.btn {
    padding: 10px 20px;
    border-radius: 4px;
    text-decoration: none;
    display: inline-block;
    font-size: 14px;
    cursor: pointer;
    border: none;
    transition: all 0.2s;
}

.btn-primary {
    background: linear-gradient(135deg, #f39c12 0%, #e67e22 100%);
    color: white;
}

.btn-primary:hover {
    background: linear-gradient(135deg, #e67e22 0%, #d35400 100%);
    transform: translateY(-1px);
    box-shadow: 0 4px 12px rgba(243, 156, 18, 0.3);
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
