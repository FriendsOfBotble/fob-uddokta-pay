<table class="table payment-method-item">
    <tbody>
    <tr class="border-pay-row">
        <td class="border-pay-col">
            <i class="fa fa-theme-payments"></i>
        </td>
        <td style="width: 20%">
            <img class="filter-black" src="{{ asset('vendor/core/plugins/uddokta-pay/images/uddokta-pay.png') }}" alt="{{ $name }}">
        </td>
        <td class="border-right">
            <ul>
                <li>
                    <a href="https://uddoktapay.com/" target="_blank">{{ $name }}</a>
                    <p>{{ __('Customer can buy products using :name', ['name' => $name]) }}</p>
                </li>
            </ul>
        </td>
    </tr>
    <tr class="bg-white">
        <td colspan="3">
            <div class="float-start" style="margin-top: 5px;">
                <div @class(['payment-name-label-group', 'hidden' => ! $status])>
                    <span class="payment-note v-a-t">{{ trans('plugins/payment::payment.use') }}:</span>
                    <label class="ws-nm inline-display method-name-label">{{ get_payment_setting('name', $moduleName) }}</label>
                </div>
            </div>
            <div class="float-end">
                <a @class(['btn btn-secondary toggle-payment-item edit-payment-item-btn-trigger', 'hidden' => ! $status])>{{ trans('plugins/payment::payment.edit') }}</a>
                <a @class(['btn btn-secondary toggle-payment-item save-payment-item-btn-trigger', 'hidden' => $status])>{{ trans('plugins/payment::payment.settings') }}</a>
            </div>
        </td>
    </tr>
    <tr class="paypal-online-payment payment-content-item hidden">
        <td class="border-left" colspan="3">
            <form>
                <input type="hidden" name="type" value="{{ $moduleName }}" class="payment_type">

                <div class="row">
                    <div class="col-sm-6">
                        <ul>
                            <li>
                                <label>{{ trans('plugins/payment::payment.configuration_instruction', ['name' => $name]) }}</label>
                            </li>
                            <li class="payment-note">
                                <p>{{ trans('plugins/payment::payment.configuration_requirement', ['name' => $name]) }}:</p>
                                <ul class="m-md-l" style="list-style-type:decimal">
                                    <li style="list-style-type:decimal">
                                        <a href="https://my.uddoktapay.com/register.php" target="_blank">
                                            {{ trans('plugins/payment::payment.service_registration', ['name' => $name]) }}
                                        </a>
                                    </li>
                                    <li style="list-style-type:decimal">
                                        <p>{{ trans('plugins/payment::payment.stripe_after_service_registration_msg', ['name' => $name]) }}</p>
                                    </li>
                                    <li style="list-style-type:decimal">
                                        <p>{{ trans('plugins/payment::payment.stripe_enter_client_id_and_secret') }}</p>
                                    </li>
                                </ul>
                            </li>
                        </ul>
                    </div>
                    <div class="col-sm-6">
                        <div class="well bg-white">
                            <x-core-setting::text-input
                                name="payment_uddokta_pay_name"
                                :label="trans('plugins/payment::payment.method_name')"
                                :value="get_payment_setting('name', $moduleName, trans('plugins/payment::payment.pay_online_via', ['name' => $name]))"
                                data-counter="400"
                            />

                            <x-core-setting::textarea
                                name="payment_uddokta_pay_description"
                                :label="trans('plugins/payment::payment.method_name')"
                                :value="get_payment_setting('description', $moduleName)"
                                data-counter="400"
                            />

                            <x-core-setting::text-input
                                :name="'payment_' . $moduleName . '_api_key'"
                                type="password"
                                :label="__('API key')"
                                :value="get_payment_setting('api_key', $moduleName)"
                                placeholder="xxxxxx"
                            />

                            <x-core-setting::select
                                :name="'payment_' . $moduleName . '_environment'"
                                :label="__('environment')"
                                :options="[
                                    'test' => __('Test'),
                                    'production' => __('Production')
                                ]"
                                :value="get_payment_setting('environment', $moduleName)"
                            />

                            <x-core-setting::text-input
                                :name="'payment_' . $moduleName . '_base_url'"
                                type="text"
                                :label="__('Base url')"
                                :value="get_payment_setting('base_url', $moduleName)"
                                placeholder="https://demo.uddoktapay.com/api"
                            />

                            {!! apply_filters(PAYMENT_METHOD_SETTINGS_CONTENT, null, $moduleName) !!}
                        </div>
                    </div>
                </div>

                <div class="col-12 bg-white text-end">
                    <button @class(['btn btn-warning disable-payment-item', 'hidden' => ! $status]) type="button">{{ trans('plugins/payment::payment.deactivate') }}</button>
                    <button @class(['btn btn-info save-payment-item btn-text-trigger-save', 'hidden' => $status]) type="button">{{ trans('plugins/payment::payment.activate') }}</button>
                    <button @class(['btn btn-info save-payment-item btn-text-trigger-update', 'hidden' => ! $status]) type="button">{{ trans('plugins/payment::payment.update') }}</button>
                </div>
            </form>
        </td>
    </tr>
    </tbody>
</table>
