<?php

namespace App\Support;

use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use JsonSerializable;

class ApiResponse
{
    public static function success(string $message, mixed $data = null, int $status = 200): JsonResponse
    {
        return response()->json([
            'success' => true,
            'message' => $message,
            'data' => self::transform($data),
        ], $status);
    }

    public static function error(string $message, mixed $data = null, int $status = 400): JsonResponse
    {
        return response()->json([
            'success' => false,
            'message' => $message,
            'data' => self::transform($data),
        ], $status);
    }

    private static function transform(mixed $data): mixed
    {
        if ($data instanceof JsonResource) {
            return $data->resolve(app(Request::class));
        }

        if ($data instanceof Arrayable) {
            return $data->toArray();
        }

        if ($data instanceof JsonSerializable) {
            return $data->jsonSerialize();
        }

        if (is_array($data)) {
            return array_map(static fn (mixed $item): mixed => self::transform($item), $data);
        }

        return $data;
    }
}
