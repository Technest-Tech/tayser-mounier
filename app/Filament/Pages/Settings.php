<?php

namespace App\Filament\Pages;

use App\Models\Setting;
use Filament\Forms;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Pages\Page;

class Settings extends Page implements HasForms
{
    use InteractsWithForms;

    protected static ?string $navigationIcon = 'heroicon-o-cog-6-tooth';
    protected static string $view = 'filament.pages.settings';
    protected static ?string $navigationLabel = 'إعدادات الموقع';
    protected static ?string $title = 'إعدادات الموقع';
    protected static ?int $navigationSort = 99;

    public ?array $data = [];

    public function mount(): void
    {
        $this->form->fill([
            'primary_color'   => Setting::get('primary_color', '#4f46e5'),
            'secondary_color' => Setting::get('secondary_color', '#f59e0b'),
        ]);
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('ألوان الموقع')
                    ->description('اختر الألوان الرئيسية للموقع. تُطبَّق فوراً على جميع الصفحات.')
                    ->schema([
                        Forms\Components\ColorPicker::make('primary_color')
                            ->label('اللون الرئيسي')
                            ->helperText('يُستخدم للأزرار والروابط والعناصر التفاعلية الرئيسية.')
                            ->required(),
                        Forms\Components\ColorPicker::make('secondary_color')
                            ->label('اللون الثانوي')
                            ->helperText('يُستخدم للمميزات والشارات والعناصر المساعدة.')
                            ->required(),
                    ])
                    ->columns(2),
            ])
            ->statePath('data');
    }

    public function save(): void
    {
        $data = $this->form->getState();

        Setting::set('primary_color', $data['primary_color']);
        Setting::set('secondary_color', $data['secondary_color']);

        Notification::make()
            ->title('تم حفظ الإعدادات بنجاح')
            ->success()
            ->send();
    }
}
