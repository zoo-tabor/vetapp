<?php /* Generic error / access-denied page. Rendered inside a layout (main or print). */ ?>
<div style="max-width:620px;margin:60px auto;text-align:center;padding:0 20px">
    <div style="font-size:56px;line-height:1">⚠️</div>
    <h1 style="margin:14px 0 8px;color:#2c3e50">
        <?= htmlspecialchars($title ?? 'Chyba', ENT_QUOTES, 'UTF-8') ?>
    </h1>
    <p style="color:#555;font-size:16px;margin:0 0 22px">
        <?= htmlspecialchars($message ?? 'Došlo k neočekávané chybě.', ENT_QUOTES, 'UTF-8') ?>
    </p>
    <a href="/" style="display:inline-block;padding:10px 20px;border:2px solid #2c3e50;border-radius:6px;color:#2c3e50;text-decoration:none">← Zpět na úvod</a>
</div>
