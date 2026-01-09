<?php include __DIR__ . '/header.php'; ?>

<main class="main-content">
    <?php if (isset($hideFooter) && $hideFooter): ?>
        <?= $content ?>
    <?php else: ?>
        <div class="container">
            <?= $content ?>
        </div>
    <?php endif; ?>
</main>

<?php if (!isset($hideFooter) || !$hideFooter): ?>
    <?php include __DIR__ . '/footer.php'; ?>
<?php endif; ?>