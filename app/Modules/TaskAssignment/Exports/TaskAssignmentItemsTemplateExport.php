<?php

namespace App\Modules\TaskAssignment\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use Maatwebsite\Excel\Concerns\WithTitle;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;

/**
 * Export file mẫu để người dùng tải về và điền dữ liệu công việc trước khi import.
 *
 * Cấu trúc file:
 * - Hàng 1: Tên cột thân thiện để người dùng đọc
 * - Hàng 2: Key kỹ thuật dùng khi import, không xóa hàng này
 * - Hàng 3+: Dữ liệu mẫu minh họa, có thể xóa hoặc ghi đè
 *
 * Lưu ý:
 * - department_ids và user_assignments được biểu diễn dưới dạng JSON string trong 1 ô
 * - Nếu Import class đọc hàng 2 làm heading row thì cần set headingRow() = 2 ở Import class
 */
class TaskAssignmentItemsTemplateExport implements FromArray, WithHeadings, WithStyles, WithColumnWidths, WithTitle
{
    /**
     * Dữ liệu mẫu minh họa.
     */
    public function array(): array
    {
        return [
            [
                'Xây dựng kế hoạch công tác tháng 5',
                1,
                1,
                'Mô tả chi tiết công việc cần làm',
                'has_deadline',
                '2026-04-01',
                '2026-04-30',
                'in_progress',
                50,
                'high',
            ],
        ];
    }

    /**
     * Hàng tiêu đề.
     * Hàng 1 là tên hiển thị.
     */
    public function headings(): array
    {
        return [
            [
                'name',
                'task_assignment_item_type_id',
                'task_assignment_document_id',
                'description',
                'deadline_type',
                'start_at',
                'end_at',
                'processing_status',
                'completion_percent',
                'priority',
            ],
        ];
    }

    /**
     * Tên sheet.
     */
    public function title(): string
    {
        return 'Task assignment items';
    }

    /**
     * Định dạng style cho file mẫu.
     */
    public function styles(Worksheet $sheet): array
    {
        // Hàng 1: tiêu đề hiển thị
        $sheet->getStyle('A1:K1')->applyFromArray([
            'font' => [
                'bold' => true,
                'color' => ['argb' => 'FFFFFFFF'],
                'size' => 11,
            ],
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'startColor' => ['argb' => 'FF4472C4'],
            ],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
                'vertical' => Alignment::VERTICAL_CENTER,
                'wrapText' => true,
            ],
            'borders' => [
                'allBorders' => [
                    'borderStyle' => Border::BORDER_THIN,
                    'color' => ['argb' => 'FFBFBFBF'],
                ],
            ],
        ]);

        // Dòng dữ liệu mẫu
        $sheet->getStyle('A3:K3')->applyFromArray([
            'alignment' => [
                'vertical' => Alignment::VERTICAL_CENTER,
                'wrapText' => true,
            ],
            'borders' => [
                'allBorders' => [
                    'borderStyle' => Border::BORDER_THIN,
                    'color' => ['argb' => 'FFD9D9D9'],
                ],
            ],
        ]);

        // Ghi chú
        // $sheet->setCellValue(
        //     'L1',
        //     'Không xóa hàng 2 vì đây là key dùng khi import. '
        //     . 'department_ids và user_assignments nhập dưới dạng JSON string. '
        //     . 'Ví dụ department_ids: [{"id":1},{"id":2}] '
        //     . 'và user_assignments: [{"user_id":12},{"user_id":25}].'
        // );

        $sheet->getStyle('L1')->applyFromArray([
            'font' => [
                'color' => ['argb' => 'FFFF0000'],
                'bold' => true,
                'size' => 10,
            ],
            'alignment' => [
                'wrapText' => true,
                'vertical' => Alignment::VERTICAL_TOP,
            ],
        ]);

        $sheet->getColumnDimension('L')->setWidth(80);

        return [];
    }

    /**
     * Độ rộng cột.
     */
    public function columnWidths(): array
    {
        return [
            'A' => 30,
            'B' => 40,
            'C' => 18,
            'D' => 24,
            'E' => 18,
            'F' => 18,
            'G' => 28,
            'H' => 20,
            'I' => 18,
            'J' => 35,
            'K' => 35,
        ];
    }
}