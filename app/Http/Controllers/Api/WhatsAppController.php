<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\BaseController;

use App\Repositories\PhoneNumberRepository;
use App\Http\Resources\PhoneNumberResource;
use App\Models\AirLine;
use App\Models\PhoneNumber;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Http;

class WhatsAppController extends BaseController
{
    // public function sendMessage(Request $request)
    // {
    //     // Validate input
    //     $request->validate([
    //         'instance_id' => 'required|string',
    //         'jid' => 'required|string',
    //         'msg' => 'required|string',
    //     ]);

    //     // Get token from .env
    //     $token = env('WHATSAPP_MESSAGE');
    //     $instanceId = $request->input('instance_id');
    //     $jid = $request->input('jid');
    //     $msg = $request->input('msg');

    //     // Send GET request to WhatsApp API
    //     $response = Http::get("https://whatapi.deropo.in/api/v1/send-text", [
    //         'token' => $token,
    //         'instance_id' => $instanceId,
    //         'jid' => $jid,
    //         'msg' => $msg,
    //     ]);
    //     dd($response);

    //     // Return the response
    //     if ($response->successful()) {
    //         return response()->json([
    //             'success' => true,
    //             'message' => 'Message sent successfully!',
    //             'response' => $response->json(),
    //         ]);
    //     } else {
    //         return response()->json([
    //             'success' => false,
    //             'message' => 'Failed to send message',
    //             'error' => $response->json(),
    //         ], $response->status());
    //     }
    // }
    public function sendMessage(Request $request)
    {
        // Validate input
        $request->validate([
            'instance_id' => 'required|string',
            'jid' => 'required|string',
            'msg' => 'required|string',
        ]);
        //dd($request->instance_id);
    
        // Get token from .env
        $token = env('WHATSAPP_MESSAGE');
        $instanceId = $request->instance_id;
        $jid = $request->jid;
        $msg = $request->msg;
       //dd($instanceId , $jid , $msg);
    
        // Send GET request with query parameters
        $response = Http::get("https://whatapi.deropo.in/api/v1/send-text", [
            'token' => $token,
            'instance_id' => $instanceId,
            'jid' => $jid,
            'msg' => $msg,
        ]);
        
    
        // Dump and die to see the response
        //dd($response);
    
        // Return the response
        if ($response->successful()) {
            return response()->json([
                'success' => true,
                'message' => 'Message sent successfully!',
                'response' => $response->json(),
            ]);
        } else {
            return response()->json([
                'success' => false,
                'message' => 'Failed to send message',
                'error' => $response->json(),
            ], $response->status());
        }
    }
    

}
