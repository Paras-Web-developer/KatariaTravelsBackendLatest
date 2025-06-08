<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\BaseController;

use App\Http\Resources\InvoiceMainResource;
use App\Http\Resources\InvoiceMainCustomResource;
use App\Repositories\InvoiceMainRepository;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Models\InvoiceMain;
use App\Http\Requests\CustomerRequest;
use App\Mail\InvoiceMainMail;
use App\Repositories\CustomerRepository;
use Illuminate\Support\Facades\Mail;
use Barryvdh\DomPDF\Facade\Pdf;
// use Spatie\LaravelPdf\Facades\Pdf;

class InvoiceMainController extends BaseController
{
	protected $invoiceMainRepo;
	protected $customerRepo;

	public function __construct(InvoiceMainRepository $invoiceMainRepo, CustomerRepository $customerRepo)
	{
		$this->invoiceMainRepo = $invoiceMainRepo;
		$this->customerRepo = $customerRepo;
	}

	public function list(Request $request)
	{

		$limit = $request->has('limit') ? $request->limit : 999;
		$response = $this->invoiceMainRepo->filter()->where('parent_id', null)->with(
			'hotelEnquiry',
			'flightEnquiry',
			'airLine',
			'customer',
			'supplier',
			'sales_agents',
			'parent',
			'children',
			'children.hotelEnquiry',
			'children.flightEnquiry',
			'children.airLine',
			'children.customer',
			'children.supplier',
			'children.sales_agents',
			'children.fromAirport',
			'children.toAirport',
			'children.updatedByUser',
			'children.createdByUser',
			'updatedByUser',
			'createdByUser',
			'fromAirport',
			'invoice',
			'toAirport',
		)->latest()->paginate($limit);
		return $this->successWithPaginateData(InvoiceMainResource::collection($response), $response);
	}

	public function customList(Request $request)
	{

		$limit = $request->has('limit') ? $request->limit : 999;
		$response = $this->invoiceMainRepo->filter()->where('parent_id', null)->with(
			'airLine',
			'customer',
			'supplier',
			'sales_agents',
			'fromAirport',
			'toAirport',
		)->latest()->paginate($limit);
		//dd($response);
		return $this->successWithPaginateData(InvoiceMainCustomResource::collection($response), $response);
	}



	// public function saveAndUpdate(Request $request)
	// {
	//     $request->validate([
	//     'id' => 'nullable|integer|exists:invoices_mains,id',
	//     //'parent_id' => 'nullable|integer|exists:invoices_mains,id',
	//     'sales_agent_id' => 'nullable|integer|exists:users,id',
	//     'flight_enquiry_id' => 'nullable|integer|exists:enquiries,id',
	//     'hotel_enquire_id' => 'nullable|integer|exists:hotel_enquires,id',
	//     'airLine_id' => 'nullable|integer|exists:air_lines,id',
	//     'customer_id' => 'nullable|integer|exists:customers,id',
	//     'supplier_id' => 'nullable|integer|exists:suppliers,id',
	//     //'sales_agent' => 'nullable|string|max:255',
	//     'itinerary' => 'nullable|string',
	//     'gds_type' => 'nullable|string',
	//     'gds_locator' => 'nullable|string',
	//     'booking_date' => 'nullable|date',
	//     'departure_date' => 'nullable|date',
	//     'ticket_number' => 'nullable|string|max:255',
	//     'travel_from' => 'nullable|string|max:255',
	//     'travel_to' => 'nullable|string|max:255',
	//     'customer_details' => 'nullable|array',
	//     'airticket' => 'nullable|array',
	//     'valid_canadian_passport' => 'nullable|in:true,false',
	//     'valid_travel_visa' => 'nullable|in:true,false',
	//     'tourist_card' => 'nullable|in:false,true',
	//     'canadian_citizenship_or_prCard' => 'nullable|in:true,false',
	//     'special_remarks' => 'nullable|string',
	//     'other_remarks' => 'nullable|string',
	//     'airticket_include' => 'nullable|in:false,true',
	//     'insurance_include' => 'nullable|in:true,false',
	//     'misc_include' => 'nullable|in:true,false',
	//     'land_package_include' => 'nullable|in:true,false',
	//     'hotel_include' => 'nullable|in:true,false',
	//     'cruise_include' => 'nullable|in:true,false',
	//     ]);

	//     $customerDetails = $request->airticket['customer_details'] ?? null;

