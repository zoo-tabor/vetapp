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
    $skippedRows = count(array_filter($data, fn($row) => !empty($row['skip'])));
    $validRows = count(array_filter($data, fn($row) => empty($row['skip']) && empty($row['errors'])));
    $errorRows = count(array_filter($data, fn($row) => empty($row['skip']) && !empty($row['errors'])));
    $warningRows = count(array_filter($data, fn($row) => empty($row['skip']) && !empty($row['warnings']) && empty($row['errors'])));
    $testKeys = [];
    foreach ($data as $row) {
        if (!empty($row['skip']) || !empty($row['errors'])) {
            continue;
        }
        $testKeys[($row['animal_id'] ?? '') . '_' . ($row['test_type'] ?? '') . '_' . ($row['test_date'] ?? '') . '_' . ($row['ldt_protocol'] ?? '')] = true;
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
                    <div class="stat-value" style="color: #7f8c8d;"><?= $skippedRows ?></div>
                    <div class="stat-label">Preskoceno</div>
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
                <p>LDT zvire se nepodarilo automaticky najit. Zacnete psat jmeno, ID nebo druh, vyberte zvire z nabidky a nahled se prepocita.</p>

                <datalist id="importAnimalsList">
                    <?php foreach ($animals as $animal): ?>
                        <?php
                        $animalLabel = trim(
                            ($animal['name'] !== '' && $animal['name'] !== null ? $animal['name'] : '(bez jmena)')
                            . (!empty($animal['identifier']) ? ' (' . $animal['identifier'] . ')' : '')
                            . (!empty($animal['species']) ? ' - ' . $animal['species'] : '')
                            . (!empty($animal['workplace_name']) ? ' / ' . $animal['workplace_name'] : '')
                        );
                        ?>
                        <option data-id="<?= (int)$animal['id'] ?>" value="<?= htmlspecialchars($animalLabel) ?>"></option>
                    <?php endforeach; ?>
                </datalist>

                <?php foreach ($animalAssignmentGroups as $group): ?>
                    <form action="/biochemistry/import/assign-animal" method="POST" class="animal-assignment-form" onsubmit="return prepareAnimalAssign(this)">
                        <input type="hidden" name="assignment_key" value="<?= htmlspecialchars($group['key']) ?>">
                        <input type="hidden" name="animal_id" value="">

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
                            <input type="text" class="form-control animal-search" list="importAnimalsList"
                                   placeholder="Hledat zvire (jmeno, ID, druh)..." autocomplete="off" required>
                            <button type="submit" class="btn btn-primary">Priradit zvire</button>
                        </div>
                    </form>
                <?php endforeach; ?>
            </div>
        </div>
    <?php endif; ?>

    <?php if (!empty($parameterAssignmentGroups)): ?>
        <div class="card">
            <div class="card-header">
                <h2>Sparovani parametru</h2>
            </div>
            <div class="card-body">
                <p>Nasledujici parametry z LDT nejsou v ciselniku. Naparujte je na existujici parametr (aby nevznikal duplikat), zalozte je jako novy, nebo je <strong>preskocte</strong> (napr. naklady za kuryra). Volba se ulozi jako alias a priste probehne automaticky.</p>

                <?php foreach ($parameterAssignmentGroups as $group): ?>
                    <form action="/biochemistry/import/assign-parameter" method="POST" class="param-assignment-form">
                        <input type="hidden" name="test_type" value="<?= htmlspecialchars($group['test_type']) ?>">
                        <input type="hidden" name="parameter_name_ldt" value="<?= htmlspecialchars($group['parameter_name_ldt']) ?>">
                        <input type="hidden" name="unit" value="<?= htmlspecialchars($group['unit']) ?>">

                        <div class="assignment-summary">
                            <strong><?= htmlspecialchars($group['parameter_name_ldt']) ?></strong>
                            <span title="Sekce odhadnutá z LDT – můžete ji změnou vybraného parametru přepsat">navrženo: <?= $group['test_type'] === 'biochemistry' ? 'Biochemie' : 'Hematologie' ?></span>
                            <?php if (!empty($group['unit'])): ?>
                                <span>Jednotka: <?= htmlspecialchars($group['unit']) ?></span>
                            <?php endif; ?>
                            <span><?= (int)$group['row_count'] ?> radku</span>
                        </div>

                        <div class="assignment-controls">
                            <select name="parameter_id" class="form-control" required>
                                <option value="">-- Vyberte parametr z ciselniku --</option>
                                <option value="__new_biochemistry__">➕ Zalozit jako novy BIOCHEMICKY parametr "<?= htmlspecialchars($group['parameter_name_ldt']) ?>"</option>
                                <option value="__new_hematology__">➕ Zalozit jako novy HEMATOLOGICKY parametr "<?= htmlspecialchars($group['parameter_name_ldt']) ?>"</option>
                                <optgroup label="Biochemie">
                                    <?php foreach (($biochemParamList ?? []) as $opt): ?>
                                        <option value="<?= (int)$opt['id'] ?>">
                                            <?= htmlspecialchars($opt['name']) ?><?= !empty($opt['unit']) ? ' (' . htmlspecialchars($opt['unit']) . ')' : '' ?>
                                        </option>
                                    <?php endforeach; ?>
                                </optgroup>
                                <optgroup label="Hematologie">
                                    <?php foreach (($hematoParamList ?? []) as $opt): ?>
                                        <option value="<?= (int)$opt['id'] ?>">
                                            <?= htmlspecialchars($opt['name']) ?><?= !empty($opt['unit']) ? ' (' . htmlspecialchars($opt['unit']) . ')' : '' ?>
                                        </option>
                                    <?php endforeach; ?>
                                </optgroup>
                            </select>
                            <button type="submit" class="btn btn-primary">Sparovat</button>
                            <button type="submit" class="btn btn-outline"
                                    formaction="/biochemistry/import/ignore-parameter" formnovalidate
                                    title="Tento parametr se nikdy neimportuje (ulozi se natrvalo)">
                                Preskocit
                            </button>
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
                            <tr class="<?= !empty($row['skip']) ? 'row-skip' : (!empty($row['errors']) ? 'row-error' : (!empty($row['warnings']) ? 'row-warning' : 'row-success')) ?>">
                                <td><?= $row['row_number'] ?></td>
                                <td>
                                    <?php if (!empty($row['skip'])): ?>
                                        <span class="badge badge-skip">Preskoceno</span>
                                    <?php elseif (!empty($row['errors'])): ?>
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
                                    <?php if (!empty($row['skip'])): ?>
                                        <span style="color: #7f8c8d;">Preskoceno – neimportuje se.</span>
                                    <?php endif; ?>
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
        .row-skip {
            background-color: #f2f3f4 !important;
            color: #7f8c8d;
        }
        .row-skip td strong {
            color: #7f8c8d;
            font-weight: normal;
            text-decoration: line-through;
        }
        .badge-skip {
            background-color: #95a5a6;
            color: #fff;
        }
        .row-error:hover,
        .row-warning:hover,
        .row-success:hover,
        .row-skip:hover {
            filter: brightness(0.97);
        }
        .animal-assignment-form,
        .param-assignment-form {
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
            grid-template-columns: minmax(220px, 1fr) auto auto;
            gap: 0.75rem;
            align-items: center;
        }
        @media (max-width: 700px) {
            .assignment-controls {
                grid-template-columns: 1fr;
            }
        }
    </style>

    <script>
        // Našeptávač zvířat: uživatel píše do textového pole s <datalist>,
        // před odesláním převedeme vybraný text na animal_id.
        function prepareAnimalAssign(form) {
            var input = form.querySelector('.animal-search');
            var hidden = form.querySelector('input[name="animal_id"]');
            var list = document.getElementById('importAnimalsList');
            var value = (input.value || '').trim();
            var matchedId = '';

            if (list) {
                var opts = list.options;
                for (var i = 0; i < opts.length; i++) {
                    if (opts[i].value === value) {
                        matchedId = opts[i].getAttribute('data-id');
                        break;
                    }
                }
            }

            if (!matchedId) {
                alert('Vyberte zvire ze seznamu – zacnete psat a vyberte polozku z nabidky.');
                input.focus();
                return false;
            }

            hidden.value = matchedId;
            return true;
        }
    </script>
</div>
