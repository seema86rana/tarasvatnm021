<?php

namespace App\Exports;

use Carbon\Carbon;
use Illuminate\Http\Request;
use App\Models\MachineStatusLog;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Color;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithHeadings;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use PhpOffice\PhpSpreadsheet\Style\Conditional;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class MachineLogExport implements FromCollection, WithHeadings, WithMapping, WithStyles, ShouldAutoSize
{
    protected $request;

    public function __construct(Request $request)
    {
        $this->request = $request;
    }

    public function collection()
    {
        ini_set('memory_limit', '2G'); // Increase memory limit
        set_time_limit(0); // Prevent timeout

        $user_id = $this->request->user_id;
        $device_id = $this->request->device_id;
        $select_shift = $this->request->select_shift;
        $select_shift_day = $this->request->select_shift_day;
        $node_id = $this->request->node_id;
        $machine_id = $this->request->machine_id;
        $dateRange = $this->request->dateRange;

        $fromDate = Carbon::now()->subDay();
        $toDate = Carbon::now();

        if ($dateRange) {
            $dateArray = explode(" - ", $dateRange);
            $fromDate = Carbon::parse(trim($dateArray[0]))->format('Y-m-d H:i:s');
            $toDate = Carbon::parse(trim($dateArray[1]))->format('Y-m-d H:i:s');
        }

        // Extract shift times
        $startTime = $endTime = '';
        if ($select_shift) {
            [$startTime, $endTime] = array_map(fn($t) => date('H:i:s', strtotime(trim($t))), explode(' - ', $select_shift));
        }

        // Extract shift days
        $startDay = $endDay = '';
        if ($select_shift_day) {
            [$startDay, $endDay] = array_map('trim', explode(' - ', $select_shift_day));
        }

        $query = MachineStatusLog::query()
            ->when(!empty($user_id), function ($query) use ($user_id) {
                return $query->whereHas('machine.node.device.user', function ($q) use ($user_id) {
                    $q->where('user_id', $user_id);
                });
            })
            ->when(!empty($device_id), function ($query) use ($device_id) {
                return $query->whereHas('machine.node.device', function ($q) use ($device_id) {
                    $q->where('device_id', $device_id);
                });
            })
            ->when(!empty($node_id), function ($query) use ($node_id) {
                return $query->whereHas('machine.node', function ($q) use ($node_id) {
                    $q->where('node_id', $node_id);
                });
            })
            ->when(!empty($machine_id), function ($query) use ($machine_id) {
                return $query->whereHas('machine', function ($q) use ($machine_id) {
                    $q->where('machine_id', $machine_id);
                });
            });

        if ($dateRange) {
            $query->whereBetween('machine_datetime', [$fromDate, $toDate]);
        }

        if ($dateRange) {
            $startDate = Carbon::parse($fromDate);
            $endDate = Carbon::parse($toDate);

            if ($startTime && $endTime) {
                // If shift crosses midnight, adjust end date
                $modifyEndDate = ($endDay == '2') ? $startDate->addDay() : $startDate;

                $start_date = Carbon::createFromFormat('Y-m-d H:i:s', "{$startDate->format('Y-m-d')} {$startTime}");
                $end_date = Carbon::createFromFormat('Y-m-d H:i:s', "{$modifyEndDate->format('Y-m-d')} {$endTime}");

                $query->whereBetween('machine_datetime', [$start_date, $end_date]);
            } else {
                // Default full-day filter
                $query->whereBetween('machine_datetime', [$fromDate, $toDate]);
            }
        } elseif ($startTime && $endTime) {
            // Filter only by time when no date is selected
            $query->whereRaw("TIME(machine_datetime) BETWEEN ? AND ?", [$startTime, $endTime]);
        }

        return $query->orderBy('id', 'ASC')->get();
    }

    public function headings(): array
    {
        return [
            'ID', 'Device Name', 'Machine Name', 'Total Running (min)', 'Total Time (min)', 
            'Efficiency (%)', 'Shift Detail', 'Device Datetime', 'Machine Datetime', 
            'Last Stop (min)', 'Last Running (min)', 'No. of Stoppage', 'Mode', 'Speed', 'Pick'
        ];
    }

    public function map($row): array
    {
        $efficiency = $row->efficiency ?? 0;

        // Determine efficiency text color
        $efficiencyColor = match (true) {
            $efficiency >= 90 => '00FF00', // Green
            $efficiency >= 70 && $efficiency < 90 => 'FFFF00', // Yellow
            $efficiency >= 50 && $efficiency < 70 => 'FFA500', // Orange
            $efficiency < 50 => 'FF0000', // Red
            default => '000000', // Black
        };

        return [
            $row->id,
            $row->machine->node->device->name ?? '--------',
            $row->machine->display_name ?? $row->machine->name,
            $row->total_running ? (int) $row->total_running : '--------',
            $row->total_time ? (int) $row->total_time : '--------',
            $efficiency,
            "Shift ({$row->shift_start_datetime} - {$row->shift_end_datetime})",
            $row->device_datetime ? date('d/m/Y H:i:s', strtotime($row->device_datetime)) : '--------',
            $row->machine_datetime ? date('d/m/Y H:i:s', strtotime($row->machine_datetime)) : '--------',
            $row->last_stop ? (int) $row->last_stop : '--------',
            $row->last_running ? (int) $row->last_running : '--------',
            $row->no_of_stoppage ?? 0,
            $row->status ?? 0,
            $row->speed ?? 0,
            self::formatIndianNumber($row->machineMasterLog->pick ?? 0),
        ];
    }

    public function styles(Worksheet $sheet)
    {
        $highestRow = $sheet->getHighestRow(); // Get last row number dynamically

        // Apply styles to the first row (header)
        $sheet->getStyle('A1:O1')->applyFromArray([
            'font' => [
                'bold' => true,
                'size' => 14,
            ],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
                'vertical' => Alignment::VERTICAL_CENTER,
            ],
        ]);

        // Center align all columns
        $sheet->getStyle('A:O')->applyFromArray([
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
                'vertical' => Alignment::VERTICAL_CENTER,
            ],
        ]);

        // Set efficiency column (F) background to gray
        $sheet->getStyle("F1:F{$highestRow}")->applyFromArray([
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'startColor' => ['rgb' => '808080'], // Gray
            ],
            'font' => ['bold' => true],
        ]);

        if ($highestRow > 1) { // Ensure there are records
            $range = 'F2:F' . $highestRow; // Dynamic range

            // Apply conditional formatting for efficiency text color
            $conditionalStyles = [];

            // Green (90%+)
            $greenCondition = new Conditional();
            $greenCondition->setConditionType(Conditional::CONDITION_CELLIS)
                ->setOperatorType(Conditional::OPERATOR_GREATERTHANOREQUAL)
                ->addCondition(90)
                ->getStyle()
                ->getFont()->getColor()->setARGB(Color::COLOR_GREEN);

            // Yellow (70-90%)
            $yellowCondition = new Conditional();
            $yellowCondition->setConditionType(Conditional::CONDITION_CELLIS)
                ->setOperatorType(Conditional::OPERATOR_BETWEEN)
                ->addCondition(70)
                ->addCondition(89.99)
                ->getStyle()
                ->getFont()->getColor()->setARGB('FFFF00'); // Yellow

            // Orange (50-70%)
            $orangeCondition = new Conditional();
            $orangeCondition->setConditionType(Conditional::CONDITION_CELLIS)
                ->setOperatorType(Conditional::OPERATOR_BETWEEN)
                ->addCondition(50)
                ->addCondition(69.99)
                ->getStyle()
                ->getFont()->getColor()->setARGB('FFA500'); // Orange

            // Red (<50%)
            $redCondition = new Conditional();
            $redCondition->setConditionType(Conditional::CONDITION_CELLIS)
                ->setOperatorType(Conditional::OPERATOR_LESSTHAN)
                ->addCondition(50)
                ->getStyle()
                ->getFont()->getColor()->setARGB(Color::COLOR_RED);

            // Apply conditions to column F (Efficiency)
            $conditionalStyles[] = $greenCondition;
            $conditionalStyles[] = $yellowCondition;
            $conditionalStyles[] = $orangeCondition;
            $conditionalStyles[] = $redCondition;
        }

        $sheet->getStyle($range)->setConditionalStyles($conditionalStyles);
    }

    private static function formatIndianNumber($num) {
        return number_format($num, 0, '.', ',');
    }
}
