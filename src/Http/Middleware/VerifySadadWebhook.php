<?php
// Built by Louis Innovations (www.louis-innovations.com)

namespace LouisInnovations\SadadLaravel\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use LouisInnovations\Sadad\Exceptions\SignatureException;
use LouisInnovations\Sadad\Signature\SignatureVerifier;
use Symfony\Component\HttpFoundation\Response;

/**
 * Middleware that verifies the SADAD webhook SHA-256 checksumhash before
 * allowing the request to reach the controller.
 *
 * The middleware uses the same SignatureVerifier that the WebhookHandler uses
 * internally, so attaching this middleware to the webhook route provides an
 * early-rejection layer without touching controller logic.
 *
 * Usage – register in bootstrap/app.php (Laravel 11+) or in Kernel.php (Laravel 10):
 *
 *   Route::post('/sadad/webhook', SadadWebhookController::class)
 *       ->middleware(VerifySadadWebhook::class);
 */
class VerifySadadWebhook
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $secretKey = (string) config('sadad.secret_key', '');

        if (empty($secretKey)) {
            if (config('sadad.logging')) {
                Log::channel(config('sadad.log_channel', 'stack'))
                    ->error('[SADAD] Webhook signature check skipped: SADAD_SECRET_KEY is not configured.');
            }

            abort(500, 'SADAD secret key is not configured.');
        }

        $payload = $request->all();

        try {
            SignatureVerifier::verifyWebhook($payload, $secretKey);
        } catch (SignatureException $e) {
            if (config('sadad.logging')) {
                Log::channel(config('sadad.log_channel', 'stack'))
                    ->warning('[SADAD] Webhook signature verification failed.', [
                        'ip'      => $request->ip(),
                        'error'   => $e->getMessage(),
                        'payload' => config('sadad.debug') ? $payload : '[redacted]',
                    ]);
            }

            abort(403, 'Invalid SADAD webhook signature.');
        }

        return $next($request);
    }
}
