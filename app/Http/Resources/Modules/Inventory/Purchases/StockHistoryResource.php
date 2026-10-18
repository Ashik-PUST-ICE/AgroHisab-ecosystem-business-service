<?php

namespace App\Http\Resources\Modules\Inventory\Purchases;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class StockHistoryResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'date'     => $this->stock_out_date,
            'product'  => $this->stock_product_id ? $this->stockProduct->name : null,
            'qty'      => $this->quantity,
            'price'    => $this->unit_price,
            'brand'    => $this->brand_id ? $this->brand->name : null,
            'serial'   => $this->serial_no,
            'mac'      => $this->mac_address,

        ];
    }
}
