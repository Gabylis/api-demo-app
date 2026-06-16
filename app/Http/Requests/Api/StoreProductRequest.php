<?php

namespace App\Http\Requests\Api;

use Gabylis\ApiFoundation\Requests\ApiFormRequest;

class StoreProductRequest extends ApiFormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'category_id' => 'required|integer|exists:categories,id',
            'name'        => 'required|string|max:255',
            'sku'         => 'required|string|max:100|unique:products,sku',
            'description' => 'nullable|string|max:2000',
            'price'       => 'required|numeric|min:0',
            'stock'       => 'nullable|integer|min:0',
            'active'      => 'nullable|boolean',
        ];
    }
}
