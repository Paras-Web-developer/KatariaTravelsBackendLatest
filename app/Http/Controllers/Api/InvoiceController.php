<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\BaseController;
use App\Repositories\InvoiceRepository;
use App\Http\Resources\InvoiceResource;
use App\Models\Invoice;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class InvoiceController extends BaseController
{
	protected $invoiceRepo;

	public function __construct(InvoiceRepository $invoiceRepo)
	{
		$this->invoiceRepo = $invoiceRepo;
	}

	public function list(Request $request)
	{
		$limit = $request->has('limit') ? $request->limit : 999;
		$response = $this->invoiceRepo->filter()->with('supplier', 'agentUser', 'transactionType', 'airLine')->latest()->paginate($limit);
		return $this->successWithPaginateData(InvoiceResource::collection($response), $response);
	}


	public function saveAndUpdate(Request $request)
	{
		// Validate request
		$request->validate([
			'transaction_type_id' => 'nullable|integer|exists:transaction_types,id',
			'agent_user_id' => 'nullable|integer|exists:users,id',
			'supplier_id' => 'nullable|integer|exists:suppliers,id',
			'id' => 'nullable|integer|exists:invoices,id',
			'enquiry_id' => 'nullable|integer|exists:enquiries,id',
			'invoice_number' => 'nullable|string|unique:invoices,invoice_number,' . $request->id,
			'date' => 'required|date',
			'pnr' => 'nullable|string|max:50',
			'cost' => 'required|numeric',
			'sold_fare' => 'required|numeric',
			'amex_card' => 'nullable|numeric',
			'cibc_card' => 'nullable|numeric',
			'td_busness_visa_card' => 'nullable|numeric',
			'bmo_master_card' => 'nullable|numeric',
			'rajni_mam' => 'nullable',
			'td_fc_visa' => 'nullable|numeric',
			'ch_eq_ue' => 'nullable|numeric',
			'ticket_number' => 'nullable|string|max:100',
			'fnu' => 'nullable|string|max:255',
			'airLine_id' => 'nullable|exists:air_lines,id',
			'ticket_status' => 'nullable|string|max:50',
			'reference_number_of_et' => 'nullable|string|max:100',
			'remarks' => 'nullable|string|max:1000',
		]);

		// Find the existing invoice with the same invoice_number if it exists
		$invoice = Invoice::find($request->id);

		if ($invoice) {
			// Update the existing invoice
			$invoice->update($request->only([
				'invoice_number',
				'date',
				'agent_user_id',
				'supplier_id',
				'transaction_type_id',
				'airLine',
				'pnr',
				'cost',
				'sold_fare',
				'amex_card',
				'cibc_card',
				'td_busness_visa_card',
				'bmo_master_card',
				'rajni_mam',
				'td_fc_visa',
				'ch_eq_ue',
				'ticket_number',
				'fnu',
				'airLine_id',
				'ticket_status',
				'reference_number_of_et',
				'remarks',
				'enquiry_id',
			]));
			$message = 'Invoice Updated Successfully';
		} else {
			// Create a new invoice
			$invoice = Invoice::create($request->only([
				'invoice_number',
				'date',
				'agent_user_id',
				'transaction_type_id',
				'supplier_id',
				'airLine',
				'pnr',
				'cost',
				'sold_fare',
				'amex_card',
				'cibc_card',
				'td_busness_visa_card',
				'bmo_master_card',
				'rajni_mam',
				'td_fc_visa',
				'ch_eq_ue',
				'ticket_number',
				'fnu',
				'airLine_id',
				'ticket_status',
				'reference_number_of_et',
				'remarks',
				'enquiry_id',
			]));
			$message = 'Invoice Created Successfully';
		}

		return $this->success(new InvoiceResource($invoice), $message);
	}


	public function delete($id)
	{
		$invoice = Invoice::find($id);

		if (!$invoice) {
			return response()->json([
				'message' => 'Invoice not found',
			], 404);
		}

		$invoice->delete();

		return $this->success([
			'flash_type' => 'success',
			'flash_message' => 'Invoice deleted successfully',
			'flash_description' => $invoice->invoice_number,
		]);
	}
}
