<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class RedirectFallbackController extends Controller
{
    public function __invoke(Request $request): never
    {
        abort(404);
    }
}
