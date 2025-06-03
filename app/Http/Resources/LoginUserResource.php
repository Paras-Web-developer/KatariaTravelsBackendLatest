<?php

namespace App\Http\Resources;

use App\Http\Controllers\Api\RoleController;
use App\Models\Attendance;
use App\Http\Resources\RoleResource;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class LoginUserResource extends JsonResource
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
            'role_id' => (int) $this->role_id,
            // 'role' => new RoleResource($this->whenLoaded('role')),
            'department_id' => $this->department_id,
            'department' => new DepartmentResource(($this->whenLoaded('department'))),
            'branch_id' => (int) $this->branch_id,
            'branch' => new BranchResource(($this->whenLoaded('branch'))),
            'age' => $this->age,
            'salary' => $this->salary,
            'name' => $this->name,
            'email' => $this->email,
            'image' => $this->image,
            'password' => $this->password,
            'full_path' => $this->full_path,
            'phone_no' => $this->phone_no,
            'is_verified' => $this->is_verified,
            'description' => $this->description,
            'employee_verified_at' => $this->employee_verified_at,
            'status' => $this->status,
            'user_login' => $this->user_login,
            'plain_text_token' => $this->when($this->plain_text_token, $this->plain_text_token),
            'enquiries' => EnquiryResource::collection($this->whenLoaded('enquiries')),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at
        ];
    }
}
