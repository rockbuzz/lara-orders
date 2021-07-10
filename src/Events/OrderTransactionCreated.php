<?php

namespace Rockbuzz\LaraOrders\Events;

use Illuminate\Queue\SerializesModels;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Foundation\Events\Dispatchable;
use Rockbuzz\LaraOrders\Models\OrderTransaction;
use Illuminate\Broadcasting\InteractsWithSockets;

class OrderTransactionCreated
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /**
     * @var OrderTransaction
     */
    public $transaction;

    public function __construct(OrderTransaction $transaction)
    {
        $this->transaction = $transaction;
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
