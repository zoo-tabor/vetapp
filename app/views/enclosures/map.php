<?php
// Data pro JS: kod => { name, encId, clickable, animals[] }
$mapData = [];
foreach ($enclosures as $e) {
    $mapData[$e['kod']] = [
        'name' => $e['name'],
        'encId' => $e['encId'],
        'clickable' => $e['clickable'],
        'animals' => array_map(function ($a) {
            return ['id' => (int)$a['id'], 'name' => $a['name'], 'species' => $a['species'], 'gender' => $a['gender']];
        }, $e['animals']),
    ];
}
?>
<div class="map-page">
    <div class="map-header">
        <div>
            <div class="breadcrumb">
                <a href="/animals">Seznam zvířat</a> /
                <a href="/animals/workplace/<?= $workplace['id'] ?>"><?= htmlspecialchars($workplace['name']) ?></a> /
                Mapa výběhů
            </div>
            <h1>Mapa výběhů — <?= htmlspecialchars($workplace['name']) ?></h1>
        </div>
        <a href="/animals/workplace/<?= $workplace['id'] ?>" class="btn btn-secondary">← Zpět na výběhy</a>
    </div>

    <div class="map-layout">
        <div class="map-wrap" style="aspect-ratio: <?= (int)$mapWidth ?> / <?= (int)$mapHeight ?>;">
            <img src="/assets/img/zoo-mapa-podklad.webp" alt="Mapa <?= htmlspecialchars($workplace['name']) ?>" class="map-bg">
            <svg viewBox="0 0 <?= (int)$mapWidth ?> <?= (int)$mapHeight ?>" class="map-svg" role="img" aria-label="Výběhy">
                <?php foreach ($enclosures as $e): ?>
                    <path d="<?= htmlspecialchars($e['d']) ?>"
                          class="vybeh <?= $e['clickable'] ? 'klik' : 'neklik' ?>"
                          data-kod="<?= htmlspecialchars($e['kod']) ?>"></path>
                <?php endforeach; ?>
                <?php foreach ($enclosures as $e): ?>
                    <?php if (!empty($e['label']) && $e['kod'] !== ''): ?>
                        <text class="cislo" x="<?= (float)$e['label'][0] ?>" y="<?= (float)$e['label'][1] ?>"><?= htmlspecialchars($e['kod']) ?></text>
                    <?php endif; ?>
                <?php endforeach; ?>
            </svg>
            <div id="mapTooltip" class="map-tooltip" style="display:none;"></div>
        </div>

        <aside class="map-side">
            <div class="search-box">
                <input type="text" id="mapSearch" placeholder="Hledat výběh nebo zvíře…" autocomplete="off">
            </div>
            <div id="searchResults" class="search-results" style="display:none;"></div>
            <div id="sideDetail" class="side-detail">
                <p class="hint">Najeďte myší na výběh, nebo klikněte pro zobrazení detailu.</p>
            </div>
        </aside>
    </div>
</div>

