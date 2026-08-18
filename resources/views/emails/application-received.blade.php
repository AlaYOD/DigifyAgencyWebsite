@php($arabic = $application->locale === 'ar')
<p>{{ $arabic ? 'مرحبًا '.$application->first_name.' '.$application->last_name.'،' : 'Hello '.$application->first_name.' '.$application->last_name.',' }}</p>
<p>{{ $arabic ? 'تم استلام طلبك لوظيفة '.$vacancyTitle.' بنجاح.' : 'We received your application for '.$vacancyTitle.'.' }}</p>
<p>{{ $arabic ? 'الرقم المرجعي: ' : 'Reference: ' }}{{ $application->jobPosting->reference_code }}</p>
<p>{{ $arabic ? 'سيقوم فريقنا بمراجعة طلبك والتواصل معك إذا انتقلت إلى المرحلة التالية.' : 'Our team will review your application and contact you if you move to the next stage.' }}</p>
