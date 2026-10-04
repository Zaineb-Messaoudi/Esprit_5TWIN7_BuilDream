<?php

namespace App\Http\Controllers;

use Illuminate\Contracts\View\View;

class AiDemoController extends Controller
{
    private const TOOLS = [
        'text' => [
            'title' => 'Text generator',
            'description' => 'Draft product copy, email, or campaign content in a local UI preview.',
            'kind' => 'text',
        ],
        'image' => [
            'title' => 'Image generator',
            'description' => 'Explore an image prompt workflow. This demo does not generate or upload images.',
            'kind' => 'image',
        ],
        'video' => [
            'title' => 'Video generator',
            'description' => 'Configure a storyboard request using demo-only controls.',
            'kind' => 'video',
        ],
        'code' => [
            'title' => 'Code generator',
            'description' => 'Draft a code request using a local-only demonstration interface.',
            'kind' => 'code',
        ],
        'chat' => [
            'title' => 'AI chat',
            'description' => 'Explore the AI chat interface. Messages remain in this browser page.',
            'kind' => 'chat',
        ],
        'history' => [
            'title' => 'Generation history',
            'description' => 'Review example generation records; there is no connected AI service.',
            'kind' => 'history',
        ],
        'settings' => [
            'title' => 'AI settings',
            'description' => 'Configure example model preferences. Nothing is sent or persisted.',
            'kind' => 'settings',
        ],
    ];

    public function index(): View
    {
        return view('pages.dashboard.ai', ['title' => 'AI Dashboard']);
    }

    public function usage(): View
    {
        return view('pages.ai.usage', ['title' => 'AI Usage']);
    }

    public function tool(string $tool): View
    {
        abort_unless(isset(self::TOOLS[$tool]), 404);

        return view('pages.ai.studio', [
            'title' => self::TOOLS[$tool]['title'],
            'tool' => self::TOOLS[$tool],
            'tools' => self::TOOLS,
        ]);
    }
}
