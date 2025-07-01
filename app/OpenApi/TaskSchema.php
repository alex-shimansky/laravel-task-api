<?php

namespace App\OpenApi;

use OpenApi\Annotations as OA;

/**
 * @OA\Schema(
 *     schema="Task",
 *     type="object",
 *     title="Task",
 *     required={"id", "title", "status", "priority"},
 *     @OA\Property(property="id", type="integer", example=1),
 *     @OA\Property(property="title", type="string", example="Buy milk"),
 *     @OA\Property(property="description", type="string", example="2 liters"),
 *     @OA\Property(property="status", type="string", example="todo"),
 *     @OA\Property(property="priority", type="integer", example=3),
 *     @OA\Property(property="created_at", type="string", format="date-time"),
 *     @OA\Property(property="completed_at", type="string", format="date-time", nullable=true),
 * )
 */
class TaskSchema {}
