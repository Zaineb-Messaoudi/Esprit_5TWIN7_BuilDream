{{--
    Contract page (front office): the renter reads the contract, then accepts or declines it.

    Variables received from Front\ContractController@show:
      $contract    : the RentalContract
      $rental      : its Rental (with user and equipment loaded)
      $role        : 'renter', 'owner' or 'admin'
      $canSign     : true only for the renter of a contract that is still a draft
      $backUrl     : target of the "Back" link
      $pdfUrl      : target of the "Download (PDF)" link
      $summary     : the AI summary already generated (['points' => [...]]) or null
      $aiAvailable : true when the AI service is configured on this server

    This page is standalone (no sidebar): a focused page to read a legal document.
--}}
<!DOCTYPE html>
{{-- dir="rtl" for Arabic, like the other layouts of the project --}}
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" dir="{{ app()->getLocale() === 'ar' ? 'rtl' : 'ltr' }}" class="h-full bg-gray-50 dark:bg-gray-900">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $title ?? __('Rental Contract') }} | SolarShare</title>
    @vite(['resources/css/app.css'])

    {{-- Same dark-mode preference as the rest of the site (saved in localStorage) --}}
    <script>
        (function () {
            if (localStorage.getItem('theme') === 'dark') {
                document.documentElement.classList.add('dark');
            }
        })();
    </script>
