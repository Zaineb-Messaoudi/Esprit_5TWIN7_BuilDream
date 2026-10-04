# English and French UI

SolarShare stores the selected locale in the session and a first-party cookie. The language control links to `locale.switch`; English and French are the supported choices shown in the interface. The selected locale sets the document's `lang` attribute and is retained when moving between public, account, and workspace pages.

## Adding translated UI

1. Wrap user-facing Blade copy in Laravel's `__()` helper, for example `{{ __('My reservations') }}`. Do not translate user-generated equipment names, reference numbers, or currency codes.
2. Add the English source string and its French translation to `lang/en.json` and `lang/fr.json`. Preserve placeholders such as `:name`, `:count`, and `:date` exactly.
3. Put validation messages and field labels in `lang/fr/validation.php` when they are Laravel validation rules.
4. For dynamic navigation or enum labels, pass the same translation key through `__()` rather than hardcoding a rendered label.
5. Add or update a feature test that selects French and checks the page's language metadata and translated content.

Run `php artisan test --filter=FrontOfficeTest` and `php artisan view:cache` after changing translations or localized views.
