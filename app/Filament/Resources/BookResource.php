<?php

namespace App\Filament\Resources;

use App\Enums\BookStatus;
use App\Filament\Resources\BookResource\Pages;
use App\Models\Book;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class BookResource extends Resource
{
    protected static ?string $model = Book::class;

    protected static ?string $navigationIcon = 'heroicon-o-book-open';

    protected static ?int $navigationSort = 2;

    public static function getNavigationLabel(): string
    {
        return __('admin.books');
    }

    public static function getModelLabel(): string
    {
        return __('admin.book');
    }

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Section::make(__('admin.book_details'))
                ->columns(2)
                ->schema([
                    Forms\Components\TextInput::make('title')
                        ->label(__('admin.title'))
                        ->required()
                        ->maxLength(255)
                        ->columnSpanFull(),

                    Forms\Components\Textarea::make('description')
                        ->label(__('admin.description'))
                        ->rows(5)
                        ->columnSpanFull(),

                    Forms\Components\FileUpload::make('cover')
                        ->label(__('admin.cover'))
                        ->image()
                        ->disk('public')
                        ->directory('book-covers')
                        ->imageEditor(),
                ]),

            Forms\Components\Section::make(__('admin.pricing_status'))
                ->columns(2)
                ->schema([
                    // Book type comes first — it drives which price/file inputs show.
                    Forms\Components\Toggle::make('is_free')
                        ->label(__('admin.is_free_book'))
                        ->helperText(__('admin.is_free_book_hint'))
                        ->live()
                        ->default(false)
                        ->columnSpanFull(),

                    Forms\Components\TextInput::make('price')
                        ->label(__('admin.price'))
                        ->numeric()
                        ->prefix(__('messages.common.currency'))
                        ->default(0)
                        ->visible(fn (Forms\Get $get) => ! $get('is_free'))
                        ->required(fn (Forms\Get $get) => ! $get('is_free')),

                    Forms\Components\Select::make('status')
                        ->label(__('admin.status'))
                        ->options(collect(BookStatus::cases())->mapWithKeys(
                            fn ($c) => [$c->value => $c->label()]
                        ))
                        ->default(BookStatus::Draft->value)
                        ->required(),
                ]),

            Forms\Components\Section::make(__('admin.book_files'))
                ->columns(2)
                ->schema([
                    // Free book → upload the entire book (what visitors read/download).
                    Forms\Components\FileUpload::make('file')
                        ->label(__('admin.full_book_file'))
                        ->helperText(__('admin.full_book_file_hint'))
                        ->disk('local')
                        ->directory('books')
                        ->acceptedFileTypes(['application/pdf'])
                        ->downloadable()
                        ->maxSize(51200)
                        ->columnSpanFull()
                        ->visible(fn (Forms\Get $get) => (bool) $get('is_free'))
                        ->required(fn (Forms\Get $get) => (bool) $get('is_free')),

                    // Paid book → upload the free preview sample only.
                    Forms\Components\FileUpload::make('sample')
                        ->label(__('admin.sample_file'))
                        ->helperText(__('admin.sample_file_hint'))
                        ->disk('local')
                        ->directory('book-samples')
                        ->acceptedFileTypes(['application/pdf'])
                        ->downloadable()
                        ->maxSize(51200)
                        ->columnSpanFull()
                        ->visible(fn (Forms\Get $get) => ! $get('is_free')),
                ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\ImageColumn::make('cover')
                    ->label('')
                    ->square(),

                Tables\Columns\TextColumn::make('title')
                    ->label(__('admin.title'))
                    ->searchable()
                    ->sortable()
                    ->weight('bold'),

                Tables\Columns\TextColumn::make('author')
                    ->label(__('admin.author'))
                    ->searchable()
                    ->toggleable(),

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

                Tables\Columns\TextColumn::make('status')
                    ->label(__('admin.status'))
                    ->badge()
                    ->formatStateUsing(fn (BookStatus $state) => $state->label())
                    ->color(fn (BookStatus $state) => $state === BookStatus::Published ? 'success' : 'gray'),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->label(__('admin.status'))
                    ->options(collect(BookStatus::cases())->mapWithKeys(
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

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListBooks::route('/'),
            'create' => Pages\CreateBook::route('/create'),
            'edit' => Pages\EditBook::route('/{record}/edit'),
        ];
    }
}
