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
} 