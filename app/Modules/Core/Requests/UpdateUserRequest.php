<?php

namespace App\Modules\Core\Requests;

use App\Modules\Core\Enums\UserStatusEnum;
use Illuminate\Foundation\Http\FormRequest;

class UpdateUserRequest extends FormRequest
{
    protected function prepareForValidation(): void
    {
        if ($this->has('user_name') && trim((string) $this->user_name) === '') {
            $this->merge(['user_name' => null]);
        }
    }

    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name'      => 'sometimes|string|max:255',
            'email'     => 'sometimes|email|unique:users,email,' . $this->route('user'),
            'user_name' => 'sometimes|nullable|string|max:100|unique:users,user_name,' . $this->route('user') . '|regex:/^[a-zA-Z0-9._-]*$/',
            'password'  => 'sometimes|string|min:6|confirmed',
            'status'    => ['sometimes', 'in:' . implode(',', UserStatusEnum::values())],
        ];
    }

    public function messages(): array
    {
        return [
            'name.string'   => 'Tên người dùng phải là một chuỗi ký tự.',
            'name.max'      => 'Tên người dùng không được vượt quá 255 ký tự.',
            'email.email'   => 'Email không hợp lệ.',
            'email.unique'     => 'Email đã tồn tại.',
            'user_name.unique' => 'Tên đăng nhập đã tồn tại.',
            'user_name.regex'  => 'Tên đăng nhập chỉ chấp nhận chữ, số, dấu chấm, gạch dưới, gạch ngang.',
            'password.string'    => 'Mật khẩu phải là một chuỗi ký tự.',
            'password.min'       => 'Mật khẩu phải có ít nhất 6 ký tự.',
            'password.confirmed' => 'Mật khẩu không khớp.',
            'status.in' => 'Trạng thái không hợp lệ. Chỉ chấp nhận active, inactive, banned.',
        ];
    }

    public function bodyParameters(): array
    {
        return [];
    }
}
