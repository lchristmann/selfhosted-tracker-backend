<?php

namespace App\Http\Requests\V1;

use Illuminate\Foundation\Http\FormRequest;

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
        $method = $this->method();

        if ($method === 'PUT') {
            return [
                'name' => 'required|string|max:128',
                'image' => 'required|image:allow_svg|max:2048'
                // 'image' rule checks MIME types: jpg, jpeg, png, bmp, gif, webp or svg
                // 'max' rule checks size: not more than 2048 KB = 2 MB
            ];
        } else { // PATCH
            return [
                'name' => 'sometimes|required|string|max:128',
                'image' => 'sometimes|required|image:allow_svg|max:2048'
            ];
        }
    }
}
