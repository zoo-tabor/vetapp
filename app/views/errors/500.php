<!DOCTYPE html>
<html lang="cs">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>500 – Chyba serveru</title>
    <link rel="stylesheet" href="/assets/css/style.css">
    <style>
        .error-page { display: flex; flex-direction: column; align-items: center; justify-content: center; min-height: 60vh; text-align: center; padding: 2rem; }
        .error-code { font-size: 6rem; font-weight: 700; color: #dc2626; line-height: 1; }
        .error-message { font-size: 1.25rem; color: #6b7280; margin: 1rem 0 2rem; }
    </style>
</head>
<body>
    <div class="error-page">
        <div class="error-code">500</div>
        <div class="error-message">Interní chyba serveru. Zkuste to prosím znovu.</div>
        <a href="/" class="btn btn-primary">← Zpět na hlavní stránku</a>
    </div>
</body>
</html>
