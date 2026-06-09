<?php

namespace App\Actions;

use App\Models\AccessCode;
use App\Models\Course;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

/**
 * Generates a batch of single-use access codes for a course.
 *
 * Returns the PLAINTEXT codes (shown to the admin once / exported to CSV).
 * Only the HMAC hash is persisted — plaintext is never stored.
 */
class GenerateCodeBatchAction
{
    /** Characters used for codes — no ambiguous 0/O/1/I/L. */
    private const ALPHABET = 'ABCDEFGHJKMNPQRSTUVWXYZ23456789';

    private const SEGMENTS = 3;

    private const SEGMENT_LENGTH = 4;

    /**
     * @return array{batch_id: string, codes: array<int, string>}
     */
    public function execute(Course $course, int $quantity, ?Carbon $expiresAt = null): array
    {
        $batchId = (string) Str::uuid();
        $plainCodes = [];
        $rows = [];
        $now = now();

        // Generate unique plaintext codes (dedupe within the batch).
        $seen = [];
        while (count($plainCodes) < $quantity) {
            $code = $this->generateCode();
            $hash = AccessCode::hashCode($code);

            if (isset($seen[$hash])) {
                continue;
            }
            $seen[$hash] = true;

            $plainCodes[] = $code;
            $rows[] = [
                'course_id' => $course->id,
                'batch_id' => $batchId,
                'code_hash' => $hash,
                'status' => \App\Enums\AccessCodeStatus::Unused->value,
                'expires_at' => $expiresAt,
                'created_at' => $now,
                'updated_at' => $now,
            ];
        }

        // Insert in chunks; ignore the rare hash collision with existing rows.
        DB::transaction(function () use ($rows) {
            foreach (array_chunk($rows, 500) as $chunk) {
                AccessCode::insertOrIgnore($chunk);
            }
        });

        return ['batch_id' => $batchId, 'codes' => $plainCodes];
    }

    private function generateCode(): string
    {
        $segments = [];
        for ($s = 0; $s < self::SEGMENTS; $s++) {
            $segment = '';
            for ($i = 0; $i < self::SEGMENT_LENGTH; $i++) {
                $segment .= self::ALPHABET[random_int(0, strlen(self::ALPHABET) - 1)];
            }
            $segments[] = $segment;
        }

        return implode('-', $segments); // e.g. ABCD-2345-WXYZ
    }
}
