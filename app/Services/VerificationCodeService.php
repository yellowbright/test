<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;

class VerificationCodeService
{
    private const CODE_TTL_SECONDS = 600;
    private const THROTTLE_SECONDS = 60;

    public function generate(string $email, string $purpose): string
    {
        $throttleKey = $this->throttleKey($email, $purpose);
        if (Cache::has($throttleKey)) {
            throw new \RuntimeException('验证码发送过于频繁，请稍后再试');
        }

        $code = (string) random_int(100000, 999999);
        Cache::put($this->codeKey($email, $purpose), $code, self::CODE_TTL_SECONDS);
        Cache::put($throttleKey, true, self::THROTTLE_SECONDS);

        return $code;
    }

    public function verify(string $email, string $purpose, string $code): bool
    {
        $cachedCode = Cache::get($this->codeKey($email, $purpose));

        return is_string($cachedCode) && hash_equals($cachedCode, $code);
    }

    public function consume(string $email, string $purpose): void
    {
        Cache::forget($this->codeKey($email, $purpose));
    }

    private function codeKey(string $email, string $purpose): string
    {
        return 'verify_code:'.Str::lower($purpose).':'.Str::lower($email);
    }

    private function throttleKey(string $email, string $purpose): string
    {
        return 'verify_code_throttle:'.Str::lower($purpose).':'.Str::lower($email);
    }
}
