    <?php
    $currentApp = $_SESSION['current_app'] ?? 'parasitology';
    $footerStyle = '';
    if ($currentApp === 'animals') {
        $footerStyle = 'background: linear-gradient(135deg, #8e44ad 0%, #7d3c98 100%) !important;';
    } elseif ($currentApp === 'biochemistry') {
        $footerStyle = 'background: linear-gradient(135deg, #c0392b 0%, #a93226 100%) !important;';
    } elseif ($currentApp === 'urineanalysis') {
        $footerStyle = 'background: linear-gradient(135deg, #f39c12 0%, #e67e22 100%) !important;';
    } elseif ($currentApp === 'vaccination') {
        $footerStyle = 'background: linear-gradient(135deg, #3498db 0%, #2980b9 100%) !important;';
    } elseif ($currentApp === 'warehouse') {
        $footerStyle = 'background: linear-gradient(135deg, #27ae60 0%, #229954 100%) !important;';
    }
    ?>
    <footer class="footer <?= $currentApp === 'animals' ? 'animals' : ($currentApp === 'biochemistry' ? 'biochemistry' : ($currentApp === 'urineanalysis' ? 'urineanalysis' : ($currentApp === 'vaccination' ? 'vaccination' : ($currentApp === 'warehouse' ? 'warehouse' : '')))) ?>" style="<?= $footerStyle ?>">
        <div class="container">
            <p>&copy; 2026 VetApp</p>
        </div>
    </footer>
    
    <script src="/assets/js/app.js"></script>
</body>
</html>