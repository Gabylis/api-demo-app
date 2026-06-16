<?php

namespace App\Http\Controllers\Api;

use OpenApi\Attributes as OA;

/**
 * Shared OpenAPI schemas used across multiple controllers.
 * This class exists only to host schema definitions — it has no methods.
 */
#[OA\Schema(
    schema: 'PaginationMeta',
    description: 'Pagination metadata included in paginated responses',
    properties: [
        new OA\Property(property: 'per_page', type: 'integer', example: 15),
        new OA\Property(property: 'current_page', type: 'integer', example: 1),
        new OA\Property(property: 'from', type: 'integer', example: 1),
        new OA\Property(property: 'to', type: 'integer', example: 15),
        new OA\Property(property: 'last_page', type: 'integer', example: 4),
        new OA\Property(property: 'total', type: 'integer', example: 60),
        new OA\Property(property: 'next_page_url', type: 'string', nullable: true, example: 'http://localhost/api/products?page=2'),
        new OA\Property(property: 'previous_page_url', type: 'string', nullable: true, example: null),
        new OA\Property(property: 'path', type: 'string', example: 'http://localhost/api/products'),
    ]
)]
class SharedSchemas {}
