<?php

namespace Modules\Common\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller as Controller;

class CommonController extends Controller
{
    /**
     * @SWG\Swagger(
     *     basePath="/api/v1",
     *     schemes={"http", "https"},
     *     host=L5_SWAGGER_CONST_HOST,
     *     @SWG\Info(
     *         version="1.0.0",
     *         title="PETBOOK API",
     *         description="API for pet app...",
     *         @SWG\Contact(
     *             email="hungcuongqn86@gmail.com"
     *         ),
     *     )
     * )
     */

    public function sendResponse($result, $message)
    {
        $response = [
            'status' => true,
            'code' => 200,
            'data' => $result,
            'message' => $message,
        ];
        return response()->json($response, 200);
    }

    public function sendError($error, $errorMessages = [], $code = 400)
    {
        $response = [
            'status' => false,
            'code' => $code,
            'message' => $error,
        ];
        if (!empty($errorMessages)) {
            $response['data'] = $errorMessages;
        }
        return response()->json($response, $code);
    }
}