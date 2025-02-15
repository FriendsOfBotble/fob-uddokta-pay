@php
    $data = $payment['data'];
    $address = app(\Botble\Ecommerce\Repositories\Interfaces\OrderAddressInterface::class)->getFirstBy([
        'order_id' => Arr::first(json_decode($data['meta']['order_id']) ?? []),
    ]);
@endphp

<div class="alert alert-success" role="alert">
    <p class="mb-2">{{ trans('plugins/payment::payment.payment_id') }}: <strong>{{ $data['id'] }}</strong></p>

    <p class="mb-2">
        {{ trans('plugins/payment::payment.card') }}:
        <span>
            {{ $data['card']['type'] }} -
            <strong>{{ $data['card']['first_6digits'] . '********' . $data['card']['last_4digits'] }}</strong>
            - {{ $data['card']['expiry'] }}
        </span>
    </p>

    <p class="mb-2">{{ trans('plugins/payment::payment.payer_name') }}: {{ $address->name }}</p>
    <p class="mb-2">{{ trans('plugins/payment::payment.email') }}: {{ $address->email }}</p>

    @if ($address->phone)
        <p class="mb-2">{{ trans('plugins/payment::payment.phone')  }}: {{ $address->phone }}</p>
    @endif

    <p class="mb-0">
        {{ trans('plugins/payment::payment.shipping_address') }}:
        {{ $address->name }}, {{ $address->city }}, {{ $address->state }}, {{ $address->country }} {{ $address->zipcode }}
    </p>
</div>

@if (isset($payment['refunds']))
    <div class="alert alert-warning">
        <h6 class="alert-heading">{{ trans('plugins/payment::payment.refunds.title') . ' (' . count($payment['refunds']) . ')'}}</h6>

        @foreach ($payment['refunds'] as $refund)
            <hr class="m-0 mb-4">
            @php
                $refund = $refund['_data_request'];
            @endphp
            <p>{{ trans('plugins/payment::payment.amount') }}: {{ $refund['refund_amount'] }} {{ strtoupper($refund['currency']) }}</p>
            <p>{{ trans('plugins/payment::payment.refunds.create_time') }}: {{ Carbon\Carbon::now()->parse($refund['created_at']) }}</p>
        @endforeach
    </div>
@endif

@include('plugins/payment::partials.view-payment-source')
