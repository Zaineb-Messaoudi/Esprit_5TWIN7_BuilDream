<?php

namespace App\Http\Controllers;

use Illuminate\Contracts\View\View;

class FaqController extends Controller
{
    public function index(): View
    {
        return view('pages.special.faq', [
            'title' => __('Frequently Asked Questions'),
            'questions' => [
                [
                    'question' => __('Are the dashboard numbers connected to SolarShare data?'),
                    'answer' => __('No. New dashboard and chart values are illustrative UI examples unless a page explicitly identifies an existing application data source.'),
                ],
                [
                    'question' => __('Do demo forms save changes?'),
                    'answer' => __('Most new page-library forms are local interaction demos and do not write to the database. Existing profile and authentication forms retain their real Laravel behavior.'),
                ],
                [
                    'question' => __('Does the AI Studio generate content?'),
                    'answer' => __('No AI provider is connected. Prompts remain in the browser demo and no generated text, images, video, or code is returned.'),
                ],
                [
                    'question' => __('Can I switch between light and dark themes?'),
                    'answer' => __('Use the theme control in the header. The selected appearance is stored in this browser and applies to the dashboard interface.'),
                ],
                [
                    'question' => __('Are checkout and payment actions live?'),
                    'answer' => __('No. The ecommerce checkout and transaction screens are non-operational examples and do not charge a payment method.'),
                ],
                [
                    'question' => __('How do I update my account password?'),
                    'answer' => __('Open Profile, then use the existing password form. The new UI examples do not replace Laravel authentication or account security.'),
                ],
            ],
        ]);
    }
}
