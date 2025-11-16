<?php

namespace App\Helpers;

class ServiceHelper
{
    // A function to handle if any query has error/exception
    public static function safeField(callable $fn, string $errorMsg) {
        try {
            return $fn();
        } catch (\Throwable $e) {
            return [
                'error' => $errorMsg,
                'details' => $e->getMessage()
            ];
        }
    }

    public static function formatSuggestedList(callable $fetcher, string $errorMessage): array
    {
        $result = ServiceHelper::safeField($fetcher, $errorMessage);

        if (is_array($result) && array_key_exists('error', $result)) {
            return [
                'data' => [],
                'error' => $result['error'],
                'details' => $result['details'] ?? null,
            ];
        }

        if (!is_array($result)) {
            return [
                'data' => [],
                'error' => $errorMessage,
                'details' => 'Μη έγκυρος τύπος δεδομένων',
            ];
        }

        return [
            'data' => $result,
            'error' => null,
            'details' => null,
        ];
    }
} 