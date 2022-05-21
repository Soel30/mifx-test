<?php

namespace App\Http\Requests;

use App\Book;
use App\Exceptions\ValidationException;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;

class PostBookRequest extends FormRequest
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
        // @TODO implement
        $rules = [
            'isbn' => 'required|string|min:10|max:13|unique:books,isbn',
            'title' => 'required|string|min:3|max:255',
            'authors' => 'required|array|min:1',
            'authors.*' => 'required|integer|exists:authors,id',
            'published_year' => 'required|integer|min:1900|max:2020',
        ];

        return $rules;
    }
}
