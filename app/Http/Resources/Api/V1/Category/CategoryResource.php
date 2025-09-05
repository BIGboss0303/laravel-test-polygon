<?php

namespace App\Http\Resources\Api\V1\Category;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CategoryResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'category_id' => $this->id,
            'name' => $this->name,
            'parent' => new CategoryResource($this->parent),
            // 'children' => new CategoryCollection($this->children)
        ];
    }
}
