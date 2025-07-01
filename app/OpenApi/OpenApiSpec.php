<?php

namespace App\OpenApi;

use OpenApi\Annotations as OA;

/**
 * @OA\SecurityScheme(
 *     securityScheme="sanctum",
 *     type="http",
 *     scheme="bearer",
 *     bearerFormat="JWT",
 *     description="Use 'Bearer {token}' to authorize"
 * )
 *
 * @OA\Info(
 *     version="1.0.0",
 *     title="Task API",
 *     description="API for managing tasks",
 *     @OA\Contact(
 *         email="support@example.com",
 *         name="Support"
 *     )
 * )
 *
 * @OA\Server(
 *     url=L5_SWAGGER_CONST_HOST,
 *     description="Main API server"
 * )
 */
class OpenApiSpec {}
