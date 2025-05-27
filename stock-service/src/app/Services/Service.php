<?php

namespace App\Services;

use App\Http\Resources\ProductResource;
use App\Models\Product;
use Exception;
use Illuminate\Support\Facades\Log;
use Illuminate\Http\JsonResponse;

class Service
{
    /**
     * @throws Exception
     */
    public function create(array $data): ProductResource
    {
        try {
            $product = Product::create($data);

            return new ProductResource($product);
        } catch (Exception $e) {
            Log::error('Failed to create product', [
                'error' => $e->getMessage(),
                'data' => $data,
            ]);
            throw $e;
        }
    }

    /**
     * @throws Exception
     */
    public function update(Product $product, array $data): ProductResource
    {
        try {
            $product->update($data);

            return new ProductResource($product);
        } catch (Exception $e) {
            Log::error('Failed to update product', [
                'error' => $e->getMessage(),
                'product_id' => $product->id,
                'data' => $data,
            ]);
            throw $e;
        }
    }

    public function destroy(Product $product): JsonResponse
    {
        try {
            $product->delete();

            return response()->json([
                'message' => 'Product deleted successfully.',
            ]);
        } catch (Exception $e) {
            Log::error('Failed to delete product', [
                'error' => $e->getMessage(),
                'product_id' => $product->id,
            ]);

            return response()->json([
                'message' => 'Failed to delete product.',
            ], 500);
        }
    }
}
