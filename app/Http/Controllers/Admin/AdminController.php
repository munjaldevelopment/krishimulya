<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Routing\Controller;

class AdminController extends Controller
{
    protected $data = []; // the information we send to the view

    /**
     * Create a new controller instance.
     */
    public function __construct()
    {
        $this->middleware(backpack_middleware());
    }

    /**
     * Show the admin dashboard.
     *
     * @return \Illuminate\Http\Response
     */
    public function dashboard()
    {
        $this->data['title'] = trans('backpack::base.dashboard'); // set the page title
        $this->data['breadcrumbs'] = [
            trans('backpack::crud.admin')     => backpack_url('dashboard'),
            trans('backpack::base.dashboard') => false,
        ];

        $chart1 = \Chart::title([
            'text' => 'New Users Past 7 Days',
        ])
        ->chart([
            'type'     => 'column', // pie , columnt ect
            'renderTo' => 'chart1', // render the chart into your div with id
        ])
        ->subtitle([
            'text' => '',
        ])
        ->xaxis([
            'categories' => [
                '6 days ago',
                '5 days ago',
                '4 days ago',
                '3 days ago',
                '2 days ago',
                'Yesterday',
                'Today'
            ],
            'crosshair' => 'true',
        ])
        ->yaxis([
            'min' => '0',
            'title' => [
                'text' => ''
            ],
        ])
        ->series(
            [
                [
                    'name'  => '',
                    'data'  => [13, 11, 23, 19, 19, 13, 11],
                    // 'color' => '#0c2959',
                ],
            ]
        )
        ->display();

        $this->data['chart1'] = $chart1;

        $chart2 = \Chart::title([
            'text' => 'New Entries',
        ])
        ->chart([
            'type'     => 'areaspline', // pie , columnt ect
            'renderTo' => 'chart2', // render the chart into your div with id
        ])
        ->subtitle([
            'text' => '',
        ])
        ->xaxis([
            'categories' => [
                "30 days ago","29 days ago","28 days ago","27 days ago","26 days ago","25 days ago","24 days ago","23 days ago","22 days ago","21 days ago","20 days ago","19 days ago","18 days ago","17 days ago","16 days ago","15 days ago","14 days ago","13 days ago","12 days ago","11 days ago","10 days ago","9 days ago","8 days ago","7 days ago","6 days ago","5 days ago","4 days ago","3 days ago","2 days ago","1 days ago","0 days ago"
            ],
            'crosshair' => 'true',
        ])
        ->yaxis([
            'min' => '0',
            'title' => [
                'text' => ''
            ],
        ])
        ->series(
            [
                [
                    'name'  => '',
                    'data'  => [13, 11, 23, 19, 19, 13, 11, 3, 4, 3, 5, 4, 10, 12],
                    // 'color' => '#0c2959',
                ],
            ]
        )
        ->display();
        $this->data['chart2'] = $chart2;

        $chart3 = \Chart::title([
            'text' => 'Voting ballon d`or 2018',
        ])
        ->chart([
            'type'     => 'line', // pie , columnt ect
            'renderTo' => 'chart3', // render the chart into your div with id
        ])
        ->subtitle([
            'text' => 'This Subtitle',
        ])
        ->colors([
            '#0c2959'
        ])
        ->xaxis([
            'categories' => [
                'Alex Turner',
                'Julian Casablancas',
                'Bambang Pamungkas',
                'Mbah Surip',
            ],
            'labels'     => [
                'rotation'  => 15,
                'align'     => 'top',
                'formatter' => 'startJs:function(){return this.value + " (Footbal Player)"}:endJs', 
                // use 'startJs:yourjavasscripthere:endJs'
            ],
        ])
        ->yaxis([
            'text' => 'This Y Axis',
        ])
        ->legend([
            'layout'        => 'vertikal',
            'align'         => 'right',
            'verticalAlign' => 'middle',
        ])
        ->series(
            [
                [
                    'name'  => 'Voting',
                    'data'  => [43934, 52503, 57177, 69658],
                    // 'color' => '#0c2959',
                ],
            ]
        )
        ->display();
        $this->data['chart3'] = $chart3;

        $chart4 = \Chart::title([
            'text' => 'Voting ballon d`or 2018',
        ])
        ->chart([
            'type'     => 'line', // pie , columnt ect
            'renderTo' => 'chart4', // render the chart into your div with id
        ])
        ->subtitle([
            'text' => 'This Subtitle',
        ])
        ->colors([
            '#0c2959'
        ])
        ->xaxis([
            'categories' => [
                'Alex Turner',
                'Julian Casablancas',
                'Bambang Pamungkas',
                'Mbah Surip',
            ],
            'labels'     => [
                'rotation'  => 15,
                'align'     => 'top',
                'formatter' => 'startJs:function(){return this.value + " (Footbal Player)"}:endJs', 
                // use 'startJs:yourjavasscripthere:endJs'
            ],
        ])
        ->yaxis([
            'text' => 'This Y Axis',
        ])
        ->legend([
            'layout'        => 'vertikal',
            'align'         => 'right',
            'verticalAlign' => 'middle',
        ])
        ->series(
            [
                [
                    'name'  => 'Voting',
                    'data'  => [43934, 52503, 57177, 69658],
                    // 'color' => '#0c2959',
                ],
            ]
        )
        ->display();
        $this->data['chart4'] = $chart4;

        $chart5 = \Chart::title([
            'text' => 'Voting ballon d`or 2018',
        ])
        ->chart([
            'type'     => 'line', // pie , columnt ect
            'renderTo' => 'chart5', // render the chart into your div with id
        ])
        ->subtitle([
            'text' => 'This Subtitle',
        ])
        ->colors([
            '#0c2959'
        ])
        ->xaxis([
            'categories' => [
                'Alex Turner',
                'Julian Casablancas',
                'Bambang Pamungkas',
                'Mbah Surip',
            ],
            'labels'     => [
                'rotation'  => 15,
                'align'     => 'top',
                'formatter' => 'startJs:function(){return this.value + " (Footbal Player)"}:endJs', 
                // use 'startJs:yourjavasscripthere:endJs'
            ],
        ])
        ->yaxis([
            'text' => 'This Y Axis',
        ])
        ->legend([
            'layout'        => 'vertikal',
            'align'         => 'right',
            'verticalAlign' => 'middle',
        ])
        ->series(
            [
                [
                    'name'  => 'Voting',
                    'data'  => [43934, 52503, 57177, 69658],
                    // 'color' => '#0c2959',
                ],
            ]
        )
        ->display();
        $this->data['chart5'] = $chart5;

        $chart6 = app()->chartjs
         ->name('barChartTest')
         ->type('bar')
         ->size(['width' => 400, 'height' => 200])
         ->labels(['Label x', 'Label y'])
         ->datasets([
             [
                 "label" => "My First dataset",
                 'backgroundColor' => ['rgba(255, 99, 132, 0.2)', 'rgba(54, 162, 235, 0.2)'],
                 'data' => [69, 59]
             ],
             [
                 "label" => "My First dataset",
                 'backgroundColor' => ['rgba(255, 99, 132, 0.3)', 'rgba(54, 162, 235, 0.3)'],
                 'data' => [65, 12]
             ]
         ])
         ->options([]);
        $this->data['chart6'] = $chart6;

        $chart7 = app()->chartjs
         ->name('barChartTest1')
         ->type('bar')
         ->size(['width' => 400, 'height' => 200])
         ->labels(['Label x', 'Label y'])
         ->datasets([
             [
                 "label" => "My First dataset1",
                 'backgroundColor' => ['rgba(255, 99, 132, 0.2)', 'rgba(54, 162, 235, 0.2)'],
                 'data' => [69, 59]
             ],
             [
                 "label" => "My First dataset2",
                 'backgroundColor' => ['rgba(255, 99, 132, 0.3)', 'rgba(54, 162, 235, 0.3)'],
                 'data' => [65, 12]
             ]
         ])
         ->options([]);
        $this->data['chart7'] = $chart7;

        return view('admin.dashboard', $this->data);
    }

    /**
     * Redirect to the dashboard.
     *
     * @return \Illuminate\Routing\Redirector|\Illuminate\Http\RedirectResponse
     */
    public function redirect()
    {
        // The '/admin' route is not to be used as a page, because it breaks the menu's active state.
        return redirect(backpack_url('dashboard'));
    }
}
