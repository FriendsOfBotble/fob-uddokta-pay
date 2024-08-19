<?php

namespace FriendsOfBotble\UddoktaPay\Providers;

use FriendsOfBotble\UddoktaPay\Services\UddoktaPayService;
use Botble\Payment\Enums\PaymentMethodEnum;
use Illuminate\Http\Request;
use Illuminate\Support\ServiceProvider;
use PaymentMethods;
use Throwable;

class HookServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        add_filter(PAYMENT_METHODS_SETTINGS_PAGE, function (string|null $settings) {
            $name = 'UddoktaPay';
            $moduleName = UddoktaPayServiceProvider::MODULE_NAME;
            $status = (bool) get_payment_setting('status', $moduleName);

            return $settings . view('plugins/uddokta-pay::settings', compact('moduleName', 'status', 'name'))->render();
        }, 999);

        add_filter(BASE_FILTER_ENUM_ARRAY, function (array $values, string $class): array {
            if ($class === PaymentMethodEnum::class) {
                $values['UddoktaPay'] = UddoktaPayServiceProvider::MODULE_NAME;
            }

            return $values;
        }, 999, 2);

        add_filter(BASE_FILTER_ENUM_LABEL, function ($value, $class): string {
            if ($class === PaymentMethodEnum::class && $value === UddoktaPayServiceProvider::MODULE_NAME) {
                $value = 'UddoktaPay';
            }

            return $value;
        }, 999, 2);

        add_filter(PAYMENT_FILTER_ADDITIONAL_PAYMENT_METHODS, function (string|null $html, array $data): string|null {
            if (get_payment_setting('status', UddoktaPayServiceProvider::MODULE_NAME)) {
                $payUddokta = new UddoktaPayService();

                if (! $payUddokta->getApiKey()) {
                    return $html;
                }

                PaymentMethods::method(UddoktaPayServiceProvider::MODULE_NAME, [
                    'html' => view('plugins/uddokta-pay::methods', $data, ['moduleName' => UddoktaPayServiceProvider::MODULE_NAME])->render(),
                ]);
            }

            return $html;
        }, 999, 2);

        add_filter(PAYMENT_FILTER_GET_SERVICE_CLASS, function (string|null $data, string $value): string|null {
            if ($value === UddoktaPayServiceProvider::MODULE_NAME) {
                $data = UddoktaPayServiceProvider::class;
            }

            return $data;
        }, 20, 2);

        add_filter(PAYMENT_FILTER_AFTER_POST_CHECKOUT, function (array $data, Request $request): array {
            if ($data['type'] !== UddoktaPayServiceProvider::MODULE_NAME) {
                return $data;
            }

            $paymentData = apply_filters(PAYMENT_FILTER_PAYMENT_DATA, [], $request);
            try {
                $payUddokta = new UddoktaPayService();

                $payUddokta->withData([
                    'full_name' => $paymentData['address']['name'],
                    'email' => $paymentData['address']['email'],
                    'amount' => $paymentData['amount'],
                    'metadata' => [
                        'order_id' => json_encode($paymentData['order_id']),
                        'token' => $paymentData['checkout_token'],
                        'customer_id' => $paymentData['customer_id'],
                        'customer_type' => $paymentData['customer_type'],
                        'currency' => $paymentData['currency'],
                    ],
                    'redirect_url' => route('payment.uddokta-pay.success'),
                    'return_type'   => 'GET',
                    'cancel_url' => route('payment.uddokta-pay.error'),
                    'webhook_url' => route('payment.uddokta-pay.webhook'),
                ]);

                $payUddokta->redirectToCheckoutPage();
            } catch (Throwable $exception) {
                $data['error'] = true;
                $data['message'] = json_encode($exception->getMessage());
            }

            return $data;
        }, 999, 2);
    }
}
