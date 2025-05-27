<?php

namespace App\Services;

use App\Http\Resources\OrderResource;
use App\Models\Order;
use Exception;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\JsonResponse;

class Service
{
    protected RabbitMQService $rabbitMQ;

    public function __construct(RabbitMQService $rabbitMQ)
    {
        $this->rabbitMQ = $rabbitMQ;
    }

    /**
     * @throws Exception
     */
    public function create(array $data): OrderResource
    {
        try {
            $order = Order::create([
                'address' => $data['address'],
                'email' => $data['email'],
                'phone' => $data['phone'],
                'products' => $data['products'],
                'status' => 'pending',
            ]);

            $orderData = $order->only(['id', 'address', 'email', 'phone', 'products', 'status']);

            $this->rabbitMQ->publish([
                'event' => 'order.created',
                'order_id' => $order->id,
                'data' => $orderData,
            ]);

            return new OrderResource($order);
        } catch (Exception $e) {
            Log::error('Failed to create order', [
                'error' => $e->getMessage(),
                'data' => $data,
            ]);
            throw $e;
        }
    }

    public function update(Order $order, array $data): OrderResource
    {
        try {
            $order->update($data);

            $this->rabbitMQ->publish([
                'event' => 'order.updated',
                'order_id' => $order->id,
                'data' => $order->toArray(),
            ]);

            return new OrderResource($order);
        } catch (Exception $e) {
            Log::error('Failed to update order', [
                'error' => $e->getMessage(),
                'order_id' => $order->id,
                'data' => $data,
            ]);
            throw $e;
        }
    }

    public function destroy(Order $order): JsonResponse
    {
        try {
            $order->delete();

            $this->rabbitMQ->publish([
                'event' => 'order.deleted',
                'order_id' => $order->id,
            ]);

            return response()->json([
                'message' => 'Order deleted successfully.',
            ]);
        } catch (Exception $e) {
            Log::error('Failed to delete order', [
                'error' => $e->getMessage(),
                'order_id' => $order->id,
            ]);

            return response()->json([
                'message' => 'Failed to delete order',
            ], 500);
        }
    }
}
