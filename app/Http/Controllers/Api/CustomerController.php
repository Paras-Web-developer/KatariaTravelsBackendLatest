<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\BaseController;
use App\Http\Requests\CustomerRequest;
use App\Repositories\CustomerRepository;
use App\Http\Resources\CustomerResource;
use App\Models\AirLine;
use App\Models\Customer;
use App\Models\OtherService;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;


class CustomerController extends BaseController
{
    protected $customerRepo;

    public function __construct(CustomerRepository $customerRepo)
    {
        $this->customerRepo = $customerRepo;
    }

    public function list(Request $request)
    {

        $limit = $request->has('limit') ? $request->limit : 999;
        $response = $this->customerRepo->filter()->with('countryName', 'stateName','cityName')->latest()->paginate($limit);
        return $this->successWithPaginateData(CustomerResource::collection($response), $response);
    }
    public function saveAndUpdate(CustomerRequest $customerRequest)
    {
        $response = $this->customerRepo->update($customerRequest->id, $customerRequest);

        if ($customerRequest->id) {
            return $this->success(new CustomerResource($response), 'Other Service Enquiry Updated Successfully');
        } else {
            return $this->success(new CustomerResource($response), 'Other Service Enquiry Created Successfully');
        }
    }
    public function delete($id)
    {

        $customer = Customer::find($id);

        if (!$customer) {
            return response()->json([
                'message' => $message ?? 'customer record not found',
            ], 500);
        }

        $customer->delete();

        return $this->success(['flash_type' => 'success', 'flash_message' => 'customer deleted successfully', 'flash_description' => $customer->full_name]);
    }
}
