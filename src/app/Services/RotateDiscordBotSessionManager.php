<?php

namespace App\Services;

class RotateDiscordBotSessionManager
{
    protected static array $sessions = [];

    public static function start(string $userId, string $sessionType = 'pirep'): void
    {
        self::$sessions[$userId] = [
            'step' => 'origin',
            'data' => [],
            'session_type' => $sessionType
        ];
    }

    public static function exists(string $userId): bool
    {
        return isset(self::$sessions[$userId]);
    }

    public static function getStep(string $userId): ?string
    {
        return self::$sessions[$userId]['step'] ?? null;
    }

    public static function getData(string $userId): array
    {
        return self::$sessions[$userId]['data'] ?? [];
    }

    public static function getSessionType(string $userId): ?string
    {
        return self::$sessions[$userId]['session_type'] ?? null;
    }

    public static function update(string $userId, string $key, string $value, string $nextStep): void
    {
        self::$sessions[$userId]['data'][$key] = $value;
        self::$sessions[$userId]['step'] = $nextStep;
    }

    public static function complete(string $userId): array
    {
        $data = self::$sessions[$userId]['data'];
        unset(self::$sessions[$userId]);
        return $data;
    }

    public static function cancel(string $userId): void
    {
        unset(self::$sessions[$userId]);
    }
}