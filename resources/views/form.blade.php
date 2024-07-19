<form action="{{ $action }}" method="get">
    <button type="submit" style="display: none">{{ __('Submit') }}</button>
</form>

<p>{{ __('Redirecting to Uddoktapay...') }}</p>

<script>
    'use strict';

    document.addEventListener('DOMContentLoaded', function () {
        document.querySelector('form').submit();
    });
</script>
