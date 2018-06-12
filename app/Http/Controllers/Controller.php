<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;
use Laravel\Lumen\Http\Redirector;
use Laravel\Lumen\Routing\Controller as BaseController;

class Controller extends BaseController
{
    /**
     * @return Redirector
     */
    public function index()
    {
        return redirect(env('APP_REPO_URL'), Response::HTTP_MOVED_PERMANENTLY);
    }

    /**
     * @param int $code
     * @param mixed $data
     * @return JsonResponse
     */
    public static function response(int $code, $data = [])
    {
        $isSuccess = false;
        if (in_array($code, [Response::HTTP_OK, Response::HTTP_CREATED, Response::HTTP_ACCEPTED])) {
            $isSuccess = true;
        }
        $data = ['data' => $data, 'is_success' => $isSuccess];

        return response()->json($data, $code);
    }
}
