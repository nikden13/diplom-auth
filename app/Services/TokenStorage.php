<?php

namespace App\Services;

class TokenStorage
{
    protected ?string $token = null;

    public function setToken(string $token): void
    {
        $this->token = $token;
    }

    public function getToken(): ?string
    {
        return $this->token;
    }
}
