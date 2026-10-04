@props(['s', 'account' => false])

@php
    $link = fn ($h) => \Illuminate\Support\Facades\Route::has($h) ? route($h) : $h;
    $card = 'rounded-3xl border border-gray-200 bg-white p-6 shadow-theme-xs dark:border-gray-800 dark:bg-white/[0.03]';
    $input = 'min-h-11 w-full rounded-lg border border-gray-300 bg-white px-3 text-theme-sm text-gray-800 placeholder:text-gray-400 focus:border-brand-500 focus:outline-none focus:ring-2 focus:ring-brand-500/30 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90';
    $btn = 'button-base button-primary px-6 py-3 text-theme-sm';
    $cols = [1 => 'lg:grid-cols-1', 2 => 'lg:grid-cols-2', 3 => 'lg:grid-cols-3', 4 => 'lg:grid-cols-4', 5 => 'lg:grid-cols-5'];
    $surface = match ($s['type']) {
        'stats' => 'bg-brand-950',
        'equipment' => 'bg-brand-25 dark:bg-gray-950',
        'quotes' => 'bg-warning-50/70 dark:bg-gray-950',
        'plans' => 'bg-gray-950',
        'features' => 'bg-gray-50 dark:bg-gray-950',
        default => 'bg-white dark:bg-gray-900',
    };
    $tone = [
        'success' => 'bg-success-50 text-success-700 dark:bg-success-500/15 dark:text-success-400',
        'warning' => 'bg-warning-50 text-warning-700 dark:bg-warning-500/15 dark:text-warning-400',
        'error' => 'bg-error-50 text-error-700 dark:bg-error-500/15 dark:text-error-400',
        'info' => 'bg-brand-50 text-brand-600 dark:bg-brand-500/15 dark:text-brand-400',
    ];
@endphp

@if ($s['type'] === 'hero')
    <section @isset($s['id']) id="{{ $s['id'] }}" @endisset class="relative isolate scroll-mt-24 overflow-hidden bg-gray-950 text-white">
        <div class="pointer-events-none absolute inset-0 -z-20 bg-linear-to-br from-gray-950 via-brand-950 to-gray-900" aria-hidden="true"></div>
        <div class="hero-energy-grid pointer-events-none absolute inset-0 -z-10 opacity-30" aria-hidden="true"></div>
        <div class="pointer-events-none absolute -end-40 -top-44 -z-10 size-[30rem] rounded-full bg-brand-400/15 blur-3xl" aria-hidden="true"></div>
        <div class="relative mx-auto w-full max-w-7xl px-6 sm:px-8 lg:px-10 {{ ($s['compact'] ?? false) ? 'py-14 sm:py-16' : 'py-20 sm:py-24' }}">
            <p class="inline-flex items-center gap-2 rounded-full border border-warning-300/25 bg-warning-300/10 px-3.5 py-2 text-theme-xs font-semibold uppercase tracking-[0.2em] text-warning-200"><span class="size-2 rounded-full bg-warning-300" aria-hidden="true"></span>{{ __($s['eyebrow']) }}</p>
            <h1 class="mt-6 max-w-4xl text-pretty text-title-lg font-semibold leading-[1.04] tracking-tight text-white sm:text-title-xl">{{ __($s['title']) }}</h1>
            <p class="mt-5 max-w-2xl text-lg leading-8 text-gray-300">{{ __($s['sub']) }}</p>
            @isset($s['cta'])
                <a href="{{ $link($s['cta']['href']) }}" class="{{ $btn }} mt-8">{{ __($s['cta']['label']) }}</a>
            @endisset
        </div>
    </section>
