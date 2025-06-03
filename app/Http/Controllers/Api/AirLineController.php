<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\BaseController;

use App\Repositories\AirLineRepository;
use App\Http\Resources\AirLineResource;
use App\Models\AirLine;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;


class AirLineController extends BaseController
{
	protected $airLineRepo;

	public function __construct(AirLineRepository $airLineRepo)
	{
		$this->airLineRepo = $airLineRepo;
	}

	public function list(Request $request)
	{

		$limit = $request->has('limit') ? $request->limit : 999;
		$response = $this->airLineRepo->filter()->latest()->paginate($limit);
		return $this->successWithPaginateData(AirLineResource::collection($response), $response);
	}
	public function saveAndUpdate(Request $request)
	{
		// Validate the request
		$request->validate([
			'id' => 'nullable|integer|exists:air_lines,id',
			'airline_name' => ['sometimes', 'string', 'max:255'],
			'slug' => ['sometimes', 'string', 'max:255', Rule::unique('air_lines', 'slug')->ignore($request->id)],
			'price' => ['nullable', 'numeric'],
			'airline_code' => 'nullable|string',
			'country' => 'nullable|string',
		]);


		$response = $this->airLineRepo->update($request->id, $request);


		if ($request->id) {
			return $this->success(new AirLineResource($response), 'AirLine Updated Successfully');
		} else {
			return $this->success(new AirLineResource($response), 'AirLine Created Successfully');
		}
	}
	public function delete($id)
	{

		$airLine = AirLine::find($id);

		if (!$airLine) {
			return response()->json([
				'message' => $message ?? 'airline record not found',
			], 500);
		}

		$airLine->delete();

		return $this->success(['flash_type' => 'success', 'flash_message' => 'airline deleted successfully', 'flash_description' => $airLine->airline_name]);
	}
}
