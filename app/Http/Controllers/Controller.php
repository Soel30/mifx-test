<?php

namespace App\Http\Controllers;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller as BaseController;
use Symfony\Component\HttpFoundation\Response;

class Controller extends BaseController
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;

    public function apiResponse($code = 200, $message = null, $data = null)
    {
        return response()->json([
            'status' => $code < 400 ? "success" : "failed",
            'message' => $message,
            'data' => $data,
        ], $code);
    }

    public function apiResponseSuccess($data = null, $message = 'Request processed successfully', $code = 200)
    {
        return $this->apiResponse($code, $message, $data);
    }

    public function apiResponseError($code = 400, $data = null, $message = null)
    {
        if (!$message) {
            $message = Response::$statusTexts[$code];
        }

        return $this->apiResponse($code, $message, $data);
    }
}
