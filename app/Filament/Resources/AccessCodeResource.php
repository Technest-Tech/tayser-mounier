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
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;

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
            // One row per course: aggregate its codes. MIN(id) gives each grouped
            // row a unique key for Filament; MAX(created_at) sorts by latest batch.
            ->modifyQueryUsing(function (Builder $query) {
                $redeemed = AccessCodeStatus::Redeemed->value;

                return $query
                    ->select('access_codes.course_id')
                    ->selectRaw('MIN(access_codes.id) as id')
                    ->selectRaw('COUNT(*) as total_codes')
                    ->selectRaw("SUM(CASE WHEN status = '{$redeemed}' THEN 1 ELSE 0 END) as used_codes")
                    ->selectRaw('MAX(access_codes.created_at) as created_at')
                    ->groupBy('access_codes.course_id');
            })
            ->defaultSort('created_at', 'desc')
            ->columns([
                Tables\Columns\TextColumn::make('course.title')
                    ->label(__('admin.course'))
                    ->weight('bold')
                    ->description(fn ($record) => __('admin.latest_batch').': '.optional($record->created_at)->translatedFormat('d M Y')),

                Tables\Columns\TextColumn::make('total_codes')
                    ->label(__('admin.total_codes'))
                    ->badge()
                    ->color('gray'),

                Tables\Columns\TextColumn::make('used_codes')
                    ->label(__('admin.used_codes'))
                    ->badge()
                    ->color('warning'),

                Tables\Columns\TextColumn::make('unused_codes')
                    ->label(__('admin.unused_codes'))
                    ->badge()
                    ->color('success')
                    ->state(fn ($record) => (int) $record->total_codes - (int) $record->used_codes),
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
                    ->action(function (array $data) {
                        $course = Course::findOrFail($data['course_id']);
                        $expires = filled($data['expires_at'] ?? null)
                            ? Carbon::parse($data['expires_at'])->endOfDay()
                            : null;

                        $result = app(GenerateCodeBatchAction::class)
                            ->execute($course, (int) $data['quantity'], $expires);

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
                // Popup: all codes for this course + usage history.
                Tables\Actions\Action::make('viewCodes')
                    ->label(__('admin.view_codes'))
                    ->icon('heroicon-o-eye')
                    ->color('primary')
                    ->modalHeading(fn ($record) => $record->course?->title)
                    ->modalWidth('5xl')
                    ->modalSubmitAction(false)
                    ->modalCancelActionLabel(__('admin.close'))
                    ->modalContent(fn ($record) => view('filament.access-codes-modal', [
                        'codes' => static::codesForCourse($record->course_id),
                    ])),

                // Download all codes for this course as CSV.
                Tables\Actions\Action::make('exportCodes')
                    ->label(__('admin.export_csv'))
                    ->icon('heroicon-o-arrow-down-tray')
                    ->color('gray')
                    ->action(function ($record) {
                        $codes = static::codesForCourse($record->course_id);
                        $title = $record->course?->title ?? 'course';

                        return response()->streamDownload(function () use ($codes) {
                            $out = fopen('php://output', 'w');
                            fputcsv($out, ['code', 'status', 'redeemed_by', 'redeemed_at', 'expires_at']);
                            foreach ($codes as $c) {
                                fputcsv($out, [
                                    $c->plainCode() ?? '—',
                                    $c->status->value,
                                    optional($c->redeemer)->name ?? '',
                                    optional($c->redeemed_at)?->toDateTimeString() ?? '',
                                    optional($c->expires_at)?->toDateTimeString() ?? '',
                                ]);
                            }
                            fclose($out);
                        }, 'codes-'.Str::slug($title).'.csv', ['Content-Type' => 'text/csv']);
                    }),

                // Revoke all still-unused codes for this course.
                Tables\Actions\Action::make('revokeUnused')
                    ->label(__('admin.revoke_unused'))
                    ->icon('heroicon-o-trash')
                    ->color('danger')
                    ->requiresConfirmation()
                    ->action(function ($record) {
                        AccessCode::where('course_id', $record->course_id)
                            ->where('status', AccessCodeStatus::Unused)
                            ->delete();
                    }),
            ]);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Collection<int, AccessCode>
     */
    protected static function codesForCourse(int $courseId)
    {
        return AccessCode::where('course_id', $courseId)
            ->with('redeemer')
            ->orderByRaw("status = '".AccessCodeStatus::Unused->value."' desc")
            ->orderByDesc('created_at')
            ->get();
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListAccessCodes::route('/'),
        ];
    }

    public static function canCreate(): bool
    {
        return false;
    }
}
