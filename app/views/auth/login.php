<!DOCTYPE html>
<html lang="cs">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Přihlášení - VetApp ZOO Tábor</title>
    <link rel="stylesheet" href="/assets/css/style.css">
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
    <div class="login-container">
        <div class="login-box">
            <div class="logo-container">
                <img src="/assets/logo.png" alt="ZOO Tábor" class="login-logo">
            </div>
            <h1>VetApp <span style="white-space: nowrap;">ZOO Tábor</span></h1>
            <p class="login-subtitle">Přihlaste se pro pokračování</p>
            
            <?php if (isset($error)): ?>
                <div class="alert alert-error">
                    <?= htmlspecialchars($error) ?>
                </div>
            <?php endif; ?>
            
            <form method="POST" action="/login" class="login-form">
                <div class="form-group">
                    <label for="username">Uživatelské jméno</label>
                    <input 
                        type="text" 
                        id="username" 
                        name="username" 
                        required 
                        autofocus
                        class="form-control"
                    >
                </div>
                
                <div class="form-group">
                    <label for="password">Heslo</label>
                    <input 
                        type="password" 
                        id="password" 
                        name="password" 
                        required
                        class="form-control"
                    >
                </div>
                
                <button type="submit" class="btn btn-primary btn-block">
                    Přihlásit se
                </button>
            </form>
        </div>
    </div>
</body>
</html>