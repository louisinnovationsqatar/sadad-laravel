<?php
// Built by Louis Innovations (www.louis-innovations.com)

namespace LouisInnovations\SadadLaravel\Events;

use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

/**
 * Fired after a refund is successfully processed via the SADAD API.
 */
class SadadRefundProcessed
{
    use Dispatchable;
    use SerializesModels;

    /**
     * @param string               $transactionNumber The transaction that was refunded.
     * @param array<string, mixed> $response          Raw API response from SADAD.
     */
    public function __construct(
        public readonly string $transactionNumber,
        public readonly array $response,
    ) {
    }

    /**
     * Convenience accessor for the refund status from the API response.
     */
    public function status(): string
    {
        return (string) ($this->response['status'] ?? '');
    }
}
