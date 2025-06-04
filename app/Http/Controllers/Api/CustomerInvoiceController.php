<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\BaseController;
use App\Http\Requests\CustomerInvoiceRequest;
use App\Http\Requests\FlightDetailsRequest;
use App\Http\Requests\PassengerDetailsRequest;
use App\Repositories\CustomerInvoiceRepository;
use App\Repositories\FlightDetailsRepository;
use App\Repositories\PassengerDetailsRepository;
use App\Repositories\PaymentDetailsRepository;
use App\Repositories\InsuranceDetailRepository;
use App\Repositories\HotelCruiseLandPackageRepository;


use App\Http\Resources\CustomerInvoiceResource;
use App\Http\Resources\PassengerDetailResource;
use App\Http\Resources\FlightDetailResource;
use App\Http\Resources\PaymentDetailResource;
use App\Http\Resources\InsuranceDetailResource;
use App\Http\Resources\HotelCruiseLandPackageResource;

use App\Models\AirLine;
use App\Models\OtherService;
use App\Models\CustomerInvoice;
use App\Models\PassengerDetails;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class CustomerInvoiceController extends BaseController
{
    protected $customerInvoiceRepo;
    protected $passengerDetailsRepo;
    protected $flightDetailsRepo;
    protected $paymentDetailsRepo;
    protected $insuranceDetailRepo;
    protected $hotelCruiseLandPackageRepo;

    public function __construct(
        CustomerInvoiceRepository $customerInvoiceRepo , 
        PassengerDetailsRepository $passengerDetailsRepo,
        FlightDetailsRepository $flightDetailsRepo,
        PaymentDetailsRepository $paymentDetailsRepo,
        InsuranceDetailRepository $insuranceDetailRepo,
        HotelCruiseLandPackageRepository $hotelCruiseLandPackageRepo
    )
    {
        $this->customerInvoiceRepo = $customerInvoiceRepo;
        $this->passengerDetailsRepo = $passengerDetailsRepo;
        $this->flightDetailsRepo = $flightDetailsRepo;
        $this->paymentDetailsRepo = $paymentDetailsRepo;
        $this->insuranceDetailRepo = $insuranceDetailRepo;
        $this->hotelCruiseLandPackageRepo = $hotelCruiseLandPackageRepo;
    }

    public function list(Request $request)
    {
        $limit = $request->has('limit') ? $request->limit : 999;
        $response = $this->customerInvoiceRepo->filter()->with(
            'airLine',
            'customer',
            'flightDetails',
            'passengerDetails',
            'paymentDetails',
            'insuranceDetails',
            'hotelCruiseLandPackages',
            'hotelCruiseLandPackages.countryName',
        )->latest()->paginate($limit);
        return $this->successWithPaginateData(CustomerInvoiceResource::collection($response), $response);
    }

    public function saveAndUpdate(CustomerInvoiceRequest $request)
    {
        //dd($request->all());
        // Save or update the customer invoice
        $customerInvoice = $this->customerInvoiceRepo->saveOrUpdate($request->all());

        if (!$customerInvoice) {
            return response()->json(['message' => 'Customer Invoice not found'], 404);
        }
        // Save or update passenger details
        $passengerDetails = [];
        if ($request->has('passenger_details')) {
            foreach ($request->passenger_details as $passengerData) {
                $passengerDetails[] = $this->passengerDetailsRepo->saveOrUpdate($passengerData, $customerInvoice->id);
            }
        }

        $flightDetails = [];
        if ($request->has('flight_details')) {
            foreach ($request->flight_details as $flightData) {
                $flightDetails[] = $this->flightDetailsRepo->saveOrUpdate($flightData, $customerInvoice->id);
            }
        }

        $paymentDetails = [];
        if ($request->has('payment_details')) {
            foreach ($request->payment_details as $paymentData) {
                $paymentDetails[] = $this->paymentDetailsRepo->saveOrUpdate($paymentData, $customerInvoice->id);
            }
        }
        $insuranceDetails = [];
        if ($request->has('insurance_details')) {
            foreach ($request->insurance_details as $insuranceData) {
                $insuranceDetails[] = $this->insuranceDetailRepo->saveOrUpdate($insuranceData, $customerInvoice->id);
            }
        }

        $hotelCruiseLandPackages = [];
        if ($request->has('hotel_cruise_land_packages')) {
            foreach ($request->hotel_cruise_land_packages as $hotelCruiseLandPackageData) {
                $hotelCruiseLandPackages[] = $this->hotelCruiseLandPackageRepo->saveOrUpdate($hotelCruiseLandPackageData, $customerInvoice->id);
            }
        }
        
       

        // Response message
        $message = $request->id
            ? 'Customer Invoice Updated Successfully'
            : 'Customer Invoice Created Successfully';

        return $this->success([
            'customer_invoice' => new CustomerInvoiceResource($customerInvoice),
            'passenger_details' => PassengerDetailResource::collection($passengerDetails),
            'flight_details' => FlightDetailResource::collection($flightDetails),
            'payment_details' => PaymentDetailResource::collection($paymentDetails),
            'insurance_details' => InsuranceDetailResource::collection($insuranceDetails),
            'hotelCruiseLandPackages' => HotelCruiseLandPackageResource::collection($hotelCruiseLandPackages),
        ], $message);
    }


    public function delete($id)
    {

        $otherService = OtherService::find($id);

        if (!$otherService) {
            return response()->json([
                'message' => $message ?? 'other Service record not found',
            ], 500);
        }

        $otherService->delete();

        return $this->success(['flash_type' => 'success', 'flash_message' => 'otherServices deleted successfully', 'flash_description' => $otherService->title]);
    }
}
