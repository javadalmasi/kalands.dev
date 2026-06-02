<?php

namespace App\Http\Controllers;

use App\Models\Faq;
use Illuminate\Http\Request;

class FaqController extends Controller
{
    public function index(Request $request)
    {
        $q = trim((string) $request->query('q', ''));

        $faqs = Faq::query()
            ->where('is_active', true)
            ->when($q !== '', function ($query) use ($q) {
                $query->where(function ($subQuery) use ($q) {
                    $subQuery->where('title', 'like', "%{$q}%")
                        ->orWhere('description', 'like', "%{$q}%");
                });
            })
            ->orderBy('sort_order')
            ->orderByDesc('id')
            ->get();

        $jsonLd = [
            '@context' => 'https://schema.org',
            '@type' => 'FAQPage',
            'mainEntity' => $faqs->map(function (Faq $faq) {
                return [
                    '@type' => 'Question',
                    'name' => $faq->title,
                    'acceptedAnswer' => [
                        '@type' => 'Answer',
                        'text' => strip_tags((string) $faq->description),
                    ],
                ];
            })->values()->all(),
        ];

        return view('faq.index', compact('faqs', 'q', 'jsonLd'));
    }
}
