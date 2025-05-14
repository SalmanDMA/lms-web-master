<?php

namespace App\Exports;

use App\Models\Submission;
use App\Models\User;
use Illuminate\Support\Facades\Log;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Border;

class TugasSubmissionExport implements FromCollection, WithHeadings, WithTitle, WithEvents, WithStyles
{
    protected $tugas_id;

    public function __construct($tugas_id)
    {
        $this->tugas_id = $tugas_id;
    }

    public function collection()
    {
        $submissions = Submission::with(['student', 'grades'])
            ->where('assignment_id', $this->tugas_id)
            ->get();

        Log::info('Submissions retrieved: ', $submissions->toArray());

        $result = $submissions->map(function ($submission) {
            $grades = $submission->grades->first();
            $user = User::find($submission->student->user_id);
            return [
                'nisn' => $submission->student->nisn,
                'name' => $user ? $user->fullname : '-',
                'knowledge' => $grades ? $grades->knowledge : '-',
                'skills' => $grades ? $grades->skills : '-',
                'status' => $grades ? $grades->status : '-',
                'publication_status' => $grades ? $grades->publication_status : '-',
            ];
        });

        Log::info('Formatted data for export: ', $result->toArray());

        return $result;
    }

    public function headings(): array
    {
        return [
            'NISN',
            'Nama',
            'Nilai Pengetahuan',
            'Nilai Keterampilan',
            'Status',
            'Status Publikasi',
        ];
    }

    public function title(): string
    {
        return 'Rekap Nilai Tugas';
    }

    public function registerEvents(): array
    {
        return [
            \Maatwebsite\Excel\Events\AfterSheet::class => function (\Maatwebsite\Excel\Events\AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();
                // Add title
                $sheet->mergeCells('A1:F1');
                $sheet->setCellValue('A1', 'REKAP NILAI');
                $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(14);
                $sheet->getStyle('A1')->getAlignment()->setHorizontal('center');

                // Move headings to row 3
                $headings = $this->headings();
                $sheet->fromArray($headings, null, 'A2');

                // Set headings bold
                $sheet->getStyle('A2:F2')->getFont()->setBold(true);

                // Add borders to all cells
                $highestRow = $sheet->getHighestRow();
                $highestColumn = $sheet->getHighestColumn();

                // Move data starting from row 4
                $sheet->fromArray($this->collection()->toArray(), null, 'A3');

                // Set borders for data range
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
            'A2:F2' => ['font' => ['bold' => true]],
        ];
    }
}
