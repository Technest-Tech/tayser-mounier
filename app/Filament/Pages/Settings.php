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
            'primary_color'    => Setting::get('primary_color', '#4f46e5'),
            'secondary_color'  => Setting::get('secondary_color', '#f59e0b'),
            'site_logo'        => Setting::get('site_logo'),
            'site_title'       => Setting::get('site_title', __('messages.app_name')),
            'hero_eyebrow'     => Setting::get('hero_eyebrow', __('messages.home.eyebrow')),
            'hero_title'       => Setting::get('hero_title', __('messages.home.hero_title')),
            'hero_subtitle'    => Setting::get('hero_subtitle', __('messages.home.hero_subtitle')),
            'hero_button_text' => Setting::get('hero_button_text', __('messages.home.browse_courses')),
            'stat_courses'     => Setting::get('stat_courses'),
            'stat_lessons'     => Setting::get('stat_lessons'),
            'stat_students'    => Setting::get('stat_students'),
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

                Forms\Components\Section::make('هوية الموقع')
                    ->description('الشعار والاسم اللذان يظهران في أعلى الموقع.')
                    ->schema([
                        Forms\Components\FileUpload::make('site_logo')
                            ->label('شعار الموقع')
                            ->image()
                            ->disk('public')
                            ->directory('site')
                            ->imageEditor()
                            ->helperText('اتركه فارغاً لعرض الحرف الافتراضي. يُفضَّل صورة مربعة بخلفية شفافة.'),
                        Forms\Components\TextInput::make('site_title')
                            ->label('اسم الموقع')
                            ->helperText('يظهر بجوار الشعار وفي عنوان المتصفح.')
                            ->required(),
                    ])
                    ->columns(2),

                Forms\Components\Section::make('القسم الرئيسي (Hero)')
                    ->description('النصوص الظاهرة في أعلى الصفحة الرئيسية.')
                    ->schema([
                        Forms\Components\TextInput::make('hero_eyebrow')
                            ->label('النص التمهيدي')
                            ->required(),
                        Forms\Components\TextInput::make('hero_button_text')
                            ->label('نص الزر الرئيسي')
                            ->required(),
                        Forms\Components\TextInput::make('hero_title')
                            ->label('العنوان الرئيسي')
                            ->columnSpanFull()
                            ->required(),
                        Forms\Components\Textarea::make('hero_subtitle')
                            ->label('العنوان الفرعي')
                            ->rows(3)
                            ->columnSpanFull()
                            ->required(),
                    ])
                    ->columns(2),

                Forms\Components\Section::make('أرقام الإحصائيات')
                    ->description('اتركها فارغة لحساب الأرقام تلقائياً من قاعدة البيانات.')
                    ->schema([
                        Forms\Components\TextInput::make('stat_courses')
                            ->label('عدد الدورات')
                            ->numeric()
                            ->minValue(0),
                        Forms\Components\TextInput::make('stat_lessons')
                            ->label('عدد الدروس')
                            ->numeric()
                            ->minValue(0),
                        Forms\Components\TextInput::make('stat_students')
                            ->label('عدد الطلاب')
                            ->numeric()
                            ->minValue(0),
                    ])
                    ->columns(3),
            ])
            ->statePath('data');
    }

    public function save(): void
    {
        $data = $this->form->getState();

        foreach ([
            'primary_color', 'secondary_color',
            'site_logo', 'site_title',
            'hero_eyebrow', 'hero_title', 'hero_subtitle', 'hero_button_text',
            'stat_courses', 'stat_lessons', 'stat_students',
        ] as $key) {
            Setting::set($key, $data[$key] ?? null);
        }

        Notification::make()
            ->title('تم حفظ الإعدادات بنجاح')
            ->success()
            ->send();
    }
}
