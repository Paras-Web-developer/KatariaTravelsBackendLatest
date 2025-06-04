<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\BaseController;

use App\Repositories\PhoneNumberRepository;
use App\Http\Resources\PhoneNumberResource;
use App\Models\AirLine;
use App\Models\PhoneNumber;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;


class PhoneNumberController extends BaseController
{
    protected $phoneNumberRepo;

    public function __construct(PhoneNumberRepository $phoneNumberRepo)
    {
        $this->phoneNumberRepo = $phoneNumberRepo;
    }

    public function list(Request $request)
    {

        $limit = $request->has('limit') ? $request->limit : 999;
        $response = $this->phoneNumberRepo->filter()->latest()->paginate($limit);
        return $this->successWithPaginateData(PhoneNumberResource::collection($response), $response);
    }
    public function saveAndUpdate(Request $request)
    {
        // Validate the request
        $request->validate([
            'id' => 'nullable|integer|exists:phone_numbers,id',
            'enquiry_id' => 'nullable|integer|exists:enquiries,id',
            'hotel_enquiry_id' => 'nullable|integer|exists:hotel_enquires,id',
            'phone_number' => [
                'required',
                'string',
                'max:15',
                Rule::unique('phone_numbers', 'phone_number')->ignore($request->id),
            ],
        ]);


        $response = $this->phoneNumberRepo->update($request->id, $request);


        if ($request->id) {
            return $this->success(new PhoneNumberResource($response), 'PhoneNumber Updated Successfully');
        } else {
            return $this->success(new PhoneNumberResource($response), 'PhoneNumber Created Successfully');
        }
    }
    public function delete($id)
    {

        $PhoneNumber = PhoneNumber::find($id);

        if (!$PhoneNumber) {
            return response()->json([
                'message' => $message ?? 'PhoneNumber record not found',
            ], 500);
        }

        $PhoneNumber->delete();

        return $this->success(['flash_type' => 'success', 'flash_message' => 'PhoneNumber deleted successfully', 'flash_description' => $PhoneNumber->sender]);
    }
}
