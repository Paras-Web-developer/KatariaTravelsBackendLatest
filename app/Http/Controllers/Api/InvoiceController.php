<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\BaseController;
use App\Repositories\InvoiceRepository;
use App\Http\Resources\InvoiceResource;
use App\Models\Invoice;
use Illuminate\Http\Request;

class InvoiceController extends BaseController
{
	protected $invoiceRepo;

	public function __construct(InvoiceRepository $invoiceRepo)
	{
		$this->invoiceRepo = $invoiceRepo;
	}

	public function list(Request $request)
	{
		$limit = $request->get('limit', 999);
		$response = $this->invoiceRepo->filter()
			->with([
				'supplier',
				'agentUser',
				'transactionType',
				'transactionTypeAgency',
				'airLine',
				'enquiry',
				'parent',
				'children',
			])->latest()
			->whereNull('parent_id')
			->paginate($limit);

		return $this->successWithPaginateData(InvoiceResource::collection($response), $response);
	}

	public function saveAndUpdate(Request $request)
	{
		//dd($request->all());
		$request->validate([
			'id' => 'nullable|integer|exists:invoices,id',
			'parent_id' => 'nullable|integer|exists:invoices,id',
			'invoice_holder_name' => 'nullable|string|max:255',
			'invoice_number' => 'sometimes|string|unique:invoices,invoice_number,' . $request->id . ',id',
			//'invoice_number' => 'nullable|string|unique:invoices,invoice_number,' . $request->id,
			'transaction_type_id' => 'nullable|integer|exists:transaction_types,id',
			'transaction_type_agency_id' => 'nullable|integer|exists:transaction_types,id',
			'agent_user_id' => 'nullable|integer|exists:users,id',
			'supplier_id' => 'nullable|integer|exists:suppliers,id',
			'enquiry_id' => 'nullable|integer|exists:enquiries,id',
			'airLine_id' => 'nullable|integer|exists:air_lines,id',
			'date' => 'required|date',
			'pnr' => 'nullable|string|max:50',
			'temp_supplier' => 'nullable|string|max:255',
			'ch_eq_ue' => 'nullable|string|max:255',
			'ticket_status' => 'nullable|string|max:50',
			'reference_number_of_et' => 'nullable|string|max:100',
			'remarks' => 'nullable|string|max:1000',
			'tickets' => 'required|array',
		]);

		$data = $request->only([
			'invoice_number',
			'invoice_holder_name',
			'transaction_type_id',
			'transaction_type_agency_id',
			'agent_user_id',
			'supplier_id',
			'enquiry_id',
			'airLine_id',
			'date',
			'pnr',
			'temp_supplier',
			'ch_eq_ue',
			'ticket_status',
			'reference_number_of_et',
			'remarks',
			'tickets',
			'parent_id',
		]);

		$invoiceParent = Invoice::updateOrCreate(
			['id' => $request->id],
			$data
		);

		if ($request->id!=Null) {
			$data['parent_id'] = $request->id;
			$data['invoice_number'] = $invoiceParent->invoice_number;
			$childInvoice = Invoice::create($data);
		}

		$message = $request->id ? 'Invoice updated successfully.' : 'Invoice created successfully.';

		return $this->success(new InvoiceResource($invoiceParent), $message);
	}



	public function delete($id)
	{
		$invoice = Invoice::find($id);

		if (!$invoice) {
			return $this->notFound('Invoice not found.');
		}

		$invoice->delete();

		return $this->success([
			'flash_type' => 'success',
			'flash_message' => 'Invoice deleted successfully.',
			'flash_description' => $invoice->invoice_number,
		]);
	}
}
