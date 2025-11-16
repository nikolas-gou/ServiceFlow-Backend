<?php

namespace App\Controllers;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use App\Helpers\ResponseHelper;
use App\Services\SuggestedService;
use Psr\Log\LoggerInterface;

class SuggestedController
{
    private SuggestedService $suggestedService;
    private LoggerInterface $logger;

    public function __construct(
        SuggestedService $suggestedService,
        LoggerInterface $logger
    ) {
        $this->suggestedService = $suggestedService;
        $this->logger = $logger;
    }

    /**
     * Suggested Valued - Form Fields(Create/edit repair)
     */
    public function getSuggestedData(Request $request, Response $response): Response
    {
        try {
            $suggestedData = $this->suggestedService->getSuggestedData();

            if (!is_array($suggestedData)) {
                $this->logger->error('SuggestedService::getSuggestedData returned non-array result', [
                    'type' => gettype($suggestedData),
                ]);

                return ResponseHelper::serverError($response, 'Unexpected suggested data format');
            }
            return ResponseHelper::success($response, $suggestedData, 'Suggested data retrieved successfully');
        } catch (\Throwable $exception) {
            $this->logger->error('Failed to fetch suggested data', [
                'message' => $exception->getMessage(),
                'trace' => $exception->getTraceAsString(),
            ]);

            return ResponseHelper::serverError($response, 'Αποτυχία λήψης προτεινόμενων δεδομένων');
        }
    }
}