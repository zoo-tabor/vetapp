<div class="container">
    <div class="page-header">
        <h1>Import LDT vysledku</h1>
        <p class="breadcrumb">
            <a href="/biochemistry">Biochemie a hematologie</a> / Import LDT
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
            <h2>Nahrat LDT soubor</h2>
        </div>
        <div class="card-body">
            <form action="/biochemistry/import/upload" method="POST" enctype="multipart/form-data">
                <div class="form-group">
                    <label for="import_file">Vyberte laboratorni soubor (.ldt)</label>
                    <input type="file"
                           id="import_file"
                           name="import_file"
                           accept=".ldt"
                           required
                           class="form-control">
                    <small class="form-text">Import prijima pouze soubory s priponou .ldt.</small>
                </div>

                <button type="submit" class="btn btn-primary">
                    Nahrat a zobrazit nahled
                </button>
            </form>
        </div>
    </div>

    <div class="card">
        <div class="card-header">
            <h2>Jak se LDT zpracuje</h2>
        </div>
        <div class="card-body">
            <p>Importer cte strukturovane LDT radky a pouziva overene field ID ze vzorovych souboru LABOKLIN:</p>

            <table class="table">
                <thead>
                    <tr>
                        <th>Field ID</th>
                        <th>Vyuziti v importu</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td><code>8310</code>, <code>8311</code></td>
                        <td>Cislo protokolu</td>
                    </tr>
                    <tr>
                        <td><code>3101</code>, <code>3204</code></td>
                        <td>Identifikator a jmeno zvirete pro sparovani s databazi</td>
                    </tr>
                    <tr>
                        <td><code>8470</code></td>
                        <td>Sekce vysledku, napriklad biochemie nebo hematologie</td>
                    </tr>
                    <tr>
                        <td><code>8410</code></td>
                        <td>Zacatek jednoho vysledkoveho bloku</td>
                    </tr>
                    <tr>
                        <td><code>8411</code>, <code>8420</code>, <code>8421</code></td>
                        <td>Nazev parametru, hodnota a jednotka</td>
                    </tr>
                    <tr>
                        <td><code>8432</code>, <code>8460</code></td>
                        <td>Datum vysledku a referencni rozmezi pro nahled</td>
                    </tr>
                </tbody>
            </table>

            <h3 style="margin-top: 2rem;">Dulezite poznamky</h3>
            <ul>
                <li>CSV a Excel import je na teto strance vypnuty; server prijme jen <code>.ldt</code>.</li>
                <li>Zvire se hleda podle LDT identifikatoru, cisla cipu nebo presneho jmena.</li>
                <li>Pred ulozenim se zobrazi nahled a radky s chybami import nepusti.</li>
                <li>Existujici test stejneho zvirete a data se aktualizuje, novy test se vytvori.</li>
                <li>Referencni rozmezi z LDT se ukazuje v nahledu, ale aktualni databazova tabulka vysledku ho samostatne neuklada.</li>
            </ul>
        </div>
    </div>

    <div class="card">
        <div class="card-header">
            <h2>Bezpecnost</h2>
        </div>
        <div class="card-body">
            <ul>
                <li>Importovat mohou pouze administratori.</li>
                <li>Soubor se nejdriv parsuje do nahledu v session.</li>
                <li>Ukladani probiha v databazovych transakcich po jednotlivych testech.</li>
                <li>Nezname LDT radky se ignoruji, aby import nespadl na nepodstatnych polich.</li>
            </ul>
        </div>
    </div>
</div>
