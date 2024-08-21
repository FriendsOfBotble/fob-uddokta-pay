<?php

namespace FriendsOfBotble\UddoktaPay\Http\Controllers;

use Botble\Hotel\Models\Booking;
use FriendsOfBotble\UddoktaPay\Http\Requests\WebhookRequest;
use FriendsOfBotble\UddoktaPay\Providers\UddoktaPayServiceProvider;
use FriendsOfBotble\UddoktaPay\Services\UddoktaPayService;
use ArchiElite\Yoomoney\Libraries\Model\PaymentInterface;
use Botble\Base\Http\Controllers\BaseController;
use Botble\Base\Http\Responses\BaseHttpResponse;
use Botble\Payment\Enums\PaymentStatusEnum;
use Botble\Payment\Supports\PaymentHelper;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;

class UddoktaPayController extends BaseController
{
    public function webhook(WebhookRequest $request, UddoktaPayService $uddoktaPayService): void
    {
        if (! $invoiceId = $request->input('invoice_id')) {
            abort(404);
        }

        $data = $uddoktaPayService->requeryTransaction($invoiceId);

        if (! $data) {
            return;
        }

        $payment = app(PaymentInterface::class)->getFirstBy([
            'charge_id' => $invoiceId,
        ]);

        if (! $payment) {
            return;
        }

        switch ($data['status']) {
            case 'COMPLETED':
                $status = PaymentStatusEnum::COMPLETED;

                break;

            case 'ERROR':
                $status = PaymentStatusEnum::FAILED;

                break;

            default:
                $status = PaymentStatusEnum::PENDING;

                break;
        }

        if (! in_array($payment->status, [PaymentStatusEnum::COMPLETED, PaymentStatusEnum::FAILED, $status])) {
            $payment->status = $status;
            $payment->save();
        }
    }

    public function success(Request $request, BaseHttpResponse $response, UddoktaPayService $uddoktaPayService): BaseHttpResponse
    {
        if (! $invoiceId = $request->input('invoice_id')) {
            return $response
                ->setError()
                ->setNextUrl(PaymentHelper::getCancelURL());
        }

        $data = $uddoktaPayService->requeryTransaction($invoiceId);

        if (! $data) {
            return $response
                ->setError()
                ->setNextUrl(PaymentHelper::getCancelURL());
        }

        switch ($data['status']) {
            case 'COMPLETED':
                $status = PaymentStatusEnum::COMPLETED;

                break;

            case 'ERROR':
                $status = PaymentStatusEnum::FAILED;

                break;

            default:
                $status = PaymentStatusEnum::PENDING;

                break;
        }

        if ($status === PaymentStatusEnum::FAILED) {
            return $response
                ->setError()
                ->setNextUrl(PaymentHelper::getCancelURL())
                ->setMessage($request->input('error_message'));
        }

        do_action(PAYMENT_ACTION_PAYMENT_PROCESSED, [
            'order_id' => $orderId = json_decode($data['metadata']['order_id']),
            'charge_id' => $data['transaction_id'],
            'amount' => $data['amount'],
            'currency' => $data['metadata']['currency'],
            'transaction_id' => $data['transaction_id'],
            'payment_channel' => UddoktaPayServiceProvider::MODULE_NAME,
            'status' => $status,
            'customer_id' => $data['metadata']['customer_id'],
            'customer_type' => $data['metadata']['customer_type'],
            'payment_type' => 'direct',
        ]);

        if (is_plugin_active('hotel')) {
            $booking = Booking::query()
                ->select('transaction_id')
                ->find(Arr::first($orderId));

            if (! $booking) {
                return $response
                    ->setNextUrl(PaymentHelper::getCancelURL())
                    ->setMessage(__('Checkout failed!'));
            }

            return $response
                ->setNextUrl(PaymentHelper::getRedirectURL($booking->transaction_id))
                ->setMessage(__('Checkout successfully!'));
        }

        $nextUrl = PaymentHelper::getRedirectURL($request->input('metadata.token'));

        if (is_plugin_active('job-board') || is_plugin_active('real-estate')) {
            $nextUrl = $nextUrl . '?charge_id=' . $data['transaction_id'];
        }

        return $response
            ->setNextUrl($nextUrl)
            ->setMessage(__('Checkout successfully!'));
    }

    public function error(BaseHttpResponse $response): BaseHttpResponse
    {
        return $response
            ->setError()
            ->setNextUrl(PaymentHelper::getCancelURL());
    }
}
