<?php

namespace Database\Seeders;

use App\Enums\CourseStatus;
use App\Enums\LessonSource;
use App\Enums\UserRole;
use App\Models\Category;
use App\Models\Course;
use App\Models\Lesson;
use App\Models\User;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // Admin (Filament panel) ------------------------------------------------
        User::factory()->admin()->create([
            'name' => 'Admin',
            'email' => 'admin@example.com',
        ]);

        // A demo student --------------------------------------------------------
        User::factory()->create([
            'name' => 'Student',
            'email' => 'student@example.com',
            'role' => UserRole::Student,
        ]);

        // Categories — Arabic (explicit slugs; Str::slug yields nothing for Arabic)
        $tajweed = Category::create([
            'name' => 'التجويد وعلوم القرآن',
            'slug' => 'tajweed-quran',
        ]);

        $arabic = Category::create([
            'name' => 'اللغة العربية',
            'slug' => 'arabic-language',
        ]);

        // Courses — Arabic ------------------------------------------------------
        $courses = [
            [
                'category_id' => $tajweed->id,
                'title' => 'أحكام التجويد للمبتدئين',
                'slug' => 'tajweed-for-beginners',
                'description' => 'دورة ميسّرة تأخذ بيدك من الصفر لإتقان مخارج الحروف وأحكام النون الساكنة والتنوين والمدود، مع تطبيقات عملية على آيات قصيرة.',
                'price' => 0,
                'is_free' => true,
                'status' => CourseStatus::Published,
                'lessons' => [
                    'مقدمة الدورة وأهمية التجويد',
                    'مخارج الحروف',
                    'أحكام النون الساكنة والتنوين',
                    'أحكام الميم الساكنة',
                    'المدود وأنواعها',
                ],
            ],
            [
                'category_id' => $tajweed->id,
                'title' => 'تحفيظ القرآن الكريم — جزء عمّ',
                'slug' => 'hifdh-juz-amma',
                'description' => 'برنامج متكامل لحفظ جزء عمّ حفظًا متقنًا مع المراجعة وضبط المتشابهات وتطبيق أحكام التجويد أثناء الحفظ.',
                'price' => 199.00,
                'is_free' => false,
                'status' => CourseStatus::Published,
                'lessons' => [
                    'منهج الحفظ والمراجعة',
                    'سورة النبأ',
                    'سورة النازعات',
                    'سورة عبس',
                    'تثبيت ومراجعة عامة',
                ],
            ],
            [
                'category_id' => $arabic->id,
                'title' => 'النحو العربي الميسّر',
                'slug' => 'easy-arabic-grammar',
                'description' => 'تعلّم قواعد النحو بأسلوب مبسّط: المرفوعات والمنصوبات والمجرورات مع أمثلة وتدريبات إعرابية تثبّت الفهم.',
                'price' => 149.00,
                'is_free' => false,
                'status' => CourseStatus::Published,
                'lessons' => [
                    'مدخل إلى علم النحو',
                    'المبتدأ والخبر',
                    'كان وأخواتها',
                    'إنّ وأخواتها',
                    'الفاعل والمفعول به',
                ],
            ],
            [
                'category_id' => $arabic->id,
                'title' => 'الإملاء والكتابة الصحيحة',
                'slug' => 'arabic-spelling',
                'description' => 'دورة عملية لإتقان قواعد الإملاء: الهمزات بأنواعها، والتاء المربوطة والمفتوحة، والألف اللينة، مع تدريبات مكثّفة.',
                'price' => 0,
                'is_free' => true,
                'status' => CourseStatus::Draft,
                'lessons' => [
                    'الهمزة في أول الكلمة',
                    'الهمزة المتوسطة',
                    'الهمزة المتطرفة',
                    'التاء المربوطة والمفتوحة',
                    'الألف اللينة',
                ],
            ],
        ];

        foreach ($courses as $data) {
            $lessonTitles = $data['lessons'];
            unset($data['lessons']);

            $course = Course::create($data);

            foreach ($lessonTitles as $index => $title) {
                Lesson::create([
                    'course_id' => $course->id,
                    'section' => $index === 0 ? 'المقدمة' : 'الدروس',
                    'title' => $title,
                    'source' => LessonSource::Bunny,
                    // Placeholder Bunny GUIDs — replace by uploading real videos
                    // from the admin panel (or paste a real GUID).
                    'video_id' => 'demo-'.$course->slug.'-'.($index + 1),
                    'duration' => 480 + ($index * 120),
                    'is_preview' => $index === 0,
                    'order' => $index + 1,
                ]);
            }
        }
    }
}
