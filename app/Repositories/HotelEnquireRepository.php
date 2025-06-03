<?php

namespace App\Repositories;

use App\Models\AirLine;
use App\Models\HotelEnquire;
use App\Models\Role;
use App\Repositories\AppRepository;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;

class HotelEnquireRepository extends AppRepository
{
	protected $model;

	public function __construct(HotelEnquire $model)
	{
		$this->model = $model;
	}

	// public function generateEnquiryCode($isChild = false, $parentId = null)
	// {
	//     $currentDate = now()->format('YmdHis');
	//     if ($isChild && $parentId) {
	//         $parentEnquiry = $this->model->find($parentId);
	//         if (!$parentEnquiry) {
	//             throw new \Exception('Parent hotel enquiry not found');
	//         }

	//         $childCount = $this->model->where('parent_id', $parentId)->count() + 1;
	//         return $parentEnquiry->enquiry_code . 'P' . $childCount;
	//     } else {
	//         $lastEnquiry = $this->model
	//             ->whereNull('parent_id')
	//             ->where('enquiry_code', 'like', 'ENQ' . $currentDate . '%')
	//             ->orderBy('id', 'desc')
	//             ->first();

	//         $nextNumber = $lastEnquiry ? ((int)substr($lastEnquiry->enquiry_code, 9)) + 1 : 1;

	//         return 'ENQ' . $currentDate;
	//     }
	// }

	public function generateEnquiryCode($isChild = false, $parentId = null)
	{
		$currentDate = now()->format('Ymd'); // Format: YYYYMMDD

		if ($isChild && $parentId) {
			$parentEnquiry = $this->model->find($parentId);
			if (!$parentEnquiry) {
				throw new \Exception('Parent hotel enquiry not found');
			}

			$childCount = $this->model->where('parent_id', $parentId)->withTrashed()->count() + 1;
			return $parentEnquiry->enquiry_code . 'H' . str_pad($childCount, 3, '0', STR_PAD_LEFT);
		} else {
			$lastEnquiry = $this->model
				->whereNull('parent_id')
				->where('enquiry_code', 'like', 'HTL' . $currentDate . '%')
				->withTrashed()
				->orderBy('id', 'desc')
				->first();

			$nextNumber = $lastEnquiry
				? ((int)substr($lastEnquiry->enquiry_code, -3)) + 1
				: 1;

			return 'HTL' . $currentDate . str_pad($nextNumber, 3, '0', STR_PAD_LEFT);
		}
	}




	public function filter()
	{
		$request = request();
		$model = $this->query();

		if ($request->has('keyword') && isset($request->keyword)) {
			$model->whereLike(['name', 'slug'], $request->keyword);
		}

		if ($request->has('oldest') && isset($request->oldest)) {
			$model->oldest();
		} else {
			$model->latest();
		}

		if ($request->has('enquiry_code') && isset($request->enquiry_code)) {
			$model->where('enquiry_code', $request->enquiry_code);
		}

		if ($request->has('created_by_user_id') && isset($request->created_by_user_id)) {
			$model->where('created_by_user_id', $request->created_by_user_id);
		}
		if ($request->has('transaction_type_id') && isset($request->transaction_type_id)) {
			$model->where('transaction_type_id', $request->transaction_type_id);
		}
		if ($request->has('enquiry_status_id') && isset($request->enquiry_status_id)) {
			$model->where('enquiry_status_id', $request->enquiry_status_id);
		}


		if ($request->has('enquiry_source_id') && isset($request->enquiry_source_id)) {
			$model->where('enquiry_source_id', $request->enquiry_source_id);
		}
		if ($request->has('admin_payment_status') && isset($request->admin_payment_status)) {
			$model->where('admin_payment_status', $request->admin_payment_status);
		}
		if ($request->has('note') && isset($request->note)) {
			$model->where('note', $request->note);
		}


		if ($request->has('title') && isset($request->title)) {
			$model->where('title', 'like', '%' . $request->title . '%');
		}
		if ($request->has('full_name') && isset($request->full_name)) {
			$model->where('full_name', 'like', '%' . $request->full_name . '%');
		}
		if ($request->has('email') && isset($request->email)) {
			$model->where('email', 'like', '%' . $request->email . '%');
		}

		if ($request->has('phone_number') && isset($request->phone_number)) {
			$model->where('phone_number', 'like', '%' . $request->phone_number . '%');
		}
		if ($request->has('enquiry_type') && isset($request->enquiry_type)) {
			$model->where('enquiry_type', 'like', '%' . $request->enquiry_type . '%');
		}
		if ($request->has('check_in_date') && isset($request->check_in_date)) {
			$model->where('check_in_date', $request->check_in_date);
		}
		if ($request->has('check_out_date') && isset($request->check_out_date)) {
			$model->where('check_out_date', $request->check_out_date);
		}
		if ($request->has('room') && isset($request->room)) {
			$model->where('room', $request->room);
		}
		if ($request->has('special_requests') && isset($request->special_requests)) {
			$model->where('special_requests', $request->special_requests);
		}

		if ($request->has('status') && isset($request->status)) {
			$model->where('status', $request->status);
		}
		if ($request->has('updated_by_user_id') && isset($request->updated_by_user_id)) {
			$model->where('updated_by_user_id', $request->updated_by_user_id);
		}
		if ($request->has('assigned_to_user_id') && isset($request->assigned_to_user_id)) {
			$model->where('assigned_to_user_id', $request->assigned_to_user_id);
		}

		if ($request->has('enquiry_payment_status') && isset($request->enquiry_payment_status)) {
			$model->where('enquiry_payment_status', $request->enquiry_payment_status);
		}
		if ($request->has('booking_reference') && isset($request->booking_reference)) {
			$model->where('booking_reference', $request->booking_reference);
		}


		if ($request->has('min_price') && $request->has('max_price')) {
			// If both min_price and max_price are provided, filter within the range
			$model->whereBetween('budget', [$request->min_price, $request->max_price]);
		} else {
			// If only min_price is provided, filter records with budget >= min_price
			if ($request->has('min_price')) {
				$model->where('budget', '>=', $request->min_price);
			}

			// If only max_price is provided, filter records with budget <= max_price
			if ($request->has('max_price')) {
				$model->where('budget', '<=', $request->max_price);
			}
		}
		if ($request->has('invoice_number') && isset($request->invoice_number)) {
			$model->where('invoice_number', 'like', '%' . $request->invoice_number . '%');
		}
		if ($request->has('paid_amount') && isset($request->paid_amount)) {
			$model->where('paid_amount', 'like', '%' . $request->paid_amount . '%');
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
