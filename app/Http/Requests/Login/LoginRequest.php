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
    public function authorize()
    {
        return true;
    }


    public function attributes()
    {
        return [
            'email' => 'user email',
            'password' => 'user paassword',
  
        ];
    }
    
    public function messages()
    {
        return [
            'required' => 'Required :attribute',
        ];
    }
    
    public function rules()
    {
        return [
            'email' => 'required|max:128',
            'password' => 'required|max:128',
        ];
    }
}
