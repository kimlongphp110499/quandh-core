<?php

namespace App\Modules\TaskAssignment\Imports;

use App\Modules\TaskAssignment\Models\TaskAssignmentDepartment;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\SkipsEmptyRows;

/**
 * Import danh sách phòng ban từ file Excel/CSV.
 *
 * Hỗ trợ cả file template mẫu (heading tiếng Việt) và file export cũ (heading tiếng Anh).
 * WithHeadingRow tự động normalize heading: lowercase, bỏ dấu, thay space bằng _.
 *
 * Mapping key sau khi normalize:
 *   - "Mã phòng ban (*)"            → ma_phong_ban
 *   - "Tên phòng ban (*)"           → ten_phong_ban
 *   - "Mô tả"                       → mo_ta
 *   - "Trạng thái (active/inactive)"→ trang_thai_active_inactive
 *   - "Thứ tự sắp xếp"              → thu_tu_sap_xep
 *   - "Mã" / "ma" / "code"          → ma / code
 */
class TaskAssignmentDepartmentsImport implements ToModel, WithHeadingRow, SkipsEmptyRows
{
    public function model(array $row)
    {
        // Lấy giá trị mã phòng ban — hỗ trợ cả heading tiếng Việt và tiếng Anh
        $code = $row['ma_phong_ban']
            ?? $row['ma']
            ?? $row['code']
            ?? null;

        // Bỏ qua dòng không có mã
        if (empty($code)) {
            return null;
        }

        return new TaskAssignmentDepartment([
            'code'        => trim($code),
            'name'        => trim(
                $row['ten_phong_ban']
                ?? $row['name']
                ?? ''
            ),
            'description' => $row['mo_ta'] ?? $row['description'] ?? null,
            'status'      => $row['trang_thai_active_inactive']
                ?? $row['trang_thai']
                ?? $row['status']
                ?? 'active',
            'sort_order'  => (int) (
                $row['thu_tu_sap_xep']
                ?? $row['thu_tu']
                ?? $row['sort_order']
                ?? 0
            ),
        ]);
    }
}
