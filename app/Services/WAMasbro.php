<?php

namespace App\Services;

use Exception;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Http\Client\Response;
use Illuminate\Http\Client\RequestException;
use Throwable;

class WAMasbro
{
    private $http;
    public function __construct() {
        $this->http = Http::withUrlParameters([
            'endpoint' => config('services.wa-masbro.url'),
            // 'page' => 'docs',
            // 'version' => '9.x',
            // 'topic' => 'validation',
        ]);
    }

    public function restart () {
        return $this->http->get('{+endpoint}/restart?cred_id='.config('services.wa-masbro.key_id'))->throw(function (Response $response, RequestException $e) {
            // report($e);
            throw new Exception($e);
        });
    }
    public function logout () {
        return $this->http->get('{+endpoint}/logout?cred_id='.config('services.wa-masbro.key_id'))->throw(function (Response $response, RequestException $e) {
            // report($e);
            throw new Exception($e);
        });}
    public function checkStatus () {
        return $this->http->get('{+endpoint}/get-state?cred_id='.config('services.wa-masbro.key_id'))->throw(function (Response $response, RequestException $e) {
            // report($e);
            throw new Exception($e);
        });
    }
    public function getQrCode () {
        return $this->http->get('{+endpoint}/get-qrcode?cred_id='.config('services.wa-masbro.key_id'))->throw(function (Response $response, RequestException $e) {
            // report($e);
            throw new Exception($e);
        });
    }
    public function sendTextMessage ($to, $message) {
        // Log::info(config('services.wa-masbro.url'));
        if (empty(config('services.wa-masbro.url')) || is_null(config('services.wa-masbro.url')) || empty(config('services.wa-masbro.key_id')) || is_null(config('services.wa-masbro.key_id'))) {
            return ;
        }
        // Log::info('try send message to : ' . $to);
        // Log::info('try send message: ' . $message);
        $response = $this->http->post('{+endpoint}/send-text-message?cred_id='.config('services.wa-masbro.key_id'), [
            'phone_number' => $to,
            'message' => $message,
        ])->throw(function (Response $response, RequestException $e) {
            // report($e);
            throw new Exception($e);
        });
        // Log::info(['response' => $response->body()]);
        if ($response->successful() && $response->body() === '"success"') {
            return true;
        } else {
            return false;
        }
    }
}
