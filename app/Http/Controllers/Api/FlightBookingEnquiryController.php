<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\BaseController;
use App\Http\Requests\FlightBookingEnquiryRequest;
use App\Repositories\FlightBookingEnquiryRepository;
use App\Http\Resources\FlightBookingEnquiryResource;
use App\Models\AirLine;
use App\Notifications\EnquiryCreateNotification;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use App\Events\SocialMediaEvent;


class FlightBookingEnquiryController extends BaseController
{
    protected $flightBookingEnquiryRepo;

    public function __construct(FlightBookingEnquiryRepository $flightBookingEnquiryRepo)
    {
        $this->flightBookingEnquiryRepo = $flightBookingEnquiryRepo;
    }

    public function list(Request $request)
    {

        $limit = $request->has('limit') ? $request->limit : 999;
        $response = $this->flightBookingEnquiryRepo->filter()->latest()->paginate($limit);
        return $this->successWithPaginateData(FlightBookingEnquiryResource::collection($response), $response);
    }
    public function saveAndUpdate(FlightBookingEnquiryRequest $flightBookingEnquiryRequest)
    {
        $response = $this->flightBookingEnquiryRepo->update($flightBookingEnquiryRequest->id, $flightBookingEnquiryRequest);
        $socialMediaResponse = $response;
        SocialMediaEvent::dispatch($socialMediaResponse);
        if ($flightBookingEnquiryRequest->id) {
            return $this->success(new FlightBookingEnquiryResource($response), 'Flight Booking Enquiry Updated Successfully');
        } else {
            return $this->success(new FlightBookingEnquiryResource($response), 'Flight Booking Enquiry Created Successfully');
        }
    }

    public function delete($id)
    {
        $user = auth()->user();

        if (!in_array($user->role_id, [1, 4])) {
            return response()->json([
                'flash_type' => 'error',
                'flash_message' => 'Unauthorized',
                'flash_description' => 'You do not have permission to delete this Enquiry.',
            ], 403);
        }

        $enquiry = $this->flightBookingEnquiryRepo->delete($id);

        return response()->json([
            'flash_type' => 'success',
            'flash_message' => 'enquiry deleted successfully',
            //'flash_description' => $enquiry->full_name,
        ], 200);
    }
}
