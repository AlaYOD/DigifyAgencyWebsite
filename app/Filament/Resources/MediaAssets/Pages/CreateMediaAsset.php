<?php

namespace App\Filament\Resources\MediaAssets\Pages;

use App\Filament\Resources\MediaAssets\MediaAssetResource;
use App\Models\MediaAsset;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Database\Eloquent\Model;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;

class CreateMediaAsset extends CreateRecord
{
    protected static string $resource = MediaAssetResource::class;

    protected function handleRecordCreation(array $data): Model
    {
        $upload = $data['upload'] ?? null;
        unset($data['upload']);
        $data['uploaded_by'] = auth()->id();
        $record = MediaAsset::create($data);

        if ($upload instanceof TemporaryUploadedFile) {
            $record->addMedia($upload->getRealPath())->usingName($record->name)->usingFileName($upload->getClientOriginalName())->toMediaCollection('default');
        }

        return $record;
    }
}
