<?php
// src/Middleware/CorsMiddleware.php
namespace App\Middleware;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

class CorsMiddleware implements MiddlewareInterface
{
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $response = $handler->handle($request);
        
        $settings = require __DIR__ . '/../../config/settings.php';
        $corsConfig = $settings['cors'];
        
        $origin = $request->getHeaderLine('Origin');
        
        if (in_array($origin, $corsConfig['allowed_origins'])) {
            $response = $response->withHeader('Access-Control-Allow-Origin', $origin);
        }
        
        $response = $response
            ->withHeader('Access-Control-Allow-Headers', implode(', ', $corsConfig['allowed_headers']))
            ->withHeader('Access-Control-Allow-Methods', implode(', ', $corsConfig['allowed_methods']));
            
        if ($corsConfig['allow_credentials']) {
            $response = $response->withHeader('Access-Control-Allow-Credentials', 'true');
        }
        
        return $response;
    }
}
