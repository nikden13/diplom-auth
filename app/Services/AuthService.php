<?php

namespace App\Services;

use App\Models\Token;

class AuthService
{
    const SUPPORTED_ALGORITHMS = [
        'stribog' => ['256'],
    ];

    const PAYLOAD = [];

    public function getTokens(string $address): array
    {
        $refresh = $this->getToken($address, 'Refresh');
//        Token::query()->updateOrInsert([
//            'address' => $address,
//        ], [
//            'token' => $this->hash($refresh),
//        ]);
        return [
            'access_token' => $this->getToken($address),
            'refresh_token' => $refresh,
        ];
    }

    public function refresh(string $token): array|false
    {
        try {
            if ($this->validateRefresh($token) && $this->verify($token, true)) {
                return $this->getTokens($this->getAddress($token));
            }
        } catch (\Exception $e) {
        }

        return false;
    }

    public function getToken(string $address, string $type = 'Access'): string
    {
        if (!in_array($type, ['Access', 'Refresh'])) {
            throw new \Exception('Incorrect token type');
        }

        $header = $this->encode($this->header());
        $payload = [
            'address' => $address,
        ];
        $method = "generate{$type}Payload";
        $payload = $this->encode(array_merge($payload, Payload::$method(self::PAYLOAD)));
        $signature = $this->sign($header, $payload, $type === 'Refresh');

        return $header . '.' . $payload . '.' . $this->encode($signature);
    }

    public function getAddress(string $token): string
    {
        try {
            $data = explode('.', $token);
            $payload = $this->decode($data[1]);

            return $payload['address'];
        } catch (\Exception $e) {
            throw new \Exception('Error parse payload');
        }
    }

    protected function header($alg = 'stribog', int $index = 0): array
    {
        return [
            'alg' => $alg . self::SUPPORTED_ALGORITHMS[$alg][$index],
            'typ' => 'JWT',
        ];
    }

    public function twoFactorCode(): int
    {
        return rand(100000, 999999);
    }

    public function verify(string $token, $refresh = false): bool
    {
        $data = explode('.', $token);

        if (!isset($data[2])) {
            throw new \Exception('Error parse token');
        }

        $header = $this->decode($data[0]);
        if (!$this->checkHeader($header)) {
            return false;
        }
        $payload = $this->decode($data[1]);
        if (!$this->checkPayload($payload)) {
            return false;
        }

        $sign = $this->sign($data[0], $data[1], $refresh);

        return $sign === $this->decode($data[2], false);
    }

    protected function validateRefresh(string $token): bool
    {
        return Token::where([
            'address' => $this->getAddress($token),
            $this->hash($token)
        ])->exists();
    }

    protected function checkHeader(array $header): bool
    {
        if (!isset($header['typ']) || $header['typ'] !== 'JWT') {
            return false;
        }
        if (!isset($header['alg']) || $header['alg'] !== 'stribog256') {
            return false;
        }

        return true;
    }

    public function logout(string $token)
    {
        Token::where([
            'token' => $token,
        ])->delete();
    }

    protected function checkPayload(array $payload): bool
    {
        try {
            return $payload['iss'] === Payload::iss() && $payload['exp'] > time();
        } catch (\Exception $e) {
            return false;
        }
    }

    protected function sign(string $encodeHeader, string $encodePayload, $refresh = false): string
    {
        $key = $refresh ? env('JWT_KEY_REFRESH') : env('JWT_KEY_ACCESS');
        return hash_hmac('stribog', "$encodeHeader.$encodePayload", $key);
    }

    protected function hash(string $data): string
    {
        return hash('stribog256', $data);
    }

    protected function encode(array|string $data): string
    {
        if (is_array($data)) {
            $data = json_encode($data);
        }

        return str_replace('=', '', strtr(base64_encode($data), '+/', '-_'));
    }

    protected function decode(string $data, $toArray = true): array|string
    {
        $decodeData = base64_decode($data);

        if ($decodeData === false) {
            throw new \Exception('Error decode token data');
        }

        if ($toArray) {
            return json_decode($decodeData, true);
        }

        return $decodeData;
    }
}
