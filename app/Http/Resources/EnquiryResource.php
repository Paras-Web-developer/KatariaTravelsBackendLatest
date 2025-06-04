<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Carbon\Carbon;

class EnquiryResource extends JsonResource
{
	/**
	 * Transform the resource into an array.
	 *
	 * @return array<string, mixed>
	 */
	public function toArray(Request $request): array
	{
		// Calculate enquiry_payment_status
		$enquiryPaymentStatus = 'not_paid';
		if ($this->paid_amount > 0) {
			if ($this->paid_amount < $this->budget) {
				$enquiryPaymentStatus = 'pending';
			} elseif ($this->paid_amount == $this->budget) {
				$enquiryPaymentStatus = 'paid';
			} elseif ($this->paid_amount > $this->budget) {
				$enquiryPaymentStatus = 'over_paid';
			}
		}

		return [
			'id' => $this->id,
			'enquiry_code' => $this->enquiry_code,
			'supplier_id' => $this->supplier_id,
			'supplier' => new SupplierResource($this->whenLoaded('supplier')),
			'transaction_type_id' => $this->transaction_type_id,
			'transactionType' => new TransactionTypeResource($this->whenLoaded('transactionType')),
			'updated_by_user_id' => $this->updated_by_user_id,
			'updatedByUser' => new UserResource($this->whenLoaded('updatedByUser')),
			'parent_id' => $this->parent_id,
			'parent' => new EnquiryResource($this->whenLoaded('parent')),
			'children' => EnquiryResource::collection($this->whenLoaded('children')),
			'created_by_user_id' => $this->created_by_user_id,
			'createdByUser' => new UserResource($this->whenLoaded('createdByUser')),
			'enquiry_source_id' => $this->enquiry_source_id,
			'enquirySource' => new EnquirySourceResource($this->whenLoaded('enquirySource')),
			'enquiry_payment_status' => $enquiryPaymentStatus,
			'assigned_to_user_id' => $this->assigned_to_user_id,
			'assignedToUser' => new UserResource($this->whenLoaded('assignedToUser')),
			'air_line_id' => $this->air_line_id,
			'airLine' => new AirLineResource($this->whenLoaded('airLine')),
			'enquiry_status_id' => $this->enquiry_status_id,
			'enquiryStatus' => new EnquiryStatusResource($this->whenLoaded('enquiryStatus')),
			'fromAirport' => new WorldAirportResource($this->whenLoaded('fromAirport')),
			'toAirport' => new WorldAirportResource($this->whenLoaded('toAirport')),
			'payment_details' => [
				'pending_amount' => $this->budget && $this->paid_amount
					? max(0, $this->budget - $this->paid_amount)
					: 0,
				'overPaidAmount' => $this->budget && $this->paid_amount
					? max(0, $this->paid_amount - $this->budget)
					: 0,
			],
			'type' => $this->type,
			'customer_name' => $this->customer_name,
			'title' => $this->title,
			'phone_number' => $this->phone_number,
			'email' => $this->email,
			'package_type' => $this->package_type,
			'departure_date' => $this->departure_date,
			// 'departure_date' => $this->departure_date ? Carbon::parse($this->departure_date)->format('d-m-Y') : null,
			// 'return_date' => $this->return_date ? Carbon::parse($this->return_date)->format('d-m-Y') : null,
			'return_date' => $this->return_date,
			'from' => $this->from,
			'to' => $this->to,
			'class_of_travel' => $this->class_of_travel,
			'adult' => $this->adult,
			'child' => $this->child,
			'infant' => $this->infant,
			'booking_reference' => $this->booking_reference,
			'budget' => $this->budget,
			'invoice_number' => $this->invoice_number,
			'remark' => $this->remark,
			'paid_amount' => $this->paid_amount,
			'status' => $this->status,
			'followed_up_at' => $this->followed_up_at,
			'packages' => PackageResource::collection($this->whenLoaded('packages')),
			'invoices' => InvoiceResource::collection($this->whenLoaded('invoices')),
			'admin_payment_status' => $this->admin_payment_status,
			'note' => $this->note,
			'created_at' => $this->created_at,
			'updated_at' => $this->updated_at,
		];
	}
}
