<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\BaseController;
use App\Http\Requests\CreditCardFormRequest;
use App\Repositories\CreditCardFormRepository;
use App\Http\Resources\CreditCardFormResource;
use App\Models\AirLine;
use App\Models\CreditCardForm;
use App\Models\OtherService;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;


class CreditCardFormController extends BaseController
{
    protected $creditCardFormRepo;

    public function __construct(CreditCardFormRepository $creditCardFormRepo)
    {
        $this->creditCardFormRepo = $creditCardFormRepo;
    }

    public function list(Request $request)
    {

        $limit = $request->has('limit') ? $request->limit : 999;
        $response = $this->creditCardFormRepo->filter()->with('airLine')->latest()->paginate($limit);
        return $this->successWithPaginateData(CreditCardFormResource::collection($response), $response);
    }

    public function saveAndUpdate(CreditCardFormRequest $creditCardFormRequest)
    {
        $signaturePath = null;

        if ($creditCardFormRequest->hasFile('signature')) {
            $signaturePath = $creditCardFormRequest->file('signature')->store('uploads', 'public');
        }

        $data = $creditCardFormRequest->validated();
        if ($signaturePath) {
            $data['signature'] = $signaturePath;
        }
        if ($creditCardFormRequest->id) {
            $response = $this->creditCardFormRepo->updateData($creditCardFormRequest->id, $data);
        } else {
            $response = $this->creditCardFormRepo->storeData($data);
        }

        $message = $creditCardFormRequest->id
            ? 'Other Service Enquiry Updated Successfully'
            : 'Other Service Enquiry Created Successfully';

        return $this->success(new CreditCardFormResource($response), $message);
    }


    public function delete($id)
    {

        $creditCardForm = CreditCardForm::find($id);

        if (!$creditCardForm) {
            return response()->json([
                'message' => $message ?? 'credit Card Form record not found',
            ], 500);
        }

        $creditCardForm->delete();

        return $this->success(['flash_type' => 'success', 'flash_message' => 'credit Card Form deleted successfully', 'flash_description' => $creditCardForm->holder_name]);
    }
}
