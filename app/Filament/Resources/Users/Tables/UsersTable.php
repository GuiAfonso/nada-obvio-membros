<?php

namespace App\Filament\Resources\Users\Tables;

use App\Mail\WelcomeMemberMail;
use App\Models\User;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Notifications\Notification;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

class UsersTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->label('Nome')
                    ->searchable(),
                TextColumn::make('email')
                    ->searchable(),
                IconColumn::make('ativo')
                    ->label('Ativo')
                    ->boolean(),
                IconColumn::make('is_admin')
                    ->label('Admin')
                    ->boolean(),
                TextColumn::make('hotmart_transaction')
                    ->label('Transação Hotmart')
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('created_at')
                    ->label('Criado em')
                    ->dateTime()
                    ->sortable(),
            ])
            ->filters([
                //
            ])
            ->recordActions([
                EditAction::make(),
                Action::make('reenviarCredenciais')
                    ->label('Reenviar credenciais')
                    ->requiresConfirmation()
                    ->action(function (User $record) {
                        $password = Str::password(12);
                        $record->update(['password' => Hash::make($password)]);
                        Mail::to($record)->send(new WelcomeMemberMail($record, $password));

                        Notification::make()
                            ->title('Credenciais reenviadas para '.$record->email)
                            ->success()
                            ->send();
                    }),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
