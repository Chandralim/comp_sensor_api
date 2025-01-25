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
            'fullname' => $this->fullname ?? '',
            'password' => "",
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'role' => $this->role ?? "Admin",
        ];
    }
}
