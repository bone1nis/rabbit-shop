<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Routing\Controller;

use App\Http\Requests\StoreRequest;
use App\Http\Requests\UpdateRequest;
use App\Http\Resources\OrderResource;
use App\Models\Order;
use App\Services\Service;

class OrderController extends Controller
{
    protected Service $service;

    public function __construct(Service $service)
    {
        $this->service = $service;
    }

    /**
     * Display a listing of the resource.
     */
    public function index(): \Illuminate\Http\Resources\Json\AnonymousResourceCollection
    {
        return OrderResource::collection(Order::all());
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreRequest $request): OrderResource
    {
        $validated = $request->validated();

        return $this->service->create($validated);
    }

    /**
     * Display the specified resource.
     */
    public function show($id): OrderResource
    {
        $order = Order::findOrFail($id);
        return new OrderResource($order);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateRequest $request, $id): OrderResource
    {
        $validated = $request->validated();

        $order = Order::findOrFail($id);
        return $this->service->update($order, $validated);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Order $order): \Illuminate\Http\JsonResponse
    {
        return $this->service->destroy($order);
    }
}
