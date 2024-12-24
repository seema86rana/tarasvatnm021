<?php

namespace App\Http\Controllers;

use Barryvdh\DomPDF\Facade\PDF;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Spatie\Browsershot\Browsershot;
use Spipu\Html2Pdf\Html2Pdf;
use TCPDF;

class ReportController extends Controller
{
    public function generatePDF()
    {
        $html2pdf = new Html2Pdf();
        $template = view('report.pdf_test')->render();
        $html2pdf->writeHTML($template);
        $html2pdf->output(public_path('/example.pdf'));

        return response()->json(['message' => 'PDF generated successfully']);

        
        $template = view('report.pdf_test')->render();
        $html = escapeshellarg($template);
        $output = shell_exec("node pn.js $html");
        echo $output;
    
        return response()->json(['message' => 'PDF generated successfully 44']);

        $pdf = new TCPDF();
        $pdf->AddPage();
        $template = view('report.pdf_test')->render();
        $pdf->writeHTML($template, true, false, true, false, '');
        $pdf->Output(public_path('/example.pdf'), 'F');

        return response()->json(['message' => 'PDF generated successfully']);

        $html2pdf = new Html2Pdf();
        $template = view('report.pdf_test')->render();
        $html2pdf->writeHTML($template);
        $html2pdf->output(public_path('/example.pdf'));

        return response()->json(['message' => 'PDF generated successfully']);


        // $output = shell_exec('C:\\Program Files\\nodejs\\node.exe C:\\wamp64\\www\\tarasvat\\vendor\\spatie\\browsershot\\bin\\browser.js');
        // echo $output;
        // die;
        // echo shell_exec('node -v');
        // die;
        putenv('PATH=C:\\Program Files\\nodejs\\' . PATH_SEPARATOR . getenv('PATH'));
        // $output = shell_exec('node --version');
        // echo $output;
        // die;

        // echo shell_exec('echo %PATH%');die;

        $template = view('report.pdf_test')->render();

        $pdf = PDF::loadHTML($template)
              ->setOptions(['isHtml5ParserEnabled' => true, 'isPhpEnabled' => true])
              ->save(public_path('/example.pdf'));


        // Browsershot::html($template)
        //     ->showBackground()
        //     ->margins(4, 0, 4, 0)
        //     ->format('A4')
        //     ->save(public_path('/example.pdf'))
        //     ->setNodeBinary('C:\\Program Files\\nodejs\\node.exe')
        //     ->setOption('args', ['--no-sandbox']);

        // return response()->json(['message' => 'PDF generated successfully', 'path' => $filePath]);
    }

    private function generateChartImage($title, $data)
    {
        $chartConfig = [
            'type' => 'bar',
            'data' => [
                'labels' => array_column($data, 'label'),
                'datasets' => [
                    [
                        'label' => 'Yesterday',
                        'data' => array_column($data, 'previous'),
                    ],
                    [
                        'label' => 'Today',
                        'data' => array_column($data, 'current'),
                    ],
                ],
            ],
            'options' => [
                'title' => [
                    'display' => true,
                    'text' => $title,
                ],
                'scales' => [
                    'yAxes' => [['ticks' => ['beginAtZero' => true]]],
                ],
            ],
        ];

        // Generate the chart URL using QuickChart API
        $chartUrl = 'https://quickchart.io/chart?c=' . urlencode(json_encode($chartConfig));

        return $chartUrl;
    }
}
