<?php

namespace App\Http\Resources;

use App\Models\Attendance;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AttendanceResource extends JsonResource
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
            'employee_user_id' => $this->employee_user_id,
            'employeeUser' => new UserResource($this->whenLoaded('employeeUser')),
            'date' => $this->date,
            'total_time' => $this->total_time,
            'entry_time' => $this->entry_time,
            'exit_time' => $this->exit_time,
            'status' => $this->status,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at
        ];
    }
}
