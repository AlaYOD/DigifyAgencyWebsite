<x-mail::message>
# New form submission

Form: {{ $form->getTranslation('name', 'en') }}

@if ($submissionId)
Submission reference: #{{ $submissionId }}
@endif

@foreach ($submissionData as $label => $value)
**{{ str($label)->replace('_', ' ')->title() }}:** {{ is_array($value) ? implode(', ', $value) : $value }}

@endforeach

<x-mail::button :url="url('/admin/form-submissions'.($submissionId ? '/'.$submissionId : ''))">
Open admin
</x-mail::button>
</x-mail::message>
