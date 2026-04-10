<?php

namespace App\Modules\TaskAssignment\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

/**
 * Export file mẫu để người dùng tải về và điền dữ liệu văn bản giao việc trước khi import.
 *
 * Cấu trúc file:
 *   - Hàng 1: Tên cột (WithHeadingRow sẽ normalize thành key để map trong Import class)
 *   - Hàng 2+: Dữ liệu mẫu minh hoạ (có thể xóa hoặc ghi đè)
 *
 * Mapping key sau normalize:
 *   "name"        → $row['name']
 *   "loai_van_ban" → $row['loai_van_ban']  (tên loại, hệ thống tự map sang ID)
 *   "ngay_ban_hanh" → $row['ngay_ban_hanh'] (định dạng d/m/Y)
 *   "tom_tat"     → $row['tom_tat']
 */
class TaskAssignmentDocumentsTemplateExport implements FromArray, WithHeadings, WithStyles, WithColumnWidths, WithTitle
{
    /**
     * Dữ liệu mẫu minh hoạ — người dùng có thể xóa hoặc ghi đè.
     */
    public function array(): array
    {
        return [
            ['Văn bản giao việc mẫu 1', '1', now()->format('Y/m/d'), 'Đây là tóm tắt nội dung văn bản giao việc mẫu.', 'draft'],
            ['Văn bản giao việc mẫu 2', '2', now()->format('Y/m/d'), 'Đây là tóm tắt nội dung văn bản giao việc mẫu.', 'issued'],
        ];
    }

    /**
     * Hàng tiêu đề — WithHeadingRow sẽ normalize thành key để map trong Import class.
     * "name"
     * "task_assignment_type_id"
     * "issue_date"
     * "summary"
     * "status"
     */
    public function headings(): array
    {
        return [
            'name',
            'task_assignment_type_id',
            'issue_date',
            'summary',
            'status',
        ];
    }

    /**
     * Tên sheet.
     */
    public function title(): string
    {
        return 'Danh sách văn bản giao việc';
    }

    /**
     * Định dạng style cho file mẫu.
     */
    public function styles(Worksheet $sheet): array
    {
        // Hàng 1: tiêu đề — nền xanh đậm, chữ trắng, in đậm
        $sheet->getStyle('A1:E1')->applyFromArray([
            'font'      => ['bold' => true, 'color' => ['argb' => 'FFFFFFFF'], 'size' => 11],
            'fill'      => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => 'FF4472C4']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'wrapText' => true],
            'borders'   => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['argb' => 'FFBFBFBF']]],
        ]);

        // Ghi chú hướng dẫn ở ô F1
        $sheet->setCellValue('F1', '(*) name là bắt buộc. Xóa các dòng mẫu và điền dữ liệu thực từ hàng 2.');
        $sheet->getStyle('F1')->applyFromArray([
            'font' => ['color' => ['argb' => 'FFFF0000'], 'bold' => true, 'size' => 10],
        ]);
        $sheet->getColumnDimension('F')->setWidth(80);

        return [];
    }

    /**
     * Độ rộng cột.
     */
    public function columnWidths(): array
    {
        return [
            'A' => 40,
            'B' => 30,
            'C' => 25,
            'D' => 50,
        ];
    }
}
