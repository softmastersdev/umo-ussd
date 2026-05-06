<?php

namespace App\Ussd\Services;

use Illuminate\Support\Facades\Cache;

class PinService
{
    private const MAX_ATTEMPTS = 3;
    private const LOCK_HOURS   = 24;

    public function isLocked(string $phone): bool
    {
        return Cache::has($this->lockKey($phone));
    }

    public function attempts(string $phone): int
    {
        return (int) Cache::get($this->attemptsKey($phone), 0);
    }

    public function recordFailure(string $phone): void
    {
        $attempts = $this->attempts($phone) + 1;

        Cache::put($this->attemptsKey($phone), $attempts, now()->addHours(self::LOCK_HOURS));

        if ($attempts >= self::MAX_ATTEMPTS) {
            Cache::put($this->lockKey($phone), true, now()->addHours(self::LOCK_HOURS));
        }
    }

    public function remaining(string $phone): int
    {
        return max(0, self::MAX_ATTEMPTS - $this->attempts($phone));
    }

    public function reset(string $phone): void
    {
        Cache::forget($this->attemptsKey($phone));
        Cache::forget($this->lockKey($phone));
    }

    private function attemptsKey(string $phone): string
    {
        return "umopay:pin_attempts:{$phone}";
    }

    private function lockKey(string $phone): string
    {
        return "umopay:pin_locked:{$phone}";
    }
}
