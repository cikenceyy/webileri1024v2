<?php

namespace App\Modules\Marketing\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CustomerResource extends JsonResource
{
    public function toArray($request): array
    {
        $data = [
            'id' => $this->id,
            'code' => $this->code,
            'name' => $this->name,
            'email' => $this->email,
            'phone' => $this->phone,
            'billing_address' => $this->billing_address,
            'shipping_address' => $this->shipping_address,
            'status' => $this->status,
            'is_active' => $this->is_active,
            'default_price_list_id' => $this->default_price_list_id,
            'payment_terms_days' => $this->payment_terms_days,
            'credit_limit' => $this->credit_limit,
        ];

        return $this->maskSensitive($data, $request);
    }

    protected function maskSensitive(array $data, ?Request $request): array
    {
        $user = $request?->user();

        if (! $user || ! method_exists($user, 'hasRole')) {
            return $data;
        }

        if (! $user->hasRole('accountant') && ! $user->hasRole('intern')) {
            return $data;
        }

        $data['email'] = null;
        $data['phone'] = null;
        $data['billing_address'] = null;
        $data['shipping_address'] = null;

        return $data;
    }
}
