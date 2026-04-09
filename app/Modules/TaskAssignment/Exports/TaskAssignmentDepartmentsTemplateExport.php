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
 * Export file mẫu để người dùng tải về và điền dữ liệu phòng ban trước khi import.
 *
 * Cấu trúc file:
 *   - Hàng 1: Tên cột thân thiện (hiển thị cho người dùng đọc)
 *   - Hàng 2: Key kỹ thuật dùng khi import — KHÔNG xóa hàng này
 *   - Hàng 3+: Dữ liệu mẫu minh hoạ (có thể xóa hoặc ghi đè)
 *
 * Import class đọc hàng 2 làm heading row (WithHeadingRow) để lấy key.
 */
class TaskAssignmentDepartmentsTemplateExport implements FromArray, WithHeadings, WithStyles, WithColumnWidths, WithTitle
{
    /**
     * Dữ liệu mẫu minh hoạ — người dùng có thể xóa hoặc ghi đè.
     */
    public function array(): array
    {
        return [
            ['PB001', 'Phòng Kế hoạch', 'Phụ trách lập kế hoạch công tác', 'active', 1],
            ['PB002', 'Phòng Tổ chức', 'Phụ trách công tác tổ chức nhân sự', 'active', 2],
        ];
    }

    /**
     * Hàng tiêu đề — WithHeadingRow sẽ normalize thành key để map trong Import class.
     * "Mã phòng ban (*)"            → ma_phong_ban
     * "Tên phòng ban (*)"           → ten_phong_ban
     * "Mô tả"                       → mo_ta
     * "Trạng thái (active/inactive)"→ trang_thai_active_inactive
     * "Thứ tự sắp xếp"              → thu_tu_sap_xep
     */
    public function headings(): array
    {
        return [
            'Mã phòng ban (*)',
            'Tên phòng ban (*)',
            'Mô tả',
            'Trạng thái (active/inactive)',
            'Thứ tự sắp xếp',
        ];
    }

    /**
     * Tên sheet.
     */
    public function title(): string
    {
        return 'Danh sách phòng ban';
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

        // Hàng 2-3: dữ liệu mẫu — nền xanh nhạt
        $sheet->getStyle('A2:E3')->applyFromArray([
            'fill'    => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => 'FFDCE6F1']],
            'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['argb' => 'FFBFBFBF']]],
        ]);

        // Ghi chú hướng dẫn ở ô F1
        $sheet->setCellValue('F1', '(*) Bắt buộc. Trạng thái nhập: active hoặc inactive. Xóa các dòng mẫu và điền dữ liệu thực từ hàng 2.');
        $sheet->getStyle('F1')->applyFromArray([
            'font' => ['color' => ['argb' => 'FFFF0000'], 'bold' => true, 'size' => 10],
        ]);
        $sheet->getColumnDimension('F')->setWidth(70);

        return [];
    }

    /**
     * Độ rộng cột.
     */
    public function columnWidths(): array
    {
        return [
            'A' => 22,
            'B' => 35,
            'C' => 45,
            'D' => 30,
            'E' => 22,
        ];
    }
}
