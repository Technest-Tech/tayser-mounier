@php
    use App\Enums\AccessCodeStatus;
    $total = $codes->count();
    $used = $codes->where('status', AccessCodeStatus::Redeemed)->count();
    $unused = $total - $used;
@endphp

<div class="space-y-4">
    {{-- Summary --}}
    <div class="grid grid-cols-3 gap-3">
        <div class="rounded-xl bg-gray-50 p-3 text-center dark:bg-white/5">
            <div class="text-2xl font-bold text-gray-900 dark:text-white">{{ $total }}</div>
            <div class="text-xs text-gray-500">{{ __('admin.total_codes') }}</div>
        </div>
        <div class="rounded-xl bg-amber-50 p-3 text-center dark:bg-amber-500/10">
            <div class="text-2xl font-bold text-amber-600">{{ $used }}</div>
            <div class="text-xs text-gray-500">{{ __('admin.used_codes') }}</div>
        </div>
        <div class="rounded-xl bg-emerald-50 p-3 text-center dark:bg-emerald-500/10">
            <div class="text-2xl font-bold text-emerald-600">{{ $unused }}</div>
            <div class="text-xs text-gray-500">{{ __('admin.unused_codes') }}</div>
        </div>
    </div>

    {{-- Codes + usage history --}}
    @if ($codes->isEmpty())
        <p class="py-8 text-center text-sm text-gray-500">{{ __('admin.no_codes') }}</p>
    @else
        <div class="max-h-[55vh] overflow-y-auto rounded-xl ring-1 ring-gray-200 dark:ring-white/10">
            <table class="w-full text-sm">
                <thead class="sticky top-0 bg-gray-50 text-start text-xs uppercase text-gray-500 dark:bg-gray-800">
                    <tr>
                        <th class="px-4 py-2.5 text-start font-semibold">{{ __('admin.code') }}</th>
                        <th class="px-4 py-2.5 text-start font-semibold">{{ __('admin.status') }}</th>
                        <th class="px-4 py-2.5 text-start font-semibold">{{ __('admin.redeemed_by') }}</th>
                        <th class="px-4 py-2.5 text-start font-semibold">{{ __('admin.redeemed_at') }}</th>
                        <th class="px-4 py-2.5 text-start font-semibold">{{ __('admin.expires_at') }}</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 dark:divide-white/5">
                    @foreach ($codes as $code)
                        <tr class="text-gray-700 dark:text-gray-200">
                            <td class="px-4 py-2.5 font-mono font-bold tracking-wider">
                                {{ $code->plainCode() ?? '—' }}
                            </td>
                            <td class="px-4 py-2.5">
                                @if ($code->status === AccessCodeStatus::Unused)
                                    <span class="inline-flex rounded-full bg-emerald-50 px-2 py-0.5 text-xs font-semibold text-emerald-700 dark:bg-emerald-500/10 dark:text-emerald-400">
                                        {{ $code->status->label() }}
                                    </span>
                                @else
                                    <span class="inline-flex rounded-full bg-gray-100 px-2 py-0.5 text-xs font-semibold text-gray-600 dark:bg-white/10 dark:text-gray-300">
                                        {{ $code->status->label() }}
                                    </span>
                                @endif
                            </td>
                            <td class="px-4 py-2.5">{{ optional($code->redeemer)->name ?? '—' }}</td>
                            <td class="px-4 py-2.5">{{ optional($code->redeemed_at)?->translatedFormat('d M Y, H:i') ?? '—' }}</td>
                            <td class="px-4 py-2.5">{{ optional($code->expires_at)?->translatedFormat('d M Y') ?? __('admin.never') }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif
</div>
