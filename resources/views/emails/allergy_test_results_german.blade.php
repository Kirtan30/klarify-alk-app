@component('mail::message')
# Allergy Test Result German Export

@component('mail::table')
| # | Action  |
|:-:|:-------:|
@foreach($files as $key => $file)
| Export {{ $key + 1 }} | <a target="_blank" href="{{ $file }}">Download</a> |
@endforeach
@endcomponent

<div align="center">
{{ count($files) > 1 ? 'Links' : 'Link'}} will expire in 48 hours
</div>

Thanks,<br>
{{ config('app.name') }}
@endcomponent
