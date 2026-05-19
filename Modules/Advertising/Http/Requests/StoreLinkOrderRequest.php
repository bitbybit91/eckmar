<?php

namespace Modules\Advertising\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Modules\Advertising\Http\Rules\NotPrivateUrl;

class StoreLinkOrderRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules(): array
    {
        return [
            'advertiser_email' => ['required', 'email', 'max:255'],
            'destination_url'  => ['required', 'url', 'max:2048', new NotPrivateUrl()],
            'anchor_text'      => ['required', 'string', 'max:60'],
            'month_count'      => ['required', 'integer', 'between:1,6'],
        ];
    }
}
