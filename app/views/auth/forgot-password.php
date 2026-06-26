<!DOCTYPE html>
<html lang="cs">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Obnova hesla - VetApp ZOO Tábor</title>
    <link rel="stylesheet" href="/assets/css/style.css">
</head>
<body class="login-page">
    <div class="background">
        <span></span><span></span><span></span><span></span><span></span>
        <span></span><span></span><span></span><span></span><span></span>
        <span></span><span></span><span></span><span></span><span></span>
        <span></span><span></span><span></span><span></span><span></span>
    </div>
    <div class="login-container">
        <div class="login-box">
            <div class="logo-container">
                <img src="/assets/logo.png" alt="ZOO Tábor" class="login-logo">
            </div>
            <h1>Obnova hesla</h1>
            <p class="login-subtitle">Zadejte své uživatelské jméno a pošleme vám odkaz pro nastavení nového hesla.</p>

            <?php if (isset($success)): ?>
                <div class="alert alert-success">
                    <?= htmlspecialchars($success) ?>
                </div>
                <p style="text-align:center;margin-top:18px">
                    <a href="/login">← Zpět na přihlášení</a>
                </p>
            <?php else: ?>
                <?php if (isset($error)): ?>
                    <div class="alert alert-error"><?= htmlspecialchars($error) ?></div>
                <?php endif; ?>

                <form method="POST" action="/forgot-password" class="login-form">
                    <div class="form-group">
                        <label for="username">Uživatelské jméno</label>
                        <input type="text" id="username" name="username" required autofocus class="form-control">
                    </div>

                    <button type="submit" class="btn btn-primary btn-block">
                        Odeslat odkaz pro obnovu
                    </button>
                </form>

                <p style="text-align:center;margin-top:18px">
                    <a href="/login">← Zpět na přihlášení</a>
                </p>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>
