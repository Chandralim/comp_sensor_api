<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class AdminResource extends JsonResource
{
    public $preserveKeys = true;
    /**
     * Transform the resource collection into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        // return parent::toArray($request);
        return [
            'id' => $this->id,
            'username' => $this->username,
            'fullname' => $this->fullname??'',
            // 'goods_receipt' => new GoodsReceiptResource($this->whenLoaded('goods_receipt')),
            // 'ordinal'=>$this->ordinal,
            // 'material'=>new MaterialResource($this->whenLoaded('material')),
            // 'qty' => $this->qty,
            // 'goods_receipt_details'=>GoodsReceiptDetailResource::collection($this->whenLoaded('goods_receipt_details')),
            'password' => "",
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'role' => $this->role??"Admin",
            // 'is_active' => $this->is_active ? 1 : 0,
        ];

    }
}
