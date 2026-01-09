<!DOCTYPE html>
<html lang="cs">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Nastavení hesla - VetApp</title>
    <link rel="stylesheet" href="/assets/css/style.css">
    <style>
        .setup-container {
            width: 100%;
            max-width: 450px;
            padding: 20px;
            position: relative;
            z-index: 1;
        }

        .setup-box {
            background: white;
            padding: 2.5rem;
            border-radius: 10px;
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.2);
        }

        .setup-header {
            text-align: center;
            margin-bottom: 30px;
        }

        .setup-header h1 {
            color: #2c3e50;
            margin: 0 0 10px 0;
            font-size: 28px;
        }

        .setup-header p {
            color: #7f8c8d;
            margin: 0;
        }

        .login-link {
            text-align: center;
            margin-top: 20px;
        }

        .login-link a {
            color: #3498db;
            text-decoration: none;
            font-weight: 600;
        }

        .login-link a:hover {
            text-decoration: underline;
        }

        .password-requirements {
            background-color: #f8f9fa;
            border-left: 4px solid #3498db;
            padding: 12px;
            margin-top: 15px;
            border-radius: 4px;
        }

        .password-requirements h4 {
            margin: 0 0 8px 0;
            font-size: 14px;
            color: #2c3e50;
        }

        .password-requirements ul {
            margin: 0;
            padding-left: 20px;
        }

        .password-requirements li {
            font-size: 13px;
            color: #666;
            margin-bottom: 4px;
        }
    </style>
</head>
<body class="login-page">
    <div class="background">
        <span></span>
        <span></span>
        <span></span>
        <span></span>
        <span></span>
        <span></span>
        <span></span>
        <span></span>
        <span></span>
        <span></span>
        <span></span>
        <span></span>
        <span></span>
        <span></span>
        <span></span>
        <span></span>
        <span></span>
        <span></span>
        <span></span>
        <span></span>
        <span></span>
        <span></span>
        <span></span>
        <span></span>
        <span></span>
        <span></span>
        <span></span>
        <span></span>
        <span></span>
        <span></span>
        <span></span>
        <span></span>
        <span></span>
        <span></span>
        <span></span>
        <span></span>
        <span></span>
        <span></span>
        <span></span>
        <span></span>
        <span></span>
        <span></span>
        <span></span>
        <span></span>
        <span></span>
        <span></span>
        <span></span>
        <span></span>
        <span></span>
        <span></span>
    </div>
    <div class="setup-container">
        <div class="setup-box">
            <div class="setup-header">
            <h1>🔐 Nastavení hesla</h1>
            <?php if (isset($full_name)): ?>
                <p>Vítejte, <?= htmlspecialchars($full_name) ?>!</p>
            <?php else: ?>
                <p>VetApp ZOO Tábor</p>
            <?php endif; ?>
        </div>

        <?php if (isset($error)): ?>
            <div class="alert alert-error">
                <?= htmlspecialchars($error) ?>
            </div>
        <?php endif; ?>

        <?php if (isset($success)): ?>
            <div class="alert alert-success">
                <?= htmlspecialchars($success) ?>
            </div>
            <div class="login-link">
                <a href="/login">→ Přejít na přihlášení</a>
            </div>
        <?php else: ?>
            <?php if (isset($token)): ?>
                <form method="POST" action="/setup-password" id="setupForm">
                    <input type="hidden" name="token" value="<?= htmlspecialchars($token) ?>">

                    <?php if (isset($username)): ?>
                        <div class="form-group">
                            <label>Uživatelské jméno:</label>
                            <input type="text" class="form-control" value="<?= htmlspecialchars($username) ?>" disabled>
                        </div>
                    <?php endif; ?>

                    <div class="form-group">
                        <label for="password">Nové heslo: *</label>
                        <input
                            type="password"
                            id="password"
                            name="password"
                            class="form-control"
                            required
                            minlength="6"
                            placeholder="Zadejte nové heslo">
                    </div>

                    <div class="form-group">
                        <label for="password_confirm">Potvrzení hesla: *</label>
                        <input
                            type="password"
                            id="password_confirm"
                            name="password_confirm"
                            class="form-control"
                            required
                            minlength="6"
                            placeholder="Zadejte heslo znovu">
                    </div>

                    <div class="password-requirements">
                        <h4>Požadavky na heslo:</h4>
                        <ul>
                            <li>Minimálně 6 znaků</li>
                            <li>Doporučujeme kombinaci písmen, číslic a speciálních znaků</li>
                        </ul>
                    </div>

                    <button type="submit" class="btn" style="margin-top: 20px;">Nastavit heslo</button>
                </form>

                <div class="login-link">
                    <a href="/login">← Zpět na přihlášení</a>
                </div>

                <script>
                    document.getElementById('setupForm').addEventListener('submit', function(e) {
                        const password = document.getElementById('password').value;
                        const passwordConfirm = document.getElementById('password_confirm').value;

                        if (password !== passwordConfirm) {
                            e.preventDefault();
                            alert('Hesla se neshodují. Zkontrolujte prosím zadaná hesla.');
                            return false;
                        }

                        if (password.length < 6) {
                            e.preventDefault();
                            alert('Heslo musí mít minimálně 6 znaků.');
                            return false;
                        }
                    });
                </script>
            <?php else: ?>
                <div class="login-link">
                    <a href="/login">← Zpět na přihlášení</a>
                </div>
            <?php endif; ?>
        <?php endif; ?>
        </div>
    </div>
</body>
</html>
