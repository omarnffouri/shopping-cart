<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class TimeSlotRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'slot_name' => 'required',
            'start_time' => 'required',
            'end_time' => 'required',
            'available_if_before' => 'required',
            'display_order' => 'required',
            'is_active' => 'required',
        ];
    }
}
