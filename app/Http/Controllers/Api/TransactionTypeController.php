<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\BaseController;

use App\Repositories\TransactionTypeRepository;
use App\Http\Resources\TransactionTypeResource;
use App\Models\TransactionType;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Symfony\Component\Mailer\Transport;

class TransactionTypeController extends BaseController
{
    protected $transactionTypeRepo;

    public function __construct(TransactionTypeRepository $transactionTypeRepo)
    {
        $this->transactionTypeRepo = $transactionTypeRepo;
    }

    public function list(Request $request)
    {

        $limit = $request->has('limit') ? $request->limit : 999;
        $response = $this->transactionTypeRepo->filter()->latest()->paginate($limit);
        return $this->successWithPaginateData(TransactionTypeResource::collection($response), $response);
    }
    public function saveAndUpdate(Request $request)
    {

        $request->validate([
            'id' => 'nullable|integer|exists:transaction_types,id',
            'name' => ['sometimes', 'string', 'max:255'],
            'slug' => [
                'sometimes',
                'string',
                Rule::unique('transaction_types', 'slug')->ignore($request->id),
            ],
            'status' => ['sometimes', 'in:0,1'],
        ]);


        $response = $this->transactionTypeRepo->update($request->id, $request);


        if ($request->id) {
            return $this->success(new TransactionTypeResource($response), 'Transaction Type Updated Successfully');
        } else {
            return $this->success(new TransactionTypeResource($response), 'Transaction Type Created Successfully');
        }
    }

    public function delete($id)
    {

        $transactionType = TransactionType::find($id);

        if (!$transactionType) {
            return response()->json([
                'message' => $message ?? 'Transaction Type status record not found',
            ], 500);
        }

        $transactionType->delete();

        return $this->success(['flash_type' => 'success', 'flash_message' => 'Transaction Type deleted successfully', 'flash_description' => $transactionType->name]);
    }
}
