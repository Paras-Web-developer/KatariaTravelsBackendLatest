<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\BaseController;

use App\Repositories\MessageRepository;
use App\Http\Resources\MessageResource;
use Illuminate\Http\Request;

class MessagesController extends BaseController
{
    protected $messageRepo;

    public function __construct(MessageRepository $messageRepo)
    {
        $this->messageRepo = $messageRepo;
    }

    public function list(Request $request)
    {
       
        $limit = $request->has('limit') ? $request->limit : 1000;
        $response = $this->messageRepo->filter()->latest()->paginate($limit);
        return $this->successWithPaginateData(MessageResource::collection($response), $response);
    }

   
}


