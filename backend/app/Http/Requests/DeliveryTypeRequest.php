<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class DeliveryTypeRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return $this->isMethod('put') || $this->isMethod('patch')
            ? $this->updateRules()
            : $this->createRules();
    }

    protected function createRules(): array
    {
        return [
            'type_name' => 'required',
            'type_code' => 'required',
            'price' => 'required',
            'currency' => 'required',
            'country' => 'nullable|string',
            'states' => 'required|array',
            'description' => 'nullable',
            'available_for_today' => 'required',
            'cutoff_time' => 'required',
            'display_order' => 'required',
            'min_hours_advance' => 'required',
            'is_active' => 'required',
            'is_default' => 'nullable|boolean',
            'time_slots' => 'required|array',
            'time_slots.*.id' => 'nullable',
            'time_slots.*.slot_name' => 'required',
            'time_slots.*.start_time' => 'required',
            'time_slots.*.end_time' => 'required',
            'time_slots.*.available_if_before' => 'required',
            'time_slots.*.display_order' => 'required',
            'time_slots.*.is_active' => 'required',
        ];
    }

    protected function updateRules(): array
    {
        $rules = $this->createRules();
        unset($rules['states']);
        $rules['state'] = 'required|string';
        return $rules;
    }
}
