<?php

namespace App\Traits;

trait ApiResponseTrait
{

    public function success($message = "Success", $data = null)
    {
        return response()->json([
            'message' => $message,
            'data' => $data
        ], 200);
    }

    public function error($message = "Error", $code = 400, $errors = null)
    {
        return response()->json([
            'message' => $message,
            'errors' => $errors
        ], $code);
    }


    public function tokenResponse($user, $token, $message = "Success", $code = 200)
    {
        return response()->json([
            'message' => $message,
            'data' => [
                'access_token' => $token,
                'token_type' => 'Bearer',
                'user' => $user
            ]
        ], $code);
    }


    public function created($message = "Created successfully", $data = null)
    {
        return response()->json([
            'message' => $message,
            'data' => $data
        ], 201);
    }


    public function deleted($message = "Deleted successfully")
    {
        return response()->json([
            'message' => $message
        ], 200);
    }


    public function unauthorized($message = "Unauthorized")
    {
        return response()->json([
            'message' => $message
        ], 401);
    }

    public function forbidden($message = "Forbidden")
    {
        return response()->json([
            'message' => $message
        ], 403);
    }

    public function notFound($message = "Resource not found")
    {
        return response()->json([
            'message' => $message
        ], 404);
    }
}
