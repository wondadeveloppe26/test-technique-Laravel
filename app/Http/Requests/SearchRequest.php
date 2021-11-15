<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class SearchRequest extends FormRequest
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

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'q' => 'required|string|max:256'
        ];
    }
    public function messages()
    {
        return [
            'q.required' => "The query parameter 'q' is required.",
            'q.string' => "The query parameter 'q' must be a string.",
            'q.max' => "The query parameter 'q' cannot be longer than 256 chars.",
        ];
    }
}
