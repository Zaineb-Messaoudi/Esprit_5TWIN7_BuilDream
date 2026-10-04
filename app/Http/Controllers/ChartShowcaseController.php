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

        $examples = [
            ...$examples,
            ...$this->additionalBarAndPieExamples($colors, $categories),
        ];

        return view('pages.chart.examples', [
            'title' => __('Chart Library'),
            'examples' => $examples,
        ]);
    }

    public function radar(): View
    {
        $colors = ['#465fff', '#12b76a', '#f79009'];
        $labels = [__('Revenue'), __('Retention'), __('Delivery'), __('Satisfaction'), __('Growth'), __('Efficiency')];

        return view('pages.chart.specialized', [
            'title' => __('Radar Charts'),
            'description' => __('Compare multiple performance dimensions with three radar formats.'),
            'examples' => [
                [
                    'title' => __('Basic Radar'),
                    'description' => __('A single data series across key performance dimensions.'),
                    'options' => $this->radarOptions($labels, [
                        ['name' => __('Current quarter'), 'data' => [78, 64, 86, 71, 82, 68]],
                    ], [$colors[0]]),
                ],
                [
                    'title' => __('Radar with Multiple Series'),
                    'description' => __('Overlay multiple series to compare team performance.'),
                    'options' => $this->radarOptions($labels, [
                        ['name' => __('Current quarter'), 'data' => [78, 64, 86, 71, 82, 68]],
                        ['name' => __('Previous quarter'), 'data' => [65, 72, 70, 78, 69, 74]],
                    ], array_slice($colors, 0, 2)),
                ],
                [
                    'title' => __('Radar with Polygon Fill'),
                    'description' => __('A filled comparison highlights the shape and spread of each series.'),
                    'options' => $this->radarOptions($labels, [
                        ['name' => __('Residential'), 'data' => [84, 71, 78, 90, 76, 82]],
                        ['name' => __('Commercial'), 'data' => [68, 88, 72, 74, 91, 79]],
                        ['name' => __('Storage'), 'data' => [76, 80, 91, 69, 73, 86]],
                    ], $colors, true),
                ],
            ],
        ]);
    }

    public function radialProgress(): View
    {
        return view('pages.chart.specialized', [
            'title' => __('Radial Progress Charts'),
            'description' => __('Four radial progress layouts for goals, completion, and multi-value indicators.'),
            'examples' => [
                $this->radialExample(__('Single Progress'), __('A compact single-value progress ring.'), [78], [__('Quarterly target')], ['hollow' => ['size' => '62%']]),
                $this->radialExample(__('Multiple Progress'), __('Compare completion values in concentric rings.'), [84, 67, 52], [__('Revenue'), __('Retention'), __('Growth')], ['hollow' => ['size' => '35%'], 'track' => ['margin' => 5]]),
                $this->radialExample(__('Semi-circle Progress'), __('A half-circle meter for at-a-glance goal tracking.'), [72], [__('Monthly goal')], ['startAngle' => -90, 'endAngle' => 90, 'hollow' => ['size' => '58%']]),
                $this->radialExample(__('Progress with Labels'), __('Show labels and values together for each progress series.'), [92, 74, 61], [__('Complete'), __('In progress'), __('Planned')], ['hollow' => ['size' => '38%'], 'track' => ['margin' => 4], 'dataLabels' => ['name' => ['show' => true], 'value' => ['show' => true]]]),
            ],
        ]);
    }

    private function radarOptions(array $labels, array $series, array $colors, bool $filled = false): array
    {
        return [
            'chart' => ['type' => 'radar', 'toolbar' => ['show' => false]],
            'series' => $series,
            'labels' => $labels,
            'xaxis' => ['categories' => $labels],
            'colors' => $colors,
            'yaxis' => ['show' => false, 'min' => 0, 'max' => 100],
            'stroke' => ['width' => 2],
            'fill' => ['opacity' => $filled ? 0.2 : 0.08],
            'markers' => ['size' => 3],
            'legend' => ['position' => 'bottom'],
            'plotOptions' => ['radar' => ['polygons' => ['strokeColors' => '#e4e7ec', 'connectorColors' => '#e4e7ec', 'fill' => ['colors' => ['#f9fafb', '#ffffff']]]]],
        ];
    }

    private function radialExample(string $title, string $description, array $series, array $labels, array $plotOptions): array
    {
        return [
            'title' => $title,
            'description' => $description,
            'height' => 300,
            'options' => [
                'chart' => ['type' => 'radialBar', 'toolbar' => ['show' => false]],
                'series' => $series,
                'labels' => $labels,
                'colors' => ['#465fff', '#12b76a', '#f79009'],
                'plotOptions' => ['radialBar' => $plotOptions],
                'stroke' => ['lineCap' => 'round'],
                'legend' => ['show' => count($series) > 1, 'position' => 'bottom'],
            ],
        ];
    }

    private function additionalBarAndPieExamples(array $colors, array $categories): array
    {
        return [
            [
                'title' => __('Bar Chart Five · Distributed'),
                'description' => __('Color each bar to distinguish individual categories.'),
                'options' => [
                    'chart' => ['type' => 'bar', 'toolbar' => ['show' => false]],
                    'series' => [['name' => __('Installations'), 'data' => [44, 58, 37, 71, 63, 82]]],
                    'xaxis' => ['categories' => [__('Tunis'), __('Sousse'), __('Sfax'), __('Bizerte'), __('Gabes'), __('Other')]],
                    'colors' => [$colors[0], $colors[1], $colors[2], '#7a5af8', '#0ba5ec', '#f04438'],
                    'plotOptions' => ['bar' => ['distributed' => true, 'borderRadius' => 4, 'columnWidth' => '52%']],
                    'dataLabels' => ['enabled' => false],
                    'legend' => ['show' => false],
                ],
            ],
            [
                'title' => __('Bar Chart Six · Stacked Comparison'),
                'description' => __('Compare completed and planned values in stacked categories.'),
                'options' => [
                    'chart' => ['type' => 'bar', 'stacked' => true, 'toolbar' => ['show' => false]],
                    'series' => [
                        ['name' => __('Completed'), 'data' => [38, 42, 31, 49, 44, 57]],
                        ['name' => __('Planned'), 'data' => [12, 16, 18, 14, 21, 17]],
                    ],
                    'xaxis' => ['categories' => $categories],
                    'colors' => array_slice($colors, 0, 2),
                    'plotOptions' => ['bar' => ['borderRadius' => 4, 'columnWidth' => '52%']],
                    'dataLabels' => ['enabled' => false],
                    'legend' => ['position' => 'top'],
                ],
            ],
            [
                'title' => __('Pie Chart Four · Semi Donut'),
                'description' => __('A half-donut variation for compact distribution summaries.'),
                'options' => [
                    'chart' => ['type' => 'donut', 'toolbar' => ['show' => false]],
                    'series' => [42, 28, 18, 12],
                    'labels' => [__('Residential'), __('Commercial'), __('Storage'), __('Other')],
                    'colors' => $colors,
                    'plotOptions' => ['pie' => ['startAngle' => -90, 'endAngle' => 90, 'donut' => ['size' => '65%']]],
                    'legend' => ['position' => 'bottom'],
                ],
            ],
            [
                'title' => __('Pie Chart Five · Data Labels'),
                'description' => __('Show percentages directly on each distribution segment.'),
                'options' => [
                    'chart' => ['type' => 'pie', 'toolbar' => ['show' => false]],
                    'series' => [46, 25, 17, 12],
                    'labels' => [__('Panels'), __('Inverters'), __('Batteries'), __('Other')],
                    'colors' => $colors,
                    'dataLabels' => ['enabled' => true],
                    'legend' => ['position' => 'bottom'],
                ],
            ],
        ];
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
