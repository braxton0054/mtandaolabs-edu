<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class PlaceSeniorRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'student_id' => 'required|exists:users,id',
            'senior_class_id' => 'required|exists:my_classes,id',
            'senior_section_id' => 'nullable|exists:sections,id',
            'pathway_id' => 'required|exists:pathways,id',
            'electives' => 'required|array',
            'electives.*' => 'exists:subjects,id',
            'kjsea_score' => 'nullable|numeric|min:0|max:100',
        ];
    }
}
