<?php

namespace App\Http\Requests;

use App\Rules\UniqueWorkspacedResource;
use Auth;
use Common\Core\BaseFormRequest;

class CrupdateTrackingPixelRequest extends BaseFormRequest
{
    /**
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * @return array
     */
    public function rules()
    {
        $required = $this->getMethod() === 'POST' ? 'required' : '';
        $ignore = $this->getMethod() === 'PUT' ? $this->route('tracking_pixel')->id : '';
        $userId = $this->route('tracking_pixel') ? $this->route('tracking_pixel')->user_id : Auth::id();

        return [
            'name' => [
                $required, 'string', 'min:3',
                (new UniqueWorkspacedResource('tracking_pixels', 'NULL', $userId))->ignore($ignore)
            ],
            'type' => 'required|string|max:40',
            'pixel_id' => 'nullable|string|max:200',
            'head_code' => 'nullable|string',
            'body_code' => 'nullable|string',
        ];
    }
}
