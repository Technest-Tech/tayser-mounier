# Data Model

All tables use `bigIncrements` primary keys and `timestamps` unless noted.
Enums are PHP backed enums stored as strings.

## Tables

### users
| Column | Type | Notes |
|--------|------|-------|
| id | bigint PK | |
| name | string | |
| email | string unique | |
| password | string | hashed |
| role | string enum | `admin` \| `student` (default `student`) |
| email_verified_at | timestamp null | |
| timestamps | | |

### categories
| Column | Type | Notes |
|--------|------|-------|
| id | bigint PK | |
| name | string | |
| slug | string unique | |
| timestamps | | |

`name` is translatable via a JSON column or a simple convention; v1 stores a
single string and relies on UI translation files for static labels. (Course/
category content is admin-entered; see note below.)

### courses
| Column | Type | Notes |
|--------|------|-------|
| id | bigint PK | |
| category_id | FK → categories | nullable, set null on delete |
| title | string | |
| slug | string unique | |
| description | text | |
| thumbnail | string null | stored path |
| price | decimal(10,2) | 0 when free |
| is_free | boolean | default false |
| status | string enum | `draft` \| `published` (default `draft`) |
| timestamps | | |

### lessons
| Column | Type | Notes |
|--------|------|-------|
| id | bigint PK | |
| course_id | FK → courses | cascade on delete |
| section | string null | grouping/title of a section |
| title | string | |
| source | string enum | `bunny` \| `youtube` |
| video_id | string | Bunny GUID or YouTube video id |
| duration | unsigned int null | seconds |
| is_preview | boolean | default false — free to watch by anyone |
| order | unsigned int | sort order within course |
| timestamps | | |

### access_codes
| Column | Type | Notes |
|--------|------|-------|
| id | bigint PK | |
| course_id | FK → courses | cascade on delete |
| batch_id | uuid/string | groups a generation batch |
| code_hash | string | **hashed** code; unique index |
| status | string enum | `unused` \| `redeemed` (default `unused`) |
| redeemed_by | FK → users null | set when redeemed |
| redeemed_at | timestamp null | |
| expires_at | timestamp null | null = never expires |
| timestamps | | |

Plaintext codes are **never stored**. They are generated, hashed for storage,
and the plaintext is returned once to the admin (shown + CSV export).

### enrollments
| Column | Type | Notes |
|--------|------|-------|
| id | bigint PK | |
| user_id | FK → users | cascade on delete |
| course_id | FK → courses | cascade on delete |
| source | string enum | `free` \| `code` |
| timestamps | | |

Unique composite index on `(user_id, course_id)` — a student enrolls in a course
once.

### lesson_progress
| Column | Type | Notes |
|--------|------|-------|
| id | bigint PK | |
| user_id | FK → users | cascade on delete |
| lesson_id | FK → lessons | cascade on delete |
| completed_at | timestamp null | set when marked complete |
| last_position | unsigned int | seconds, for resume; default 0 |
| timestamps | | |

Unique composite index on `(user_id, lesson_id)`.

## Relationships
- `Category` hasMany `Course`
- `Course` belongsTo `Category`; hasMany `Lesson`, `AccessCode`, `Enrollment`
- `Lesson` belongsTo `Course`; hasMany `LessonProgress`
- `AccessCode` belongsTo `Course`, belongsTo `User` (redeemer)
- `Enrollment` belongsTo `User`, `Course`
- `User` hasMany `Enrollment`, `LessonProgress`; belongsToMany `Course` through enrollments

## Enums (app/Enums)
- `UserRole`: `admin`, `student`
- `CourseStatus`: `draft`, `published`
- `LessonSource`: `bunny`, `youtube`
- `AccessCodeStatus`: `unused`, `redeemed`
- `EnrollmentSource`: `free`, `code`

## Note on translating admin-entered content
v1 keeps course/category titles & descriptions as single strings (entered by the
admin in the language they teach in — primarily Arabic). Static UI chrome is fully
translated via `lang/ar` + `lang/en`. If per-field bilingual *content* is needed
later, the columns can move to JSON translatable fields (e.g. `spatie/laravel-
translatable`) without changing relationships.
