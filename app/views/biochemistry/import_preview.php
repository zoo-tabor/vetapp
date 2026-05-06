<div class="container">
    <div class="page-header">
        <h1>Nahled LDT importu</h1>
        <p class="breadcrumb">
            <a href="/biochemistry">Biochemie a hematologie</a> /
            <a href="/biochemistry/import">Import LDT</a> /
            Nahled
        </p>
    </div>

    <?php if (isset($_SESSION['error'])): ?>
        <div class="alert alert-error">
            <?= htmlspecialchars($_SESSION['error']) ?>
            <?php unset($_SESSION['error']); ?>
        </div>
    <?php endif; ?>

    <?php if (isset($_SESSION['success'])): ?>
        <div class="alert alert-success">
            <?= htmlspecialchars($_SESSION['success']) ?>
            <?php unset($_SESSION['success']); ?>
        </div>
    <?php endif; ?>

    <?php
    $totalRows = count($data);
    $validRows = count(array_filter($data, fn($row) => empty($row['errors'])));
    $errorRows = $totalRows - $validRows;
    $warningRows = count(array_filter($data, fn($row) => !empty($row['warnings']) && empty($row['errors'])));
    $testKeys = [];
    foreach ($data as $row) {
        if (empty($row['errors'])) {
            $testKeys[($row['animal_id'] ?? '') . '_' . ($row['test_type'] ?? '') . '_' . ($row['test_date'] ?? '') . '_' . ($row['ldt_protocol'] ?? '')] = true;
        }
    }
    $testCount = count($testKeys);
    ?>

    <div class="card">
        <div class="card-header">
            <h2>Souhrn</h2>
        </div>
        <div class="card-body">
            <p><strong>Soubor:</strong> <?= htmlspecialchars($filename) ?></p>
            <div class="stats-grid">
                <div class="stat-card">
                    <div class="stat-value"><?= $totalRows ?></div>
                    <div class="stat-label">Parametru</div>
                </div>
                <div class="stat-card">
                    <div class="stat-value" style="color: #27ae60;"><?= $validRows ?></div>
                    <div class="stat-label">Platnych</div>
                </div>
                <div class="stat-card">
                    <div class="stat-value" style="color: #3498db;"><?= $testCount ?></div>
                    <div class="stat-label">Testu</div>
                </div>
                <div class="stat-card">
                    <div class="stat-value" style="color: #f39c12;"><?= $warningRows ?></div>
                    <div class="stat-label">S varovanim</div>
                </div>
                <div class="stat-card">
                    <div class="stat-value" style="color: #e74c3c;"><?= $errorRows ?></div>
                    <div class="stat-label">S chybami</div>
                </div>
            </div>

            <?php if ($errorRows === 0): ?>
                <div class="alert alert-success" style="margin-top: 1.5rem;">
                    LDT data jsou platna a pripravena k importu.
                </div>

                <form action="/biochemistry/import/execute" method="POST" style="margin-top: 1rem;">
                    <button type="submit" class="btn btn-success btn-lg">
                        Potvrdit a importovat <?= $testCount ?> testu
                    </button>
                    <a href="/biochemistry/import" class="btn btn-outline">
                        Zrusit
                    </a>
                </form>
            <?php else: ?>
                <div class="alert alert-error" style="margin-top: 1.5rem;">
                    Data obsahuji <?= $errorRows ?> chyb. Opravte je prosim v databazi nebo nahrajte spravny LDT soubor.
                </div>
                <a href="/biochemistry/import" class="btn btn-primary">
                    Zpet na import
                </a>
            <?php endif; ?>
        </div>
    </div>

    <?php if (!empty($animalAssignmentGroups)): ?>
        <div class="card">
            <div class="card-header">
                <h2>Rucni sparovani zvirete</h2>
            </div>
            <div class="card-body">
                <p>LDT zvire se nepodarilo automaticky najit. Vyberte spravne zvire z databaze a nahled se prepocita.</p>

                <?php foreach ($animalAssignmentGroups as $group): ?>
                    <form action="/biochemistry/import/assign-animal" method="POST" class="animal-assignment-form">
                        <input type="hidden" name="assignment_key" value="<?= htmlspecialchars($group['key']) ?>">

                        <div class="assignment-summary">
                            <strong><?= htmlspecialchars($group['animal_name_ldt'] ?: 'Bez jmena v LDT') ?></strong>
                            <?php if (!empty($group['animal_identifier_ldt'])): ?>
                                <span>ID: <?= htmlspecialchars($group['animal_identifier_ldt']) ?></span>
                            <?php endif; ?>
                            <?php if (!empty($group['animal_chip'])): ?>
                                <span>Cip: <?= htmlspecialchars($group['animal_chip']) ?></span>
                            <?php endif; ?>
                            <?php if (!empty($group['ldt_protocol'])): ?>
                                <span>Protokol: <?= htmlspecialchars($group['ldt_protocol']) ?></span>
                            <?php endif; ?>
                            <span><?= (int)$group['row_count'] ?> radku</span>
                        </div>

                        <div class="assignment-controls">
                            <select name="animal_id" class="form-control" required>
                                <option value="">-- Vyberte zvire z databaze --</option>
                                <?php foreach ($animals as $animal): ?>
                                    <option value="<?= (int)$animal['id'] ?>">
                                        <?= htmlspecialchars($animal['name']) ?>
                                        <?php if (!empty($animal['identifier'])): ?>
                                            (<?= htmlspecialchars($animal['identifier']) ?>)
                                        <?php endif; ?>
                                        - <?= htmlspecialchars($animal['species'] ?? '') ?>
                                        <?php if (!empty($animal['workplace_name'])): ?>
                                            / <?= htmlspecialchars($animal['workplace_name']) ?>
                                        <?php endif; ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                            <button type="submit" class="btn btn-primary">Priradit zvire</button>
                        </div>
                    </form>
                <?php endforeach; ?>
            </div>
        </div>
    <?php endif; ?>

    <div class="card">
        <div class="card-header">
            <h2>Nahled dat</h2>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Status</th>
                            <th>Protokol</th>
                            <th>Zvire v LDT</th>
                            <th>Zvire v databazi</th>
                            <th>Typ</th>
                            <th>Datum</th>
                            <th>Parametr</th>
                            <th>Hodnota</th>
                            <th>Jednotka</th>
                            <th>Ref. rozmezi</th>
                            <th>Zpravy</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($data as $row): ?>
                            <tr class="<?= !empty($row['errors']) ? 'row-error' : (!empty($row['warnings']) ? 'row-warning' : 'row-success') ?>">
                                <td><?= $row['row_number'] ?></td>
                                <td>
                                    <?php if (!empty($row['errors'])): ?>
                                        <span class="badge badge-danger">Chyba</span>
                                    <?php elseif (!empty($row['warnings'])): ?>
                                        <span class="badge badge-warning">Varovani</span>
                                    <?php else: ?>
                                        <span class="badge badge-success">OK</span>
                                    <?php endif; ?>
                                </td>
                                <td><?= htmlspecialchars($row['ldt_protocol'] ?? '') ?></td>
                                <td>
                                    <?= htmlspecialchars($row['animal_name_ldt'] ?? '') ?>
                                    <?php if (!empty($row['animal_identifier_ldt'])): ?>
                                        <br><small><?= htmlspecialchars($row['animal_identifier_ldt']) ?></small>
                                    <?php endif; ?>
                                    <?php if (!empty($row['animal_chip'])): ?>
                                        <br><small>Cip: <?= htmlspecialchars($row['animal_chip']) ?></small>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?= htmlspecialchars($row['animal_name'] ?? '-') ?>
                                    <?php if (!empty($row['animal_identifier'])): ?>
                                        <br><small><?= htmlspecialchars($row['animal_identifier']) ?></small>
                                    <?php endif; ?>
                                </td>
                                <td><?= ($row['test_type'] ?? '') === 'biochemistry' ? 'Biochemie' : 'Hematologie' ?></td>
                                <td><?= htmlspecialchars($row['test_date'] ?? '') ?></td>
                                <td><strong><?= htmlspecialchars($row['parameter_name'] ?? '') ?></strong></td>
                                <td><?= htmlspecialchars($row['value'] ?? '') ?></td>
                                <td><?= htmlspecialchars($row['unit'] ?? '') ?></td>
                                <td><?= htmlspecialchars($row['reference_range'] ?? '') ?></td>
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
        .animal-assignment-form {
            border: 1px solid #e0e6ed;
            border-radius: 6px;
            padding: 1rem;
            margin-top: 1rem;
        }
        .assignment-summary {
            display: flex;
            flex-wrap: wrap;
            gap: 0.75rem;
            margin-bottom: 0.75rem;
            color: #2c3e50;
        }
        .assignment-summary span {
            color: #6c757d;
        }
        .assignment-controls {
            display: grid;
            grid-template-columns: minmax(260px, 1fr) auto;
            gap: 0.75rem;
            align-items: center;
        }
        @media (max-width: 700px) {
            .assignment-controls {
                grid-template-columns: 1fr;
            }
        }
    </style>
</div>
