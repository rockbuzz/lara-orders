<?php

namespace Rockbuzz\LaraOrders\Events;

use Illuminate\Queue\SerializesModels;
use Rockbuzz\LaraOrders\Models\OrderItem;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Broadcasting\InteractsWithSockets;

class OrderItemCreated
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /**
     * @var OrderItem
     */
    public $item;

    public function __construct(OrderItem $item)
    {
        $this->item = $item;
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return \Illuminate\Broadcasting\Channel|array
     */
    public function broadcastOn()
    {
        return new PrivateChannel('channel-name');
    }
}
