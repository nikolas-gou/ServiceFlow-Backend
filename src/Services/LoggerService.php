<?php

namespace App\Services;

use Psr\Log\LoggerInterface;
use Psr\Log\LoggerTrait;

class LoggerService implements LoggerInterface
{
    use LoggerTrait;

    private $logFile;

    public function __construct(string $logFile = null)
    {
        $this->logFile = $logFile ?? __DIR__ . '/../../logs/app.log';
        
        // Create logs directory if it doesn't exist
        $logDir = dirname($this->logFile);
        if (!is_dir($logDir)) {
            mkdir($logDir, 0755, true);
        }
    }

    public function log($level, $message, array $context = []): void
    {
        $timestamp = date('Y-m-d H:i:s');
        $logEntry = "[{$timestamp}] [{$level}] {$message}";
        
        if (!empty($context)) {
            $logEntry .= ' ' . json_encode($context, JSON_UNESCAPED_UNICODE);
        }
        
        $logEntry .= PHP_EOL;
        
        file_put_contents($this->logFile, $logEntry, FILE_APPEND | LOCK_EX);
    }

    public function logRequest($method, $uri, $ip, $userAgent = null): void
    {
        $this->info('HTTP Request', [
            'method' => $method,
            'uri' => $uri,
            'ip' => $ip,
            'user_agent' => $userAgent
        ]);
    }

    public function logError(\Throwable $exception, array $context = []): void
    {
        $this->error('Exception occurred', [
            'message' => $exception->getMessage(),
            'file' => $exception->getFile(),
            'line' => $exception->getLine(),
            'trace' => $exception->getTraceAsString(),
            'context' => $context
        ]);
    }
} 