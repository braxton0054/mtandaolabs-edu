<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class SemesterStoreRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'name' => 'string|max:255',
            'start_date' => 'nullable|date',
            'stop_date' => 'nullable|date|after_or_equal:start_date',
            'midterm_start' => 'nullable|date',
            'midterm_stop' => 'nullable|date|after_or_equal:midterm_start',
        ];
    }
}
