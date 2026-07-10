<?php

namespace App\Filament\Resources\Suppliers\RelationManagers;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class ReviewsRelationManager extends RelationManager
{
    protected static string $relationship = 'reviews';

    public function form(Schema $schema): Schema
    {
        return $schema->components([]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('comentario')
            ->columns([
                TextColumn::make('user.name')
                    ->label('Membro'),
                TextColumn::make('nota')
                    ->label('Nota'),
                TextColumn::make('destaques')
                    ->label('Destaques')
                    ->formatStateUsing(fn (?array $state) => collect($state)
                        ->map(fn ($key) => config("suppliers.destaques.{$key}.label", $key))
                        ->join(', ')),
                TextColumn::make('comentario')
                    ->wrap(),
                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable(),
            ])
            ->recordActions([
                DeleteAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
