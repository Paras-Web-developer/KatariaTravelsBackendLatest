<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\BaseController;
use App\Http\Requests\FeedbackRequest;
use App\Repositories\FeedbackRepository;
use App\Http\Resources\FeedbackResource;
use App\Models\AirLine;
use App\Models\Feedback;
use App\Models\OtherService;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;


class FeedbackController extends BaseController
{
    protected $feedbackRepo;

    public function __construct(FeedbackRepository $feedbackRepo)
    {
        $this->feedbackRepo = $feedbackRepo;
    }

    public function list(Request $request)
    {

        $limit = $request->has('limit') ? $request->limit : 999;
        $response = $this->feedbackRepo->filter()->latest()->paginate($limit);
        return $this->successWithPaginateData(FeedbackResource::collection($response), $response);
    }
    public function saveAndUpdate(FeedbackRequest $feedbackRequest)
    {
        $response = $this->feedbackRepo->update($feedbackRequest->id, $feedbackRequest);

        if ($feedbackRequest->id) {
            return $this->success(new FeedbackResource($response), 'Other Service Enquiry Updated Successfully');
        } else {
            return $this->success(new FeedbackResource($response), 'Other Service Enquiry Created Successfully');
        }
    }
    public function delete($id)
    {

        $feedback = Feedback::find($id);

        if (!$feedback) {
            return response()->json([
                'message' => $message ?? 'feedback record not found',
            ], 500);
        }

        $feedback->delete();

        return $this->success(['flash_type' => 'success', 'flash_message' => 'otherServices deleted successfully', 'flash_description' => $feedback->title]);
    }
}
