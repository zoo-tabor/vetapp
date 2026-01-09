<div class="container">
    <div class="page-header">
        <h1>Náhled importu</h1>
        <p class="breadcrumb">
            <a href="/biochemistry">Biochemie a hematologie</a> /
            <a href="/biochemistry/import">Import</a> /
            Náhled
        </p>
    </div>

    <?php if (isset($_SESSION['error'])): ?>
        <div class="alert alert-error">
            <?= htmlspecialchars($_SESSION['error']) ?>
            <?php unset($_SESSION['error']); ?>
        </div>
    <?php endif; ?>

    <?php
    $totalRows = count($data);
    $validRows = count(array_filter($data, fn($row) => empty($row['errors'])));
    $errorRows = $totalRows - $validRows;
    $warningRows = count(array_filter($data, fn($row) => !empty($row['warnings']) && empty($row['errors'])));
    ?>

    <div class="card">
        <div class="card-header">
            <h2>📊 Souhrn</h2>
        </div>
        <div class="card-body">
            <div class="stats-grid">
                <div class="stat-card">
                    <div class="stat-value"><?= $totalRows ?></div>
                    <div class="stat-label">Celkem řádků</div>
                </div>
                <div class="stat-card">
                    <div class="stat-value" style="color: #27ae60;"><?= $validRows ?></div>
                    <div class="stat-label">Platných</div>
                </div>
                <div class="stat-card">
                    <div class="stat-value" style="color: #f39c12;"><?= $warningRows ?></div>
                    <div class="stat-label">S varováními</div>
                </div>
                <div class="stat-card">
                    <div class="stat-value" style="color: #e74c3c;"><?= $errorRows ?></div>
                    <div class="stat-label">S chybami</div>
                </div>
            </div>

            <?php if ($errorRows === 0): ?>
                <div class="alert alert-success" style="margin-top: 1.5rem;">
                    ✅ Všechna data jsou platná a připravena k importu!
                </div>

                <form action="/biochemistry/import/execute" method="POST" style="margin-top: 1rem;">
                    <button type="submit" class="btn btn-success btn-lg">
                        ✔️ Potvrdit a importovat <?= $validRows ?> záznamů
                    </button>
                    <a href="/biochemistry/import" class="btn btn-outline">
                        ← Zrušit
                    </a>
                </form>
            <?php else: ?>
                <div class="alert alert-error" style="margin-top: 1.5rem;">
                    ❌ Data obsahují <?= $errorRows ?> chyb. Opravte je prosím v souboru a nahrajte znovu.
                </div>
                <a href="/biochemistry/import" class="btn btn-primary">
                    ← Zpět na import
                </a>
            <?php endif; ?>
        </div>
    </div>

    <div class="card">
        <div class="card-header">
            <h2>📋 Náhled dat</h2>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Status</th>
                            <th>Kód zvířete</th>
                            <th>Jméno zvířete</th>
                            <th>Typ testu</th>
                            <th>Datum</th>
                            <th>Parametr</th>
                            <th>Hodnota</th>
                            <th>Jednotka</th>
                            <th>Zprávy</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($data as $row): ?>
                            <tr class="<?= !empty($row['errors']) ? 'row-error' : (!empty($row['warnings']) ? 'row-warning' : 'row-success') ?>">
                                <td><?= $row['row_number'] ?></td>
                                <td>
                                    <?php if (!empty($row['errors'])): ?>
                                        <span class="badge badge-danger">❌ Chyba</span>
                                    <?php elseif (!empty($row['warnings'])): ?>
                                        <span class="badge badge-warning">⚠️ Varování</span>
                                    <?php else: ?>
                                        <span class="badge badge-success">✅ OK</span>
                                    <?php endif; ?>
                                </td>
                                <td><?= htmlspecialchars($row['animal_code'] ?? '') ?></td>
                                <td><?= htmlspecialchars($row['animal_name'] ?? '-') ?></td>
                                <td>
                                    <?php if (!empty($row['test_type'])): ?>
                                        <?= $row['test_type'] === 'biochemistry' ? '🧪 Biochemie' : '🩸 Hematologie' ?>
                                    <?php else: ?>
                                        -
                                    <?php endif; ?>
                                </td>
                                <td><?= htmlspecialchars($row['test_date'] ?? '') ?></td>
                                <td><strong><?= htmlspecialchars($row['parameter_name'] ?? '') ?></strong></td>
                                <td><?= htmlspecialchars($row['value'] ?? '') ?></td>
                                <td><?= htmlspecialchars($row['unit'] ?? '') ?></td>
                                <td>
                                    <?php if (!empty($row['errors'])): ?>
                                        <ul style="margin: 0; padding-left: 1.2rem; color: #e74c3c;">
                                            <?php foreach ($row['errors'] as $error): ?>
                                                <li><?= htmlspecialchars($error) ?></li>
                                            <?php endforeach; ?>
                                        </ul>
                                    <?php endif; ?>
                                    <?php if (!empty($row['warnings'])): ?>
                                        <ul style="margin: 0; padding-left: 1.2rem; color: #f39c12;">
                                            <?php foreach ($row['warnings'] as $warning): ?>
                                                <li><?= htmlspecialchars($warning) ?></li>
                                            <?php endforeach; ?>
                                        </ul>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <style>
        .row-error {
            background-color: #fee !important;
        }
        .row-warning {
            background-color: #fff3cd !important;
        }
        .row-success {
            background-color: #e8f5e9 !important;
        }
        .row-error:hover,
        .row-warning:hover,
        .row-success:hover {
            filter: brightness(0.95);
        }
    </style>
</div>
