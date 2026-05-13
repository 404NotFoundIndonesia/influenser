@component('mail::message')
# Payment Confirmed

Dear **{{ $notifiable->name }}**,

Your payment for the **{{ $invoice->campaign->name }}** campaign has been confirmed.

## Payment Details

| Field | Value |
|-------|-------|
| Campaign | {{ $invoice->campaign->name }} |
| Amount | Rp {{ number_format($invoice->amount, 0, ',', '.') }} |
@if($invoice->paid_at)
| Paid Date | {{ $invoice->paid_at->format('d F Y') }} |
@endif
| Status | Paid |

Thank you for your contribution to the campaign.

@component('mail::button', ['url' => config('app.url')])
Open {{ config('app.name') }}
@endcomponent

{{ config('app.name') }}
@endcomponent
