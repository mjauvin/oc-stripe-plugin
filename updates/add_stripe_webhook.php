<?php namespace StudioAzura\Stripe\Updates;

use Lang;
use Winter\Storm\Database\Updates\Migration;
use Stripe\StripeClient;
use StudioAzura\Stripe\Models\Settings;
use StudioAzura\Stripe\Plugin;
use Url;

class addStripeWebhook extends Migration
{
    public function up()
    {
        foreach (['test', 'live'] as $mode) {
            $hook = null;
            $sk_key = 'sk_' . $mode;
            $we_key = 'webhook_' . $mode;

            $sk_value = Settings::get($sk_key);
            $we_value = Settings::get($we_key);
            if ($sk_value && !$we_value) {
                $stripe = new StripeClient(['api_key' => $sk_value]);
                try {
                    $hook = $stripe->webhookEndpoints->create([
                        'url' => Url::secure(Plugin::$webhook),
                            'enabled_events' => ['checkout.session.completed'],
                            'description' => Lang::get('studioazura.stripe::lang.plugin.description'),
                    ]);
                } catch (\Exception $e) {
                }
                if ($hook && isset($hook->id)) {
                    Settings::set($we_key, $hook->id);
                }
            }
        }
    }

    public function down()
    {
        foreach (['test', 'live'] as $mode) {
            $sk_key = 'sk_' . $mode;
            $we_key = 'webhook_' . $mode;

            $sk_value = Settings::get($sk_key);
            $we_value = Settings::get($we_key);
            if ($sk_value && $we_value) {
                $stripe = new StripeClient(['api_key' => $sk_value]);
                try {
                    $hook = $stripe->webhookEndpoints->delete($we_value, []);
                    Settings::set($we_key, null);
                } catch (\Exception $e) {
                }
            }
        }
    }
}
