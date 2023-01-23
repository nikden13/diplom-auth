<?php

namespace App\Http\Controllers;

use App\Helpers\CacheKeysHelper;
use App\Helpers\RandomHelper;
use App\Http\Requests\LoginRequest;
use App\Jobs\SendEmail;
use App\Services\AuthService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;

class AuthController extends ApiController
{
    private string $node;

    public function __construct(
        private AuthService $authService
    ) {
        $this->node = env('NODE');
    }

    public function generateCode(LoginRequest $request): \Illuminate\Http\JsonResponse
    {
        $publicKey = $request->get('publicKey');
        $address = $request->get('address');
        $signature = $request->get('signature');
        $email = $request->get('email');
        $message = $request->get('message');

        if ($this->getAddressByEmail($email) !== $address) {
            return $this->dispatch('Неверный адрес или email');
        }

        $randomString = Cache::get(CacheKeysHelper::messageForSign($address));
        if ($randomString !== $message || !$this->verifyMessage($publicKey, $signature, $message)) {
            $this->dispatch('Неверная подпись');
        }

        $code = $this->authService->twoFactorCode();
        Cache::put(CacheKeysHelper::twoFactorCode($address), $code, 300);
        Cache::put(CacheKeysHelper::addressEmail($address), $email, 300);
        SendEmail::dispatch($code, $email)->onQueue('emails');

        return $this->dispatch(true);
    }

    protected function getAddressByEmail(string $email)
    {
        return json_decode(Http::get($this->node . '/account/' . $email)->body())->Data;
    }

    protected function verifyMessage(string $publicKey, string $signature, string $message): bool
    {
        return json_decode(Http::get($this->node . '/messages/verify', [
            'Key' => $publicKey,
            'Sign' => $signature,
            'Message' => $message,
        ])->body())->Data;
    }

    public function getTokens(Request $request): \Illuminate\Http\JsonResponse
    {
        $code = $request->get('code');
        $address = $request->get('address');
        if ($code !== Cache::get(CacheKeysHelper::twoFactorCode($address))) {
            return $this->dispatch(false, 400);
        }

        Cache::forget(CacheKeysHelper::twoFactorCode($address));
        return $this->dispatch($this->authService->getTokens($address));
    }

    public function refresh(Request $request): \Illuminate\Http\JsonResponse
    {
        $token = $request->get('refresh_token');
        if (!$token) {
            return $this->dispatch(false, 401);
        }

        return $this->dispatch($this->authService->refresh($token));
    }

    public function check(Request $request): \Illuminate\Http\JsonResponse
    {
        $token = $request->get('token');
        if (!$token) {
            return $this->dispatch(false, 401);
        }

        return $this->dispatch($this->authService->verify($token));
    }

    public function getMessageForSign(Request $request): \Illuminate\Http\JsonResponse
    {
        $address = $request->get('address');
        if (!$address) {
            return $this->dispatch(false);
        }

        $randomString = RandomHelper::randomString();
        Cache::put(CacheKeysHelper::messageForSign($address), $randomString, 60);

        return $this->dispatch($randomString);
    }

    public function logout(Request $request): \Illuminate\Http\JsonResponse
    {
        $token = app()->make('token')->getToken();
        if (!$token) {
            return $this->dispatch(false, 400);
        }
        $this->authService->logout($token);

        return $this->dispatch(true);
    }
}