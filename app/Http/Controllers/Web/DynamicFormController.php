<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Http\Requests\DynamicFormRequest;
use App\Models\Form;
use App\Services\CaptchaVerifier;
use App\Services\FormSubmissionService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Validation\ValidationException;

class DynamicFormController extends Controller
{
    public function store(DynamicFormRequest $request, Form $form, FormSubmissionService $submissions, CaptchaVerifier $captcha): RedirectResponse
    {
        if (filled($request->input('_website'))) {
            return back()->with('form_success', $form->getTranslation('success_message', app()->getLocale()));
        }

        if ($form->captcha_enabled && ! $captcha->verify((string) $request->input('captcha_token'), $request->ip())) {
            throw ValidationException::withMessages(['captcha_token' => __('Captcha verification failed. Please try again.')]);
        }

        $submissions->submit($form, $request->validated(), (string) $request->ip(), $request->userAgent(), $request->headers->get('referer'));

        return back()->with('form_success', $form->getTranslation('success_message', app()->getLocale()));
    }
}
