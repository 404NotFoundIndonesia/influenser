<?php

namespace App\Http\Requests\Influencer;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class UpdateInfluencerRequest extends FormRequest
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
     * @return array<string, ValidationRule|array|string>
     */
    public function rules(): array
    {
        return [
            'name' => 'required|string',
            'bio' => 'nullable|string',
            'location' => 'nullable|string',
            'phone' => 'nullable|string',
            'whatsapp' => 'nullable|string',
            'email' => 'nullable|string|email',
            'status' => 'required|string|in:active,inactive,banned',
            'keyOpinionLeaders.*.username' => 'required|string',
            'keyOpinionLeaders.*.platform' => 'required|string',
            'keyOpinionLeaders.*.link' => 'required|string',
        ];
    }

    public function attributes(): array
    {
        return [
            'keyOpinionLeaders.*.username' => 'username',
            'keyOpinionLeaders.*.platform' => 'platform',
            'keyOpinionLeaders.*.link' => 'link',
        ];
    }
}
