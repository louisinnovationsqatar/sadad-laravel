<?php
// Built by Louis Innovations (www.louis-innovations.com)

namespace LouisInnovations\SadadLaravel\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Log;
use LouisInnovations\SadadLaravel\Events\SadadPaymentFailed;
use LouisInnovations\SadadLaravel\Events\SadadPaymentSucceeded;
use LouisInnovations\SadadLaravel\Facades\Sadad;

/**
 * Handles SADAD payment callback (return) POST requests.
 *
 * After a customer completes (or abandons) payment on the SADAD gateway,
 * SADAD POSTs the result to the callback URL. This controller:
 *
 *   1. Parses and verifies the callback data via Sadad::handleCallback().
 *   2. Fires SadadPaymentSucceeded or SadadPaymentFailed.
 *   3. Redirects the user to the configured success or failure URL.
 *
 * Configure redirect destinations in config/sadad.php:
 *   'redirect' => ['success' => '/thank-you', 'failure' => '/payment-failed']
 */
class SadadCallbackController extends Controller
{
    /**
     * Handle a SADAD callback POST request.
     */
    public function __invoke(Request $request): RedirectResponse
    {
        $postData     = $request->all();
        $checkoutMode = (string) config('sadad.checkout_mode', 'v1.1');

        if (config('sadad.logging')) {
            Log::channel(config('sadad.log_channel', 'stack'))
                ->info('[SADAD] Callback received.', [
                    'ip'      => $request->ip(),
                    'payload' => config('sadad.debug') ? $postData : '[redacted – enable SADAD_DEBUG to log]',
                ]);
        }

        try {
            $result = Sadad::handleCallback($postData, $checkoutMode);
        } catch (\Throwable $e) {
            if (config('sadad.logging')) {
                Log::channel(config('sadad.log_channel', 'stack'))
                    ->error('[SADAD] Callback processing error.', [
                        'error' => $e->getMessage(),
                        'trace' => config('sadad.debug') ? $e->getTraceAsString() : '[redacted]',
                    ]);
            }

            return redirect(config('sadad.redirect.failure', '/'))
                ->with('sadad_error', $e->getMessage());
        }

        if ($result->isSuccess) {
            SadadPaymentSucceeded::dispatch(
                $this->buildWebhookResultFromCallback($result),
                $postData
            );

            if (config('sadad.logging')) {
                Log::channel(config('sadad.log_channel', 'stack'))
                    ->info('[SADAD] Callback payment succeeded.', [
                        'transaction' => $result->transactionNumber,
                        'order'       => $result->orderNumber,
                        'amount'      => $result->amount,
                    ]);
            }

            return redirect(config('sadad.redirect.success', '/'))
                ->with([
                    'sadad_success'      => true,
                    'sadad_order'        => $result->orderNumber,
                    'sadad_transaction'  => $result->transactionNumber,
                    'sadad_amount'       => $result->amount,
                ]);
        }

        SadadPaymentFailed::dispatch(
            $this->buildWebhookResultFromCallback($result),
            $postData
        );

        if (config('sadad.logging')) {
            Log::channel(config('sadad.log_channel', 'stack'))
                ->warning('[SADAD] Callback payment failed.', [
                    'transaction'      => $result->transactionNumber,
                    'order'            => $result->orderNumber,
                    'response_code'    => $result->responseCode,
                    'response_message' => $result->responseMessage,
                ]);
        }

        return redirect(config('sadad.redirect.failure', '/'))
            ->with([
                'sadad_success' => false,
                'sadad_error'   => $result->responseMessage,
                'sadad_order'   => $result->orderNumber,
            ]);
    }

    /**
     * Build a minimal WebhookResult-compatible object from a CallbackResult so
     * the same SadadPaymentSucceeded/Failed events carry consistent data whether
     * they are fired from a webhook or a callback.
     *
     * We use a real WebhookResult here so listeners do not need to type-check.
     */
    private function buildWebhookResultFromCallback(
        \LouisInnovations\Sadad\Callback\CallbackResult $callbackResult
    ): \LouisInnovations\Sadad\Webhook\WebhookResult {
        return new \LouisInnovations\Sadad\Webhook\WebhookResult(
            isSuccess:         $callbackResult->isSuccess,
            message:           $callbackResult->responseMessage,
            transactionNumber: $callbackResult->transactionNumber,
            orderNumber:       $callbackResult->orderNumber,
            amount:            $callbackResult->amount,
            merchantId:        (string) config('sadad.merchant_id', ''),
            isTestMode:        config('sadad.environment', 'test') === 'test',
            invoiceNumber:     null,
        );
    }
}
