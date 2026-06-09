<?php

namespace App\Filament\Widgets;

use App\Enums\AccessCodeStatus;
use App\Enums\CourseStatus;
use App\Enums\UserRole;
use App\Models\AccessCode;
use App\Models\Course;
use App\Models\Enrollment;
use App\Models\User;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class StatsOverview extends BaseWidget
{
    protected function getStats(): array
    {
        return [
            Stat::make(__('admin.courses'), Course::where('status', CourseStatus::Published)->count())
                ->description(__('enums.course_status.published'))
                ->color('success')
                ->icon('heroicon-o-academic-cap'),

            Stat::make(__('enums.role.student'), User::where('role', UserRole::Student)->count())
                ->icon('heroicon-o-users'),

            Stat::make(__('courses.my.title'), Enrollment::count())
                ->icon('heroicon-o-check-badge'),

            Stat::make(__('admin.access_codes'), AccessCode::where('status', AccessCodeStatus::Redeemed)->count())
                ->description(__('enums.code_status.redeemed'))
                ->color('warning')
                ->icon('heroicon-o-key'),
        ];
    }
}
