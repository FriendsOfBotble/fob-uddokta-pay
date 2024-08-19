<?php

namespace FriendsOfBotble\UddoktaPay\Services;

use FriendsOfBotble\UddoktaPay\Providers\UddoktaPayServiceProvider;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Support\Facades\Http;

class UddoktaPayService
{
    protected string $apiKey;

    protected array $processUrls = [
        'test' => 'https://sandbox.uddoktapay.com/api',
        'production' => null,
    ];

    protected array $data = [];

    public function __construct()
    {
        $this->apiKey = get_payment_setting('api_key', UddoktaPayServiceProvider::MODULE_NAME);
        $this->processUrls['production'] = get_payment_setting('base_url', UddoktaPayServiceProvider::MODULE_NAME);
    }

    public function getApiKey(): string
    {
        return $this->apiKey;
    }

    protected function request(): PendingRequest
    {
        $request = Http::withHeaders([
            'Accept' => 'application/json',
            'Content-Type' => 'application/json',
            'RT-UDDOKTAPAY-API-KEY' => $this->apiKey,
        ]);

        return $request;
    }

    protected function getProcessUrl(string $uri = ''): string
    {
        return $this->processUrls[
            get_payment_setting('environment', UddoktaPayServiceProvider::MODULE_NAME) ?: 'test'] . $uri;
    }

    public function redirectToCheckoutPage(): void
    {
        echo view('plugins/uddokta-pay::form', [
            'action' => $this->getPaymentUrl(),
        ]);

        exit();
    }

    public function initPayment($requestData): string
    {
        $response = $this
            ->request()
            ->post($this->getProcessUrl('/checkout-v2'), $requestData);

        if ($response->successful()) {
            return $response->collect()['payment_url'];
        }

        return '';
    }

    protected function getPaymentUrl(): string
    {
        return $this->initPayment($this->data);
    }

    public function withData(array $data): self
    {
        $this->data = $data;

        $this->withAdditionalData();

        return $this;
    }

    protected function withAdditionalData(): void
    {
        $this->data = array_merge($this->data, [
            'key' => $this->getApiKey(),
        ]);
    }

    public function requeryTransaction(string $referenceNumber): array
    {
        $response = $this
            ->request()
            ->post($this->getProcessUrl('/verify-payment'), ['invoice_id' => $referenceNumber]);

        if (! $response->ok()) {
            return [
                'error' => false,
                'message' => $response->reason(),
            ];
        }

        return $response->json();
    }

    public function refundOrder(array $data): array
    {
        $response = Http::asJson()
            ->withToken($this->getApiKey())
            ->post($this->getProcessUrl('/refund-payment'), $data);

        if ($response->status() == 400) {
            return [
                'error' => true,
                'message' => $response->json('message'),
            ];
        }

        return [
            'error' => $response->failed(),
            'message' => $response->json('message'),
        ];
    }
}
