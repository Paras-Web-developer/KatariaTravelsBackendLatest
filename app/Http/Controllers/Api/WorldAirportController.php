<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\BaseController;
use Illuminate\Http\Request;
use App\Models\WorldAirport;  // Import the WorldAirport model
use App\Repositories\WorldAirportRepository;
use App\Http\Resources\WorldAirportResource;
use Illuminate\Validation\Rule;

class WorldAirportController extends BaseController
{
    // public function list(Request $request)
    // {
    //     // Get the limit from the request or default to 10
    //     $limit = $request->has('limit') ? $request->limit : 100;

    //     // Fetch airports with pagination
    //     $response = WorldAirport::latest()->paginate($limit);

    //     // Return paginated data with success response
    //     return response()->json([
    //         'success' => true,
    //         'message' => 'Airports fetched successfully!',
    //         'data' => $response
    //     ]);
    // }

    protected $worldAirportRepo;

    public function __construct(WorldAirportRepository $worldAirportRepo)
    {
        $this->worldAirportRepo = $worldAirportRepo;
    }

    public function list(Request $request)
    {

        $limit = $request->has('limit') ? $request->limit : 999;
        $response = $this->worldAirportRepo->filter()->latest()->with('supplier')->paginate($limit);
        return $this->successWithPaginateData(WorldAirportResource::collection($response), $response);
    }

    public function saveAndUpdate(Request $request)
    {
        // Validate the request
        $validated = $request->validate([
            'id' => 'nullable|integer|exists:world_airports,id',
            'icao' => [
                'required', 
                'string', 
                'max:10', 
                Rule::unique('world_airports', 'icao')->ignore($request->id)
            ],
            'supplier_id' => 'nullable',
            'iata' => 'nullable|string|max:10',
            'name' => 'required|string|max:255',
            'city' => 'nullable|string|max:255',
            'state' => 'nullable|string|max:255',
            'country' => 'nullable|string|max:10',
            'elevation' => 'nullable|integer',
            'lat' => 'nullable|numeric',
            'lon' => 'nullable|numeric',
            'tz' => 'nullable|string|max:100',
        ]);

        // Create or update the WorldAirport record
        $airport = WorldAirport::updateOrCreate(
            ['id' => $request->id],  // Find by ID if it exists
            $validated  // Use validated data for creating or updating
        );

        // Return success response with appropriate message
        if ($request->id) {
            return response()->json([
                'success' => true,
                'message' => 'World Airport Updated Successfully',
                'data' => new WorldAirportResource($airport)
            ]);
        } else {
            return response()->json([
                'success' => true,
                'message' => 'World Airport Created Successfully',
                'data' => new WorldAirportResource($airport)
            ]);
        }
    }

    /**
     * Delete World Airport
     */
    public function delete($id)
    {
        $airport = WorldAirport::find($id);

        if (!$airport) {
            return response()->json([
                'success' => false,
                'message' => 'World Airport record not found',
            ], 404);
        }

        $airportName = $airport->name;
        $airport->delete();

        return response()->json([
            'success' => true,
            'message' => 'World Airport deleted successfully',
            'data' => [
                'flash_type' => 'success',
                'flash_message' => 'World Airport deleted successfully',
                'flash_description' => $airportName,
            ],
        ]);
    }


}