</head>
<body class="antialiased text-gray-800 dark:text-gray-200">
<div class="mx-auto max-w-3xl space-y-6 px-4 py-8">

    {{-- Top bar: brand + back link --}}
    <div class="flex flex-wrap items-center justify-between gap-3">
        <div class="flex items-center gap-2">
            <img src="{{ asset('images/brand/solarshare-icon.png') }}" alt="" width="28" height="28">
            <span class="text-lg font-semibold text-gray-900 dark:text-white">SolarShare</span>
        </div>
        <a href="{{ $backUrl }}" class="text-sm font-medium text-brand-600 underline-offset-4 hover:underline dark:text-brand-400">&larr; {{ __('Back to the rental') }}</a>
    </div>

    {{-- Messages sent by the controller --}}
    @if (session('status'))
        <p role="status" aria-live="polite" class="rounded-lg border border-success-200 bg-success-50 px-4 py-3 text-sm text-success-700 dark:border-success-500/20 dark:bg-success-500/10 dark:text-success-300">
            {{ match (session('status')) {
                'contract-signed'        => __('Contract signed. A copy is available as a PDF.'),
                'contract-declined'      => __('You declined the contract. The rental has been cancelled.'),
                'contract-read-first'    => __('Please read the contract carefully, then accept it to sign.'),
                'contract-summary-ready' => __('The summary is ready: see "Contract in simple words" below.'),
                default                  => session('status'),
            } }}
        </p>
    @endif
    @if ($errors->any())
        <p role="alert" class="rounded-lg border border-error-200 bg-error-50 px-4 py-3 text-sm text-error-700 dark:border-error-500/20 dark:bg-error-500/10 dark:text-error-300">{{ $errors->first() }}</p>
    @endif

    {{-- The two steps, only shown while the renter still has to decide --}}
    @if ($canSign)
        <ol class="flex flex-wrap gap-4 text-sm">
            <li class="flex items-center gap-2"><span class="flex size-6 items-center justify-center rounded-full bg-brand-500 text-xs font-semibold text-white">1</span> {{ __('Read the contract') }}</li>
            <li class="flex items-center gap-2"><span class="flex size-6 items-center justify-center rounded-full bg-gray-200 text-xs font-semibold text-gray-700 dark:bg-gray-700 dark:text-gray-200">2</span> {{ __('Accept and sign, or decline') }}</li>
        </ol>
    @endif

    {{-- The contract itself --}}
    <article class="rounded-2xl border border-gray-200 bg-white p-6 shadow-theme-xs dark:border-gray-800 dark:bg-white/[0.03] sm:p-8">
        <header class="flex flex-wrap items-start justify-between gap-4 border-b border-gray-100 pb-5 dark:border-gray-800">
            <div>
                <p class="text-xs font-semibold uppercase tracking-wider text-brand-700 dark:text-brand-300">{{ __('Rental contract') }}</p>
                <h1 class="mt-2 text-xl font-semibold text-gray-900 dark:text-white">{{ $contract->contract_number }}</h1>
                <p class="mt-1 text-xs text-gray-500">{{ __('Rental') }} {{ $rental->reference }}</p>
            </div>
            <x-ui.badge :color="$contract->contract_status->color()">{{ $contract->contract_status->label() }}</x-ui.badge>
        </header>

        {{-- Rental information --}}
        <dl class="grid gap-4 py-5 text-sm sm:grid-cols-2">
            <div>
                <dt class="text-xs text-gray-500">{{ __('Equipment') }}</dt>
                <dd class="mt-1 font-semibold">{{ $rental->equipment?->name ?? '—' }}</dd>
            </div>
            <div>
                <dt class="text-xs text-gray-500">{{ __('Owner') }}</dt>
                <dd class="mt-1 font-semibold">{{ $rental->equipment?->owner?->name ?? '—' }}</dd>
            </div>
            <div>
                <dt class="text-xs text-gray-500">{{ __('Renter') }}</dt>
                <dd class="mt-1 font-semibold">{{ $rental->user?->name ?? '—' }}</dd>
            </div>
            <div>
                <dt class="text-xs text-gray-500">{{ __('Rental period') }}</dt>
                <dd class="mt-1 font-semibold">{{ $rental->start_date->format('d/m/Y') }} → {{ $rental->end_date->format('d/m/Y') }}</dd>
            </div>
            <div>
                <dt class="text-xs text-gray-500">{{ __('Rental amount') }}</dt>
                <dd class="mt-1 font-semibold">{{ number_format((float) $rental->total_amount, 2) }} TND</dd>
            </div>
            <div>
                <dt class="text-xs text-gray-500">{{ __('Security deposit') }}</dt>
                <dd class="mt-1 font-semibold">{{ number_format((float) $contract->deposit_amount, 2) }} TND</dd>
            </div>
            <div>
                <dt class="text-xs text-gray-500">{{ __('Signed at') }}</dt>
                <dd class="mt-1 font-semibold">{{ $contract->signed_at?->format('d/m/Y H:i') ?? __('Not signed yet') }}</dd>
            </div>
        </dl>

        {{-- Terms and conditions: e() escapes any HTML, nl2br keeps the line breaks --}}
        <section class="border-t border-gray-100 pt-5 dark:border-gray-800">
            <h2 class="font-semibold text-gray-900 dark:text-white">{{ __('Terms and conditions') }}</h2>
            <p class="mt-3 text-sm leading-7 text-gray-700 dark:text-gray-300">{!! nl2br(e($contract->terms)) !!}</p>
        </section>

        {{-- Tools: PDF and print --}}
        <div class="mt-6 flex flex-wrap gap-3">
            <a href="{{ $pdfUrl }}" class="inline-flex min-h-10 items-center rounded-lg border border-gray-300 px-4 text-sm font-medium text-gray-700 hover:bg-gray-50 dark:border-gray-700 dark:text-gray-200 dark:hover:bg-gray-800">{{ __('Download contract (PDF)') }}</a>
            <button type="button" onclick="window.print()" class="inline-flex min-h-10 items-center rounded-lg border border-gray-300 px-4 text-sm font-medium text-gray-700 hover:bg-gray-50 dark:border-gray-700 dark:text-gray-200 dark:hover:bg-gray-800">{{ __('Print') }}</button>
        </div>
    </article>

    {{-- AI summary: shown when a summary exists, or when the AI service is available (button).
         On a server without the AI key, and without a saved summary, the whole card is hidden. --}}
    @if ($summary || $aiAvailable)
        <section class="rounded-2xl border border-gray-200 bg-white p-6 shadow-theme-xs dark:border-gray-800 dark:bg-white/[0.03] sm:p-8">
            <div class="flex flex-wrap items-start justify-between gap-4">
                <div>
                    <h2 class="font-semibold text-gray-900 dark:text-white">{{ __('Contract in simple words') }}</h2>
                    <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">{{ __('The 5 key points of this contract, explained simply.') }}</p>
                </div>

                {{-- The button only appears while no summary exists. The button is disabled after the click (one call only). --}}
                @if (! $summary)
                    <form method="POST" action="{{ route('contracts.summary', $contract) }}" onsubmit="var b = this.querySelector('button'); b.disabled = true; b.textContent = '{{ __('Generating...') }}';">
                        @csrf
                        <button type="submit" class="inline-flex min-h-10 items-center justify-center rounded-lg bg-brand-500 px-4 text-sm font-medium text-white hover:bg-brand-600 disabled:cursor-not-allowed disabled:opacity-60">{{ __('Explain this contract in simple words') }}</button>
                    </form>
                @endif
            </div>

            @if ($summary)
                <ol class="mt-5 space-y-3 text-sm leading-6 text-gray-700 dark:text-gray-300">
                    @foreach ($summary['points'] as $index => $point)
                        <li class="flex gap-3">
                            <span class="flex size-6 shrink-0 items-center justify-center rounded-full bg-brand-50 text-xs font-semibold text-brand-700 dark:bg-brand-500/15 dark:text-brand-300">{{ $index + 1 }}</span>
                            <span>{{ $point }}</span>
                        </li>
                    @endforeach
                </ol>
                <p class="mt-5 text-xs text-gray-500 dark:text-gray-400">
                    {{ __('Generated by AI from the contract text only. This is a simplified explanation, not legal advice: the contract above is the reference.') }}
                </p>
            @endif
        </section>
    @endif

    {{-- Step 2: accept and sign, or decline (renter of a draft contract only) --}}
    @if ($canSign)
        <section class="rounded-2xl border border-gray-200 bg-white p-6 shadow-theme-xs dark:border-gray-800 dark:bg-white/[0.03] sm:p-8">
            <h2 class="font-semibold text-gray-900 dark:text-white">{{ __('Your decision') }}</h2>
            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">{{ __('You are free to decline. If you decline, the contract ends and the rental is cancelled.') }}</p>

            {{-- Accept: the button stays disabled until the box is ticked (the server checks it too) --}}
            <form method="POST" action="{{ route('contracts.accept', $contract) }}" class="mt-5 space-y-4">
                @csrf
                <label class="flex items-start gap-3 text-sm text-gray-700 dark:text-gray-300">
                    <input id="accept_terms" type="checkbox" name="accept_terms" value="1" class="mt-0.5 size-4 rounded border-gray-300 text-brand-600 focus:ring-brand-500">
                    <span>{{ __('I have read and understood this contract and I accept its terms.') }}</span>
                </label>
                <button id="sign-button" type="submit" disabled class="inline-flex min-h-11 items-center justify-center rounded-lg bg-brand-500 px-5 text-sm font-medium text-white hover:bg-brand-600 disabled:cursor-not-allowed disabled:opacity-50">{{ __('Accept and sign the contract') }}</button>
            </form>

            {{-- Decline --}}
            <form method="POST" action="{{ route('contracts.decline', $contract) }}" class="mt-6 border-t border-gray-100 pt-5 dark:border-gray-800">
                @csrf
                <button type="submit" onclick="return confirm('{{ __('Decline this contract? The rental will be cancelled.') }}')" class="inline-flex min-h-10 items-center justify-center rounded-lg border border-error-300 px-4 text-sm font-medium text-error-700 hover:bg-error-50 dark:border-error-500/40 dark:text-error-300 dark:hover:bg-error-500/10">{{ __('Decline the contract') }}</button>
            </form>
        </section>
    @endif
</div>

{{-- Enables the sign button only when the checkbox is ticked --}}
<script>
    (function () {
        var box = document.getElementById('accept_terms');
        var button = document.getElementById('sign-button');
        if (!box || !button) { return; }
        function sync() { button.disabled = !box.checked; }
        box.addEventListener('change', sync);
        sync();
    })();
</script>
</body>
</html>