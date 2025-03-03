<div class="income-summary">
    <div class="summary-chart">
        <div class="chart-title">Répartition des revenus par type</div>
        <div class="chart-container">
            <div class="chart-legend">
                @foreach($statistics['by_type'] as $type => $typeStats)
                    <div class="legend-item">
                        <span class="legend-color" style="background-color: var(--chart-color-{{ $loop->index % 5 + 1 }});"></span>
                        <span class="legend-text">{{ $type }}</span>
                    </div>
                @endforeach
            </div>

            <div class="chart-pie">
                @php $cumulativePercentage = 0; @endphp

                @foreach($statistics['by_type'] as $type => $typeStats)
                    @php
                        $percentage = $statistics['total'] > 0 ? ($typeStats['total'] / $statistics['total']) * 100 : 0;
                        $rotation = $cumulativePercentage * 3.6; // 3.6 = 360/100
                        $cumulativePercentage += $percentage;
                    @endphp

                    <div class="pie-segment" style="--segment-start: {{ $rotation }}deg; --segment-end: {{ $cumulativePercentage * 3.6 }}deg; --segment-color: var(--chart-color-{{ $loop->index % 5 + 1 }});">
                    </div>
                @endforeach

                <div class="pie-center">
                    <div class="pie-total">{{ number_format($statistics['total'], 0, ',', ' ') }} €</div>
                    <div class="pie-label">Total</div>
                </div>
            </div>
        </div>
    </div>

    <div class="summary-metrics">
        <div class="metric-card">
            <div class="metric-title">Revenus taxables</div>
            <div class="metric-value">{{ number_format($statistics['total_taxable'], 2, ',', ' ') }} €</div>
            <div class="metric-chart">
                <div class="progress-bar">
                    <div class="progress-fill" style="width: {{ $statistics['total'] > 0 ? ($statistics['total_taxable'] / $statistics['total']) * 100 : 0 }}%;"></div>
                </div>
                <div class="progress-label">{{ $statistics['total'] > 0 ? number_format(($statistics['total_taxable'] / $statistics['total']) * 100, 1) : 0 }}%</div>
            </div>
        </div>

        <div class="metric-card">
            <div class="metric-title">Revenus à déclarer</div>
            <div class="metric-value">{{ number_format($statistics['total_must_declare'], 2, ',', ' ') }} €</div>
            <div class="metric-chart">
                <div class="progress-bar">
                    <div class="progress-fill" style="width: {{ $statistics['total'] > 0 ? ($statistics['total_must_declare'] / $statistics['total']) * 100 : 0 }}%;"></div>
                </div>
                <div class="progress-label">{{ $statistics['total'] > 0 ? number_format(($statistics['total_must_declare'] / $statistics['total']) * 100, 1) : 0 }}%</div>
            </div>
        </div>
    </div>
</div>

<style>
:root {
    --chart-color-1: #2196F3;
    --chart-color-2: #4CAF50;
    --chart-color-3: #FF9800;
    --chart-color-4: #9C27B0;
    --chart-color-5: #F44336;
}

.income-summary {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 2rem;
    margin: 2rem 0;
    background: white;
    padding: 2rem;
    border-radius: var(--radius);
    box-shadow: var(--shadow);
}

.chart-title, .metric-title {
    font-size: 1.1rem;
    font-weight: 500;
    margin-bottom: 1.5rem;
    color: #2c3e50;
}

.chart-container {
    display: flex;
    align-items: center;
    gap: 2rem;
}

.chart-legend {
    flex: 1;
}

.legend-item {
    display: flex;
    align-items: center;
    margin-bottom: 0.75rem;
}

.legend-color {
    width: 16px;
    height: 16px;
    border-radius: 50%;
    margin-right: 0.75rem;
}

.legend-text {
    font-size: 0.9rem;
}

.chart-pie {
    position: relative;
    width: 180px;
    height: 180px;
    border-radius: 50%;
    background: #f5f5f5;
}

.pie-segment {
    position: absolute;
    width: 100%;
    height: 100%;
    border-radius: 50%;
    clip-path: polygon(50% 50%, 50% 0%, 100% 0%, 100% 100%, 0% 100%, 0% 0%, 50% 0%);
    background: conic-gradient(
        var(--segment-color) var(--segment-start),
        var(--segment-color) var(--segment-end),
        transparent var(--segment-end)
    );
    transform: rotate(var(--segment-start));
}

.pie-center {
    position: absolute;
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%);
    width: 60%;
    height: 60%;
    background: white;
    border-radius: 50%;
    display: flex;
    flex-direction: column;
    justify-content: center;
    align-items: center;
    box-shadow: 0 0 10px rgba(0,0,0,0.1);
}

.pie-total {
    font-size: 1.1rem;
    font-weight: 600;
    color: var(--primary-color);
}

.pie-label {
    font-size: 0.8rem;
    color: var(--gray-dark);
}

.summary-metrics {
    display: flex;
    flex-direction: column;
    gap: 1.5rem;
}

.metric-card {
    flex: 1;
    padding: 1.5rem;
    background: var(--gray-light);
    border-radius: var(--radius);
}

.metric-value {
    font-size: 1.5rem;
    font-weight: 600;
    color: var(--primary-color);
    margin-bottom: 1rem;
}

.metric-chart {
    display: flex;
    align-items: center;
    gap: 1rem;
}

.progress-bar {
    flex: 1;
    height: 10px;
    background: #ddd;
    border-radius: 5px;
    overflow: hidden;
}

.progress-fill {
    height: 100%;
    background: var(--primary-color);
}

.progress-label {
    font-weight: 600;
    color: var(--primary-color);
    min-width: 48px;
    text-align: right;
}

@media (max-width: 992px) {
    .income-summary {
        grid-template-columns: 1fr;
    }
}

@media (max-width: 768px) {
    .chart-container {
        flex-direction: column;
    }

    .chart-pie {
        margin: 0 auto;
    }
}
</style>
