<div class="container">
    <div class="breadcrumb">
        <a href="/biochemistry">Biochemie a hematologie</a> /
        <a href="/biochemistry/reference-ranges">Referenční hodnoty</a> /
        <span>Správa parametrů</span>
    </div>

    <?php if (isset($_SESSION['success'])): ?>
        <div class="alert alert-success"><?= htmlspecialchars($_SESSION['success']) ?></div>
        <?php unset($_SESSION['success']); ?>
    <?php endif; ?>
    <?php if (isset($_SESSION['error'])): ?>
        <div class="alert alert-error"><?= htmlspecialchars($_SESSION['error']) ?></div>
        <?php unset($_SESSION['error']); ?>
    <?php endif; ?>

    <div class="page-header">
        <h1>Správa parametrů</h1>
        <p>Pořadí (číslo <strong>Pořadí</strong>) určuje, jak se parametry řadí v tabulkách a tisku. Menší číslo = výš. Duplicitní parametry lze sloučit.</p>
    </div>

    <?php
    $sections = [
        'biochemistry' => ['label' => 'Biochemie', 'params' => $biochemParams],
        'hematology'   => ['label' => 'Hematologie', 'params' => $hematoParams],
    ];
    foreach ($sections as $testType => $section):
        $params = $section['params'];
    ?>
        <div class="param-section">
            <h2><?= htmlspecialchars($section['label']) ?></h2>

            <p class="params-hint">Tip: přetáhni řádek za ikonu ⠿ pro změnu pořadí. Čísla „Pořadí" se přepočítají, pak ulož.</p>
            <form method="POST" action="/biochemistry/parameters/save" class="params-form">
                <div class="table-responsive">
                    <table class="params-table">
                        <thead>
                            <tr>
                                <th style="width:32px;"></th>
                                <th style="width:90px;">Pořadí</th>
                                <th>Název parametru</th>
                                <th style="width:120px;">Jednotka</th>
                                <th style="width:90px;">Výsledků</th>
                            </tr>
                        </thead>
                        <tbody class="sortable">
                            <?php foreach ($params as $p): ?>
                                <tr draggable="true" class="param-row">
                                    <td class="drag-handle" title="Přetáhni pro změnu pořadí">⠿</td>
                                    <td>
                                        <input type="number" class="form-control ord"
                                               name="params[<?= (int)$p['id'] ?>][sort_order]"
                                               value="<?= (int)$p['sort_order'] ?>">
                                    </td>
                                    <td>
                                        <input type="text" class="form-control"
                                               name="params[<?= (int)$p['id'] ?>][name]"
                                               value="<?= htmlspecialchars($p['name']) ?>">
                                    </td>
                                    <td>
                                        <input type="text" class="form-control"
                                               name="params[<?= (int)$p['id'] ?>][unit]"
                                               value="<?= htmlspecialchars($p['unit']) ?>">
                                    </td>
                                    <td class="usage"><?= (int)($usage[(int)$p['id']] ?? 0) ?></td>
                                </tr>
                            <?php endforeach; ?>
                            <?php if (empty($params)): ?>
                                <tr><td colspan="5" class="empty">Zatím žádné parametry.</td></tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
                <div class="form-actions">
                    <button type="submit" class="btn btn-primary">Uložit pořadí a názvy</button>
                </div>
            </form>

            <div class="panel-grid">
                <!-- Sloučení duplicit -->
                <div class="sub-panel">
                    <h3>Sloučit duplicitní parametr</h3>
                    <form method="POST" action="/biochemistry/parameters/merge" class="inline-form"
                          onsubmit="return confirm('Opravdu sloučit? Výsledky se přesunou pod cílový parametr a zdrojový parametr se smaže.');">
                        <select name="source_id" class="form-control" required>
                            <option value="">-- Sloučit tento --</option>
                            <?php foreach ($params as $p): ?>
                                <option value="<?= (int)$p['id'] ?>"><?= htmlspecialchars($p['name']) ?></option>
                            <?php endforeach; ?>
                        </select>
                        <span class="arrow">→ do →</span>
                        <select name="target_id" class="form-control" required>
                            <option value="">-- cílový parametr --</option>
                            <?php foreach ($params as $p): ?>
                                <option value="<?= (int)$p['id'] ?>"><?= htmlspecialchars($p['name']) ?></option>
                            <?php endforeach; ?>
                        </select>
                        <button type="submit" class="btn btn-outline">Sloučit</button>
                    </form>
                </div>

                <!-- Přidání parametru -->
                <div class="sub-panel">
                    <h3>Přidat nový parametr</h3>
                    <form method="POST" action="/biochemistry/parameters/add" class="inline-form">
                        <input type="hidden" name="test_type" value="<?= htmlspecialchars($testType) ?>">
                        <input type="text" name="name" class="form-control" placeholder="Název" required>
                        <input type="text" name="unit" class="form-control unit-in" placeholder="Jednotka">
                        <input type="number" name="sort_order" class="form-control ord" placeholder="Pořadí">
                        <button type="submit" class="btn btn-primary">Přidat</button>
                    </form>
                </div>
            </div>
        </div>
    <?php endforeach; ?>
