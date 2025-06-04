<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PackageResource extends JsonResource
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
			'parent_id' => $this->parent_id,
			'parent' => new PackageResource($this->whenLoaded('parent')),
			'children' => PackageResource::collection($this->whenLoaded('children')),
			'enquiry_id' => $this->enquiry_id,
			'enquiry' => new EnquiryResource($this->whenLoaded('enquiry')),
			'package_type' => $this->package_type,
			'departure_date' => $this->departure_date,
			'from' => $this->from,
			'to' => $this->to,
			'fromAirport' => new WorldAirportResource($this->whenLoaded('fromAirport')),
			'toAirport' => new WorldAirportResource($this->whenLoaded('toAirport')),
			'created_at' => $this->created_at,
			'updated_at' => $this->updated_at,
		];
	}
}
