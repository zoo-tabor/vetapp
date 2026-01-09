<div class="container">
    <div class="page-header">
        <h1>Import biochemie a hematologie</h1>
        <p class="breadcrumb">
            <a href="/biochemistry">Biochemie a hematologie</a> / Import dat
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

    <div class="card">
        <div class="card-header">
            <h2>Nahrát soubor</h2>
        </div>
        <div class="card-body">
            <form action="/biochemistry/import/upload" method="POST" enctype="multipart/form-data">
                <div class="form-group">
                    <label for="import_file">Vyberte soubor s daty (.xlsx, .xls, .csv)</label>
                    <input type="file"
                           id="import_file"
                           name="import_file"
                           accept=".xlsx,.xls,.csv"
                           required
                           class="form-control">
                    <small class="form-text">Podporované formáty: Excel (.xlsx, .xls) a CSV (.csv)</small>
                </div>

                <button type="submit" class="btn btn-primary">
                    📤 Nahrát a zobrazit náhled
                </button>
            </form>
        </div>
    </div>

    <div class="card">
        <div class="card-header">
            <h2>📋 Formát souboru</h2>
        </div>
        <div class="card-body">
            <p>Soubor musí obsahovat následující sloupce (hlavičku):</p>

            <table class="table">
                <thead>
                    <tr>
                        <th>Název sloupce</th>
                        <th>Popis</th>
                        <th>Povinný</th>
                        <th>Příklad</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td><code>animal_code</code></td>
                        <td>Kód zvířete v systému</td>
                        <td>Ano</td>
                        <td>A001</td>
                    </tr>
                    <tr>
                        <td><code>test_type</code></td>
                        <td>Typ testu (biochemistry nebo hematology)</td>
                        <td>Ano</td>
                        <td>biochemistry</td>
                    </tr>
                    <tr>
                        <td><code>test_date</code></td>
                        <td>Datum testu</td>
                        <td>Ano</td>
                        <td>2024-01-15</td>
                    </tr>
                    <tr>
                        <td><code>parameter_name</code></td>
                        <td>Název parametru</td>
                        <td>Ano</td>
                        <td>ALT</td>
                    </tr>
                    <tr>
                        <td><code>value</code></td>
                        <td>Hodnota parametru</td>
                        <td>Ne</td>
                        <td>45.2</td>
                    </tr>
                    <tr>
                        <td><code>unit</code></td>
                        <td>Jednotka</td>
                        <td>Ne</td>
                        <td>U/L</td>
                    </tr>
                    <tr>
                        <td><code>test_location</code></td>
                        <td>Místo provedení testu</td>
                        <td>Ne</td>
                        <td>Idexx</td>
                    </tr>
                    <tr>
                        <td><code>reference_source</code></td>
                        <td>Zdroj referenčních hodnot</td>
                        <td>Ne</td>
                        <td>Idexx</td>
                    </tr>
                    <tr>
                        <td><code>notes</code></td>
                        <td>Poznámky</td>
                        <td>Ne</td>
                        <td>Kontrolní vyšetření</td>
                    </tr>
                </tbody>
            </table>

            <h3 style="margin-top: 2rem;">Příklad CSV souboru:</h3>
            <pre style="background: #f5f5f5; padding: 1rem; border-radius: 5px; overflow-x: auto;">animal_code;test_type;test_date;parameter_name;value;unit;test_location;reference_source;notes
A001;biochemistry;2024-01-15;ALT;45.2;U/L;Idexx;Idexx;
A001;biochemistry;2024-01-15;AST;32.1;U/L;Idexx;Idexx;
A002;hematology;2024-01-16;WBC;8.5;10^9/L;Idexx;Idexx;Kontrola po nemoci</pre>

            <h3 style="margin-top: 2rem;">⚠️ Důležité poznámky:</h3>
            <ul>
                <li>CSV soubor musí použít středník (;) jako oddělovač</li>
                <li>První řádek musí obsahovat názvy sloupců</li>
                <li>Kódy zvířat musí existovat v systému</li>
                <li>Pokud test pro dané zvíře a datum už existuje, bude aktualizován</li>
                <li>Datum musí být ve formátu YYYY-MM-DD (např. 2024-01-15)</li>
                <li>Více parametrů pro jeden test = více řádků se stejným zvířetem, typem a datem</li>
            </ul>

            <h3 style="margin-top: 2rem;">📥 Stáhnout šablonu:</h3>
            <p>
                <a href="/assets/templates/biochemistry_import_template.csv" class="btn btn-outline" download>
                    ⬇️ Stáhnout CSV šablonu
                </a>
            </p>
        </div>
    </div>

    <div class="card">
        <div class="card-header">
            <h2>🔒 Bezpečnost</h2>
        </div>
        <div class="card-body">
            <ul>
                <li>✅ Před importem se zobrazí náhled všech dat</li>
                <li>✅ Každý řádek je validován před importem</li>
                <li>✅ Import probíhá v transakcích - při chybě se data nepoškodí</li>
                <li>✅ Existující testy se aktualizují, nové se vytvoří</li>
                <li>✅ Veškeré operace jsou logovány</li>
                <li>✅ Pouze administrátoři mohou importovat data</li>
            </ul>
        </div>
    </div>
</div>