</div>

<style>
.breadcrumb { margin-bottom: 20px; color: #7f8c8d; font-size: 14px; }
.breadcrumb a { color: #c0392b; text-decoration: none; }
.breadcrumb a:hover { text-decoration: underline; }
.page-header { margin-bottom: 25px; }
.page-header h1 { margin: 0 0 8px 0; color: #2c3e50; }
.page-header p { margin: 0; color: #7f8c8d; }

.param-section {
    background: white; border-radius: 8px; padding: 24px; margin-bottom: 30px;
    box-shadow: 0 2px 8px rgba(0,0,0,0.08);
}
.param-section h2 { margin: 0 0 16px 0; color: #c0392b; }

.params-table { width: 100%; border-collapse: collapse; }
.params-table thead { background: linear-gradient(135deg, #c0392b 0%, #a93226 100%); color: #fff; }
.params-table th { padding: 10px 12px; text-align: left; font-size: 14px; }
.params-table td { padding: 6px 12px; border-bottom: 1px solid #f0f0f0; }
.params-table tbody tr:hover { background: #fbf6f5; }
.params-table .usage { color: #7f8c8d; text-align: center; }
.params-table .empty { color: #7f8c8d; text-align: center; padding: 16px; }

.form-control { width: 100%; padding: 7px 9px; border: 1px solid #d0d0d0; border-radius: 4px; font-size: 14px; }
.form-control:focus { outline: none; border-color: #c0392b; }
.ord { max-width: 80px; }

.form-actions { margin-top: 16px; display: flex; justify-content: flex-end; }

.panel-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin-top: 24px; }
@media (max-width: 800px) { .panel-grid { grid-template-columns: 1fr; } }
.sub-panel { background: #f8f9fa; border: 1px solid #e9ecef; border-radius: 6px; padding: 16px; }
.sub-panel h3 { margin: 0 0 12px 0; font-size: 15px; color: #2c3e50; }
.inline-form { display: flex; flex-wrap: wrap; gap: 8px; align-items: center; }
.inline-form .form-control { width: auto; flex: 1; min-width: 120px; }
.inline-form .arrow { color: #7f8c8d; font-size: 13px; }

.btn { padding: 9px 18px; border-radius: 4px; font-size: 14px; cursor: pointer; border: none; text-decoration: none; display: inline-block; }
.btn-primary { background: #c0392b; color: #fff; }
.btn-primary:hover { background: #a93226; }
.btn-outline { background: #fff; border: 2px solid #c0392b; color: #c0392b; }
.btn-outline:hover { background: #c0392b; color: #fff; }

.alert { padding: 14px 18px; border-radius: 6px; margin-bottom: 18px; }
.alert-success { background: #d4edda; border: 1px solid #c3e6cb; color: #155724; }
.alert-error { background: #f8d7da; border: 1px solid #f5c6cb; color: #721c24; }

.drag-handle { cursor: grab; text-align: center; color: #b0b0b0; font-size: 18px; user-select: none; }
.drag-handle:active { cursor: grabbing; }
.param-row.dragging { opacity: 0.4; }
.param-row.drop-target td { border-top: 2px solid #c0392b; }
.params-hint { color: #7f8c8d; font-size: 13px; margin: 0 0 10px 0; }
</style>

<script>
(function () {
    document.querySelectorAll('tbody.sortable').forEach(function (tbody) {
        var dragged = null;

        tbody.addEventListener('dragstart', function (e) {
            var row = e.target.closest('.param-row');
            if (!row) return;
            dragged = row;
            row.classList.add('dragging');
            e.dataTransfer.effectAllowed = 'move';
        });

        tbody.addEventListener('dragend', function () {
            if (dragged) dragged.classList.remove('dragging');
            tbody.querySelectorAll('.drop-target').forEach(function (r) { r.classList.remove('drop-target'); });
            dragged = null;
            renumber(tbody);
        });

        tbody.addEventListener('dragover', function (e) {
            e.preventDefault();
            var row = e.target.closest('.param-row');
            if (!row || row === dragged) return;
            tbody.querySelectorAll('.drop-target').forEach(function (r) { r.classList.remove('drop-target'); });
            var rect = row.getBoundingClientRect();
            var after = (e.clientY - rect.top) > rect.height / 2;
            if (after) {
                row.parentNode.insertBefore(dragged, row.nextSibling);
            } else {
                row.parentNode.insertBefore(dragged, row);
            }
        });
    });

    // Přepočítá čísla "Pořadí" podle aktuálního pořadí řádků (krok 10).
    function renumber(tbody) {
        var order = 0;
        tbody.querySelectorAll('.param-row .ord').forEach(function (input) {
            order += 10;
            input.value = order;
        });
    }
})();
</script>
