<?php

namespace App\Filament\RelationManagers;

use App\Models\Revision;
use Filament\Actions\Action;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class RevisionsRelationManager extends RelationManager
{
    protected static string $relationship = 'revisions';

    protected static ?string $title = 'Revision history';

    public function table(Table $table): Table
    {
        return $table->columns([
            TextColumn::make('created_at')->dateTime()->sortable(), TextColumn::make('user.name')->label('Changed by')->placeholder('System'), TextColumn::make('label'),
        ])->recordActions([
            Action::make('restore')->requiresConfirmation()->visible(fn (): bool => auth()->user()?->can('update', $this->getOwnerRecord()) ?? false)
                ->action(function (Revision $record): void {
                    $payload = $record->getAttribute('payload');

                    if (is_array($payload)) {
                        $this->getOwnerRecord()->update($payload);
                    }
                }),
        ])->defaultSort('created_at', 'desc');
    }
}
