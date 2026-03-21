<?php
// Built by Louis Innovations (www.louis-innovations.com)

namespace LouisInnovations\SadadLaravel\Facades;

use Illuminate\Support\Facades\Facade;
use LouisInnovations\Sadad\Callback\CallbackResult;
use LouisInnovations\Sadad\Checkout\CheckoutResult;
use LouisInnovations\Sadad\Webhook\WebhookResult;

/**
 * @method static CheckoutResult  checkout(array $orderData, string $version = 'v1.1')
 * @method static array           refund(string $transactionNumber)
 * @method static array           createInvoice(array $data)
 * @method static array           shareInvoice(string $invoiceNumber, string $method, string $recipient)
 * @method static array           listInvoices(array $filters = [])
 * @method static array           getTransaction(string $transactionNumber)
 * @method static WebhookResult   handleWebhook(array $payload)
 * @method static CallbackResult  handleCallback(array $postData, string $version = 'v1.1')
 *
 * @see \LouisInnovations\Sadad\SadadClient
 */
class Sadad extends Facade
{
    /**
     * Get the registered name of the component.
     */
    protected static function getFacadeAccessor(): string
    {
        return 'sadad';
    }
}
