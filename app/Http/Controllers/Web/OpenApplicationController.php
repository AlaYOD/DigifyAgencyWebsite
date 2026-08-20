<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Http\Resources\PublicFormResource;
use App\Models\Form;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class OpenApplicationController extends Controller
{
    public function __invoke(Request $request): Response
    {
        $form = Form::query()->where('key', 'open-application')->where('is_active', true)->with('fields')->firstOrFail();

        return Inertia::render('Forms/Standalone', ['form' => (new PublicFormResource($form))->resolve($request)]);
    }
}
