@component('mail::message')
# Hey,

You have some incomplete todos.

@component('mail::table')
|id|Title|Due date|
|:--------|:--------|:--------|
@foreach($reminders as $reminder)
|{{ $reminder->id }}|{{ $reminder->title }}|{{ $reminder->due_date }}|
@endforeach
@endcomponent

Thanks,<br>
{{ config('app.name') }}
@endcomponent
