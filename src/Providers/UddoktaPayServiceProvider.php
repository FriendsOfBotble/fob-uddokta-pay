<?php

namespace FriendsOfBotble\UddoktaPay\Providers;

use Botble\Base\Traits\LoadAndPublishDataTrait;
use Illuminate\Support\ServiceProvider;

class UddoktaPayServiceProvider extends ServiceProvider
{
    use LoadAndPublishDataTrait;

    public const MODULE_NAME = 'uddokta_pay';

    public function boot(): void
    {
        if (! is_plugin_active('payment')) {
            return;
        }

        if (
            ! is_plugin_active('ecommerce') &&
            ! is_plugin_active('job-board') &&
            ! is_plugin_active('real-estate') &&
            ! is_plugin_active('hotel')
        ) {
            return;
        }

        $this->setNamespace('plugins/uddokta-pay')
            ->loadAndPublishViews()
            ->publishAssets()
            ->loadRoutes();

        $this->app->booted(function () {
            $this->app->register(HookServiceProvider::class);
        });
    }
}
