<?php

namespace App\Helpers;

use Psr\Http\Message\ResponseInterface;

class ResponseHelper
{
    public static function success(ResponseInterface $response, $data = null, string $message = 'Success', int $statusCode = 200, $pagination = null): ResponseInterface
    {
        $payload = [
            'status' => 'success',
            'message' => $message,
            'data' => $data
        ];

        // Add pagination metadata if provided
        if ($pagination !== null) {
            $payload['pagination'] = $pagination;
        }

        $response->getBody()->write(json_encode($payload, JSON_UNESCAPED_UNICODE));
        return $response
            ->withHeader('Content-Type', 'application/json')
            ->withStatus($statusCode);
    }

    public static function error(ResponseInterface $response, string $message = 'Error', int $statusCode = 400, $errors = null): ResponseInterface
    {
        $payload = [
            'status' => 'error',
            'message' => $message
        ];

        if ($errors !== null) {
            $payload['errors'] = $errors;
        }

        $response->getBody()->write(json_encode($payload, JSON_UNESCAPED_UNICODE));
        return $response
            ->withHeader('Content-Type', 'application/json')
            ->withStatus($statusCode);
    }

    public static function notFound(ResponseInterface $response, string $message = 'Resource not found'): ResponseInterface
    {
        return self::error($response, $message, 404);
    }

    public static function validationError(ResponseInterface $response, array $errors): ResponseInterface
    {
        return self::error($response, 'Validation failed', 422, $errors);
    }

    public static function serverError(ResponseInterface $response, string $message = 'Internal server error'): ResponseInterface
    {
        return self::error($response, $message, 500);
    }

    public static function badRequest(ResponseInterface $response, string $message = 'Bad request'): ResponseInterface
    {
        return self::error($response, $message, 400);
    }

    public static function binary(ResponseInterface $response, string $data, string $contentType, int $statusCode = 200, array $headers = []): ResponseInterface
    {
        if ($headers) {
            foreach ($headers as $key => $value) {
                $response = $response->withHeader($key, $value);
            }
        }
        
        // Προσθέτουμε Content-Type μόνο αν δεν έχει ήδη δοθεί
        if (!$headers || !isset($headers['Content-Type'])) {
            $response = $response->withHeader('Content-Type', $contentType);
        }

        $response->getBody()->write($data);
        return $response->withStatus($statusCode);
    }
}
