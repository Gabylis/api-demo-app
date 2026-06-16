<?php

namespace App\Http\Controllers\Api;

use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use App\Http\Resources\ProductResource;
use App\Http\Requests\Api\StoreProductRequest;
use App\Http\Requests\Api\UpdateProductRequest;
use Gabylis\ApiFoundation\Controllers\ApiBaseController;
use OpenApi\Attributes as OA;

#[OA\Tag(name: 'Products', description: 'Product catalog management')]
class ProductApiController extends ApiBaseController
{
    // ── LIST ─────────────────────────────────────────────────────────────────

    #[OA\Get(
        path: '/products',
        operationId: 'get-products-index',
        summary: 'List all products with optional filters',
        tags: ['Products'],
        parameters: [
            new OA\Parameter(name: 'per_page', in: 'query', required: false, schema: new OA\Schema(type: 'integer', default: 15)),
            new OA\Parameter(name: 'category_id', in: 'query', required: false, schema: new OA\Schema(type: 'integer')),
            new OA\Parameter(name: 'active', in: 'query', required: false, schema: new OA\Schema(type: 'boolean')),
            new OA\Parameter(name: 'search', in: 'query', required: false, description: 'Search by name or SKU', schema: new OA\Schema(type: 'string')),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Products retrieved successfully',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'success', type: 'boolean', example: true),
                        new OA\Property(property: 'status', type: 'string', example: 'success'),
                        new OA\Property(property: 'message', type: 'string', example: 'Products retrieved successfully'),
                        new OA\Property(property: 'data', type: 'array', items: new OA\Items(ref: '#/components/schemas/Product')),
                        new OA\Property(property: 'meta', ref: '#/components/schemas/PaginationMeta'),
                    ]
                )
            ),
        ]
    )]
    public function index(Request $request): JsonResponse
    {
        $products = Product::with('category')
            ->when($request->category_id, fn ($q) => $q->where('category_id', $request->category_id))
            ->when($request->has('active'), fn ($q) => $q->where('active', filter_var($request->active, FILTER_VALIDATE_BOOLEAN)))
            ->when($request->search, fn ($q) => $q->where(function ($q) use ($request) {
                $q->where('name', 'like', "%{$request->search}%")
                  ->orWhere('sku', 'like', "%{$request->search}%");
            }))
            ->orderBy('name')
            ->paginate($request->input('per_page', 15));

        return $this->sendPaginatedResponse($products, 'Products retrieved successfully', ProductResource::class);
    }

    // ── SHOW ──────────────────────────────────────────────────────────────────

    #[OA\Get(
        path: '/products/{id}',
        operationId: 'get-products-show',
        summary: 'Get a single product',
        tags: ['Products'],
        parameters: [
            new OA\Parameter(name: 'id', in: 'path', required: true, schema: new OA\Schema(type: 'integer')),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Product retrieved successfully',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'success', type: 'boolean', example: true),
                        new OA\Property(property: 'data', ref: '#/components/schemas/Product'),
                    ]
                )
            ),
            new OA\Response(response: 404, description: 'Product not found'),
        ]
    )]
    public function show(int $id): JsonResponse
    {
        $product = Product::with('category')->find($id);

        if (!$product) {
            return $this->sendError('Product not found', [], 404);
        }

        return $this->sendResponse(new ProductResource($product), 'Product retrieved successfully');
    }

    // ── STORE ─────────────────────────────────────────────────────────────────

    #[OA\Post(
        path: '/products',
        operationId: 'post-products-store',
        summary: 'Create a new product',
        tags: ['Products'],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ['category_id', 'name', 'sku', 'price'],
                properties: [
                    new OA\Property(property: 'category_id', type: 'integer', example: 1),
                    new OA\Property(property: 'name', type: 'string', example: 'Wireless Headphones'),
                    new OA\Property(property: 'sku', type: 'string', example: 'WH-XM5'),
                    new OA\Property(property: 'description', type: 'string', nullable: true),
                    new OA\Property(property: 'price', type: 'number', format: 'float', example: 349.99),
                    new OA\Property(property: 'stock', type: 'integer', example: 50),
                    new OA\Property(property: 'active', type: 'boolean', example: true),
                ]
            )
        ),
        responses: [
            new OA\Response(response: 201, description: 'Product created successfully'),
            new OA\Response(response: 422, description: 'Validation error'),
        ]
    )]
    public function store(StoreProductRequest $request): JsonResponse
    {
        $product = Product::create($request->validated());
        $product->load('category');

        return $this->sendResponse(new ProductResource($product), 'Product created successfully', 201);
    }

    // ── UPDATE ────────────────────────────────────────────────────────────────

    #[OA\Put(
        path: '/products/{id}',
        operationId: 'put-products-update',
        summary: 'Update a product',
        tags: ['Products'],
        parameters: [
            new OA\Parameter(name: 'id', in: 'path', required: true, schema: new OA\Schema(type: 'integer')),
        ],
        requestBody: new OA\RequestBody(
            content: new OA\JsonContent(
                properties: [
                    new OA\Property(property: 'category_id', type: 'integer'),
                    new OA\Property(property: 'name', type: 'string'),
                    new OA\Property(property: 'sku', type: 'string'),
                    new OA\Property(property: 'description', type: 'string', nullable: true),
                    new OA\Property(property: 'price', type: 'number', format: 'float'),
                    new OA\Property(property: 'stock', type: 'integer'),
                    new OA\Property(property: 'active', type: 'boolean'),
                ]
            )
        ),
        responses: [
            new OA\Response(response: 200, description: 'Product updated successfully'),
            new OA\Response(response: 404, description: 'Product not found'),
            new OA\Response(response: 422, description: 'Validation error'),
        ]
    )]
    public function update(UpdateProductRequest $request, int $id): JsonResponse
    {
        $product = Product::find($id);

        if (!$product) {
            return $this->sendError('Product not found', [], 404);
        }

        $product->update($request->validated());
        $product->load('category');

        return $this->sendResponse(new ProductResource($product), 'Product updated successfully');
    }

    // ── DESTROY ───────────────────────────────────────────────────────────────

    #[OA\Delete(
        path: '/products/{id}',
        operationId: 'delete-products-destroy',
        summary: 'Soft delete a product',
        tags: ['Products'],
        parameters: [
            new OA\Parameter(name: 'id', in: 'path', required: true, schema: new OA\Schema(type: 'integer')),
        ],
        responses: [
            new OA\Response(response: 200, description: 'Product deleted successfully'),
            new OA\Response(response: 404, description: 'Product not found'),
        ]
    )]
    public function destroy(int $id): JsonResponse
    {
        $product = Product::find($id);

        if (!$product) {
            return $this->sendError('Product not found', [], 404);
        }

        $product->delete();

        return $this->sendSuccess('Product deleted successfully');
    }
}
