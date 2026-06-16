<?php

namespace App\Http\Requests\Api;

use Gabylis\ApiFoundation\Requests\ApiFormRequest;

class UpdateProductRequest extends ApiFormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $productId = $this->route('product');

        return [
            'category_id' => 'sometimes|integer|exists:categories,id',
            'name'        => 'sometimes|string|max:255',
            'sku'         => "sometimes|string|max:100|unique:products,sku,{$productId}",
            'description' => 'nullable|string|max:2000',
            'price'       => 'sometimes|numeric|min:0',
            'stock'       => 'sometimes|integer|min:0',
            'active'      => 'sometimes|boolean',
        ];
    }
}