@elseif ($s['type'] === 'cta')
    <section @isset($s['id']) id="{{ $s['id'] }}" @endisset class="scroll-mt-24 px-6 py-14 sm:px-8 lg:px-10">
        <div class="relative isolate mx-auto flex w-full max-w-7xl flex-col items-start justify-between gap-6 overflow-hidden rounded-[2rem] bg-linear-to-br from-brand-950 via-brand-900 to-gray-950 px-7 py-10 shadow-theme-xl sm:px-10 sm:py-14 md:flex-row md:items-center">
            <div class="hero-energy-grid pointer-events-none absolute inset-0 -z-10 opacity-30" aria-hidden="true"></div>
            <div class="pointer-events-none absolute -end-16 -top-28 -z-10 size-80 rounded-full border border-brand-300/20" aria-hidden="true"></div>
            <div class="pointer-events-none absolute -end-4 -top-16 -z-10 size-56 rounded-full border border-warning-300/20" aria-hidden="true"></div>
            <div class="max-w-xl">
                <p class="text-theme-xs font-semibold uppercase tracking-[0.2em] text-brand-200">{{ __('Power is better shared') }}</p>
                <h2 class="mt-3 text-pretty text-title-sm font-semibold text-white sm:text-title-md">{{ __($s['title']) }}</h2>
                <p class="mt-3 max-w-lg leading-7 text-gray-300">{{ __($s['text']) }}</p>
            </div>
            <a href="{{ $link($s['href']) }}" class="{{ $btn }} shrink-0">{{ __($s['label']) }}</a>
        </div>
    </section>
