<?php

namespace App\Filament\Resources;

use App\Enums\UserRole;
use App\Filament\Resources\UserResource\Pages;
use App\Filament\Resources\UserResource\RelationManagers\EnrollmentsRelationManager;
use App\Models\User;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Hash;

class UserResource extends Resource
{
    protected static ?string $model = User::class;

    protected static ?string $navigationIcon = 'heroicon-o-users';

    protected static ?int $navigationSort = 4;

    public static function getNavigationLabel(): string
    {
        return __('admin.users');
    }

    public static function getModelLabel(): string
    {
        return __('admin.user');
    }

    public static function getPluralModelLabel(): string
    {
        return __('admin.users');
    }

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Section::make(__('admin.account_details'))
                ->columns(2)
                ->schema([
                    Forms\Components\TextInput::make('name')
                        ->label(__('admin.name'))
                        ->required()
                        ->maxLength(255),

                    Forms\Components\TextInput::make('email')
                        ->label(__('admin.email'))
                        ->email()
                        ->required()
                        ->unique(ignoreRecord: true)
                        ->maxLength(255),

                    Forms\Components\Select::make('role')
                        ->label(__('admin.role'))
                        ->options(collect(UserRole::cases())->mapWithKeys(
                            fn ($r) => [$r->value => $r->label()]
                        ))
                        ->default(UserRole::Student->value)
                        ->required(),

                    Forms\Components\TextInput::make('password')
                        ->label(__('admin.password'))
                        ->password()
                        ->revealable()
                        ->dehydrateStateUsing(fn (string $state): string => Hash::make($state))
                        ->dehydrated(fn (?string $state): bool => filled($state))
                        ->required(fn (string $operation): bool => $operation === 'create')
                        ->helperText(fn (string $operation) => $operation === 'edit' ? __('admin.password_edit_hint') : null)
                        ->maxLength(255),
                ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label(__('admin.name'))
                    ->searchable()
                    ->sortable()
                    ->weight('bold'),

                Tables\Columns\TextColumn::make('email')
                    ->label(__('admin.email'))
                    ->searchable()
                    ->sortable()
                    ->copyable(),

                Tables\Columns\TextColumn::make('role')
                    ->label(__('admin.role'))
                    ->badge()
                    ->formatStateUsing(fn (UserRole $state) => $state->label())
                    ->color(fn (UserRole $state) => $state === UserRole::Admin ? 'warning' : 'gray'),

                Tables\Columns\TextColumn::make('enrollments_count')
                    ->label(__('admin.enrolled_courses'))
                    ->counts('enrollments')
                    ->sortable()
                    ->badge()
                    ->color('success'),

                Tables\Columns\IconColumn::make('email_verified_at')
                    ->label(__('admin.verified'))
                    ->boolean()
                    ->sortable(),

                Tables\Columns\TextColumn::make('created_at')
                    ->label(__('admin.created_at'))
                    ->dateTime('Y-m-d')
                    ->sortable()
                    ->toggleable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('role')
                    ->label(__('admin.role'))
                    ->options(collect(UserRole::cases())->mapWithKeys(
                        fn ($r) => [$r->value => $r->label()]
                    )),
                Tables\Filters\TernaryFilter::make('email_verified_at')
                    ->label(__('admin.verified'))
                    ->nullable(),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make()
                    ->visible(fn (User $record): bool => $record->id !== auth()->id()),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make()
                        ->before(function (Tables\Actions\DeleteBulkAction $action, \Illuminate\Support\Collection $records) {
                            // Never allow an admin to delete their own account in bulk.
                            $records->forget($records->search(fn (User $u) => $u->id === auth()->id()));
                        }),
                ]),
            ])
            ->defaultSort('created_at', 'desc');
    }

    public static function getRelations(): array
    {
        return [
            EnrollmentsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListUsers::route('/'),
            'create' => Pages\CreateUser::route('/create'),
            'edit' => Pages\EditUser::route('/{record}/edit'),
        ];
    }
}
