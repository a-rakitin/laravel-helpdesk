<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class NotificationResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $data = $this->data ?? [];

        return [
            'id' => $this->id,
            'type' => $this->type,
            'data' => [
                'ticket_id' => isset($data['ticket_id']) ? (int) $data['ticket_id'] : null,
                'ticket_title' => $data['ticket_title'] ?? null,
                'comment_id' => isset($data['comment_id']) ? (int) $data['comment_id'] : null,
                'comment_body' => $data['comment_body'] ?? null,
                'comment_author_id' => isset($data['comment_author_id']) ? (int) $data['comment_author_id'] : null,
            ],
            'read_at' => $this->read_at === null ? null : $this->read_at->toJSON(),
            'created_at' => $this->created_at === null ? null : $this->created_at->toJSON(),
        ];
    }
}
