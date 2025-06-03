<?php

namespace App\Http\Resources;

use App\Models\Attendance;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class MultiPackageResource extends JsonResource
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
            'package_id' => $this->package_id,
            'package' => new PackageResource(($this->whenLoaded('package'))),
            'from' => $this->from,
            'to' => $this->to,
            'departure' => $this->departure,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at
        ];
    }
}
