<?php
// Built by Louis Innovations (www.louis-innovations.com)

namespace LouisInnovations\SadadLaravel\Events;

use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use LouisInnovations\Sadad\Webhook\WebhookResult;

/**
 * Fired immediately after a SADAD webhook payload is received and verified,
 * before any success/failure classification.
 */
class SadadWebhookReceived
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
}
