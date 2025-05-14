<?php

namespace App\Exports;

use App\Models\Response;
use App\Models\Grade;
use App\Models\User;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithEvents;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use Maatwebsite\Excel\Events\AfterSheet;

class UlanganSubmissionExport implements FromCollection, WithHeadings, WithEvents
{
    protected $ulangan_id;

    public function __construct($ulangan_id)
    {
        $this->ulangan_id = $ulangan_id;
    }

    public function collection()
    {
        $response = Response::with(['student', 'grades'])
            ->where('exam_id', $this->ulangan_id)
            ->get();
        $grouped_by_student = $response->groupBy('student_id');
        $latest_responses = $grouped_by_student->map(function ($responses) {
            return $responses->sortBy('created_at')->first();
        })->values();

        $result = $latest_responses->map(function ($response) {
            $grades = $response->grades->first();
            $user = User::find($response->student->user_id);
            return [
                'nisn' => $response->student->nisn,
                'name' => $user ? $user->fullname : '-',
                'class_exam_id' => $grades ? $grades->class_exam : '-',
                'status' => $grades ? $grades->status : '-',
                'publication_status' => $grades ? $grades->publication_status : '-',
            ];
        });

        return $result;
    }

    public function headings(): array
    {
        return [
            'NISN',
            'Nama',
            'Ulangan',
            'Status',
            'Status Publikasi',
        ];
    }

    public function title(): string
    {
        return 'Rekap Nilai Ulangan';
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();

                // Add title
                $sheet->mergeCells('A1:E1');
                $sheet->setCellValue('A1', $this->title());
                $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(14);
                $sheet->getStyle('A1')->getAlignment()->setHorizontal('center');

                // Add headings
                $headings = $this->headings();
                $sheet->fromArray($headings, null, 'A2');

                // Set headings bold
                $sheet->getStyle('A2:E2')->getFont()->setBold(true);

                // Add data
                $data = $this->collection()->toArray();
                $sheet->fromArray($data, null, 'A3');

                // Set borders for all cells
                $highestRow = $sheet->getHighestRow();
                $highestColumn = $sheet->getHighestColumn();

                // Apply borders to all cells with data
                $sheet->getStyle('A2:' . $highestColumn . $highestRow)
                    ->getBorders()
                    ->getAllBorders()
                    ->setBorderStyle(Border::BORDER_THIN);
            },
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => ['font' => ['bold' => true]],
            'A2:E2' => ['font' => ['bold' => true]],
        ];
    }
}