@else
    <section @isset($s['id']) id="{{ $s['id'] }}" @endisset class="relative scroll-mt-24 overflow-hidden {{ $surface }}">
        @if (in_array($s['type'], ['stats', 'plans'], true))
            <div class="hero-energy-grid pointer-events-none absolute inset-0 opacity-20" aria-hidden="true"></div>
        @endif
        <div class="relative {{ $account ? '' : 'mx-auto w-full max-w-7xl px-6 py-14 sm:px-8 sm:py-20 lg:px-10' }}">
        @isset($s['title'])
            <div class="mb-8 flex flex-wrap items-end justify-between gap-4">
                <div>
                    <h2 class="text-title-sm font-semibold tracking-tight {{ in_array($s['type'], ['stats', 'plans'], true) ? 'text-white' : 'text-gray-950 dark:text-white' }}">{{ __($s['title']) }}</h2>
                    @isset($s['intro'])<p class="mt-2 max-w-2xl {{ in_array($s['type'], ['stats', 'plans'], true) ? 'text-gray-300' : 'text-gray-600 dark:text-gray-400' }}">{{ __($s['intro']) }}</p>@endisset
                </div>
                @isset($s['action'])
                    <a href="{{ $link($s['action']['href']) }}" class="inline-flex min-h-11 items-center text-theme-sm font-medium text-brand-700 hover:underline focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-brand-500 dark:text-brand-300">{{ __($s['action']['label']) }} <span class="inline-block rtl:rotate-180" aria-hidden="true">→</span></a>
                @endisset
            </div>
        @endisset

        @switch($s['type'])
            @case('features')
                <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
                    @foreach ($s['items'] as $it)
                        <article class="group relative overflow-hidden rounded-3xl border border-gray-200 bg-white p-6 transition duration-300 hover:-translate-y-1 hover:border-brand-300 hover:shadow-theme-md motion-reduce:transition-none motion-reduce:hover:translate-y-0 dark:border-gray-800 dark:bg-gray-900 sm:p-7">
                            <div class="flex items-center justify-between gap-4">
                                <span class="flex size-12 items-center justify-center rounded-2xl bg-brand-950 text-brand-200 transition-colors group-hover:bg-brand-600 group-hover:text-white motion-reduce:transition-none dark:bg-brand-500/15 dark:text-brand-200" aria-hidden="true"><x-front.icon :name="$it['icon']" /></span>
                                <span class="font-outfit text-3xl font-light tabular-nums text-gray-200 dark:text-gray-700">{{ str_pad((string) $loop->iteration, 2, '0', STR_PAD_LEFT) }}</span>
                            </div>
                            <h3 class="mt-7 text-lg font-semibold tracking-tight text-gray-950 dark:text-white">{{ __($it['title']) }}</h3>
                            <p class="mt-2 leading-6 text-theme-sm text-gray-600 dark:text-gray-400">{{ __($it['text']) }}</p>
                            <span class="absolute inset-x-0 bottom-0 h-0.5 origin-start scale-x-0 bg-linear-to-r from-brand-500 to-warning-400 transition-transform duration-300 group-hover:scale-x-100 motion-reduce:transition-none" aria-hidden="true"></span>
                        </article>
                    @endforeach
                </div>
                @break

            @case('steps')
                <ol class="grid gap-x-5 gap-y-8 sm:grid-cols-2 {{ $cols[min(count($s['items']), 5)] }}">
                    @foreach ($s['items'] as $it)
                        <li class="relative border-s-2 border-brand-200 py-1 ps-5 dark:border-brand-500/30 lg:border-s-0 lg:ps-0">
                            <div class="flex items-center gap-3">
                                <span class="flex size-11 shrink-0 items-center justify-center rounded-full bg-brand-950 text-theme-xs font-semibold tracking-wider text-warning-300 shadow-theme-xs dark:bg-brand-800">{{ str_pad((string) $loop->iteration, 2, '0', STR_PAD_LEFT) }}</span>
                                @if (! $loop->last)
                                    <span class="hidden h-px flex-1 bg-linear-to-r from-brand-300 to-brand-100 lg:block dark:from-brand-500/40 dark:to-gray-800" aria-hidden="true"></span>
                                @endif
                            </div>
                            <h3 class="mt-4 font-semibold tracking-tight text-gray-900 dark:text-white">{{ __($it['title']) }}</h3>
                            <p class="mt-2 text-theme-sm leading-6 text-gray-600 dark:text-gray-400">{{ __($it['text']) }}</p>
                        </li>
                    @endforeach
                </ol>
                @break

            @case('stats')
                <dl class="grid gap-4 sm:grid-cols-2 {{ $cols[min(count($s['items']), 4)] }}">
                    @foreach ($s['items'] as $it)
                        <div class="rounded-3xl border border-white/15 bg-white/5 p-6 backdrop-blur-sm">
                            <dd class="text-title-sm font-semibold tracking-tight text-warning-300">{{ $it['value'] }}</dd>
                            <dt class="mt-2 text-theme-sm text-gray-300">{{ __($it['label']) }}</dt>
                        </div>
                    @endforeach
                </dl>
                @break

            @case('faq')
                <div class="mx-auto max-w-3xl divide-y divide-gray-200 rounded-2xl border border-gray-200 bg-white dark:divide-gray-800 dark:border-gray-800 dark:bg-white/[0.03]" x-data="{ open: 0 }">
                    @foreach ($s['items'] as $i => $it)
                        <div>
                            <button type="button" @click="open = open === {{ $i + 1 }} ? 0 : {{ $i + 1 }}" :aria-expanded="open === {{ $i + 1 }}" aria-controls="faq-answer-{{ $s['id'] ?? 'faq' }}-{{ $loop->index }}"
                                class="flex min-h-11 w-full items-center justify-between gap-4 px-6 py-4 text-start font-medium text-gray-800 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-inset focus-visible:ring-brand-500 dark:text-white/90">
                                {{ __($it['q']) }}
                                <span class="text-xl text-brand-600 transition motion-reduce:transition-none" :class="open === {{ $i + 1 }} && 'rotate-45'" aria-hidden="true">+</span>
                            </button>
                            <p id="faq-answer-{{ $s['id'] ?? 'faq' }}-{{ $loop->index }}" x-show="open === {{ $i + 1 }}" x-cloak class="px-6 pb-5 text-theme-sm text-gray-600 dark:text-gray-400">{{ __($it['a']) }}</p>
                        </div>
                    @endforeach
                </div>
                @break

            @case('plans')
                <div class="grid gap-6 lg:grid-cols-3">
                    @foreach ($s['items'] as $p)
                        <div class="relative flex flex-col rounded-3xl border bg-white p-7 text-gray-900 transition duration-300 hover:-translate-y-1 hover:shadow-theme-lg motion-reduce:transition-none motion-reduce:hover:translate-y-0 dark:bg-gray-900 dark:text-white {{ ($p['highlight'] ?? false) ? 'border-warning-400 shadow-theme-lg' : 'border-gray-200 shadow-theme-xs dark:border-gray-800' }}">
                            @if ($p['highlight'] ?? false)
                                <span class="absolute -top-3 start-6 rounded-full bg-brand-600 px-3 py-1 text-theme-xs font-semibold text-white">{{ __('Most popular') }}</span>
                            @endif
                            <h3 class="text-lg font-semibold text-gray-800 dark:text-white/90">{{ __($p['name']) }}</h3>
                            <p class="mt-1 text-theme-sm text-gray-500 dark:text-gray-400">{{ __($p['tagline']) }}</p>
                            <p class="mt-5"><span class="text-title-sm font-semibold text-gray-900 dark:text-white">{{ $p['price'] }}</span> <span class="text-theme-sm text-gray-500 dark:text-gray-400">{{ __($p['period']) }}</span></p>
                            <ul class="mt-6 flex-1 space-y-3 text-theme-sm text-gray-600 dark:text-gray-300">
                                @foreach ($p['features'] as $f)
                                    <li class="flex gap-2"><span class="text-success-600 dark:text-success-400" aria-hidden="true">✔</span>{{ __($f) }}</li>
                                @endforeach
                            </ul>
                            <a href="{{ $link($p['href']) }}" class="{{ ($p['highlight'] ?? false) ? $btn : 'button-base border border-gray-300 px-6 py-3 text-theme-sm font-medium text-gray-700 hover:bg-gray-50 dark:border-gray-700 dark:text-gray-300 dark:hover:bg-white/5' }} mt-8">{{ __($p['label']) }}</a>
                        </div>
                    @endforeach
                </div>
                @break

            @case('quotes')
                <div class="grid gap-5 md:grid-cols-3">
                    @foreach ($s['items'] as $q)
                        <figure class="relative overflow-hidden rounded-3xl border border-gray-200 bg-linear-to-br from-brand-50 via-white to-warning-50 p-6 dark:border-gray-800 dark:from-brand-950/40 dark:via-gray-900 dark:to-warning-950/20">
                            <span class="text-5xl font-semibold leading-none text-brand-300 dark:text-brand-700" aria-hidden="true">“</span>
                            <blockquote class="text-lg leading-7 text-gray-800 dark:text-gray-200">{{ __($q['quote']) }}</blockquote>
                            <figcaption class="mt-5 border-t border-brand-100 pt-4 text-theme-sm dark:border-gray-800"><span class="font-semibold text-gray-900 dark:text-white">{{ $q['name'] }}</span> <span class="text-gray-500 dark:text-gray-400">· {{ __($q['role']) }}</span></figcaption>
                        </figure>
                    @endforeach
                </div>
                @break

            @case('text')
                <div class="max-w-3xl space-y-4 text-gray-600 dark:text-gray-400">
                    @foreach ($s['paragraphs'] as $para)<p>{{ __($para) }}</p>@endforeach
                </div>
                @break

            @case('equipment')
                <div class="grid gap-6 sm:grid-cols-2 lg:grid-cols-4">
                    @foreach (\App\Support\FrontDemo::equipment()->take($s['limit'] ?? 4) as $item)
                        <x-front.equipment-card :item="$item" />
                    @endforeach
                </div>
                @break

            @case('table')
                <div role="region" aria-label="{{ __($s['title'] ?? 'Scrollable table') }}" tabindex="0" class="overflow-x-auto rounded-2xl border border-gray-200 bg-white focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-brand-500 dark:border-gray-800 dark:bg-white/[0.03]">
                    <table class="w-full min-w-xl text-start text-theme-sm">
                        <thead class="bg-gray-50 text-theme-xs uppercase text-gray-500 dark:bg-white/5 dark:text-gray-400">
                            <tr>@foreach ($s['cols'] as $c)<th scope="col" class="px-4 py-3 text-start font-medium">{{ __($c) }}</th>@endforeach</tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200 text-gray-700 dark:divide-gray-800 dark:text-gray-300">
                            @foreach ($s['rows'] as $row)
                                <tr>
                                    @foreach ($row as $cell)
                                        <td class="px-4 py-3">
                                            @if (is_array($cell))
                                                <span class="rounded-full px-2.5 py-1 text-theme-xs font-medium {{ $tone[$cell['tone']] }}">{{ __($cell['text']) }}</span>
                                            @else{{ $cell }}@endif
                                        </td>
                                    @endforeach
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                @break

            @case('form')
                <form x-data="{ sent: false }" @submit.prevent="{{ isset($s['href']) ? 'window.location = '.json_encode($link($s['href'])) : 'sent = true' }}" class="{{ $card }} grid max-w-3xl gap-5 sm:grid-cols-2">
                    @foreach ($s['fields'] as $f)
                        @php $wide = in_array($f['type'], ['textarea', 'radio', 'checkbox']) ? 'sm:col-span-2' : ''; @endphp
                        <div class="{{ $wide }}">
                            @if ($f['type'] === 'checkbox')
                                <label class="flex min-h-11 items-center gap-3 text-theme-sm text-gray-700 dark:text-gray-300"><input type="checkbox" name="{{ $f['name'] }}" required class="size-4 rounded border-gray-300 text-brand-600 focus:ring-brand-500" /> {{ __($f['label']) }}</label>
                            @elseif ($f['type'] === 'radio')
                                <fieldset>
                                    <legend class="mb-2 text-theme-sm font-medium text-gray-700 dark:text-gray-300">{{ __($f['label']) }}</legend>
                                    <div class="flex flex-wrap gap-3">
                                        @foreach ($f['options'] as $o)
                                            <label class="flex min-h-11 cursor-pointer items-center gap-2 rounded-lg border border-gray-300 px-4 py-2.5 text-theme-sm text-gray-700 has-checked:border-brand-500 has-checked:bg-brand-50 dark:border-gray-700 dark:text-gray-300 dark:has-checked:bg-brand-500/10">
                                                <input type="radio" name="{{ $f['name'] }}" value="{{ $o }}" @checked($loop->first) class="text-brand-600 focus:ring-brand-500" /> {{ __($o) }}
                                            </label>
                                        @endforeach
                                    </div>
                                </fieldset>
                            @else
                                <label for="{{ $f['name'] }}" class="mb-1.5 block text-theme-sm font-medium text-gray-700 dark:text-gray-300">{{ __($f['label']) }}</label>
                                @if ($f['type'] === 'textarea')
                                    <textarea id="{{ $f['name'] }}" name="{{ $f['name'] }}" rows="4" class="min-h-28 w-full rounded-lg border border-gray-300 bg-white px-3 py-2.5 text-theme-sm text-gray-800 placeholder:text-gray-400 focus:border-brand-500 focus:outline-none focus:ring-2 focus:ring-brand-500/30 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90"></textarea>
                                @elseif ($f['type'] === 'select')
                                    <select id="{{ $f['name'] }}" name="{{ $f['name'] }}" class="{{ $input }}">@foreach ($f['options'] as $o)<option>{{ __($o) }}</option>@endforeach</select>
                                @else
                                    <input id="{{ $f['name'] }}" type="{{ $f['type'] }}" name="{{ $f['name'] }}" autocomplete="{{ in_array($f['name'], ['name', 'email'], true) ? $f['name'] : 'off' }}" @if ($f['name'] === 'email') spellcheck="false" @endif placeholder="{{ $f['placeholder'] ?? '' }}" class="{{ $input }}" />
                                @endif
                            @endif
                        </div>
                    @endforeach
                    <div class="sm:col-span-2">
                        <button type="submit" class="{{ $btn }}">{{ __($s['submit']) }}</button>
                        <p x-show="sent" x-cloak role="status" aria-live="polite" class="mt-4 rounded-lg bg-success-50 px-4 py-3 text-theme-sm text-success-700 dark:bg-success-500/15 dark:text-success-400">{{ __($s['success'] ?? 'Saved. This demo form is not connected to a backend yet.') }}</p>
                    </div>
                </form>
                @break
        @endswitch
        </div>
    </section>
@endif
