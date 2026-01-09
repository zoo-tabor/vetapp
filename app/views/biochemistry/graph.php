<div class="container">
    <div class="page-header">
        <div class="breadcrumb">
            <a href="/">Pracoviště</a> /
            <a href="/biochemistry/workplace/<?= $animal['workplace_id'] ?>">
                <?= htmlspecialchars($animal['workplace_name'] ?? 'Pracoviště') ?>
            </a> /
            <a href="/biochemistry/animal/<?= $animal['id'] ?>">
                <?= htmlspecialchars($animal['name']) ?>
            </a> /
            <span>Graf parametrů</span>
        </div>

        <h1>Graf parametrů</h1>
        <p class="subtitle">
            <strong><?= htmlspecialchars($animal['name']) ?></strong> |
            ID: <?= htmlspecialchars($animal['identifier']) ?> |
            Druh: <?= htmlspecialchars($animal['species']) ?>
        </p>
    </div>

    <div class="graph-container">
        <div class="graph-header">
            <h2>Vývoj vybraných parametrů (posledních <?= $sampleCount ?> vzorků)</h2>
            <div class="graph-actions">
                <?php if (!empty($referenceSource)): ?>
                    <button id="toggleRanges" onclick="toggleReferenceRanges()" class="btn btn-toggle">
                        <span id="toggleIcon">👁️</span> Referenční rozsahy (<?= htmlspecialchars($referenceSource) ?>)
                    </button>
                <?php endif; ?>
                <button onclick="window.print()" class="btn btn-outline">
                    🖨️ Vytisknout
                </button>
                <button onclick="window.close()" class="btn btn-primary">
                    ← Zavřít
                </button>
            </div>
        </div>

        <div class="graph-legend">
            <?php foreach ($graphData as $series): ?>
                <div class="legend-item">
                    <span class="legend-color" style="background-color: <?= htmlspecialchars($series['color']) ?>"></span>
                    <span class="legend-label">
                        <?= htmlspecialchars($series['name']) ?>
                        <small>(<?= $series['type'] === 'biochemistry' ? 'Biochemie' : 'Hematologie' ?>)</small>
                    </span>
                </div>
            <?php endforeach; ?>
        </div>

        <div class="canvas-wrapper">
            <canvas id="parametersChart"></canvas>
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
    color: #c0392b;
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

.graph-container {
    background: white;
    border-radius: 8px;
    padding: 30px;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
}

.graph-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 20px;
    padding-bottom: 20px;
    border-bottom: 2px solid #c0392b;
}

.graph-header h2 {
    margin: 0;
    color: #2c3e50;
    font-size: 20px;
}

.graph-actions {
    display: flex;
    gap: 10px;
}

.btn {
    padding: 10px 20px;
    border-radius: 4px;
    border: none;
    cursor: pointer;
    font-size: 14px;
    transition: all 0.2s;
}

