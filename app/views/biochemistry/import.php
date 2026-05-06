<div class="container">
    <div class="page-header">
        <h1>Import LDT výsledků</h1>
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
            <h2>Nahrát LDT soubor</h2>
        </div>
        <div class="card-body">
            <form action="/biochemistry/import/upload" method="POST" enctype="multipart/form-data">
                <div class="form-group">
                    <label for="import_file">Vyberte laboratorní soubor (.ldt)</label>
                    <input type="file"
                           id="import_file"
                           name="import_file"
                           accept=".ldt"
                           required
                           class="form-control">
                    <small class="form-text">Import přijímá pouze soubory s příponou .ldt.</small>
                </div>

                <button type="submit" class="btn btn-primary">
                    Nahrát a zobrazit náhled
                </button>
            </form>
        </div>
    </div>

    <div class="card">
        <div class="card-header">
            <h2>Jak se LDT zpracuje</h2>
        </div>
        <div class="card-body">
            <p>Importer čte strukturované LDT řádky a používá ověřené field ID ze vzorových souborů z LABOKLINu:</p>

            <table class="table">
                <thead>
                    <tr>
                        <th>Field ID</th>
                        <th>Využití v Importu</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td><code>8310</code>, <code>8311</code></td>
                        <td>Číslo protokolu</td>
                    </tr>
                    <tr>
                        <td><code>3101</code>, <code>3204</code></td>
                        <td>Identifikátor a jméno zvířete pro spárování s databází</td>
                    </tr>
                    <tr>
                        <td><code>8470</code></td>
                        <td>Sekce výsledků, například biochemie nebo hematologie</td>
                    </tr>
                    <tr>
                        <td><code>8410</code></td>
                        <td>Začátek jednoho výsledkového bloku</td>
                    </tr>
                    <tr>
                        <td><code>8411</code>, <code>8420</code>, <code>8421</code></td>
                        <td>Název parametru, hodnota a jednotka</td>
                    </tr>
                    <tr>
                        <td><code>8432</code>, <code>8460</code></td>
                        <td>Datum výsledků a referenční rozmezí pro náhled</td>
                    </tr>
                </tbody>
            </table>

            <h3 style="margin-top: 2rem;">Důležité poznámky</h3>
            <ul>
               <!-- <li>CSV a Excel import je na teto strance vypnuty; server prijme jen <code>.ldt</code>.</li> -->
                <li>Zvíře se hledá podle LDT indetifikátoru, čísla čipu, nebo přesného jména.</li>
                <li>Před uložením se zobrazí náhled a řádky s chybami import nepustí.</li>
                <li>Již existující test stejného zvířete a data se aktualizuje, nový test se vytvoří.</li>
                <li>Referenční rozmezí z LDT se ukazuje v náhledu, ale aktuální databázová tabulka výsledků ho samotné neukládá.</li>
            </ul>
        </div>
    </div>

    <div class="card">
        <div class="card-header">
            <h2>Bezpečnost</h2>
        </div>
        <div class="card-body">
            <ul>
                <li>Importovat mohou pouze administrátoři.</li>
                <li>Soubor se nejprve parsuje do náhledu v session.</li>
                <li>Ukládání probíhá v databázových transakcích po jednotlivých testech.</li>
                <li>Neznámé LDT řádky se ignorují, aby import nespadl na nepodstatných polích.</li>
            </ul>
        </div>
    </div>
</div>
