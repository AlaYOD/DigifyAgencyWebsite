<?php

namespace App\Observers;

use App\Models\Locale;

class LocaleObserver
{
    public function saved(Locale $locale): void
    {
        if ($locale->is_default) {
            Locale::query()->whereKeyNot($locale->getKey())->update(['is_default' => false]);
        }
    }
}
