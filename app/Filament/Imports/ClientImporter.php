<?php

namespace App\Filament\Imports;

use App\Models\User;
use App\Support\PhoneNumber;
use Filament\Actions\Imports\Exceptions\RowImportFailedException;
use Filament\Actions\Imports\ImportColumn;
use Filament\Actions\Imports\Importer;
use Filament\Actions\Imports\Models\Import;
use Illuminate\Support\Number;

class ClientImporter extends Importer
{
    protected static ?string $model = User::class;

    public static function getColumns(): array
    {
        return [
            ImportColumn::make('name')
                ->label('Nume')
                ->requiredMapping()
                ->rules(['required', 'string', 'max:255']),
            ImportColumn::make('phone')
                ->label('Telefon')
                ->requiredMapping()
                ->castStateUsing(function (mixed $state): mixed {
                    if (blank($state)) {
                        return $state;
                    }

                    return PhoneNumber::normalize((string) $state) ?? $state;
                })
                ->rules(['required', 'regex:/^07\d{8}$/']),
            ImportColumn::make('email')
                ->label('E-mail')
                ->ignoreBlankState()
                ->rules(['nullable', 'email', 'max:255']),
        ];
    }

    public function resolveRecord(): User
    {
        $phone = PhoneNumber::normalize((string) ($this->data['phone'] ?? ''));

        if ($phone === null) {
            return new User;
        }

        return User::query()->firstOrNew([
            'phone' => $phone,
        ]);
    }

    public function fillRecord(): void
    {
        if ($this->record->exists && ! $this->record->isUnclaimed()) {
            return;
        }

        parent::fillRecord();
    }

    protected function beforeCreate(): void
    {
        $this->record->role = 'client';
    }

    protected function beforeSave(): void
    {
        $email = $this->data['email'] ?? null;

        if (blank($email) || ($this->record->exists && ! $this->record->isUnclaimed())) {
            return;
        }

        $taken = User::query()
            ->where('email', $email)
            ->when($this->record->exists, fn ($query) => $query->whereKeyNot($this->record->id))
            ->exists();

        if ($taken) {
            throw new RowImportFailedException('E-mailul este deja folosit.');
        }
    }

    public static function getCompletedNotificationBody(Import $import): string
    {
        $body = 'Importul s-a terminat: '.Number::format($import->successful_rows).' '.str('rând')->plural($import->successful_rows).' importate.';

        if ($failedRowsCount = $import->getFailedRowsCount()) {
            $body .= ' '.Number::format($failedRowsCount).' '.str('rând')->plural($failedRowsCount).' au eșuat.';
        }

        return $body;
    }
}
