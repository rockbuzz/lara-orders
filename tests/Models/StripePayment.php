<?php

namespace Tests\Models;

use Rockbuzz\LaraOrders\Transaction;

class StripePayment implements Transaction
{

    /**
     * @var string
     */
    private $identify;

    /**
     * @var string
     */
    private $notes;

    public function __construct($identify, string $notes)
    {
        $this->identify = $identify;
        $this->notes = $notes;
    }

    /**
     * @inheritDoc
     */
    public function identify()
    {
        return $this->identify;
    }

    /**
     * @inheritDoc
     */
    public function about(): array
    {
        return [
            'type' => 'payment',
            'driver' => 'stripe',
            'notes' => $this->notes
        ];
    }
}
