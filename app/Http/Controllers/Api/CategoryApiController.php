<?php

namespace App\Http\Controllers\Api;

use App\Models\Category;
use Illuminate\Http\JsonResponse;
use App\Http\Resources\CategoryResource;
use App\Http\Requests\Api\StoreCategoryRequest;
use Gabylis\ApiFoundation\Controllers\ApiBaseController;
use OpenApi\Attributes as OA;

#[OA\Tag(name: 'Categories', description: 'Product category management')]
class CategoryApiController extends ApiBaseController
{
    // ── LIST ─────────────────────────────────────────────────────────────────

    #[OA\Get(
        path: '/categories',
        operationId: 'get-categories-index',
        summary: 'List all categories',
        tags: ['Categories'],
        parameters: [
            new OA\Parameter(name: 'per_page', in: 'query', required: false, schema: new OA\Schema(type: 'integer', default: 15)),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Categories retrieved successfully',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'success', type: 'boolean', example: true),
                        new OA\Property(property: 'status', type: 'string', example: 'success'),
                        new OA\Property(property: 'message', type: 'string', example: 'Categories retrieved successfully'),
                        new OA\Property(property: 'data', type: 'array', items: new OA\Items(ref: '#/components/schemas/Category')),
                        new OA\Property(property: 'meta', ref: '#/components/schemas/PaginationMeta'),
                    ]
                )
            ),
        ]
    )]
    public function index(): JsonResponse
    {
        $categories = Category::withCount('products')
            ->orderBy('name')
            ->paginate(request('per_page', 15));

        return $this->sendPaginatedResponse($categories, 'Categories retrieved successfully', CategoryResource::class);
    }

    // ── SHOW ──────────────────────────────────────────────────────────────────

    #[OA\Get(
        path: '/categories/{id}',
        operationId: 'get-categories-show',
        summary: 'Get a single category with its products',
        tags: ['Categories'],
        parameters: [
            new OA\Parameter(name: 'id', in: 'path', required: true, schema: new OA\Schema(type: 'integer')),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Category retrieved successfully',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'success', type: 'boolean', example: true),
                        new OA\Property(property: 'status', type: 'string', example: 'success'),
                        new OA\Property(property: 'message', type: 'string', example: 'Category retrieved successfully'),
                        new OA\Property(property: 'data', ref: '#/components/schemas/Category'),
                    ]
                )
            ),
            new OA\Response(response: 404, description: 'Category not found'),
        ]
    )]
    public function show(int $id): JsonResponse
    {
        $category = Category::withCount('products')->find($id);

        if (!$category) {
            return $this->sendError('Category not found', [], 404);
        }

        return $this->sendResponse(new CategoryResource($category), 'Category retrieved successfully');
    }

    // ── STORE ─────────────────────────────────────────────────────────────────

    #[OA\Post(
        path: '/categories',
        operationId: 'post-categories-store',
        summary: 'Create a new category',
        tags: ['Categories'],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ['name'],
                properties: [
                    new OA\Property(property: 'name', type: 'string', example: 'Electronics'),
                    new OA\Property(property: 'description', type: 'string', nullable: true, example: 'All electronic devices'),
                ]
            )
        ),
        responses: [
            new OA\Response(
                response: 201,
                description: 'Category created successfully',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'success', type: 'boolean', example: true),
                        new OA\Property(property: 'data', ref: '#/components/schemas/Category'),
                    ]
                )
            ),
            new OA\Response(response: 422, description: 'Validation error'),
        ]
    )]
    public function store(StoreCategoryRequest $request): JsonResponse
    {
        $category = Category::create($request->validated() + ['slug' => $request->slug]);

        return $this->sendResponse(new CategoryResource($category), 'Category created successfully', 201);
    }

    // ── UPDATE ────────────────────────────────────────────────────────────────

    #[OA\Put(
        path: '/categories/{id}',
        operationId: 'put-categories-update',
        summary: 'Update a category',
        tags: ['Categories'],
        parameters: [
            new OA\Parameter(name: 'id', in: 'path', required: true, schema: new OA\Schema(type: 'integer')),
        ],
        requestBody: new OA\RequestBody(
            content: new OA\JsonContent(
                properties: [
                    new OA\Property(property: 'name', type: 'string', example: 'Updated Electronics'),
                    new OA\Property(property: 'description', type: 'string', nullable: true),
                ]
            )
        ),
        responses: [
            new OA\Response(response: 200, description: 'Category updated successfully'),
            new OA\Response(response: 404, description: 'Category not found'),
            new OA\Response(response: 422, description: 'Validation error'),
        ]
    )]
    public function update(StoreCategoryRequest $request, int $id): JsonResponse
    {
        $category = Category::find($id);

        if (!$category) {
            return $this->sendError('Category not found', [], 404);
        }

        $category->update($request->validated());

        return $this->sendResponse(new CategoryResource($category->fresh()), 'Category updated successfully');
    }

    // ── DESTROY ───────────────────────────────────────────────────────────────

    #[OA\Delete(
        path: '/categories/{id}',
        operationId: 'delete-categories-destroy',
        summary: 'Delete a category',
        tags: ['Categories'],
        parameters: [
            new OA\Parameter(name: 'id', in: 'path', required: true, schema: new OA\Schema(type: 'integer')),
        ],
        responses: [
            new OA\Response(response: 200, description: 'Category deleted successfully'),
            new OA\Response(response: 404, description: 'Category not found'),
        ]
    )]
    public function destroy(int $id): JsonResponse
    {
        $category = Category::find($id);

        if (!$category) {
            return $this->sendError('Category not found', [], 404);
        }

        $category->delete();

        return $this->sendSuccess('Category deleted successfully');
    }
}
