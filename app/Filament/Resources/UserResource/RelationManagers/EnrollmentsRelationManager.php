<?php

namespace App\Filament\Resources\UserResource\RelationManagers;

use App\Enums\EnrollmentSource;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;

class EnrollmentsRelationManager extends RelationManager
{
    protected static string $relationship = 'enrollments';

    public static function getTitle(\Illuminate\Database\Eloquent\Model $ownerRecord, string $pageClass): string
    {
        return __('admin.enrolled_courses');
    }

    public function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Select::make('course_id')
                ->label(__('admin.course'))
                ->relationship(
                    'course',
                    'title',
                    // Exclude courses the user is already enrolled in (unique user_id+course_id).
                    fn (\Illuminate\Database\Eloquent\Builder $query) => $query->whereNotIn(
                        'id',
                        $this->getOwnerRecord()->enrollments()->pluck('course_id')
                    ),
                )
                ->searchable()
                ->preload()
                ->required(),

            Forms\Components\Select::make('source')
                ->label(__('admin.source'))
                ->options(collect(EnrollmentSource::cases())->mapWithKeys(
                    fn ($s) => [$s->value => $s->label()]
                ))
                ->default(EnrollmentSource::Free->value)
                ->required(),
        ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('course.title')
            ->columns([
                Tables\Columns\TextColumn::make('course.title')
                    ->label(__('admin.course'))
                    ->searchable()
                    ->sortable()
                    ->weight('bold'),

                Tables\Columns\TextColumn::make('source')
                    ->label(__('admin.source'))
                    ->badge()
                    ->formatStateUsing(fn (EnrollmentSource $state) => $state->label())
                    ->color(fn (EnrollmentSource $state) => $state === EnrollmentSource::Code ? 'success' : 'gray'),

                Tables\Columns\TextColumn::make('created_at')
                    ->label(__('admin.enrolled_at'))
                    ->dateTime('Y-m-d')
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('source')
                    ->label(__('admin.source'))
                    ->options(collect(EnrollmentSource::cases())->mapWithKeys(
                        fn ($s) => [$s->value => $s->label()]
                    )),
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make()
                    ->label(__('admin.grant_access')),
            ])
            ->actions([
                Tables\Actions\DeleteAction::make()
                    ->label(__('admin.revoke_access')),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }
}
