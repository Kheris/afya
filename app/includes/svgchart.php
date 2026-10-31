<?php
/**
 * Inatengeneza chati rahisi ya mstari (line chart) kwa SVG safi,
 * bila kuhitaji maktaba yoyote ya nje (JS library au PHP extension).
 * Inafaa vizuri kwa InfinityFree kwa sababu haihitaji kitu chochote maalum.
 *
 * @param array $points [['label' => '12 Sep', 'value' => 62.5], ...]
 * @param string $color Rangi ya mstari (hex)
 * @param string $unit Kiambatanisho cha thamani (mfano 'kg', '°C')
 */
function renderSvgLineChart(array $points, string $color = '#2f80ed', string $unit = ''): string {
    if (count($points) < 2) {
        return '<p class="muted">Bado hakuna data ya kutosha kuonyesha mwenendo (angalau vipimo 2 vinahitajika).</p>';
    }

    $width = 320;
    $height = 140;
    $paddingX = 30;
    $paddingY = 20;

    $values = array_column($points, 'value');
    $minVal = min($values);
    $maxVal = max($values);
    if ($minVal === $maxVal) { $minVal -= 1; $maxVal += 1; }

    $n = count($points);
    $stepX = ($width - 2 * $paddingX) / ($n - 1);

    $coords = [];
    foreach ($points as $i => $p) {
        $x = $paddingX + $i * $stepX;
        $y = $height - $paddingY - (($p['value'] - $minVal) / ($maxVal - $minVal)) * ($height - 2 * $paddingY);
        $coords[] = [$x, $y];
    }

    $pathD = 'M ' . implode(' L ', array_map(fn($c) => round($c[0], 1) . ' ' . round($c[1], 1), $coords));

    $circles = '';
    foreach ($coords as $c) {
        $circles .= '<circle cx="' . round($c[0], 1) . '" cy="' . round($c[1], 1) . '" r="3.5" fill="' . $color . '" />';
    }

    $lastVal = end($values);
    $firstLabel = htmlspecialchars($points[0]['label']);
    $lastLabel = htmlspecialchars(end($points)['label']);

    return '
    <svg viewBox="0 0 ' . $width . ' ' . $height . '" width="100%" height="' . $height . '" xmlns="http://www.w3.org/2000/svg">
        <path d="' . $pathD . '" fill="none" stroke="' . $color . '" stroke-width="2.5" stroke-linejoin="round" stroke-linecap="round"/>
        ' . $circles . '
        <text x="' . $paddingX . '" y="' . ($height - 4) . '" font-size="10" fill="#5b6472">' . $firstLabel . '</text>
        <text x="' . ($width - $paddingX) . '" y="' . ($height - 4) . '" font-size="10" fill="#5b6472" text-anchor="end">' . $lastLabel . '</text>
    </svg>
    <div class="chart-latest">Ya mwisho: <strong>' . htmlspecialchars((string)$lastVal) . ' ' . htmlspecialchars($unit) . '</strong></div>';
}
