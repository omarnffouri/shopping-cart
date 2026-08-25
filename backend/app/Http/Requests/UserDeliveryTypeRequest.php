<?php

namespace App\Http\Requests;

use App\Models\GuestUser;
use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;

class UserDeliveryTypeRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'delivery_type_id' => 'required',
            'delivery_date' => 'required',
            'time_slot_id' => 'required',
            'user_id' => 'required',
            'cart_ids' => 'required',
            'user_type' => 'required',
        ];
    }

    public function prepareForValidation(): void
    {
        $modelType = GuestUser::class;

        if ($this->user_type === 'user') {
            $modelType = User::class;
        }

        $this->merge([
            'userable_id' => $this->user_id,
            "userable_type" => $modelType,
        ]);
    }
}
