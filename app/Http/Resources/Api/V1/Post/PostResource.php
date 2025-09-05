<?php

namespace App\Http\Resources\Api\V1\Post;

use App\Http\Resources\Api\V1\Category\CategoryCollection;
use App\Http\Resources\Api\V1\Category\CategoryResource;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PostResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'post_id' => $this->id,
            'name' => $this->name,
            'content' => $this->content,
            'author' => $this->author,
            // 'categories' => new CategoryCollection($this->categories)
        ];
    }
}
