<?php

namespace App\Filament\Resources\CourseResource\RelationManagers;

use App\Enums\LessonSource;
use App\Services\Bunny\BunnyStreamService;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Storage;

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

            // Upload a file straight to Bunny Stream (bunny source only). Tucked
            // into a collapsed section so it doesn't dominate the modal — expand
            // it only when uploading. On save the file is streamed to Bunny and
            // the returned guid fills `video_id`.
            Forms\Components\Section::make(__('admin.bunny_upload'))
                ->description(__('admin.bunny_upload_hint'))
                ->icon('heroicon-o-cloud-arrow-up')
                ->collapsible()
                ->collapsed()
                ->visible(fn (Forms\Get $get) => $get('source') === LessonSource::Bunny->value)
                ->columnSpanFull()
                ->schema([
                    Forms\Components\FileUpload::make('video_file')
                        ->hiddenLabel()
                        ->disk('local')
                        ->directory('bunny-uploads')
                        ->acceptedFileTypes(['video/mp4', 'video/quicktime', 'video/x-matroska', 'video/webm'])
                        ->maxSize(2 * 1024 * 1024) // 2 GB (kB)
                        ->previewable(false)
                        ->columnSpanFull(),
                ]),

            Forms\Components\TextInput::make('video_id')
                ->label(fn (Forms\Get $get) => $get('source') === LessonSource::Youtube->value
                    ? __('admin.youtube_id')
                    : __('admin.bunny_id'))
                ->helperText(fn (Forms\Get $get) => $get('source') === LessonSource::Youtube->value
                    ? __('admin.youtube_id_hint')
                    : __('admin.bunny_id_hint'))
                // Optional when a file is being uploaded — the upload fills it in.
                ->required(fn (Forms\Get $get) => blank($get('video_file')))
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
                Tables\Actions\CreateAction::make()
                    ->label(__('admin.add_lesson'))
                    ->mutateFormDataUsing(fn (array $data): array => $this->handleBunnyUpload($data)),
            ])
            ->actions([
                Tables\Actions\EditAction::make()
                    ->mutateFormDataUsing(fn (array $data): array => $this->handleBunnyUpload($data)),
                Tables\Actions\DeleteAction::make(),
            ]);
    }

    /**
     * If the admin attached a video file for a Bunny lesson, stream it up to
     * Bunny Stream and replace the form data's `video_id` with the new guid.
     * The temp upload is removed afterwards and the non-column `video_file`
     * key is stripped so it never reaches the model.
     */
    protected function handleBunnyUpload(array $data): array
    {
        $path = $data['video_file'] ?? null;
        unset($data['video_file']);

        if (blank($path) || ($data['source'] ?? null) !== LessonSource::Bunny->value) {
            return $data;
        }

        try {
            $guid = app(BunnyStreamService::class)->upload(
                $data['title'] ?? 'Lesson video',
                Storage::disk('local')->path($path),
            );

            $data['video_id'] = $guid;

            Notification::make()
                ->title(__('admin.bunny_upload_done'))
                ->success()
                ->send();
        } catch (\Throwable $e) {
            Notification::make()
                ->title(__('admin.bunny_upload_failed'))
                ->body($e->getMessage())
                ->danger()
                ->persistent()
                ->send();

            // Stop the save so the admin can retry rather than persisting a
            // lesson that points at no video.
            throw new \Filament\Support\Exceptions\Halt;
        } finally {
            Storage::disk('local')->delete($path);
        }

        return $data;
    }
}
