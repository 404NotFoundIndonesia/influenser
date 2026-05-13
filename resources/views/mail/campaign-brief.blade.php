@component('mail::message')
# Campaign Brief

Dear **{{ $notifiable->name }}**,

You have been selected to participate in the **{{ $campaign->name }}** campaign.

## Campaign Details

@if($campaign->description)
{{ $campaign->description }}

@endif
| Field | Value |
|-------|-------|
| Campaign | {{ $campaign->name }} |
@if($campaign->start_date)
| Start Date | {{ $campaign->start_date->format('d F Y') }} |
@endif
@if($campaign->end_date)
| End Date | {{ $campaign->end_date->format('d F Y') }} |
@endif

## Your Account

| Field | Value |
|-------|-------|
| Platform | {{ $kol->platform_name }} |
| Username | {{ $kol->username }} |
@if($kol->pivot?->deliverable)
| Deliverable | {{ $kol->pivot->deliverable }} |
@endif

Please review the details and prepare your content accordingly.

@component('mail::button', ['url' => config('app.url')])
Open {{ config('app.name') }}
@endcomponent

Thank you for your collaboration!

{{ config('app.name') }}
@endcomponent
