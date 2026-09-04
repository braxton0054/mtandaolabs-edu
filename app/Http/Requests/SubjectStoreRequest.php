<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class SubjectStoreRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'name' => 'required|max:255',
            'short_name' => 'required|max:255',
            'my_class_id' => 'exists:my_classes,id',
            'teachers.*' => 'exists:users,id',
            'pathway_id' => 'nullable|exists:pathways,id',
            'is_compulsory' => 'nullable|boolean',
            'is_examinable' => 'nullable|boolean',
        ];
    }
}
