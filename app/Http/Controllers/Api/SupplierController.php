<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\BaseController;
use App\Http\Requests\SupplierRequest;
use App\Repositories\SupplierRepository;
use App\Http\Resources\SupplierResource;
use App\Models\EnquirySource;
use App\Models\Supplier;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;


class SupplierController extends BaseController
{
    protected $supplierRepo;

    public function __construct(SupplierRepository $supplierRepo)
    {
        $this->supplierRepo = $supplierRepo;
    }

    public function list(Request $request)
    {
        $limit = $request->has('limit') ? $request->limit : 999;
        $response = $this->supplierRepo->filter()->with('countryName', 'stateName','cityName')->latest()->paginate($limit);
        return $this->successWithPaginateData(SupplierResource::collection($response), $response);
    }
    public function saveAndUpdate(Request $request)
    {
        $request->validate([
            'id' => 'nullable|integer|exists:suppliers,id',
            'country_id' => 'nullable|integer|exists:countries,id',
            'state_id' => 'nullable|integer|exists:states,id',
            'city_id' => 'nullable|integer|exists:cities,id',
            'name' => ['sometimes', 'string', 'max:255'],
            'slug' => [
                'sometimes',
                'string',
                Rule::unique('suppliers', 'slug')->ignore($request->id),
            ],
            'type' => 'required|string|max:255',
            'supplier_code' => 'required|string',
            'reservations_phone' => 'nullable|string',
            'reservations_email' => 'nullable|string|email',
            'contact_name' => 'nullable|string',
            'phone' => 'nullable|string',
            'fax' => 'nullable|string',
            'email' => 'nullable|string|email',
            'address' => 'nullable|string',
            'postal_code' => 'nullable|string',
        ]);


        $response = $this->supplierRepo->update($request->id, $request);


        if ($request->id) {
            return $this->success(new SupplierResource($response), 'supplier Updated Successfully');
        } else {
            return $this->success(new SupplierResource($response), 'supplier Created Successfully');
        }
    }

    public function delete($id)
    {

        $supplier = Supplier::find($id);

        if (!$supplier) {
            return response()->json([
                'message' => $message ?? 'supplier record not found',
            ], 500);
        }

        $supplier->delete();

        return $this->success(['flash_type' => 'success', 'flash_message' => 'supplier deleted successfully', 'flash_description' => $supplier->name]);
    }
}
