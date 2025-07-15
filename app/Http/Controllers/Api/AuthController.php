<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;

use App\Http\Requests\Auth\LoginRequest;
use App\Services\AuthService;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use OpenApi\Annotations as OA;

use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

/**
 * @OA\Tag(
 *     name="Authentication",
 *     description="Endpoints for obtaining and revoking Sanctum tokens"
 * )
 */
class AuthController extends Controller
{
    public function __construct(
        private readonly AuthService $service
    ) {
    }

    /**
     * @OA\Post(
     *     path="/api/login",
     *     summary="Authenticate user and return Sanctum token",
     *     tags={"Authentication"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"email","password"},
     *             @OA\Property(property="email", type="string", format="email", example="test@example.com"),
     *             @OA\Property(property="password", type="string", format="password", example="123")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Authenticated",
     *         @OA\JsonContent(
     *             @OA\Property(property="token", type="string", example="1|abc123..."),
     *             @OA\Property(property="user", ref="#/components/schemas/User")
     *         )
     *     ),
     *     @OA\Response(response=401, description="Invalid credentials")
     * )
     */
    public function login(LoginRequest $request): JsonResponse
    {
        $result = $this->service->attemptLogin(
            $request->string('email')->toString(),
            $request->string('password')->toString(),
        );

        return response()->json($result);
    }

    /**
     * @OA\Post(
     *     path="/api/logout",
     *     summary="Revoke current access token",
     *     tags={"Authentication"},
     *     security={{"sanctum":{}}},
     *     @OA\Response(response=200, description="Logged out",
     *         @OA\JsonContent(@OA\Property(property="message", type="string", example="Logged out"))
     *     ),
     *     @OA\Response(response=401, description="Unauthenticated")
     * )
     */
    public function logout(): JsonResponse
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();
        $this->service->logout($user);

        return response()->json(['message' => 'Logged out']);
    }
}
