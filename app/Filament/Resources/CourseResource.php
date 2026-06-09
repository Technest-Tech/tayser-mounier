<?php

namespace App\Filament\Resources;

use App\Enums\CourseStatus;
use App\Filament\Resources\CourseResource\Pages;
use App\Filament\Resources\CourseResource\RelationManagers\LessonsRelationManager;
use App\Models\Course;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class CourseResource extends Resource
{
    protected static ?string $model = Course::class;

    protected static ?string $navigationIcon = 'heroicon-o-academic-cap';

    protected static ?int $navigationSort = 1;

    public static function getNavigationLabel(): string
    {
        return __('admin.courses');
    }

    public static function getModelLabel(): string
    {
        return __('admin.course');
    }

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Section::make(__('admin.course_details'))
                ->columns(2)
                ->schema([
                    Forms\Components\TextInput::make('title')
                        ->label(__('admin.title'))
                        ->required()
                        ->maxLength(255)
                        ->columnSpanFull(),

                    Forms\Components\TextInput::make('slug')
                        ->label(__('admin.slug'))
                        ->helperText(__('admin.slug_hint'))
                        ->maxLength(255),

                    Forms\Components\Select::make('category_id')
                        ->label(__('admin.category'))
                        ->relationship('category', 'name')
                        ->searchable()
                        ->preload()
                        ->createOptionForm([
                            Forms\Components\TextInput::make('name')->required(),
                        ]),

                    Forms\Components\Textarea::make('description')
                        ->label(__('admin.description'))
                        ->rows(5)
                        ->columnSpanFull(),

                    Forms\Components\FileUpload::make('thumbnail')
                        ->label(__('admin.thumbnail'))
                        ->image()
                        ->directory('course-thumbnails')
                        ->imageEditor()
                        ->columnSpanFull(),
                ]),

            Forms\Components\Section::make(__('admin.pricing_status'))
                ->columns(2)
                ->schema([
                    Forms\Components\Toggle::make('is_free')
                        ->label(__('admin.is_free'))
                        ->live()
                        ->default(false),

                    Forms\Components\TextInput::make('price')
                        ->label(__('admin.price'))
                        ->numeric()
                        ->prefix(__('messages.common.currency'))
                        ->default(0)
                        ->visible(fn (Forms\Get $get) => ! $get('is_free'))
                        ->required(fn (Forms\Get $get) => ! $get('is_free')),

                    Forms\Components\Select::make('status')
                        ->label(__('admin.status'))
                        ->options(collect(CourseStatus::cases())->mapWithKeys(
                            fn ($c) => [$c->value => $c->label()]
                        ))
                        ->default(CourseStatus::Draft->value)
                        ->required(),
                ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\ImageColumn::make('thumbnail')
                    ->label('')
                    ->square(),

                Tables\Columns\TextColumn::make('title')
                    ->label(__('admin.title'))
                    ->searchable()
                    ->sortable()
                    ->weight('bold'),

                Tables\Columns\TextColumn::make('category.name')
                    ->label(__('admin.category'))
                    ->badge()
                    ->sortable(),

                Tables\Columns\IconColumn::make('is_free')
                    ->label(__('admin.free'))
                    ->boolean(),

                Tables\Columns\TextColumn::make('price')
                    ->label(__('admin.price'))
                    ->money('EGP')
                    ->sortable(),

                Tables\Columns\TextColumn::make('lessons_count')
                    ->label(__('admin.lessons'))
                    ->counts('lessons'),

                Tables\Columns\TextColumn::make('status')
                    ->label(__('admin.status'))
                    ->badge()
                    ->formatStateUsing(fn (CourseStatus $state) => $state->label())
                    ->color(fn (CourseStatus $state) => $state === CourseStatus::Published ? 'success' : 'gray'),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->label(__('admin.status'))
                    ->options(collect(CourseStatus::cases())->mapWithKeys(
                        fn ($c) => [$c->value => $c->label()]
                    )),
                Tables\Filters\TernaryFilter::make('is_free')
                    ->label(__('admin.free')),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            LessonsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListCourses::route('/'),
            'create' => Pages\CreateCourse::route('/create'),
            'edit' => Pages\EditCourse::route('/{record}/edit'),
        ];
    }
}
