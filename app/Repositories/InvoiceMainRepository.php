<?php

namespace App\Repositories;

use App\Models\InvoiceMain;
use App\Repositories\AppRepository;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;

class InvoiceMainRepository extends AppRepository
{
	protected $model;

	public function __construct(InvoiceMain $model)
	{
		$this->model = $model;
	}


	public function filter()
	{
		$request = request();
		$model = $this->query();

		if ($request->has('keyword') && isset($request->keyword)) {
			$model->whereLike(['name'], $request->keyword);
		}

		if ($request->has('oldest') && isset($request->oldest)) {
			$model->oldest();
		} else {
			$model->latest();
		}

		if ($request->has('created_by_user_id') && isset($request->created_by_user_id)) {
			$model->where('created_by_user_id', $request->created_by_user_id);
		}
		if ($request->has('updated_by_user_id') && isset($request->updated_by_user_id)) {
			$model->where('updated_by_user_id', $request->updated_by_user_id);
		}
		
		if ($request->has('invoice_id') && isset($request->invoice_id)) {
			$model->where('invoice_id', $request->invoice_id);
		}
		if ($request->has('parent_id') && isset($request->parent_id)) {
			$model->where('parent_id', $request->parent_id);
		}
		if ($request->has('flight_enquiry_id') && isset($request->flight_enquiry_id)) {
			$model->where('flight_enquiry_id', $request->flight_enquiry_id);
		}
		if ($request->has('hotel_enquire_id') && isset($request->hotel_enquire_id)) {
			$model->where('hotel_enquire_id', $request->hotel_enquire_id);
		}
		if ($request->has('airLine_id') && isset($request->airLine_id)) {
			$model->where('airLine_id', $request->airLine_id);
		}

		if ($request->has('customer_id') && isset($request->customer_id)) {
			$model->where('customer_id', $request->customer_id);
		}
		if ($request->has('supplier_id') && isset($request->supplier_id)) {
			$model->where('supplier_id', $request->supplier_id);
		}
		if ($request->has('to') && isset($request->to)) {
			$model->where('to', $request->to);
		}
		if ($request->has('invoice_number') && isset($request->invoice_number)) {
			$model->where('invoice_number', $request->invoice_number);
		}
		if ($request->has('ticket_number') && isset($request->ticket_number)) {
			$model->where('ticket_number', $request->ticket_number);
		}
		if ($request->has('sales_agent') && isset($request->sales_agent)) {
			$model->where('sales_agent', 'like', '%' . $request->sales_agent . '%');
		}
		if ($request->has('itinerary') && isset($request->itinerary)) {
			$model->where('itinerary', 'like', '%' . $request->itinerary . '%');
		}
		if ($request->has('gds_type') && isset($request->gds_type)) {
			$model->where('gds_type', 'like', '%' . $request->gds_type . '%');
		}
		if ($request->has('gds_locator') && isset($request->gds_locator)) {
			$model->where('gds_locator', 'like', '%' . $request->gds_locator . '%');
		}
		if ($request->has('booking_date') && isset($request->booking_date)) {
			$model->where('booking_date', 'like', '%' . $request->booking_date . '%');
		}
		if ($request->has('departure_date') && isset($request->departure_date)) {
			$model->where('departure_date', 'like', '%' . $request->departure_date . '%');
		}
		if ($request->has('ticket_number') && isset($request->ticket_number)) {
			$model->where('ticket_number', 'like', '%' . $request->ticket_number . '%');
		}
		if ($request->has('travel_from') && isset($request->travel_from)) {
			$model->where('travel_from', $request->travel_from);
		}
		if ($request->has('travel_to') && isset($request->travel_to)) {
			$model->where('travel_to', $request->travel_to);
		}

		if ($request->has('airticket_include') && isset($request->airticket_include)) {
			$model->where('airticket_include', $request->airticket_include);
		}
		if ($request->has('insurance_include') && isset($request->insurance_include)) {
			$model->where('insurance_include', $request->insurance_include);
		}
		if ($request->has('misc_include') && isset($request->misc_include)) {
			$model->where('misc_include', $request->misc_include);
		}
		if ($request->has('land_package_include') && isset($request->land_package_include)) {
			$model->where('land_package_include', $request->land_package_include);
		}
		if ($request->has('hotel_include') && isset($request->hotel_include)) {
			$model->where('hotel_include', $request->hotel_include);
		}
		if ($request->has('cruise_include') && isset($request->cruise_include)) {
			$model->where('cruise_include', $request->cruise_include);
		}
		if ($request->has('valid_canadian_passport') && isset($request->valid_canadian_passport)) {
			$model->where('valid_canadian_passport', $request->valid_canadian_passport);
		}
		if ($request->has('valid_travel_visa') && isset($request->valid_travel_visa)) {
			$model->where('valid_travel_visa', $request->valid_travel_visa);
		}
		if ($request->has('tourist_card') && isset($request->tourist_card)) {
			$model->where('tourist_card', $request->tourist_card);
		}
		if ($request->has('canadian_citizenship_or_prCard') && isset($request->canadian_citizenship_or_prCard)) {
			$model->where('canadian_citizenship_or_prCard', $request->canadian_citizenship_or_prCard);
		}


		if ($request->has('created_by_user_id') && isset($request->created_by_user_id)) {
			$model->where('created_by_user_id', $request->created_by_user_id);
		}
		if ($request->has('updated_by_user_id') && isset($request->updated_by_user_id)) {
			$model->where('updated_by_user_id', $request->updated_by_user_id);
		}
		if ($request->has('sales_agent_id') && isset($request->sales_agent_id)) {
			$model->where('sales_agent_id', $request->sales_agent_id);
		}


		if ($request->has('start_date') && $request->has('end_date')) {
			$model->whereBetween('created_at', [
				$request->start_date,
				date('Y-m-d 23:59:59', strtotime($request->end_date))
			]);
		} elseif ($request->has('start_date')) {
			$model->where('created_at', '>=', $request->start_date);
		} elseif ($request->has('end_date')) {
			$model->where('created_at', '<=', date('Y-m-d 23:59:59', strtotime($request->end_date)));
		}


		return $model;
	}
}
