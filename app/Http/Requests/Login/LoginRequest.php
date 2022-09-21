<?php

namespace App\Http\Requests\Login;

use Illuminate\Foundation\Http\FormRequest;
// [OAT\Schema(
//     schema: 'LoginRequest',
//     required: ['email', 'password'],
//     properties: [
//         new OAT\Property(
//             property: 'email',
//             type: 'string',
//             format: 'email',
//             example: 'root@admin.com'
//         ),
//         new OAT\Property(
//             property: 'password',
//             type: 'string',
//             example: '123456'
//         ),
//     ]
// )]
class LoginRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize(): bool
    {
        return false;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
        ];
    }
}
