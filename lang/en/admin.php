<?php

return [
    // Navigation / models
    'courses' => 'Courses',
    'course' => 'Course',
    'categories' => 'Categories',
    'category' => 'Category',
    'access_codes' => 'Access codes',
    'access_code' => 'Access code',
    'lessons' => 'Lessons',

    // Common fields
    'name' => 'Name',
    'title' => 'Title',
    'slug' => 'Slug',
    'slug_hint' => 'Leave blank to generate automatically.',
    'description' => 'Description',
    'thumbnail' => 'Thumbnail',
    'status' => 'Status',
    'price' => 'Price',
    'free' => 'Free',
    'is_free' => 'Free course',
    'created_at' => 'Created',

    // Course sections
    'course_details' => 'Course details',
    'pricing_status' => 'Pricing & status',

    // Lessons
    'section' => 'Section',
    'section_hint' => 'Group lessons under a heading (e.g. "Introduction").',
    'order' => 'Order',
    'source' => 'Video source',
    'bunny_id' => 'Bunny video ID',
    'bunny_id_hint' => 'The GUID of the video in your Bunny Stream library. Leave empty if you upload a file above.',
    'bunny_upload' => 'Upload video to Bunny Stream',
    'bunny_upload_hint' => 'Pick a video file — it is uploaded to your Bunny library on save and the ID is filled in automatically.',
    'bunny_upload_done' => 'Video uploaded to Bunny successfully.',
    'bunny_upload_failed' => 'Failed to upload the video to Bunny.',
    'youtube_id' => 'YouTube video ID',
    'youtube_id_hint' => 'The id from the YouTube URL (use an unlisted video).',
    'duration_seconds' => 'Duration (seconds)',
    'is_preview' => 'Free preview',
    'is_preview_hint' => 'Anyone can watch this lesson without enrolling.',
    'preview' => 'Preview',
    'add_lesson' => 'Add lesson',

    // Access codes
    'batch' => 'Batch',
    'redeemed_by' => 'Redeemed by',
    'redeemed_at' => 'Redeemed at',
    'expires_at' => 'Expires at',
    'expires_hint' => 'Leave blank for codes that never expire.',
    'never' => 'Never',
    'quantity' => 'How many codes',
    'generate_codes' => 'Generate codes',
    'revoke' => 'Revoke',
];
