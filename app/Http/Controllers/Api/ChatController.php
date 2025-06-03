<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Models\Chat;
use App\Events\ChatMessageSent;
use Pusher\Pusher;



class ChatController extends Controller
{


    // public function testChannel($receiverId)
    // {
    //     $user = auth()->user(); // Get the authenticated user

    //     // Simulate channel authorization logic
    //     $isAuthorized = (int) $user->id === (int) $receiverId || (int) $user->id !== null;

    //     return response()->json([
    //         'authorized' => $isAuthorized,
    //         'user' => $user,
    //         'receiverId' => $receiverId,
    //     ]);
    // }

    public function allChats(Request $request)
    {
        $message = Chat::where('sender_id', auth()->id())->get();
        return response()->json($message, 201);
    }
    public function index($receiverId)
    {
        return Chat::where(function ($query) use ($receiverId) {
            $query->where('sender_id', auth()->id())
                ->where('receiver_id', $receiverId);
        })->orWhere(function ($query) use ($receiverId) {
            $query->where('sender_id', $receiverId)
                ->where('receiver_id', auth()->id());
        })->get();
    }


    // public function store(Request $request)
    // {
    //     $request->validate([
    //         'receiver_id' => 'required|exists:users,id',
    //         'message' => 'required|string|max:1000',
    //     ]);

    //     // Create a new chat message
    //     $chat = Chat::create([
    //         'sender_id' => auth()->id(),
    //         'receiver_id' => $request->receiver_id,
    //         'message' => $request->message,
    //     ]);

    //     // Broadcast the chat message event

    //     broadcast(new ChatMessageSent($chat))->toOthers();
    //     //dd(broadcast(new ChatMessageSent($chat))->toOthers());

    //     // Return the chat message as a response
    //     return response()->json($chat, 201);
    // }

    public function store(Request $request)
    {
        // Validate the incoming request data
        $request->validate([
            'receiver_id' => 'required|exists:users,id',
            'message' => 'required|string|max:1000',
        ]);

        // Create a new chat message
        $chat = Chat::create([
            'sender_id' => auth()->id(),
            'receiver_id' => $request->receiver_id,
            'message' => $request->message,
        ]);

        // Pusher setup
        $options = array(
            'cluster' => 'ap2',
            'useTLS' => true
        );

        // $pusher = new Pusher(
        //     '91c8a0fa751bddeef247',
        //     '371e7ab4c09707bb4d9c',
        //     '1919831',
        //     $options
        // );

        $pusher = new Pusher(
            env('PUSHER_APP_KEY'),
            env('PUSHER_APP_SECRET'),
            env('PUSHER_APP_ID'),
            $options
        );

        // Prepare the data to send with Pusher
        $data['message'] = $request->message;
        $data['sender_id'] = auth()->id();
        $data['receiver_id'] = (int) $request->receiver_id;

        // Trigger the Pusher event
        $pusher->trigger('my-channel', 'my-event', $data);

        // Broadcast the chat message event (if you want to broadcast through Laravel broadcasting as well)
        broadcast(new ChatMessageSent($chat))->toOthers();

        // Return the chat message as a response
        return response()->json($chat, 201);
    }
}
