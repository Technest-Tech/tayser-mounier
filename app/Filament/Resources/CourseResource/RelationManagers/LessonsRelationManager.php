<?php

namespace App\Filament\Resources\CourseResource\RelationManagers;

use App\Enums\LessonSource;
use App\Models\Lesson;
use App\Services\Bunny\BunnySignedUrlService;
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

            // A lesson can carry any combination of a video, a voice file and a
            // PDF. Each block is optional; at least one is enforced on save.
            Forms\Components\Section::make(__('admin.lesson_content'))
                ->description(__('admin.lesson_content_hint'))
                ->columns(2)
                ->schema([
                    Forms\Components\Select::make('source')
                        ->label(__('admin.source'))
                        ->options(collect(LessonSource::cases())->mapWithKeys(
                            fn ($s) => [$s->value => $s->label()]
                        ))
                        ->default(LessonSource::Bunny->value)
                        ->live()
                        ->required(),

                    // The admin uploads the video in the Bunny dashboard, then pastes the
                    // video's URL (or raw GUID) here. We extract the id on save.
                    Forms\Components\TextInput::make('video_id')
                        ->label(fn (Forms\Get $get) => $get('source') === LessonSource::Youtube->value
                            ? __('admin.youtube_id')
                            : __('admin.bunny_id'))
                        ->helperText(fn (Forms\Get $get) => $get('source') === LessonSource::Youtube->value
                            ? __('admin.youtube_id_hint')
                            : __('admin.bunny_id_hint'))
                        ->placeholder(fn (Forms\Get $get) => $get('source') === LessonSource::Youtube->value
                            ? 'https://youtu.be/…'
                            : 'https://iframe.mediadelivery.net/embed/…')
                        ->maxLength(255)
                        ->columnSpanFull(),

                    Forms\Components\FileUpload::make('audio_path')
                        ->label(__('admin.audio_file'))
                        ->helperText(__('admin.audio_file_hint'))
                        ->disk('local')
                        ->directory('lesson-audio')
                        ->acceptedFileTypes(['audio/mpeg', 'audio/mp3', 'audio/wav', 'audio/ogg', 'audio/x-m4a', 'audio/aac'])
                        ->downloadable()
                        ->maxSize(51200),

                    Forms\Components\FileUpload::make('pdf_path')
                        ->label(__('admin.pdf_file'))
                        ->helperText(__('admin.pdf_file_hint'))
                        ->disk('local')
                        ->directory('lesson-pdf')
                        ->acceptedFileTypes(['application/pdf'])
                        ->downloadable()
                        ->maxSize(51200),
                ])
                ->columnSpanFull(),

            Forms\Components\TextInput::make('duration')
                ->label(__('admin.duration_seconds'))
                ->helperText(__('admin.duration_hint'))
                ->numeric()
                ->suffix('s'),

            Forms\Components\Toggle::make('is_preview')
                ->label(__('admin.is_preview'))
                ->helperText(__('admin.is_preview_hint'))
                ->columnSpanFull(),

            // Optional multiple-choice quiz the student takes after the lesson.
            // Each question carries its own choices; mark the correct one.
            Forms\Components\Section::make(__('admin.quiz'))
                ->description(__('admin.quiz_hint'))
                ->icon('heroicon-o-clipboard-document-check')
                ->collapsible()
                ->collapsed(fn (?Lesson $record): bool => ! $record || ! $record->hasQuiz())
                ->schema([
                    Forms\Components\Repeater::make('questions')
                        ->label(__('admin.questions'))
                        ->addActionLabel(__('admin.add_question'))
                        ->collapsible()
                        ->defaultItems(0)
                        ->itemLabel(fn (array $state): ?string => $state['question'] ?? null)
                        ->schema([
                            Forms\Components\TextInput::make('question')
                                ->label(__('admin.question_text'))
                                ->required()
                                ->maxLength(500)
                                ->columnSpanFull(),

                            Forms\Components\Repeater::make('options')
                                ->label(__('admin.options'))
                                ->addActionLabel(__('admin.add_option'))
                                ->minItems(2)
                                ->defaultItems(2)
                                ->columnSpanFull()
                                ->grid(2)
                                ->schema([
                                    Forms\Components\TextInput::make('text')
                                        ->label(__('admin.option_text'))
                                        ->required()
                                        ->maxLength(255),

                                    Forms\Components\Toggle::make('is_correct')
                                        ->label(__('admin.is_correct'))
                                        ->helperText(__('admin.is_correct_hint'))
                                        ->inline(false),
                                ]),
                        ]),
                ])
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
                Tables\Columns\ImageColumn::make('thumbnail')
                    ->label(__('admin.video'))
                    ->state(fn (Lesson $record): ?string => $this->lessonThumbnail($record))
                    ->height(40)
                    ->width(71)
                    ->extraImgAttributes(['class' => 'rounded-md object-cover', 'loading' => 'lazy'])
                    ->defaultImageUrl($this->thumbnailPlaceholder()),
                Tables\Columns\TextColumn::make('title')
                    ->label(__('admin.title'))
                    ->searchable()
                    ->weight('bold'),
                Tables\Columns\TextColumn::make('section')
                    ->label(__('admin.section'))
                    ->badge()
                    ->toggleable(),
                Tables\Columns\TextColumn::make('contents')
                    ->label(__('admin.lesson_content'))
                    ->badge()
                    ->state(fn (Lesson $record): array => $this->lessonContentBadges($record))
                    ->color(fn (string $state): string => match ($state) {
                        __('admin.content_video') => 'success',
                        __('admin.content_audio') => 'warning',
                        __('admin.content_pdf') => 'danger',
                        default => 'gray',
                    }),
                Tables\Columns\IconColumn::make('is_preview')
                    ->label(__('admin.preview'))
                    ->boolean(),
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make()
                    ->label(__('admin.add_lesson'))
                    ->mutateFormDataUsing(fn (array $data): array => $this->normalizeVideoId($data))
                    ->after(fn (Lesson $record, array $data) => $this->syncQuestions($record, $data)),
            ])
            ->actions([
                Tables\Actions\Action::make('preview')
                    ->label(__('admin.preview_video'))
                    ->icon('heroicon-o-play-circle')
                    ->color('gray')
                    ->modalHeading(fn (Lesson $record): string => $record->title)
                    ->modalContent(fn (Lesson $record) => view(
                        'filament.admin.lesson-preview',
                        ['url' => $this->lessonPlayerUrl($record)],
                    ))
                    ->modalWidth('3xl')
                    ->modalSubmitAction(false)
                    ->modalCancelActionLabel(__('admin.close'))
                    ->visible(fn (Lesson $record): bool => filled($record->video_id)),
                Tables\Actions\EditAction::make()
                    ->mutateRecordDataUsing(fn (array $data, Lesson $record): array => $this->fillQuestions($data, $record))
                    ->mutateFormDataUsing(fn (array $data): array => $this->normalizeVideoId($data))
                    ->after(fn (Lesson $record, array $data) => $this->syncQuestions($record, $data)),
                Tables\Actions\DeleteAction::make(),
            ]);
    }

    /**
     * Labels for the content types a lesson currently carries.
     *
     * @return array<int, string>
     */
    protected function lessonContentBadges(Lesson $record): array
    {
        $badges = [];

        if ($record->hasVideo()) {
            $badges[] = __('admin.content_video');
        }
        if ($record->hasAudio()) {
            $badges[] = __('admin.content_audio');
        }
        if ($record->hasPdf()) {
            $badges[] = __('admin.content_pdf');
        }

        return $badges;
    }

    /**
     * Poster image URL for the lesson's video (Bunny thumbnail or YouTube still),
     * or null when there's no usable video id yet.
     */
    protected function lessonThumbnail(Lesson $record): ?string
    {
        if (blank($record->video_id)) {
            return null;
        }

        return match ($record->source) {
            LessonSource::Bunny => app(BunnySignedUrlService::class)->thumbnailUrl($record),
            LessonSource::Youtube => "https://img.youtube.com/vi/{$record->video_id}/mqdefault.jpg",
        };
    }

    /**
     * Embeddable player URL for the in-modal preview.
     */
    protected function lessonPlayerUrl(Lesson $record): string
    {
        return match ($record->source) {
            LessonSource::Bunny => app(BunnySignedUrlService::class)->embedUrl($record),
            LessonSource::Youtube => "https://www.youtube-nocookie.com/embed/{$record->video_id}?rel=0&modestbranding=1",
        };
    }

    /**
     * Accept either a full Bunny/YouTube URL or a raw id in the video_id field
     * and store just the id, so admins can paste straight from the dashboard.
     */
    protected function normalizeVideoId(array $data): array
    {
        $value = trim((string) ($data['video_id'] ?? ''));

        if ($value === '') {
            return $data;
        }

        if (($data['source'] ?? null) === LessonSource::Youtube->value) {
            // youtu.be/ID, watch?v=ID, /embed/ID, /shorts/ID
            if (preg_match('~(?:youtu\.be/|v=|/embed/|/shorts/)([A-Za-z0-9_-]{11})~', $value, $m)) {
                $value = $m[1];
            }
        } else {
            // Pull the GUID out of any Bunny URL; otherwise keep what was pasted.
            if (preg_match('/[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}/i', $value, $m)) {
                $value = $m[0];
            }
        }

        $data['video_id'] = $value;

        return $data;
    }

    /**
     * Load the lesson's questions (and their options) into the form's repeater
     * state when the edit modal opens. Relationship repeaters don't re-hydrate
     * reliably inside RelationManager modals, so we fill them by hand.
     */
    protected function fillQuestions(array $data, Lesson $record): array
    {
        $data['questions'] = $record->questions()->with('options')->get()
            ->map(fn (\App\Models\LessonQuestion $q): array => [
                'question' => $q->question,
                'options' => $q->options
                    ->map(fn (\App\Models\LessonQuestionOption $o): array => [
                        'text' => $o->text,
                        'is_correct' => $o->is_correct,
                    ])
                    ->all(),
            ])
            ->all();

        return $data;
    }

    /**
     * Persist the questions repeater for a lesson. Replaces the existing set so
     * the saved state always matches the form (order follows the array index).
     */
    protected function syncQuestions(Lesson $record, array $data): void
    {
        $record->questions()->delete();

        foreach (array_values($data['questions'] ?? []) as $qOrder => $question) {
            $created = $record->questions()->create([
                'question' => $question['question'] ?? '',
                'order' => $qOrder,
            ]);

            foreach (array_values($question['options'] ?? []) as $oOrder => $option) {
                $created->options()->create([
                    'text' => $option['text'] ?? '',
                    'is_correct' => (bool) ($option['is_correct'] ?? false),
                    'order' => $oOrder,
                ]);
            }
        }
    }

    /**
     * Inline SVG placeholder shown until a video/thumbnail exists.
     */
    protected function thumbnailPlaceholder(): string
    {
        $svg = '<svg xmlns="http://www.w3.org/2000/svg" width="71" height="40" viewBox="0 0 71 40">'
            .'<rect width="71" height="40" rx="6" fill="#e0e7ff"/>'
            .'<path d="M30 14l12 6-12 6z" fill="#6366f1"/></svg>';

        return 'data:image/svg+xml;base64,'.base64_encode($svg);
    }
}
