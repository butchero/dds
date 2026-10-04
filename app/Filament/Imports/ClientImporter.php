<?php

namespace App\Filament\Imports;

use App\Models\User;
use Filament\Actions\Imports\ImportColumn;
use Filament\Actions\Imports\Importer;
use Filament\Actions\Imports\Models\Import;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Number;
use Illuminate\Support\Str;

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
            ImportColumn::make('email')
                ->label('E-mail')
                ->requiredMapping()
                ->rules(['required', 'email', 'max:255']),
            ImportColumn::make('phone')
                ->label('Telefon')
                ->rules(['nullable', 'string', 'max:30']),
            ImportColumn::make('equipment_name')
                ->label('Echipament')
                ->fillRecordUsing(fn () => null)
                ->rules(['nullable', 'string', 'max:255']),
            ImportColumn::make('last_revision_on')
                ->label('Data ultimei revizii')
                ->fillRecordUsing(fn () => null)
                ->rules(['nullable', 'date']),
            ImportColumn::make('interval_months')
                ->label('Interval luni')
                ->fillRecordUsing(fn () => null)
                ->numeric()
                ->rules(['nullable', 'integer', 'min:1']),
        ];
    }

    public function resolveRecord(): User
    {
        return User::firstOrNew([
            'email' => $this->data['email'],
        ]);
    }

    protected function beforeCreate(): void
    {
        $this->record->role = 'client';
        $this->record->password = Hash::make(Str::password(12));
    }

    protected function afterSave(): void
    {
        if (blank($this->data['equipment_name'] ?? null)) {
            return;
        }

        $this->record->equipment()->updateOrCreate(
            ['name' => $this->data['equipment_name']],
            [
                'last_revision_on' => $this->data['last_revision_on'] ?: null,
                'interval_months' => $this->data['interval_months'] ?: 24,
            ],
        );
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
