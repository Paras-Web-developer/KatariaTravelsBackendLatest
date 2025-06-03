<?php

namespace App\Http\Controllers\Api;

use App\Events\FollowupReminderSent;
use App\Repositories\AirLineRepository;
use App\Http\Resources\AirLineResource;
use App\Http\Controllers\Controller;
use App\Models\FollowupMessage;
use Illuminate\Http\Request;
use Carbon\Carbon;

class FollowupMessageController extends Controller
{
	protected $airLineRepo;

	public function __construct(AirLineRepository $airLineRepo)
	{
		$this->airLineRepo = $airLineRepo;
	}

	// public function store(Request $request, $id)
	// {
	//     $request->validate([
	//         'followed_up_at' => 'required|date_format:Y-m-d H:i:s',
	//         'follow_up_message' => 'required|string',
	//     ]);

	//     $followup = FollowupMessage::create([
	//         'enquiry_id' => $id,
	//         'followed_up_at' => $request->followed_up_at,
	//         'follow_up_message' => $request->follow_up_message,
	//     ]);

	//     return response()->json(['message' => 'Follow-up scheduled successfully', 'data' => $followup], 201);
	// }

	public function store(Request $request, $id)
	{
		$request->validate([
			'followed_up_at' => 'required|date_format:Y-m-d H:i:s',
			'follow_up_message' => 'required|string',
			'followupable_type' => 'required|string|in:flight,hotel,other',
			'followupable_id' => 'required|integer',
		]);


		$followup = FollowupMessage::create([
			'followed_up_at' => $request->followed_up_at,
			'follow_up_message' => $request->follow_up_message,
			'receiver_id' => $request->user()->id,

			'followupable_type' => $request->followupable_type == 'flight' ? 'App\Models\Enquiry' : ($request->followupable_type == 'hotel' ? 'App\Models\HotelEnquire' : 'App\Models\OtherService'),

			'followupable_id' => $request->followupable_id,
		])->load('followupable');


		FollowupMessage::where('is_sent', 0)
			->where('receiver_id', $request->user()->id)
			->where('followed_up_at', '>', Carbon::now())
			->where('followupable_type', $followup->followupable_type)
			->where('followupable_id', $followup->followupable_id)
			->where('id', '!=', $followup->id)
			->delete();

		return response()->json(['message' => 'Follow-up scheduled successfully', 'data' => $followup], 201);
	}
}
