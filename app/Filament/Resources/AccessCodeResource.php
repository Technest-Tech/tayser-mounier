<?php

namespace App\Filament\Resources;

use App\Actions\GenerateCodeBatchAction;
use App\Enums\AccessCodeStatus;
use App\Filament\Resources\AccessCodeResource\Pages;
use App\Models\AccessCode;
use App\Models\Course;
use Filament\Forms;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\Carbon;

class AccessCodeResource extends Resource
{
    protected static ?string $model = AccessCode::class;

    protected static ?string $navigationIcon = 'heroicon-o-key';

    protected static ?int $navigationSort = 3;

    public static function getNavigationLabel(): string
    {
        return __('admin.access_codes');
    }

    public static function getModelLabel(): string
    {
        return __('admin.access_code');
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('created_at', 'desc')
            ->columns([
                Tables\Columns\TextColumn::make('course.title')
                    ->label(__('admin.course'))
                    ->searchable()
                    ->sortable()
                    ->weight('bold'),

                Tables\Columns\TextColumn::make('batch_id')
                    ->label(__('admin.batch'))
                    ->formatStateUsing(fn (string $state) => substr($state, 0, 8))
                    ->badge()
                    ->copyable()
                    ->toggleable(),

                Tables\Columns\TextColumn::make('status')
                    ->label(__('admin.status'))
                    ->badge()
                    ->formatStateUsing(fn (AccessCodeStatus $state) => $state->label())
                    ->color(fn (AccessCodeStatus $state) => $state === AccessCodeStatus::Unused ? 'success' : 'gray'),

                Tables\Columns\TextColumn::make('redeemer.name')
                    ->label(__('admin.redeemed_by'))
                    ->placeholder('—'),

                Tables\Columns\TextColumn::make('redeemed_at')
                    ->label(__('admin.redeemed_at'))
                    ->dateTime()
                    ->placeholder('—')
                    ->toggleable(),

                Tables\Columns\TextColumn::make('expires_at')
                    ->label(__('admin.expires_at'))
                    ->dateTime()
                    ->placeholder(__('admin.never'))
                    ->toggleable(),

                Tables\Columns\TextColumn::make('created_at')
                    ->label(__('admin.created_at'))
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('course_id')
                    ->label(__('admin.course'))
                    ->relationship('course', 'title')
                    ->searchable()
                    ->preload(),
                Tables\Filters\SelectFilter::make('status')
                    ->label(__('admin.status'))
                    ->options(collect(AccessCodeStatus::cases())->mapWithKeys(
                        fn ($s) => [$s->value => $s->label()]
                    )),
            ])
            ->headerActions([
                Tables\Actions\Action::make('generate')
                    ->label(__('admin.generate_codes'))
                    ->icon('heroicon-o-sparkles')
                    ->form([
                        Forms\Components\Select::make('course_id')
                            ->label(__('admin.course'))
                            ->relationship('course', 'title')
                            ->searchable()
                            ->preload()
                            ->required(),
                        Forms\Components\TextInput::make('quantity')
                            ->label(__('admin.quantity'))
                            ->numeric()
                            ->minValue(1)
                            ->maxValue(1000)
                            ->default(10)
                            ->required(),
                        Forms\Components\DatePicker::make('expires_at')
                            ->label(__('admin.expires_at'))
                            ->helperText(__('admin.expires_hint'))
                            ->minDate(now()),
                    ])
                    ->action(function (array $data, GenerateCodeBatchAction $action) {
                        $course = Course::findOrFail($data['course_id']);
                        $expires = filled($data['expires_at'] ?? null)
                            ? Carbon::parse($data['expires_at'])->endOfDay()
                            : null;

                        $result = $action->execute($course, (int) $data['quantity'], $expires);

                        // Stream the plaintext codes as CSV — they are shown only once.
                        $filename = 'codes-'.substr($result['batch_id'], 0, 8).'.csv';

                        return response()->streamDownload(function () use ($result, $course) {
                            $out = fopen('php://output', 'w');
                            fputcsv($out, ['code', 'course', 'batch_id']);
                            foreach ($result['codes'] as $code) {
                                fputcsv($out, [$code, $course->title, $result['batch_id']]);
                            }
                            fclose($out);
                        }, $filename, ['Content-Type' => 'text/csv']);
                    }),
            ])
            ->actions([
                // Allow revoking an unused code.
                Tables\Actions\DeleteAction::make()
                    ->label(__('admin.revoke'))
                    ->visible(fn (AccessCode $record) => $record->status === AccessCodeStatus::Unused),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make()->label(__('admin.revoke')),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListAccessCodes::route('/'),
        ];
    }

    public static function canCreate(): bool
    {
        // Codes are only created via the "Generate codes" action.
        return false;
    }
}
