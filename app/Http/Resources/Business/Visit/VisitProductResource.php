<?php

namespace App\Http\Resources\Business\Visit;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class VisitProductResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'visit_id' => $this->visit_id,
            'product_id' => $this->product_id,
            'quantity' => $this->quantity,
            'availability' => $this->availability,
            'notes' => $this->notes,
            'product' => $this->whenLoaded('product', fn() => [
                'id' => $this->product->id,
                'name' => $this->product->name,
                'price' => $this->product->price,
            ]),
        ];
    }
}
