<?php $layout = 'main'; ?>

<div class="page-header">
    <h1>Přehled pracovišť</h1>
    <p>Vyberte pracoviště pro zobrazení detailů a práci s daty</p>
</div>

<?php if (empty($workplaces)): ?>
    <div class="alert alert-info">
        <strong>Nemáte přístup k žádnému pracovišti.</strong><br>
        Kontaktujte administrátora pro přidělení oprávnění.
    </div>
<?php else: ?>
    <div class="workplace-grid">
        <?php foreach ($workplaces as $workplace): ?>
            <div class="workplace-card">
                <div class="workplace-card-header">
                    <h3><?= htmlspecialchars($workplace['name']) ?></h3>
                    <span class="workplace-code"><?= htmlspecialchars($workplace['code']) ?></span>
                </div>
                
                <div class="workplace-card-body">
                    <?php if ($workplace['description']): ?>
                        <p><?= htmlspecialchars($workplace['description']) ?></p>
                    <?php endif; ?>
                    
                    <div class="workplace-permissions">
                        <?php if ($workplace['can_view']): ?>
                            <span class="badge badge-success">Čtení</span>
                        <?php endif; ?>
                        <?php if ($workplace['can_edit']): ?>
                            <span class="badge badge-primary">Editace</span>
                        <?php endif; ?>
                    </div>
                </div>
                
                <div class="workplace-card-footer">
                    <a href="/workplace/<?= $workplace['id'] ?>" class="btn btn-primary">
                        Otevřít pracoviště
                    </a>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
<?php endif; ?>