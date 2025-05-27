<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreRequest;
use App\Http\Requests\UpdateRequest;
use App\Http\Resources\ProductResource;
use App\Models\Product;
use App\Services\Service;
use Illuminate\Http\Request;

class ProductController extends Controller
{

    protected Service $service;

    public function __construct(Service $service)
    {
        $this->service = $service;
    }

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return ProductResource::collection(Product::all());
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreRequest $request)
    {
        $validated = $request->validated();

        return $this->service->create($validated);
    }

    /**
     * Display the specified resource.
     */
    public function show($id): ProductResource
    {
        $product = Product::findOrFail($id);
        return new ProductResource($product);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateRequest $request, $id)
    {
        $validated = $request->validated();

        $product = Product::findOrFail($id);
        return $this->service->update($product, $validated);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Product $product)
    {
        return $this->service->destroy($product);
    }
}
