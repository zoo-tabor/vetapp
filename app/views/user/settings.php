<div class="container">
    <div class="page-header">
        <h1>⚙️ Moje nastavení</h1>
    </div>

    <?php if (isset($errors) && !empty($errors)): ?>
        <div class="alert alert-danger">
            <strong>Chyba:</strong>
            <ul style="margin: 10px 0 0 0; padding-left: 20px;">
                <?php foreach ($errors as $error): ?>
                    <li><?= htmlspecialchars($error) ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php endif; ?>

    <?php if (isset($success)): ?>
        <div class="alert alert-success">
            <?= htmlspecialchars($success) ?>
        </div>
    <?php endif; ?>

    <div class="card" style="max-width: 600px;">
        <div class="card-header">
            <h3>Osobní údaje</h3>
        </div>
        <div class="card-body">
            <form method="POST" action="/user/settings/update">
                <div class="form-group">
                    <label>Uživatelské jméno</label>
                    <input type="text" class="form-control" value="<?= htmlspecialchars($user['username']) ?>" disabled>
                    <small class="form-text">Uživatelské jméno nelze změnit</small>
                </div>

                <div class="form-group">
                    <label>Celé jméno *</label>
                    <input type="text" name="full_name" class="form-control"
                           value="<?= htmlspecialchars($old_full_name ?? $user['full_name']) ?>"
                           required>
                </div>

                <hr style="margin: 30px 0;">

                <h4 style="margin-bottom: 20px;">Změna hesla</h4>
                <p style="color: #666; font-size: 14px; margin-bottom: 20px;">
                    Pokud nechcete měnit heslo, ponechte pole prázdná
                </p>

                <div class="form-group">
                    <label>Současné heslo</label>
                    <input type="password" name="current_password" class="form-control"
                           autocomplete="current-password">
                </div>

                <div class="form-group">
                    <label>Nové heslo</label>
                    <input type="password" name="new_password" class="form-control"
                           autocomplete="new-password">
                    <small class="form-text">Minimálně 6 znaků</small>
                </div>

                <div class="form-group">
                    <label>Potvrzení nového hesla</label>
                    <input type="password" name="new_password_confirm" class="form-control"
                           autocomplete="new-password">
                </div>

                <div class="form-actions" style="margin-top: 30px;">
                    <button type="submit" class="btn btn-primary">💾 Uložit změny</button>
                    <a href="/" class="btn btn-outline">Zrušit</a>
                </div>
            </form>
        </div>
    </div>
</div>

<style>
.form-group {
    margin-bottom: 20px;
}

.form-group label {
    display: block;
    margin-bottom: 5px;
    font-weight: 500;
    color: #2c3e50;
}

.form-control {
    width: 100%;
    padding: 10px;
    border: 1px solid #ddd;
    border-radius: 4px;
    font-size: 14px;
}

.form-control:disabled {
    background-color: #f5f5f5;
    cursor: not-allowed;
}

.form-control:focus {
    outline: none;
    border-color: #667eea;
    box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
}

.form-text {
    display: block;
    margin-top: 5px;
    font-size: 12px;
    color: #666;
}

.form-actions {
    display: flex;
    gap: 10px;
}

.alert {
    padding: 15px 20px;
    border-radius: 6px;
    margin-bottom: 20px;
}

.alert-danger {
    background-color: #fee;
    border: 1px solid #fcc;
    color: #c33;
}

.alert-success {
    background-color: #d4edda;
    border: 1px solid #c3e6cb;
    color: #155724;
}
</style>
