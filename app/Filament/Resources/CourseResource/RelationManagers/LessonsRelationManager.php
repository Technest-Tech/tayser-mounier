<?php

namespace App\Filament\Resources\CourseResource\RelationManagers;

use App\Enums\LessonSource;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;

class LessonsRelationManager extends RelationManager
{
    protected static string $relationship = 'lessons';

    public static function getTitle(\Illuminate\Database\Eloquent\Model $ownerRecord, string $pageClass): string
    {
        return __('admin.lessons');
    }

    public function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\TextInput::make('title')
                ->label(__('admin.title'))
                ->required()
                ->maxLength(255)
                ->columnSpanFull(),

            Forms\Components\TextInput::make('section')
                ->label(__('admin.section'))
                ->helperText(__('admin.section_hint'))
                ->maxLength(255),

            Forms\Components\TextInput::make('order')
                ->label(__('admin.order'))
                ->numeric()
                ->default(0),

            Forms\Components\Select::make('source')
                ->label(__('admin.source'))
                ->options(collect(LessonSource::cases())->mapWithKeys(
                    fn ($s) => [$s->value => $s->label()]
                ))
                ->default(LessonSource::Bunny->value)
                ->live()
                ->required(),

            Forms\Components\TextInput::make('video_id')
                ->label(fn (Forms\Get $get) => $get('source') === LessonSource::Youtube->value
                    ? __('admin.youtube_id')
                    : __('admin.bunny_id'))
                ->helperText(fn (Forms\Get $get) => $get('source') === LessonSource::Youtube->value
                    ? __('admin.youtube_id_hint')
                    : __('admin.bunny_id_hint'))
                ->required()
                ->maxLength(255),

            Forms\Components\TextInput::make('duration')
                ->label(__('admin.duration_seconds'))
                ->numeric()
                ->suffix('s'),

            Forms\Components\Toggle::make('is_preview')
                ->label(__('admin.is_preview'))
                ->helperText(__('admin.is_preview_hint'))
                ->columnSpanFull(),
        ])->columns(2);
    }

    public function table(Table $table): Table
    {
        return $table
            ->defaultSort('order')
            ->reorderable('order')
            ->columns([
                Tables\Columns\TextColumn::make('order')
                    ->label('#')
                    ->sortable(),
                Tables\Columns\TextColumn::make('title')
                    ->label(__('admin.title'))
                    ->searchable()
                    ->weight('bold'),
                Tables\Columns\TextColumn::make('section')
                    ->label(__('admin.section'))
                    ->badge()
                    ->toggleable(),
                Tables\Columns\TextColumn::make('source')
                    ->label(__('admin.source'))
                    ->badge()
                    ->formatStateUsing(fn (LessonSource $state) => $state->label())
                    ->color(fn (LessonSource $state) => $state === LessonSource::Bunny ? 'success' : 'danger'),
                Tables\Columns\IconColumn::make('is_preview')
                    ->label(__('admin.preview'))
                    ->boolean(),
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make()->label(__('admin.add_lesson')),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ]);
    }
}
