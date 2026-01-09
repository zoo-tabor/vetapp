<div class="container">
    <div class="page-header">
        <div class="breadcrumb">
            <a href="/">Pracoviště</a> /
            <a href="/urineanalysis/workplace/<?= $workplace['id'] ?>">
                <?= htmlspecialchars($workplace['name']) ?>
            </a> /
            <span>Volba zvířete</span>
        </div>

        <h1>Volba zvířete</h1>
        <p class="subtitle">
            <strong><?= htmlspecialchars($workplace['name']) ?></strong> |
            <?= count($animals) ?> zvířat
        </p>
    </div>

    <div class="search-container">
        <div class="search-section">
            <h2>1. Vyberte zvíře</h2>
            <p class="instruction">Vyberte zvíře, pro které chcete zobrazit parametry</p>

            <div class="animals-grid">
                <?php if (empty($animals)): ?>
                    <p class="no-data">Žádná zvířata nejsou k dispozici</p>
                <?php else: ?>
                    <?php foreach ($animals as $animal): ?>
                        <div class="animal-card" data-animal-id="<?= $animal['id'] ?>">
                            <input type="radio"
                                   name="selected_animal"
                                   value="<?= $animal['id'] ?>"
                                   id="animal_<?= $animal['id'] ?>"
                                   onchange="selectAnimal(<?= $animal['id'] ?>)">
                            <label for="animal_<?= $animal['id'] ?>">
                                <div class="animal-name"><?= htmlspecialchars($animal['name']) ?></div>
                                <div class="animal-details">
                                    <small>ID: <?= htmlspecialchars($animal['identifier']) ?></small>
                                    <small><?= htmlspecialchars($animal['species']) ?></small>
                                </div>
                            </label>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>

        <div id="animalSearchContainer" style="display: none;">
            <!-- This will be populated via JavaScript when an animal is selected -->
        </div>

        <div class="form-actions">
            <a href="/urineanalysis/workplace/<?= $workplace['id'] ?>" class="btn btn-outline">
                ← Zpět
            </a>
        </div>
    </div>
</div>

<style>
.container {
    max-width: 1400px;
    margin: 0 auto;
    padding: 20px;
}

.page-header {
    margin-bottom: 30px;
}

.breadcrumb {
    margin-bottom: 15px;
    color: #7f8c8d;
    font-size: 14px;
}

.breadcrumb a {
    color: #f39c12;
    text-decoration: none;
}

.breadcrumb a:hover {
    text-decoration: underline;
}

.page-header h1 {
    margin: 0 0 10px 0;
    color: #2c3e50;
}

.subtitle {
    color: #7f8c8d;
    font-size: 14px;
    margin: 0;
}

.search-container {
    background: white;
    border-radius: 8px;
    padding: 30px;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
}

.search-section {
    margin-bottom: 40px;
    padding-bottom: 30px;
    border-bottom: 2px solid #f0f0f0;
}

.search-section:last-of-type {
    border-bottom: none;
}

.search-section h2 {
    margin: 0 0 10px 0;
    color: #f39c12;
    font-size: 20px;
}

.instruction {
    color: #7f8c8d;
    font-size: 14px;
    margin: 0 0 20px 0;
}

.animals-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
    gap: 15px;
}

.animal-card {
    background: #fef5e7;
    border: 2px solid #f8e3b0;
    border-radius: 6px;
    padding: 15px;
    transition: all 0.2s;
    cursor: pointer;
}

.animal-card:hover {
    border-color: #f39c12;
    background: #fff;
}

.animal-card input[type="radio"] {
    display: none;
}

.animal-card input[type="radio"]:checked + label {
    color: #f39c12;
}

.animal-card input[type="radio"]:checked ~ * {
    border-color: #f39c12;
}

.animal-card label {
    cursor: pointer;
    margin: 0;
}

.animal-name {
    font-weight: 600;
    font-size: 16px;
    color: #2c3e50;
    margin-bottom: 8px;
}

.animal-details {
    display: flex;
    flex-direction: column;
    gap: 4px;
}

.animal-details small {
    color: #7f8c8d;
    font-size: 12px;
}

.no-data {
    color: #7f8c8d;
    font-style: italic;
    padding: 20px;
    text-align: center;
}

.form-actions {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-top: 30px;
    padding-top: 20px;
    border-top: 2px solid #f0f0f0;
}

.btn {
    padding: 12px 24px;
    border-radius: 4px;
    border: none;
    cursor: pointer;
    font-size: 15px;
    font-weight: 600;
    transition: all 0.2s;
    text-decoration: none;
    display: inline-block;
}

.btn-outline {
    background: white;
    border: 2px solid #f39c12;
    color: #f39c12;
}

.btn-outline:hover {
    background: #f39c12;
    color: white;
}
</style>

<script>
function selectAnimal(animalId) {
    // Redirect to the animal-specific graph page
    window.location.href = `/urineanalysis/animal/${animalId}/graph`;
}
</script>
