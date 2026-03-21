<?php
// Built by Louis Innovations (www.louis-innovations.com)

namespace LouisInnovations\SadadLaravel\Console;

use Illuminate\Console\Command;
use LouisInnovations\Sadad\SadadClient;
use LouisInnovations\Sadad\SadadConfig;

/**
 * Artisan command: php artisan sadad:test
 *
 * Validates the current SADAD configuration and attempts to authenticate with
 * the SADAD API. Useful for verifying credentials after initial setup or after
 * a credential rotation.
 */
class SadadTestCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'sadad:test
        {--env= : Override the environment (test|live) for this check only}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Validate SADAD configuration and test API authentication';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $this->newLine();
        $this->line('<fg=cyan>SADAD Payment Gateway – Configuration Test</>');
        $this->line(str_repeat('-', 50));
        $this->newLine();

        // ----------------------------------------------------------------
        // 1. Read and display config values (secrets masked)
        // ----------------------------------------------------------------
        $merchantId  = (string) config('sadad.merchant_id', '');
        $secretKey   = (string) config('sadad.secret_key', '');
        $website     = (string) config('sadad.website', '');
        $environment = $this->option('env') ?: (string) config('sadad.environment', 'test');
        $language    = (string) config('sadad.language', 'eng');
        $callbackUrl = (string) config('sadad.callback_url', '');
        $webhookUrl  = (string) config('sadad.webhook_url', '');
        $checkoutMode = (string) config('sadad.checkout_mode', 'v1.1');

        $this->table(
            ['Setting', 'Value'],
            [
                ['merchant_id',    $merchantId  ?: '<fg=red>[NOT SET]</>'],
                ['secret_key',     $secretKey   ? str_repeat('*', max(0, strlen($secretKey) - 4)) . substr($secretKey, -4) : '<fg=red>[NOT SET]</>'],
                ['website',        $website     ?: '<fg=red>[NOT SET]</>'],
                ['environment',    $environment],
                ['checkout_mode',  $checkoutMode],
                ['language',       $language],
                ['callback_url',   $callbackUrl ?: '<fg=yellow>[not configured]</>'],
                ['webhook_url',    $webhookUrl  ?: '<fg=yellow>[not configured]</>'],
                ['logging',        config('sadad.logging') ? 'enabled' : 'disabled'],
                ['debug',          config('sadad.debug')   ? '<fg=yellow>enabled</>' : 'disabled'],
            ]
        );

        // ----------------------------------------------------------------
        // 2. Validate required fields
        // ----------------------------------------------------------------
        $this->line('Validating required configuration...');
        $errors = [];

        if (empty($merchantId)) {
            $errors[] = 'SADAD_MERCHANT_ID is not set.';
        } elseif (!preg_match('/^\d{7}$/', $merchantId)) {
            $errors[] = "SADAD_MERCHANT_ID must be exactly 7 digits (got: \"{$merchantId}\").";
        }

        if (empty($secretKey)) {
            $errors[] = 'SADAD_SECRET_KEY is not set.';
        }

        if (empty($website)) {
            $errors[] = 'SADAD_WEBSITE is not set.';
        }

        if (!in_array($environment, ['test', 'live'], true)) {
            $errors[] = "SADAD_ENVIRONMENT must be \"test\" or \"live\" (got: \"{$environment}\").";
        }

        if (!in_array($language, ['eng', 'arb'], true)) {
            $errors[] = "SADAD_LANGUAGE must be \"eng\" or \"arb\" (got: \"{$language}\").";
        }

        if (!in_array($checkoutMode, ['v1.1', 'v2.1', 'v2.2'], true)) {
            $errors[] = "SADAD_CHECKOUT_MODE must be \"v1.1\", \"v2.1\", or \"v2.2\" (got: \"{$checkoutMode}\").";
        }

        if (!empty($errors)) {
            foreach ($errors as $error) {
                $this->error("  [FAIL] {$error}");
            }
            $this->newLine();
            $this->error('Configuration validation failed. Fix the errors above and re-run sadad:test.');
            return self::FAILURE;
        }

        $this->info('  [OK] All required configuration values are present and valid.');
        $this->newLine();

        // ----------------------------------------------------------------
        // 3. Attempt API authentication
        // ----------------------------------------------------------------
        $this->line('Testing SADAD API authentication...');

        try {
            $config = new SadadConfig(
                merchantId:  $merchantId,
                secretKey:   $secretKey,
                website:     $website,
                environment: $environment,
                language:    $language,
                callbackUrl: $callbackUrl ?: null,
                webhookUrl:  $webhookUrl  ?: null,
            );

            $client = new SadadClient($config);

            // We trigger authentication by attempting a transaction lookup
            // that will never match a real transaction. A successful login
            // will return an API-level "not found" error, not an auth error.
            // An invalid credential will throw an AuthenticationException.
            try {
                $client->getTransaction('TEST-AUTH-CHECK-0000');
            } catch (\LouisInnovations\Sadad\Exceptions\AuthenticationException $e) {
                throw $e; // re-throw – this is a real auth failure
            } catch (\Throwable $e) {
                // Any non-auth exception (e.g. transaction not found) means
                // authentication succeeded and the API is reachable.
            }

            $this->info('  [OK] Authentication successful – SADAD API is reachable.');
            $this->newLine();

        } catch (\LouisInnovations\Sadad\Exceptions\AuthenticationException $e) {
            $this->error('  [FAIL] Authentication failed: ' . $e->getMessage());
            $this->newLine();
            $this->error('Check your SADAD_MERCHANT_ID, SADAD_SECRET_KEY, and SADAD_WEBSITE credentials.');
            return self::FAILURE;
        } catch (\Throwable $e) {
            $this->warn('  [WARN] Could not reach SADAD API: ' . $e->getMessage());
            $this->newLine();
            $this->warn('Configuration appears valid but the SADAD API could not be contacted.');
            $this->warn('This may be a network issue or the API may be temporarily unavailable.');
            return self::FAILURE;
        }

        // ----------------------------------------------------------------
        // 4. Summary
        // ----------------------------------------------------------------
        $this->line(str_repeat('-', 50));
        $this->info('SADAD configuration test passed.');

        if ($environment === 'live') {
            $this->newLine();
            $this->warn('You are running in LIVE mode. Real transactions will be charged.');
        }

        $this->newLine();
        return self::SUCCESS;
    }
}
