<?php

namespace App\Console\Commands;

use App\Services\AutomatedTranslationService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

class SyncBladeTranslations extends Command
{
    protected $signature = 'translations:sync-views
                            {--dry-run : Show what would be added without modifying files}
                            {--force : Overwrite existing translations}';

    protected $description = 'Scan Blade views for __() strings and auto-translate to target locales';

    protected AutomatedTranslationService $translator;
    protected string $sourceLocale;
    protected array $targetLocales;

    public function __construct(AutomatedTranslationService $translator)
    {
        parent::__construct();
        $this->translator = $translator;
        $this->sourceLocale = $this->translator->getSourceLocale();
        $this->targetLocales = $this->translator->getSupportedLocales();
    }

    public function handle(): int
    {
        $viewsPath = resource_path('views');
        $bladeFiles = $this->getBladeFiles($viewsPath);

        if (empty($bladeFiles)) {
            $this->error('No Blade files found in ' . $viewsPath);
            return self::FAILURE;
        }

        $this->info(sprintf('Scanning %d Blade files for translation strings...', count($bladeFiles)));

        $strings = $this->extractStringsFromBlade($bladeFiles);

        $this->info(sprintf('Found %d unique translatable strings.', count($strings)));

        $existingSource = $this->loadJsonTranslations('en');
        $existingTargets = [];

        foreach ($this->targetLocales as $locale) {
            $existingTargets[$locale] = $this->loadJsonTranslations($locale);
        }

        $newSource = 0;
        $newTargets = [];
        foreach ($this->targetLocales as $locale) {
            $newTargets[$locale] = 0;
        }

        $bar = $this->output->createProgressBar(count($strings));
        $bar->start();

        foreach ($strings as $string) {
            if (!array_key_exists($string, $existingSource)) {
                $existingSource[$string] = $string;
                $newSource++;
            }

            foreach ($this->targetLocales as $locale) {
                if (!array_key_exists($string, $existingTargets[$locale])) {
                    $translation = $this->translator->translate($string, $locale);
                    $existingTargets[$locale][$string] = $translation;
                    $newTargets[$locale]++;
                } elseif ($this->option('force')) {
                    $translation = $this->translator->translate($string, $locale);
                    $existingTargets[$locale][$string] = $translation;
                }
            }

            $bar->advance();
        }

        $bar->finish();
        $this->newLine();

        ksort($existingSource);
        foreach ($this->targetLocales as $locale) {
            ksort($existingTargets[$locale]);
        }

        if ($this->option('dry-run')) {
            $this->warn('[DRY RUN] No files were modified.');
        } else {
            $this->saveJsonTranslations('en', $existingSource);
            foreach ($this->targetLocales as $locale) {
                $this->saveJsonTranslations($locale, $existingTargets[$locale]);
            }
        }

        $this->info(sprintf('Added %d new key(s) to lang/en.json', $newSource));
        foreach ($this->targetLocales as $locale) {
            $this->info(sprintf('Added %d new key(s) to lang/%s.json', $newTargets[$locale], $locale));
        }

        return self::SUCCESS;
    }

    protected function getBladeFiles(string $path): array
    {
        $files = [];
        $iterator = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($path, \FilesystemIterator::SKIP_DOTS)
        );

        foreach ($iterator as $file) {
            if ($file->getExtension() === 'blade.php' || $file->getExtension() === 'php') {
                $files[] = $file->getRealPath();
            }
        }

        return $files;
    }

    protected function extractStringsFromBlade(array $files): array
    {
        $strings = [];
        $patterns = [
            '/__\s*\(\s*["\']([^"\']+)["\']\s*\)/',
            '/trans\s*\(\s*["\']([^"\']+)["\']\s*\)/',
            '/@lang\s*\(\s*["\']([^"\']+)["\']\s*\)/',
            '/Lang::get\s*\(\s*["\']([^"\']+)["\']\s*\)/',
            '/Lang::trans\s*\(\s*["\']([^"\']+)["\']\s*\)/',
        ];

        foreach ($files as $file) {
            $content = File::get($file);

            foreach ($patterns as $pattern) {
                if (preg_match_all($pattern, $content, $matches)) {
                    foreach ($matches[1] as $match) {
                        if (!Str::contains($match, ['->', ':']) && !is_numeric($match)) {
                            $strings[$match] = true;
                        }
                    }
                }
            }
        }

        return array_keys($strings);
    }

    protected function loadJsonTranslations(string $locale): array
    {
        $path = lang_path($locale . '.json');

        if (!File::exists($path)) {
            return [];
        }

        $content = File::get($path);
        $data = json_decode($content, true);

        return $data ?? [];
    }

    protected function saveJsonTranslations(string $locale, array $translations): void
    {
        $path = lang_path($locale . '.json');
        $json = json_encode($translations, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);

        if ($this->option('dry-run')) {
            return;
        }

        File::put($path, $json . "\n");
    }
}
