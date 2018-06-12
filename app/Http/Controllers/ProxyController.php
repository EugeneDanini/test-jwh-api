<?php

namespace App\Http\Controllers;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\Exception\RequestException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Log;

class ProxyController extends Controller
{
    const JSON_PLACEHOLDER_URL = 'https://jsonplaceholder.typicode.com/posts/';

    /**
     * @param int $id
     * @return JsonResponse
     */
    public function jsonPlaceholder(int $id)
    {
        $client = new Client(['verify' => false]);
        try {
            $result = $client->request('GET', self::JSON_PLACEHOLDER_URL . (int) $id);
            $data = json_decode($result->getBody(), true);
            Log::info('proxy.result', ['content' => $result->getBody()]);
            if (!is_array($data)) {
                Log::warning('proxy.decode.error', ['content' => $data]);
                return self::response(Response::HTTP_FAILED_DEPENDENCY, ['errors' => ['Invalid content']]);
            }
            return self::response(Response::HTTP_OK, $data);
        } catch (ClientException $e) {
            return self::response(Response::HTTP_NOT_FOUND, ['errors' => ['Not found']]);
        } catch (RequestException $e) {
            Log::warning('proxy.request.error', ['error' => $e->getMessage()]);
            return self::response(Response::HTTP_FAILED_DEPENDENCY, ['errors' => ['Target server error']]);
        }
    }
}