	//     if ($customerDetails) {
	//     // Check if customer exists by email or phone number
	//     $customer = \App\Models\Customer::where('email', $customerDetails['email'])
	//         ->orWhere('phone_number', $customerDetails['phone_number'])
	//         ->first();

	//     if (!$customer) {
	//         // Prepare request for customer creation
	//         $customerRequest = new CustomerRequest([
	//             'id' => null,
	//             'full_name' => $customerDetails['full_name'],
	//             'email' => $customerDetails['email'],
	//             'phone_number' => $customerDetails['phone_number'],
	//             'alternate_phone' => $customerDetails['alternate_phone'] ?? null,
	//             'address' => $customerDetails['address'],
	//             'city_id' => $customerDetails['city_id'],
	//             'country_id' => $customerDetails['country_id'],
	//             'state_id' => $customerDetails['state_id'],
	//             'passport_number' => $customerDetails['passport_number'],
	//             'postal_code' => $customerDetails['postal_code']
	//         ]);

	//         // Use repository to create a new customer
	//         $customer = $this->customerRepo->update(null, $customerRequest);
	//     }

	//     // Set the customer_id in the request
	//     $request->merge(['customer_id' => $customer->id]);
	//     }

	//     if (!$request->id) {
	//     // Generate a new Invoice Number
	//         $lastInvoice = \App\Models\InvoiceMain::latest('id')->first();
	//         $newInvoiceNumber = $lastInvoice
	//             ? 'IN-' . str_pad(((int) str_replace('KTT-', '', $lastInvoice->invoice_number)) + 1, 3, '0', STR_PAD_LEFT)
	//             : 'KTT-001';
	//         $request->merge(['invoice_number' => $newInvoiceNumber]);
	//         $response = $this->invoiceMainRepo->update($request->id, $request);

	//     }else{
	//         $response = $this->invoiceMainRepo->update($request->id, $request);
	//         $lastInvoice = \App\Models\InvoiceMain::latest('id')->first();
	//         $newInvoiceNumber = $lastInvoice
	//             ? 'IN-' . str_pad(((int) str_replace('KTT-', '', $lastInvoice->invoice_number)) + 1, 3, '0', STR_PAD_LEFT)
	//             : 'KTTC-001';
	//         $request->merge(['invoice_number' => $newInvoiceNumber,
	//                 'parent_id' => $request->id,
	//             ]);
	//         $childResponse = $this->invoiceMainRepo->store($request);
	//         //dd($childResponse);
	//     }
	//     return $this->success(new InvoiceMainResource($response), $request->id ? 'Invoice Updated Successfully' : 'Invoice Created Successfully');
	// }

