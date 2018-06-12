<?php

namespace App\Http\Controllers;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\Exception\RequestException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;

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
            if (!is_array($data)) {
                return self::response(Response::HTTP_FAILED_DEPENDENCY, ['errors' => ['Invalid content']]);
            }
            return self::response(Response::HTTP_OK, $data);
        } catch (ClientException $e) {
            return self::response(Response::HTTP_NOT_FOUND, ['errors' => ['Not found']]);
        } catch (RequestException $e) {
            return self::response(Response::HTTP_FAILED_DEPENDENCY, ['errors' => ['Target server error']]);
        }
    }
}