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
 * Export file mẫu để người dùng tải về và điền dữ liệu loại công việc trước khi import.
 *
 * Cấu trúc file:
 *   - Hàng 1: Tên cột thân thiện (hiển thị cho người dùng đọc)
 *   - Hàng 2+: Dữ liệu mẫu minh hoạ (có thể xóa hoặc ghi đè)
 *
 * Import class đọc hàng 1 làm heading row (WithHeadingRow) để lấy key.
 */
class TaskAssignmentItemTypesTemplateExport implements FromArray, WithHeadings, WithStyles, WithColumnWidths, WithTitle
{
    /**
     * Dữ liệu mẫu minh hoạ — người dùng có thể xóa hoặc ghi đè.
     */
    public function array(): array
    {
        return [
            ['Công việc 1', 'Các công việc thuộc lĩnh vực chuyên môn', 'active'],
            ['Công việc 2', 'Công việc phát sinh ngoài kế hoạch', 'active'],
        ];
    }

    /**
     * Hàng tiêu đề — WithHeadingRow sẽ normalize thành key để map trong Import class.
     * "Tên loại công việc (*)" → ten_loai_cong_viec
     * "Mô tả"                  → mo_ta
     * "Trạng thái (active/inactive)" → trang_thai_active_inactive
     */
    public function headings(): array
    {
        return [
            'name',
            'description',
            'status',
        ];
    }

    /**
     * Tên sheet.
     */
    public function title(): string
    {
        return 'Danh sách loại công việc';
    }

    /**
     * Định dạng style cho file mẫu.
     */
    public function styles(Worksheet $sheet): array
    {
        // Hàng 1: tiêu đề — nền xanh đậm, chữ trắng, in đậm
        $sheet->getStyle('A1:C1')->applyFromArray([
            'font'      => ['bold' => true, 'color' => ['argb' => 'FFFFFFFF'], 'size' => 11],
            'fill'      => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => 'FF4472C4']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'wrapText' => true],
            'borders'   => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['argb' => 'FFBFBFBF']]],
        ]);

        // Hàng 2-3: dữ liệu mẫu — nền xanh nhạt
        $sheet->getStyle('A2:C3')->applyFromArray([
            'fill'    => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => 'FFDCE6F1']],
            'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['argb' => 'FFBFBFBF']]],
        ]);

        // Ghi chú hướng dẫn ở ô D1
        $sheet->setCellValue('D1', '(*) Bắt buộc. Trạng thái nhập: active hoặc inactive. Xóa các dòng mẫu và điền dữ liệu thực từ hàng 2.');
        $sheet->getStyle('D1')->applyFromArray([
            'font' => ['color' => ['argb' => 'FFFF0000'], 'bold' => true, 'size' => 10],
        ]);
        $sheet->getColumnDimension('D')->setWidth(70);

        return [];
    }

    /**
     * Độ rộng cột.
     */
    public function columnWidths(): array
    {
        return [
            'A' => 35,
            'B' => 45,
            'C' => 30,
        ];
    }
}
