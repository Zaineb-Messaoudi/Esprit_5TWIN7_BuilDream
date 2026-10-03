<?php

namespace App\Http\Controllers;

use Illuminate\Contracts\View\View;

class ChartShowcaseController extends Controller
{
    public function index(): View
    {
        $categories = [__('Jan'), __('Feb'), __('Mar'), __('Apr'), __('May'), __('Jun')];
        $colors = [
            '#465fff',
            '#0ba5ec',
            '#12b76a',
            '#f79009',
        ];

        $examples = [
            $this->cartesian('Line chart', 'Smooth trend series with interactive points.', 'line', [
                ['name' => __('Revenue'), 'data' => [31, 42, 36, 52, 48, 65]],
            ], $categories, $colors),
            $this->cartesian('Area chart', 'Filled time series with a responsive tooltip.', 'area', [
                ['name' => __('Visitors'), 'data' => [18, 34, 29, 48, 42, 61]],
            ], $categories, $colors),
            $this->cartesian('Bar chart', 'Vertical category comparison.', 'bar', [
                ['name' => __('Orders'), 'data' => [24, 31, 27, 42, 35, 51]],
            ], $categories, $colors),
            $this->cartesian('Horizontal bar', 'Horizontal ranking for category labels.', 'bar', [
                ['name' => __('Orders'), 'data' => [44, 38, 32, 27, 19, 14]],
            ], [__('Panels'), __('Inverters'), __('Batteries'), __('Mounts'), __('Cables'), __('Other')], $colors, true),
            $this->cartesian('Stacked bar', 'Stacked series reveal totals and composition.', 'bar', [
                ['name' => __('Residential'), 'data' => [18, 22, 19, 27, 25, 31]],
                ['name' => __('Commercial'), 'data' => [8, 11, 10, 13, 12, 17]],
            ], $categories, $colors, false, true),
            $this->cartesian('Grouped bar', 'Side-by-side comparison across two periods.', 'bar', [
                ['name' => __('This year'), 'data' => [21, 29, 25, 38, 33, 46]],
                ['name' => __('Last year'), 'data' => [16, 24, 20, 31, 28, 37]],
            ], $categories, $colors),
            [
                'title' => __('Pie chart'),
                'description' => __('Part-to-whole comparison for a small set of categories.'),
                'options' => [
                    'chart' => ['type' => 'pie', 'toolbar' => ['show' => false]],
                    'series' => [44, 28, 18, 10],
                    'labels' => [__('Residential'), __('Commercial'), __('Storage'), __('Other')],
                    'colors' => $colors,
                    'legend' => ['position' => 'bottom'],
                    'responsive' => [['breakpoint' => 640, 'options' => ['legend' => ['position' => 'bottom']]]],
                ],
            ],
            [
                'title' => __('Donut chart'),
                'description' => __('Distribution chart with a clear center label.'),
                'options' => [
                    'chart' => ['type' => 'donut', 'toolbar' => ['show' => false]],
                    'series' => [48, 26, 16, 10],
                    'labels' => [__('Residential'), __('Commercial'), __('Storage'), __('Other')],
                    'colors' => $colors,
                    'legend' => ['position' => 'bottom'],
                    'plotOptions' => ['pie' => ['donut' => ['size' => '68%', 'labels' => ['show' => true, 'total' => ['show' => true, 'label' => __('Total')]]]]],
                ],
            ],
            [
                'title' => __('Radial chart'),
                'description' => __('Compact progress visualization for goal tracking.'),
                'options' => [
                    'chart' => ['type' => 'radialBar', 'toolbar' => ['show' => false]],
                    'series' => [78],
                    'labels' => [__('Quarterly target')],
                    'colors' => [$colors[0]],
                    'plotOptions' => ['radialBar' => ['hollow' => ['size' => '62%'], 'dataLabels' => ['name' => ['show' => true], 'value' => ['fontSize' => '28px']]]],
                ],
            ],
            [
                'title' => __('Radar chart'),
                'description' => __('Compare multiple performance dimensions at a glance.'),
                'options' => [
                    'chart' => ['type' => 'radar', 'toolbar' => ['show' => false]],
                    'series' => [
                        ['name' => __('Current quarter'), 'data' => [78, 64, 86, 71, 82, 68]],
                        ['name' => __('Previous quarter'), 'data' => [65, 72, 70, 78, 69, 74]],
                    ],
                    'xaxis' => ['categories' => [__('Revenue'), __('Retention'), __('Delivery'), __('Satisfaction'), __('Growth'), __('Efficiency')]],
                    'colors' => array_slice($colors, 0, 2),
                    'yaxis' => ['show' => false, 'max' => 100],
                    'legend' => ['position' => 'bottom'],
                ],
            ],
            [
                'title' => __('Sparkline'),
                'description' => __('Small trend chart suited to KPI cards and compact summaries.'),
                'height' => 110,
                'options' => [
                    'chart' => ['type' => 'area', 'sparkline' => ['enabled' => true], 'toolbar' => ['show' => false]],
                    'series' => [['name' => __('Weekly orders'), 'data' => [12, 18, 15, 23, 20, 29, 26, 34, 31, 39]]],
                    'colors' => [$colors[0]],
                    'stroke' => ['curve' => 'smooth', 'width' => 2],
                    'fill' => ['type' => 'gradient', 'gradient' => ['opacityFrom' => 0.35, 'opacityTo' => 0.05]],
                    'tooltip' => ['x' => ['show' => false]],
                ],
            ],
            [
                'title' => __('Mixed chart'),
                'description' => __('Combine column and line series to compare values and rates.'),
                'options' => [
                    'chart' => ['type' => 'line', 'stacked' => false, 'toolbar' => ['show' => false]],
                    'series' => [
                        ['name' => __('Orders'), 'type' => 'column', 'data' => [24, 31, 27, 42, 35, 51]],
                        ['name' => __('Conversion rate'), 'type' => 'line', 'data' => [12, 16, 14, 19, 17, 23]],
                    ],
                    'colors' => array_slice($colors, 0, 2),
                    'xaxis' => ['categories' => $categories],
                    'stroke' => ['width' => [0, 3], 'curve' => 'smooth'],
                    'dataLabels' => ['enabled' => false],
                    'legend' => ['position' => 'top'],
                ],
            ],
            $this->cartesian('Comparison chart', 'Compare two periods with synchronized category labels.', 'line', [
                ['name' => __('Current period'), 'data' => [34, 43, 39, 57, 53, 68]],
                ['name' => __('Previous period'), 'data' => [26, 35, 33, 44, 40, 52]],
            ], $categories, $colors),
            [
                'title' => __('KPI chart'),
                'description' => __('A metric trend with a compact summary value and change.'),
                'height' => 170,
                'options' => [
                    'chart' => ['type' => 'area', 'sparkline' => ['enabled' => true], 'toolbar' => ['show' => false]],
                    'series' => [['name' => __('Monthly recurring revenue'), 'data' => [38, 42, 40, 51, 54, 62, 58, 73]]],
                    'colors' => [$colors[2]],
                    'stroke' => ['curve' => 'smooth', 'width' => 2],
                    'fill' => ['type' => 'gradient', 'gradient' => ['opacityFrom' => 0.4, 'opacityTo' => 0.05]],
                ],
                'metric' => '$73.4k',
                'change' => '+12.8%',
            ],
        ];

        return view('pages.chart.examples', [
            'title' => __('Chart Library'),
            'examples' => $examples,
        ]);
    }

    private function cartesian(
        string $title,
        string $description,
        string $type,
        array $series,
        array $categories,
        array $colors,
        bool $horizontal = false,
        bool $stacked = false,
    ): array {
        $categoryAxis = $horizontal
            ? ['yaxis' => ['categories' => $categories]]
            : ['xaxis' => ['categories' => $categories]];

        return [
            'title' => __($title),
            'description' => __($description),
            'options' => [
                'chart' => ['type' => $type, 'stacked' => $stacked, 'toolbar' => ['show' => false]],
                'series' => $series,
                'colors' => $colors,
                ...$categoryAxis,
                'plotOptions' => ['bar' => ['horizontal' => $horizontal, 'borderRadius' => 4, 'columnWidth' => '48%']],
                'stroke' => ['curve' => 'smooth', 'width' => $type === 'line' ? 3 : 2],
                'dataLabels' => ['enabled' => false],
                'grid' => ['strokeDashArray' => 4],
                'legend' => ['position' => 'top'],
            ],
        ];
    }
}
