<?php

namespace App\Middleware;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use App\Helpers\ResponseHelper;

class ValidationMiddleware implements MiddlewareInterface
{
    private $rules;

    public function __construct(array $rules = [])
    {
        $this->rules = $rules;
    }

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        if (empty($this->rules)) {
            return $handler->handle($request);
        }

        $data = json_decode($request->getBody()->getContents(), true);
        $errors = $this->validate($data);

        if (!empty($errors)) {
            $response = new \Slim\Psr7\Response();
            return ResponseHelper::validationError($response, $errors);
        }

        // Restore body for next middleware/controller
        $request = $request->withParsedBody($data);
        return $handler->handle($request);
    }

    private function validate(array $data): array
    {
        $errors = [];

        foreach ($this->rules as $field => $rule) {
            if (strpos($rule, 'required') !== false && (!isset($data[$field]) || empty($data[$field]))) {
                $errors[$field] = "The {$field} field is required.";
                continue;
            }

            if (isset($data[$field])) {
                $value = $data[$field];

                if (strpos($rule, 'email') !== false && !filter_var($value, FILTER_VALIDATE_EMAIL)) {
                    $errors[$field] = "The {$field} must be a valid email address.";
                }

                if (strpos($rule, 'min:') !== false) {
                    preg_match('/min:(\d+)/', $rule, $matches);
                    $min = (int) $matches[1];
                    if (strlen($value) < $min) {
                        $errors[$field] = "The {$field} must be at least {$min} characters.";
                    }
                }
            }
        }

        return $errors;
    }
} 