.btn-primary {
    background: linear-gradient(135deg, #c0392b 0%, #a93226 100%);
    color: white;
}

.btn-primary:hover {
    background: linear-gradient(135deg, #a93226 0%, #8f2a20 100%);
    transform: translateY(-1px);
    box-shadow: 0 4px 12px rgba(192, 57, 43, 0.3);
}

.btn-outline {
    background: white;
    border: 2px solid #c0392b;
    color: #c0392b;
}

.btn-outline:hover {
    background: #c0392b;
    color: white;
}

.btn-toggle {
    background: #3498db;
    color: white;
    transition: all 0.3s;
}

.btn-toggle:hover {
    background: #2980b9;
    transform: translateY(-1px);
    box-shadow: 0 4px 12px rgba(52, 152, 219, 0.3);
}

.btn-toggle.hidden {
    background: #95a5a6;
}

.btn-toggle.hidden:hover {
    background: #7f8c8d;
}

.graph-legend {
    display: flex;
    flex-wrap: wrap;
    gap: 15px;
    margin-bottom: 30px;
    padding: 15px;
    background: #f8f9fa;
    border-radius: 4px;
}

.legend-item {
    display: flex;
    align-items: center;
    gap: 8px;
}

.legend-color {
    width: 20px;
    height: 20px;
    border-radius: 3px;
    border: 1px solid #ddd;
}

.legend-label {
    font-size: 14px;
    color: #2c3e50;
}

.legend-label small {
    color: #7f8c8d;
    font-size: 12px;
}

.canvas-wrapper {
    position: relative;
    height: 500px;
}

@media print {
    body * {
        visibility: hidden;
    }

    .graph-container,
    .graph-container * {
        visibility: visible;
    }

    .graph-container {
        position: absolute;
        left: 0;
        top: 0;
        width: 100%;
        box-shadow: none;
    }

    .graph-actions {
        display: none;
    }

    .canvas-wrapper {
        height: 600px;
    }
}
</style>

<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<script>
const graphData = <?= json_encode($graphData) ?>;
const referenceRanges = <?= json_encode($referenceRanges) ?>;
const referenceSource = <?= json_encode($referenceSource) ?>;
let rangesVisible = true;

// Prepare data for Chart.js
const allDates = [];
const datasets = [];

// Collect all unique dates
graphData.forEach(series => {
    series.data.forEach(point => {
        const dateStr = point.test_date;
        if (!allDates.includes(dateStr)) {
            allDates.push(dateStr);
        }
    });
});

// Sort dates
allDates.sort();

// Format dates for display
const labels = allDates.map(date => {
    const d = new Date(date);
    return d.toLocaleDateString('cs-CZ', { day: '2-digit', month: '2-digit', year: 'numeric' });
});

// Create datasets
graphData.forEach(series => {
    const data = allDates.map(date => {
        const point = series.data.find(p => p.test_date === date);
        if (!point) return null;

        // Check if value is numeric
        const numericValue = parseFloat(point.value);
        if (isNaN(numericValue)) {
            // Non-numeric value (e.g., "neg.", "pozitivní"), skip it
            return null;
        }
        return numericValue;
    });

    datasets.push({
        label: series.name,
        data: data,
        borderColor: series.color,
        backgroundColor: series.color + '33', // Add transparency
        borderWidth: 2,
        pointRadius: 5,
        pointHoverRadius: 7,
        tension: 0.1,
        spanGaps: true,
        order: 1
    });

    // Add reference ranges if available
    if (referenceSource) {
        const rangeKey = series.name + '_' + series.type;
        const range = referenceRanges[rangeKey];

        if (range) {
            // Add min reference line
            if (range.min !== null) {
                datasets.push({
                    label: series.name + ' - Min (' + referenceSource + ')',
                    data: allDates.map(() => parseFloat(range.min)),
                    borderColor: series.color,
                    backgroundColor: 'transparent',
                    borderWidth: 2,
                    borderDash: [5, 5],
                    pointRadius: 0,
                    pointHoverRadius: 0,
                    tension: 0,
                    fill: false,
                    order: 2,
                    hidden: !rangesVisible
                });
            }

            // Add max reference line
            if (range.max !== null) {
                datasets.push({
                    label: series.name + ' - Max (' + referenceSource + ')',
                    data: allDates.map(() => parseFloat(range.max)),
                    borderColor: series.color,
                    backgroundColor: 'transparent',
                    borderWidth: 2,
                    borderDash: [5, 5],
                    pointRadius: 0,
                    pointHoverRadius: 0,
                    tension: 0,
                    fill: false,
                    order: 2,
                    hidden: !rangesVisible
                });
            }
        }
    }
});

// Create chart
const ctx = document.getElementById('parametersChart').getContext('2d');
const chart = new Chart(ctx, {
    type: 'line',
    data: {
        labels: labels,
        datasets: datasets
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
            legend: {
                display: false // We have our own legend
            },
            title: {
                display: false
            },
            tooltip: {
                mode: 'index',
                intersect: false,
                callbacks: {
                    label: function(context) {
                        let label = context.dataset.label || '';
                        if (label) {
                            label += ': ';
                        }
                        if (context.parsed.y !== null) {
                            label += context.parsed.y.toFixed(2);
                        }
                        return label;
                    }
                }
            }
        },
        scales: {
            y: {
                beginAtZero: false,
                ticks: {
                    callback: function(value) {
                        return value.toFixed(2);
                    }
                },
                grid: {
                    color: '#e0e0e0'
                }
            },
            x: {
                grid: {
                    color: '#e0e0e0'
                }
            }
        },
        interaction: {
            mode: 'nearest',
            axis: 'x',
            intersect: false
        }
    }
});

// Toggle reference ranges visibility
function toggleReferenceRanges() {
    rangesVisible = !rangesVisible;

    const toggleBtn = document.getElementById('toggleRanges');
    const toggleIcon = document.getElementById('toggleIcon');

    // Update button style
    if (rangesVisible) {
        toggleBtn.classList.remove('hidden');
        toggleIcon.textContent = '👁️';
    } else {
        toggleBtn.classList.add('hidden');
        toggleIcon.textContent = '🚫';
    }

    // Toggle visibility of reference range datasets
    chart.data.datasets.forEach((dataset, index) => {
        if (dataset.label.includes(' - Min (') || dataset.label.includes(' - Max (')) {
            chart.setDatasetVisibility(index, rangesVisible);
        }
    });

    chart.update();
}
</script>
