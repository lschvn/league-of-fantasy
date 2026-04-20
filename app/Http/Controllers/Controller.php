<?php

namespace App\Http\Controllers;

use App\Support\ApiResponse;
use Illuminate\Http\JsonResponse;

abstract class Controller
{
    protected function successResponse(string $message, mixed $data = null, int $status = 200): JsonResponse
    {
        return ApiResponse::success($message, $data, $status);
    }

    protected function errorResponse(string $message, mixed $data = null, int $status = 400): JsonResponse
    {
        return ApiResponse::error($message, $data, $status);
    }

    protected function forbiddenResponse(string $message = 'forbidden.'): JsonResponse
    {
        return $this->errorResponse($message, null, 403);
    }

    protected function notFoundResponse(string $message = 'resource not found.'): JsonResponse
    {
        return $this->errorResponse($message, null, 404);
    }

    protected function unprocessableResponse(string $message, mixed $data = null): JsonResponse
    {
        return $this->errorResponse($message, $data, 422);
    }
}
