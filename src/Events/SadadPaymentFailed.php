<?php
// Built by Louis Innovations (www.louis-innovations.com)

namespace LouisInnovations\SadadLaravel\Events;

use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use LouisInnovations\Sadad\Webhook\WebhookResult;

/**
 * Fired when a SADAD webhook indicates a failed or declined payment
 * (transactionStatus !== 3).
 */
class SadadPaymentFailed
{
    use Dispatchable;
    use SerializesModels;

    /**
     * @param WebhookResult        $result  The parsed, verified webhook result.
     * @param array<string, mixed> $payload The raw POST payload from SADAD.
     */
    public function __construct(
        public readonly WebhookResult $result,
        public readonly array $payload,
    ) {
    }

    /**
     * Convenience accessor for the failure message.
     */
    public function message(): string
    {
        return $this->result->message;
    }

    /**
     * Convenience accessor for the order number.
     */
    public function orderNumber(): string
    {
        return $this->result->orderNumber;
    }

    /**
     * Convenience accessor for the transaction number.
     */
    public function transactionNumber(): string
    {
        return $this->result->transactionNumber;
    }
}
