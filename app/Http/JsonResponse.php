<?php
namespace App\Http;

class JsonResponse
{
    /**
     * Create a JSON success response.
     *
     * @param mixed[] $data
     * @param string $message
     * @param int $status
     * @param string[] $headers
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public static function success(array $data = [], $message = 'Success', $status = 200, array $headers = [])
    {
        return response()->json([
            'success' => true,
            'message' => $message,
            'data' => $data
        ], $status, $headers);
    }

    /**
     * Create a JSON error response.
     *
     * @param mixed[] $data
     * @param string $message
     * @param int $status
     * @param string[] $headers
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public static function error(array $data = [], $message = 'Error', $status = 400, array $headers = [])
    {
        return response()->json([
            'success' => false,
            'message' => $message,
            'data' => $data
        ], $status, $headers);
    }
}
