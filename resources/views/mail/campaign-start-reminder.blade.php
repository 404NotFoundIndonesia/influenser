@component('mail::message')
# Campaign Starting Tomorrow

Dear **{{ $notifiable->name }}**,

This is a reminder that the campaign **{{ $campaign->name }}** starts tomorrow.

## Campaign Details

| Field | Value |
|-------|-------|
| Campaign | {{ $campaign->name }} |
@if($campaign->start_date)
| Start Date | {{ $campaign->start_date->format('d F Y') }} |
@endif
@if($campaign->end_date)
| End Date | {{ $campaign->end_date->format('d F Y') }} |
@endif
| Status | {{ ucfirst($campaign->status->value ?? $campaign->status) }} |

@if($campaign->description)
{{ $campaign->description }}
@endif

@component('mail::button', ['url' => config('app.url')])
View Campaign
@endcomponent

{{ config('app.name') }}
@endcomponent
