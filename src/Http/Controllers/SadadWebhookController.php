<?php
// Built by Louis Innovations (www.louis-innovations.com)

namespace LouisInnovations\SadadLaravel\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Log;
use LouisInnovations\Sadad\Exceptions\SignatureException;
use LouisInnovations\SadadLaravel\Events\SadadPaymentFailed;
use LouisInnovations\SadadLaravel\Events\SadadPaymentSucceeded;
use LouisInnovations\SadadLaravel\Events\SadadWebhookReceived;
use LouisInnovations\SadadLaravel\Facades\Sadad;

/**
 * Handles incoming SADAD webhook POST requests.
 *
 * SADAD posts transaction status notifications to the webhook URL configured
 * in your SADAD merchant dashboard (or via SADAD_WEBHOOK_URL in .env).
 *
 * The controller:
 *   1. Delegates payload verification and parsing to the SDK via Sadad::handleWebhook().
 *   2. Fires SadadWebhookReceived for every verified webhook.
 *   3. Fires SadadPaymentSucceeded or SadadPaymentFailed depending on outcome.
 *   4. Always returns HTTP 200 {"status":"success"} so SADAD stops retrying.
 */
class SadadWebhookController extends Controller
{
    /**
     * Handle a SADAD webhook POST request.
     */
    public function __invoke(Request $request): JsonResponse
    {
        $payload = $request->all();

        if (config('sadad.logging')) {
            Log::channel(config('sadad.log_channel', 'stack'))
                ->info('[SADAD] Webhook received.', [
                    'ip'      => $request->ip(),
                    'payload' => config('sadad.debug') ? $payload : '[redacted – enable SADAD_DEBUG to log]',
                ]);
        }

        try {
            $result = Sadad::handleWebhook($payload);
        } catch (SignatureException $e) {
            if (config('sadad.logging')) {
                Log::channel(config('sadad.log_channel', 'stack'))
                    ->warning('[SADAD] Webhook signature verification failed.', [
                        'error' => $e->getMessage(),
                    ]);
            }

            // Still return 200 to prevent indefinite retries;
            // the signature failure is logged and the event is not fired.
            return response()->json(['status' => 'invalid_signature'], 200);
        } catch (\Throwable $e) {
            if (config('sadad.logging')) {
                Log::channel(config('sadad.log_channel', 'stack'))
                    ->error('[SADAD] Webhook processing error.', [
                        'error' => $e->getMessage(),
                        'trace' => config('sadad.debug') ? $e->getTraceAsString() : '[redacted]',
                    ]);
            }

            return response()->json(['status' => 'error'], 200);
        }

        // Always fire the generic "received" event first
        SadadWebhookReceived::dispatch($result, $payload);

        if ($result->isSuccess) {
            SadadPaymentSucceeded::dispatch($result, $payload);

            if (config('sadad.logging')) {
                Log::channel(config('sadad.log_channel', 'stack'))
                    ->info('[SADAD] Payment succeeded.', [
                        'transaction' => $result->transactionNumber,
                        'order'       => $result->orderNumber,
                        'amount'      => $result->amount,
                    ]);
            }
        } else {
            SadadPaymentFailed::dispatch($result, $payload);

            if (config('sadad.logging')) {
                Log::channel(config('sadad.log_channel', 'stack'))
                    ->warning('[SADAD] Payment failed or declined.', [
                        'transaction' => $result->transactionNumber,
                        'order'       => $result->orderNumber,
                        'message'     => $result->message,
                    ]);
            }
        }

        return response()->json(['status' => 'success'], 200);
    }
}