<style>
.map-page { max-width: 1500px; margin: 0 auto; padding: 20px; }
.map-header { display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 20px; gap: 16px; }
.map-header h1 { margin: 6px 0 0 0; color: #2c3e50; font-size: 24px; }
.breadcrumb { font-size: 14px; color: #7f8c8d; }
.breadcrumb a { color: #8e44ad; text-decoration: none; }
.btn { padding: 8px 16px; border-radius: 6px; text-decoration: none; display: inline-block; font-weight: 600; border: none; cursor: pointer; font-family: inherit; font-size: 14px; white-space: nowrap; }
.btn-secondary { background: #95a5a6; color: #fff; }
.btn-secondary:hover { background: #7f8c8d; }
.btn-primary { background: #8e44ad; color: #fff; }
.btn-primary:hover { background: #7d3c98; }

.map-layout { display: grid; grid-template-columns: 1fr 360px; gap: 20px; align-items: start; }

.map-wrap { position: relative; width: 100%; border-radius: 10px; overflow: hidden; box-shadow: 0 2px 10px rgba(0,0,0,0.12); background: #eef; }
.map-bg { width: 100%; height: 100%; display: block; }
.map-svg { position: absolute; inset: 0; width: 100%; height: 100%; }

.vybeh { fill: #688e3d; fill-opacity: .32; stroke: #14300f; stroke-width: 3; stroke-linejoin: round; transition: fill-opacity .12s, fill .12s; }
.vybeh.klik { cursor: pointer; }
.vybeh.neklik { fill: #8a5a20; fill-opacity: .18; pointer-events: none; }
.vybeh.klik:hover { fill: #f2e02a; fill-opacity: .78; }
.vybeh.selected { fill: #f2e02a; fill-opacity: .9; stroke-width: 6; }
.vybeh.dim { fill-opacity: .06; }
.vybeh.match { fill: #e67e22; fill-opacity: .7; }
.cislo { font: bold 26px system-ui, sans-serif; fill: #fff; stroke: #000; stroke-width: 5; paint-order: stroke fill; text-anchor: middle; dominant-baseline: middle; pointer-events: none; user-select: none; }

.map-tooltip { position: absolute; z-index: 30; background: rgba(20,20,20,.92); color: #fff; padding: 8px 11px; border-radius: 6px; font-size: 13px; max-width: 260px; pointer-events: none; box-shadow: 0 2px 10px rgba(0,0,0,.4); }
.map-tooltip b { display: block; margin-bottom: 3px; font-size: 14px; }
.map-tooltip .t-animals { color: #d8d8d8; line-height: 1.35; }

.map-side { position: sticky; top: 16px; }
.search-box input { width: 100%; padding: 11px 14px; border: 2px solid #ddd; border-radius: 8px; font-size: 15px; box-sizing: border-box; }
.search-box input:focus { outline: none; border-color: #8e44ad; }
.search-results { margin-top: 10px; max-height: 260px; overflow-y: auto; border: 1px solid #ecf0f1; border-radius: 8px; }
.search-results .res { padding: 9px 12px; border-bottom: 1px solid #f0f0f0; cursor: pointer; font-size: 14px; }
.search-results .res:last-child { border-bottom: none; }
.search-results .res:hover { background: #f6effa; }
.search-results .res .r-name { font-weight: 600; color: #2c3e50; }
.search-results .res .r-sub { color: #7f8c8d; font-size: 12px; }

.side-detail { margin-top: 14px; background: #fff; border-radius: 10px; padding: 16px; box-shadow: 0 2px 8px rgba(0,0,0,0.08); min-height: 120px; }
.side-detail .hint { color: #7f8c8d; margin: 0; }
.sd-head { display: flex; justify-content: space-between; align-items: center; margin-bottom: 8px; gap: 8px; }
.sd-title { margin: 0; font-size: 18px; color: #2c3e50; }
.sd-code { color: #7f8c8d; font-size: 13px; font-family: 'Courier New', monospace; }
.sd-animals { list-style: none; padding: 0; margin: 10px 0 0 0; }
.sd-animals li { padding: 0; }
.sd-animals a { display: flex; justify-content: space-between; gap: 8px; padding: 8px 10px; border-radius: 6px; text-decoration: none; color: #2c3e50; border: 1px solid #f0f0f0; margin-bottom: 6px; }
.sd-animals a:hover { background: #f6effa; border-color: #d9c2ec; }
.sd-animals .a-name { font-weight: 600; }
.sd-animals .a-species { color: #7f8c8d; font-size: 13px; }
.sd-empty { color: #7f8c8d; font-size: 14px; margin: 8px 0 0 0; }
.sd-open { margin-top: 12px; display: inline-block; }

@media (max-width: 900px) {
    .map-layout { grid-template-columns: 1fr; }
    .map-side { position: static; }
}
</style>

<script>
const MAP_DATA = <?= json_encode($mapData, JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) ?>;
const GENDER = { male: '♂', female: '♀', unknown: '?' };

const svg = document.querySelector('.map-svg');
const wrap = document.querySelector('.map-wrap');
const tooltip = document.getElementById('mapTooltip');
const sideDetail = document.getElementById('sideDetail');
const searchInput = document.getElementById('mapSearch');
const searchResults = document.getElementById('searchResults');
let pinnedKod = null;

function esc(s) {
    return String(s == null ? '' : s).replace(/[&<>"']/g, c => ({ '&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;' }[c]));
}

function animalsSummary(animals) {
    if (!animals.length) return 'Žádná aktivní zvířata';
    return animals.map(a => esc(a.name || a.species)).join(', ');
}

function renderTooltip(kod) {
    const d = MAP_DATA[kod];
    if (!d) return;
    tooltip.innerHTML = '<b>' + esc(d.name) + '</b><span class="t-animals">' + animalsSummary(d.animals) + '</span>';
    tooltip.style.display = 'block';
}

function renderSideDetail(kod) {
    const d = MAP_DATA[kod];
    if (!d) return;
    let html = '<div class="sd-head"><h3 class="sd-title">' + esc(d.name) + '</h3>';
    if (kod !== '') html += '<span class="sd-code">kód ' + esc(kod) + '</span>';
    html += '</div>';
    if (d.animals.length) {
        html += '<ul class="sd-animals">';
        d.animals.forEach(a => {
            html += '<li><a href="/animals/detail/' + a.id + '">'
                 + '<span class="a-name">' + esc(a.name || 'Bez jména') + '</span>'
                 + '<span class="a-species">' + (GENDER[a.gender] || '') + ' ' + esc(a.species) + '</span></a></li>';
        });
        html += '</ul>';
    } else {
        html += '<p class="sd-empty">V tomto výběhu nejsou žádná aktivní zvířata.</p>';
    }
    if (d.encId) {
        html += '<a class="btn btn-primary sd-open" href="/enclosures/detail/' + d.encId + '">Otevřít výběh →</a>';
    }
    sideDetail.innerHTML = html;
}

function highlight(kod) {
    svg.querySelectorAll('.vybeh.selected').forEach(p => p.classList.remove('selected'));
    if (kod !== null) {
        const p = svg.querySelector('.vybeh[data-kod="' + CSS.escape(kod) + '"]');
        if (p) p.classList.add('selected');
    }
}

svg.querySelectorAll('.vybeh.klik').forEach(path => {
    const kod = path.getAttribute('data-kod');
    path.addEventListener('mouseenter', () => {
        if (pinnedKod === null) { renderSideDetail(kod); highlight(kod); }
        renderTooltip(kod);
    });
    path.addEventListener('mousemove', (ev) => {
        const r = wrap.getBoundingClientRect();
        let x = ev.clientX - r.left + 14, y = ev.clientY - r.top + 14;
        if (x + 270 > r.width) x = ev.clientX - r.left - 270;
        tooltip.style.left = x + 'px';
        tooltip.style.top = y + 'px';
    });
    path.addEventListener('mouseleave', () => {
        tooltip.style.display = 'none';
        if (pinnedKod === null) highlight(null);
    });
    path.addEventListener('click', () => {
        pinnedKod = (pinnedKod === kod) ? null : kod;
        if (pinnedKod) { renderSideDetail(kod); highlight(kod); }
        else { highlight(null); resetSide(); }
    });
});

function resetSide() {
    sideDetail.innerHTML = '<p class="hint">Najeďte myší na výběh, nebo klikněte pro zobrazení detailu.</p>';
}

// --- Vyhledávání ---
searchInput.addEventListener('input', function () {
    const q = this.value.trim().toLowerCase();
    if (!q) {
        searchResults.style.display = 'none';
        searchResults.innerHTML = '';
        svg.querySelectorAll('.vybeh').forEach(p => p.classList.remove('dim', 'match'));
        return;
    }
    const matches = [];
    Object.keys(MAP_DATA).forEach(kod => {
        const d = MAP_DATA[kod];
        if (!d.clickable) return;
        const inName = d.name.toLowerCase().includes(q);
        const inAnimal = d.animals.some(a => (a.name || '').toLowerCase().includes(q) || (a.species || '').toLowerCase().includes(q));
        if (inName || inAnimal) matches.push({ kod, d });
    });

    // zvýraznění na mapě
    const matchKods = new Set(matches.map(m => m.kod));
    svg.querySelectorAll('.vybeh.klik').forEach(p => {
        const k = p.getAttribute('data-kod');
        p.classList.toggle('match', matchKods.has(k));
        p.classList.toggle('dim', !matchKods.has(k));
    });

    // výpis výsledků
    if (!matches.length) {
        searchResults.innerHTML = '<div class="res"><span class="r-sub">Nic nenalezeno</span></div>';
    } else {
        searchResults.innerHTML = matches.map(m =>
            '<div class="res" data-kod="' + esc(m.kod) + '">'
            + '<div class="r-name">' + esc(m.d.name) + '</div>'
            + '<div class="r-sub">' + (m.d.animals.length ? m.d.animals.length + ' zvířat' : 'bez zvířat') + '</div>'
            + '</div>'
        ).join('');
        searchResults.querySelectorAll('.res[data-kod]').forEach(el => {
            el.addEventListener('click', () => {
                const kod = el.getAttribute('data-kod');
                pinnedKod = kod;
                renderSideDetail(kod);
                highlight(kod);
            });
        });
    }
    searchResults.style.display = 'block';
});
</script>
