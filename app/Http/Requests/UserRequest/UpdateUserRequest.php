<?php

namespace App\Http\Requests\UserRequest;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateUserRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            "name" => "required|string|max:255",
            "email" => [
                'required',
                'string',
                'email',
                'max:255',
                // allow the current user to keep their email
                Rule::unique('users')->ignore($this->route('user')),
            ],
            "password" => "required|string|min:8|confirmed",
            "role" => "required|string|exists:roles,name",
        ];
    }
}
