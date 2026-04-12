<?php

namespace App\Modules\Core\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\Response;

/**
 * Tương tự SetPermissionsTeamId nhưng không bắt buộc header X-Organization-Id.
 * Nếu không có header, tự động lấy tổ chức đầu tiên của user để đặt team context.
 * Dùng cho các module không phụ thuộc tổ chức nhưng vẫn cần kiểm tra quyền Spatie.
 */
class SetPermissionsTeamIdOptional
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = Auth::guard('sanctum')->user();

        if ($user) {
            Auth::guard('web')->setUser($user);

            $organizationId = $this->resolveOrganizationId($request, (int) $user->id);

            if ($organizationId !== null) {
                setPermissionsTeamId($organizationId);
            }
        }

        return $next($request);
    }

    /**
     * Lấy organization_id từ header nếu có,
     * nếu không thì tự động lấy tổ chức đầu tiên user thuộc về.
     */
    protected function resolveOrganizationId(Request $request, int $userId): ?int
    {
        // Ưu tiên lấy từ header nếu client gửi lên
        $headerValue = $request->header('X-Organization-Id')
            ?? $request->header('x-organization-id');

        if ($headerValue !== null && $headerValue !== '' && is_numeric($headerValue)) {
            return (int) $headerValue;
        }

        // Tự động lấy tổ chức đầu tiên user có role/quyền
        $columnNames = config('permission.column_names');
        $tableNames = config('permission.table_names');
        $modelMorphKey = $columnNames['model_morph_key'] ?? 'model_id';
        $teamForeignKey = $columnNames['team_foreign_key'] ?? 'organization_id';
        $modelHasRolesTable = $tableNames['model_has_roles'] ?? 'model_has_roles';
        $modelType = \App\Modules\Core\Models\User::class;

        $row = DB::table($modelHasRolesTable)
            ->where($modelMorphKey, $userId)
            ->where('model_type', $modelType)
            ->whereNotNull($teamForeignKey)
            ->orderBy($teamForeignKey)
            ->first([$teamForeignKey]);

        if ($row && isset($row->{$teamForeignKey})) {
            return (int) $row->{$teamForeignKey};
        }

        return null;
    }
}
