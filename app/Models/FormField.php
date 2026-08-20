<?php

namespace App\Models;

use App\Enums\FormFieldType;
use App\Models\Concerns\LogsModelChanges;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Spatie\Translatable\HasTranslations;

/**
 * @property FormFieldType $type
 * @property array<int, array{value?: string, label?: array<string, string>}>|null $options
 * @property array<int, string>|null $rules
 * @property array<string, mixed>|null $conditional_logic
 * @property-read Form $form
 */
class FormField extends Model
{
    use HasTranslations, LogsModelChanges;

    public array $translatable = ['label', 'placeholder', 'help_text'];

    protected $fillable = ['form_id', 'key', 'type', 'label', 'placeholder', 'help_text', 'options', 'rules', 'conditional_logic', 'width', 'sort_order'];

    protected function casts(): array
    {
        return ['type' => FormFieldType::class, 'options' => 'array', 'rules' => 'array', 'conditional_logic' => 'array', 'sort_order' => 'integer'];
    }

    public function form(): BelongsTo
    {
        return $this->belongsTo(Form::class);
    }
}
