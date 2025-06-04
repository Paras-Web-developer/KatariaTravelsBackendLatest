<?php

namespace App\Http\Controllers;

use Exception;
use stdClass;
use Illuminate\Http\Request;

class BaseController extends Controller
{
    public function success($message = null)
    {
        return response()->json([
            'status' => true,
            'message' => $message ?? 'success'
        ], 200);
    }

    public function successWithData($data, $message = null)
    {
        return response()->json([
            'status' => true,
            'message' => $message ?? 'success',
            'data' => $data
        ], 200);
    }

    public function successWithPaginateData($data, $paginateData, $message = null)
    {
        return response()->json([
            'status' => true,
            'message' => $message ?? 'success',
            'data' => $data,
            'paginate_data' => $this->paginateDataExtract($paginateData),
        ], 200);
    }

    public function validationError($message)
    {
        $object = new stdClass;
        $object->message = [$message];

        return response()->json([
            "message" => "validation error.",
            "errors" => $object
        ], 422);
    }

    public function notFound($message = null)
    {
        return response()->json([
            'message' => $message ?? 'not found'
        ], 404);
    }

    public function badRequest($message = null)
    {
        return response()->json([
            'message' => $message ?? 'invalid request'
        ], 400);
    }

    public function error(Exception $ex, $message = null)
    {
        return response()->json([
            'message' => $message ?? 'an error occurred',
            'exception' => $ex
        ], 500);
    }
    public function errorMessage($message, $statusCode = 400)
    {
        return response()->json([
            'message' => $message,
        ], $statusCode);
    }

    function paginateDataExtract($model)
    {

        //return $model->toArray();
        return collect($model->toArray())->forget('data');
    }
    function alertWithDelete($message = null)
    {
        return response()->json([
            'message' => $message ?? 'record deleted'
        ], 200);
    }
}
