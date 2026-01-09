<?php $layout = 'main'; ?>

<div class="page-header">
    <div class="breadcrumb">
        <a href="/">Pracoviště</a> / 
        <a href="/workplace/<?= $workplace['id'] ?>"><?= htmlspecialchars($workplace['name']) ?></a> / 
        <a href="/workplace/<?= $workplace['id'] ?>/animals">Zvířata</a> / 
        Přidat zvíře
    </div>
    <h1>Přidat nové zvíře</h1>
</div>

<div class="card <?= isset($fromBiochemistry) && $fromBiochemistry ? 'biochemistry-card' : '' ?>">
    <div class="card-body">
        <form method="POST" action="/workplace/<?= $workplace['id'] ?>/animals/create<?= isset($fromBiochemistry) && $fromBiochemistry ? '?from=biochemistry' : '' ?>">
            <div class="form-group">
                <label for="name">Jméno *</label>
                <input
                    type="text"
                    id="name"
                    name="name"
                    class="form-control"
                    required
                >
            </div>

            <div class="form-group">
                <label for="species">Druh *</label>
                <input
                    type="text"
                    id="species"
                    name="species"
                    class="form-control"
                    required
                    placeholder="např. Slon africký"
                >
            </div>

            <div class="form-group">
                <label for="identifier">Identifikátor *</label>
                <input
                    type="text"
                    id="identifier"
                    name="identifier"
                    class="form-control"
                    required
                    placeholder="např. ZOO-123, ID-456, nebo jiný jedinečný kód"
                >
                <small class="form-text">Jedinečný identifikátor pro vyhledávání a reference zvířete</small>
            </div>

            <div class="form-group">
                <label for="birth_date">Datum narození</label>
                <input
                    type="date"
                    id="birth_date"
                    name="birth_date"
                    class="form-control"
                >
            </div>
            
            <div class="form-group">
                <label for="gender">Pohlaví</label>
                <select id="gender" name="gender" class="form-control">
                    <option value="unknown">Neznámé</option>
                    <option value="male">Samec</option>
                    <option value="female">Samice</option>
                </select>
            </div>
            
            <div class="form-group">
                <label for="enclosure_id">Výběh</label>
                <select id="enclosure_id" name="enclosure_id" class="form-control">
                    <option value="">-- Vyberte výběh --</option>
                    <?php foreach ($enclosures as $enclosure): ?>
                        <option value="<?= $enclosure['id'] ?>">
                            <?= htmlspecialchars($enclosure['name']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            
            <div class="form-group">
                <label for="notes">Poznámky</label>
                <textarea 
                    id="notes" 
                    name="notes" 
                    class="form-control" 
                    rows="4"
                    placeholder="Volitelné poznámky k zvířeti..."
                ></textarea>
            </div>
            
            <div class="form-actions" style="display: flex; gap: 1rem; margin-top: 2rem;">
                <button type="submit" class="btn btn-primary">
                    Přidat zvíře
                </button>
                <a href="/workplace/<?= $workplace['id'] ?>/animals" class="btn btn-outline">
                    Zrušit
                </a>
            </div>
        </form>
    </div>
</div>

<?php if (isset($fromBiochemistry) && $fromBiochemistry): ?>
<style>
/* Biochemistry theme styling */
.biochemistry-card {
    border-top: 4px solid #c0392b;
}

.biochemistry-card .page-header h1 {
    color: #c0392b;
}

.biochemistry-card .btn-primary {
    background: linear-gradient(135deg, #c0392b 0%, #a93226 100%);
    border: none;
}

.biochemistry-card .btn-primary:hover {
    background: linear-gradient(135deg, #a93226 0%, #8f2a20 100%);
    transform: translateY(-1px);
    box-shadow: 0 4px 12px rgba(192, 57, 43, 0.3);
}

.biochemistry-card .form-control:focus {
    border-color: #c0392b;
    box-shadow: 0 0 0 0.2rem rgba(192, 57, 43, 0.25);
}

.biochemistry-card label {
    color: #2c3e50;
    font-weight: 600;
}

.biochemistry-card .breadcrumb a {
    color: #c0392b;
}

.biochemistry-card .breadcrumb a:hover {
    color: #a93226;
}
</style>
<?php endif; ?>