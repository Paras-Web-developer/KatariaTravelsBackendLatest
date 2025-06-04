<?php

namespace App\Repositories;

use App\Models\AirLine;
use App\Models\OtherService;
use App\Models\Role;
use App\Repositories\AppRepository;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;

class OtherServiceRepository extends AppRepository
{
	protected $model;

	public function __construct(OtherService $model)
	{
		$this->model = $model;
	}


	// public function generateEnquiryCode($isChild = false, $parentId = null)
	// {
	//     $currentDate = now()->format('YmdHis');

	//     if ($isChild && $parentId) {
	//         $parentEnquiry = $this->model->find($parentId);
	//         if (!$parentEnquiry) {
	//             throw new \Exception('Parent of Other Services enquiry not found');
	//         }

	//         $childCount = $this->model->where('parent_id', $parentId)->count() + 1;
	//         return $parentEnquiry->enquiry_code . 'P' . $childCount;
	//     } else {
	//         $lastEnquiry = $this->model
	//             ->whereNull('parent_id')
	//             ->where('enquiry_code', 'like', 'OtherENQ' . $currentDate . '%')
	//             ->orderBy('id', 'desc')
	//             ->first();

	//         // $nextNumber = $lastEnquiry ? ((int)substr($lastEnquiry->enquiry_code, 9)) + 1 : 1;
	//         // return 'OtherENQ' . $currentDate . str_pad($nextNumber, 4, '0', STR_PAD_LEFT)
	//         return 'OtherENQ' . $currentDate;
	//     }
	// }

	public function generateEnquiryCode($isChild = false, $parentId = null)
	{
		$currentDate = now()->format('Ymd'); // Format: YYYYMMDD

		if ($isChild && $parentId) {
			$parentEnquiry = $this->model->find($parentId);
			if (!$parentEnquiry) {
				throw new \Exception('Parent of Other Services enquiry not found');
			}

			$childCount = $this->model->where('parent_id', $parentId)->count() + 1;
			return $parentEnquiry->enquiry_code . 'H' . str_pad($childCount, 3, '0', STR_PAD_LEFT);
		} else {
			$lastEnquiry = $this->model
				->whereNull('parent_id')
				->where('enquiry_code', 'like', 'ENQOS' . $currentDate . '%')
				->orderBy('id', 'desc')
				->first();

			$nextNumber = $lastEnquiry
				? ((int)substr($lastEnquiry->enquiry_code, -3)) + 1
				: 1;

			return 'ENQOS' . $currentDate . str_pad($nextNumber, 3, '0', STR_PAD_LEFT);
		}
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


		if ($request->has('transaction_type_id') && isset($request->transaction_type_id)) {
			$model->where('transaction_type_id', $request->transaction_type_id);
		}

		if ($request->has('parent_id') && isset($request->parent_id)) {
			$model->where('parent_id', $request->parent_id);
		}
		if ($request->has('enquiry_code') && isset($request->enquiry_code)) {
			$model->where('enquiry_code', $request->enquiry_code);
		}

		if ($request->has('enquiry_source_id') && isset($request->enquiry_source_id)) {
			$model->where('enquiry_source_id', $request->enquiry_source_id);
		}
		if ($request->has('enquiry_status_id') && isset($request->enquiry_status_id)) {
			$model->where('enquiry_status_id', $request->enquiry_status_id);
		}
		if ($request->has('assigned_to_user_id') && isset($request->assigned_to_user_id)) {
			$model->where('assigned_to_user_id', $request->assigned_to_user_id);
		}
		if ($request->has('title') && isset($request->title)) {
			$model->where('title', 'like', '%' . $request->title . '%');
		}
		if ($request->has('customer_name') && isset($request->customer_name)) {
			$model->where('customer_name', 'like', '%' . $request->customer_name . '%');
		}
		if ($request->has('email') && isset($request->email)) {
			$model->where('email', 'like', '%' . $request->email . '%');
		}
		if ($request->has('phone_number') && isset($request->phone_number)) {
			$model->where('phone_number', 'like', '%' . $request->phone_number . '%');
		}
		if ($request->has('invoice_number') && isset($request->invoice_number)) {
			$model->where('invoice_number', 'like', '%' . $request->invoice_number . '%');
		}
		if ($request->has('booking_reference_no') && isset($request->booking_reference_no)) {
			$model->where('booking_reference_no', 'like', '%' . $request->booking_reference_no . '%');
		}
		if ($request->has('special_requests') && isset($request->special_requests)) {
			$model->where('special_requests', $request->special_requests);
		}
		if ($request->has('service_name') && isset($request->service_name)) {
			$model->where('service_name', $request->service_name);
		}

		if ($request->has('price_quote') && isset($request->price_quote)) {
			$model->where('price_quote', $request->price_quote);
		}

		if ($request->has('paid_amount') && isset($request->paid_amount)) {
			$model->where('paid_amount', $request->paid_amount);
		}


		if ($request->has('min_price') && $request->has('max_price')) {
			$model->whereBetween('price_quote', [$request->min_price, $request->max_price]);
		} else {
			if ($request->has('min_price')) {
				$model->where('price_quote', '>=', $request->min_price);
			}
			if ($request->has('max_price')) {
				$model->where('price_quote', '<=', $request->max_price);
			}
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

		if ($request->has('status') && isset($request->status)) {
			$model->where('status', $request->status);
		}

		if ($request->has('enquiry_payment_status') && isset($request->enquiry_payment_status)) {
			$model->where('enquiry_payment_status', $request->enquiry_payment_status);
		}

		if ($request->has('admin_payment_status') && isset($request->admin_payment_status)) {
			$model->where('admin_payment_status', $request->admin_payment_status);
		}
		if ($request->has('created_by_user_id') && isset($request->created_by_user_id)) {
			$model->where('created_by_user_id', $request->created_by_user_id);
		}
		if ($request->has('updated_by_user_id') && isset($request->updated_by_user_id)) {
			$model->where('updated_by_user_id', $request->updated_by_user_id);
		}
		return $model;
	}
}