	public function saveAndUpdate(Request $request)
	{
		$request->validate([
			'id' => 'nullable|integer|exists:invoices_mains,id',
			'invoice_id' =>  'nullable|integer|exists:invoices,id',		
			'sales_agent_id' => 'nullable|integer|exists:users,id',
			'flight_enquiry_id' => 'nullable|integer|exists:enquiries,id',
			'hotel_enquire_id' => 'nullable|integer|exists:hotel_enquires,id',
			'airLine_id' => 'nullable|integer|exists:air_lines,id',
			'customer_id' => 'nullable|integer|exists:customers,id',
			'supplier_id' => 'nullable|integer|exists:suppliers,id',
			'itinerary' => 'nullable|string',
			'gds_type' => 'nullable|string',
			'gds_locator' => 'nullable|string',
			'booking_date' => 'nullable|date',
			'departure_date' => 'nullable|date',
			'ticket_number' => 'nullable|string|max:255',
			'mco' => 'nullable|string|max:255',
			'status' => 'nullable|string|max:255',
			'travel_from' => 'nullable|string|max:255',
			'travel_to' => 'nullable|string|max:255',
			'customer_details' => 'nullable|array',
			'airticket' => 'nullable|array',
			'valid_canadian_passport' => 'nullable|in:true,false',
			'valid_travel_visa' => 'nullable|in:true,false',
			'tourist_card' => 'nullable|in:false,true',
			'canadian_citizenship_or_prCard' => 'nullable|in:true,false',
			'special_remarks' => 'nullable|string',
			'other_remarks' => 'nullable|string',
			'airticket_include' => 'nullable|in:false,true',
			'insurance_include' => 'nullable|in:true,false',
			'misc_include' => 'nullable|in:true,false',
			'land_package_include' => 'nullable|in:true,false',
			'hotel_include' => 'nullable|in:true,false',
			'cruise_include' => 'nullable|in:true,false',
		]);

		$user_id = auth()->user()->id;

		$customerDetails = $request->airticket['customer_details'] ?? ($request->customer_details ?? null);

		if ($customerDetails) {
			$customer = \App\Models\Customer::where('email', $customerDetails['email'])
				->orWhere('phone_number', $customerDetails['phone_number'])
				->first();

			if (!$customer) {
				$customerRequest = new CustomerRequest([
					'id' => null,
					'full_name' => $customerDetails['full_name'],
					'email' => $customerDetails['email'],
					'phone_number' => $customerDetails['phone_number'],
					'alternate_phone' => $customerDetails['alternate_phone'] ?? null,
					'address' => $customerDetails['address'],
					'city_id' => $customerDetails['city_id'],
					'country_id' => $customerDetails['country_id'],
					'state_id' => $customerDetails['state_id'],
					'passport_number' => $customerDetails['passport_number'],
					'postal_code' => $customerDetails['postal_code']
				]);
				$customer = $this->customerRepo->update(null, $customerRequest);
			}
			$request->merge(['customer_id' => $customer->id]);
		}

		// Generate a unique invoice number
		if (!$request->id) {
			$lastInvoice = \App\Models\InvoiceMain::latest('id')->where('parent_id', null)->first();
			$newInvoiceNumber = $lastInvoice
				? 'KTT-' . str_pad(((int) str_replace('KTT-', '', $lastInvoice->invoice_number)) + 1, 3, '0', STR_PAD_LEFT)
				: 'KTT-001';
			$request->merge(['invoice_number' => $newInvoiceNumber, 'created_by_user_id' => $user_id]);
			$response = $this->invoiceMainRepo->update($request->id, $request);
		} else {
			$request->merge(['updated_by_user_id' => $user_id]);
			$response = $this->invoiceMainRepo->update($request->id, $request);
			$lastInvoice = \App\Models\InvoiceMain::latest('id')->where('parent_id', null)->first();
			$newInvoiceNumber = $lastInvoice
				? 'KTT-' . str_pad(((int) str_replace('KTT-', '', $lastInvoice->invoice_number)) + 1, 3, '0', STR_PAD_LEFT)
				: 'KTT-001';

			// Generate history pattern: KTT-001-H1, KTT-001-H2, etc.
			$parentInvoice = \App\Models\InvoiceMain::find($request->id);
			$childCount = \App\Models\InvoiceMain::where('parent_id', $request->id)->count();
			$historyPattern = $parentInvoice->invoice_number . '-H' . ($childCount + 1);

			$request->merge([
				'invoice_number' => $historyPattern,
				'parent_id' => $request->id,
				'updated_by_user_id' => $user_id,
			]);

			$childResponse = $this->invoiceMainRepo->store($request);
		}

		return $this->success(new InvoiceMainResource($response), $request->id ? 'Invoice Updated Successfully' : 'Invoice Created Successfully');
	}


	public function delete($id)
	{

		$invoiceMain = InvoiceMain::find($id);

		if (!$invoiceMain) {
			return response()->json([
				'message' => $message ?? 'invoice record not found',
			], 500);
		}

		$invoiceMain->delete();

		return $this->success(['flash_type' => 'success', 'flash_message' => 'invoice deleted successfully', 'flash_description' => $invoiceMain->invoice_number]);
	}

	public function sendInvoiceMail($id, $type) {
		$invoiceMain = InvoiceMain::with('supplier', 'sales_agents', 'airLine', 'customer')->findOrFail($id);

		if(!$invoiceMain->customer_details['email']){
			return response()->json([
				'message' => 'Invoice mail not sent. Email not found',
			], 500);
		}
		
		// if(!$invoiceMain->pdf_path){
			if($type == 'airticket'){				
				$pdfPath = 'assets/invoices/' . $invoiceMain->invoice_number . '.pdf';
				$fullPdfPath = public_path($pdfPath);

				Pdf::loadView('pdf.air-ticket-invoice-main-pdf', ['invoiceMain' => $invoiceMain])->save($fullPdfPath);
				// return Pdf::loadView('pdf.air-ticket-invoice-main-pdf', ['invoiceMain' => $invoiceMain])->stream();				
				$invoiceMain->update(['pdf_path' => $pdfPath]);
			}
		// }
		
		Mail::to($invoiceMain->customer_details['email'])->send(new InvoiceMainMail($invoiceMain, $type));
		return $this->success(['flash_type' => 'success', 'flash_message' => 'invoice mail sent successfully', 'flash_description' => $invoiceMain->invoice_number]);
	}
}
