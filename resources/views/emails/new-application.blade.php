<p>New application received.</p>
<p>Vacancy: {{ $application->jobPosting->getTranslation('title', 'en') }}</p>
<p>Candidate: {{ $application->first_name }} {{ $application->last_name }}</p>
<p>Reference: {{ $application->jobPosting->reference_code }}</p>
<p><a href="{{ $adminUrl }}">Open application in admin</a></p>
