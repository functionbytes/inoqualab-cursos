<?php

namespace App\Services;

class SchemaOrgService
{
    public function organization(): array
    {
        return array_filter([
            '@context' => 'https://schema.org',
            '@type' => 'Organization',
            'name' => setting('seo_site_name', config('app.name')),
            'url' => url('/'),
            'logo' => getlogo(),
            'sameAs' => array_values(array_filter([
                setting('social_media_facebook', ''),
                setting('social_media_instagram', ''),
                setting('social_media_linkedin', ''),
                setting('social_media_twitter', ''),
                setting('social_media_youtube', ''),
            ])),
        ]);
    }

    public function course(object $course): array
    {
        return array_filter([
            '@context' => 'https://schema.org',
            '@type' => 'Course',
            'name' => $course->title ?? '',
            'description' => strip_tags($course->short ?? $course->description ?? ''),
            'url' => $course->url ?? url('/'),
            'provider' => [
                '@type' => 'Organization',
                'name' => setting('seo_site_name', config('app.name')),
                'url' => url('/'),
            ],
            'image' => $course->seoMeta?->og_image ?? '',
        ]);
    }

    public function bundle(object $bundle, string $image = ''): array
    {
        return array_filter([
            '@context' => 'https://schema.org',
            '@type' => 'Course',
            'name' => $bundle->title ?? '',
            'description' => strip_tags($bundle->description ?? ''),
            'url' => $bundle->url ?? url()->current(),
            'provider' => [
                '@type' => 'Organization',
                'name' => setting('seo_site_name', config('app.name')),
                'url' => url('/'),
            ],
            'image' => $image,
            'offers' => $bundle->price ? [
                '@type' => 'Offer',
                'price' => (string) $bundle->price,
                'priceCurrency' => 'COP',
                'availability' => 'https://schema.org/InStock',
                'url' => $bundle->url ?? url()->current(),
            ] : null,
        ]);
    }

    public function article(object $post): array
    {
        return array_filter([
            '@context' => 'https://schema.org',
            '@type' => 'Article',
            'headline' => $post->title ?? '',
            'description' => $post->description ?? '',
            'url' => $post->url ?? url('/'),
            'datePublished' => $post->created_at?->toIso8601String(),
            'dateModified' => $post->updated_at?->toIso8601String(),
            'publisher' => [
                '@type' => 'Organization',
                'name' => setting('seo_site_name', config('app.name')),
                'logo' => ['@type' => 'ImageObject', 'url' => getlogo()],
            ],
        ]);
    }

    public function breadcrumbs(array $items): array
    {
        $list = [];
        foreach ($items as $position => $item) {
            $list[] = [
                '@type' => 'ListItem',
                'position' => $position + 1,
                'name' => $item['name'],
                'item' => $item['url'] ?? null,
            ];
        }

        return [
            '@context' => 'https://schema.org',
            '@type' => 'BreadcrumbList',
            'itemListElement' => $list,
        ];
    }

    public function faq(array $questions): array
    {
        $entities = [];
        foreach ($questions as $q) {
            $entities[] = [
                '@type' => 'Question',
                'name' => $q['question'],
                'acceptedAnswer' => ['@type' => 'Answer', 'text' => $q['answer']],
            ];
        }

        return [
            '@context' => 'https://schema.org',
            '@type' => 'FAQPage',
            'mainEntity' => $entities,
        ];
    }

    public function webPage(string $title, string $description, string $url): array
    {
        return [
            '@context' => 'https://schema.org',
            '@type' => 'WebPage',
            'name' => $title,
            'description' => $description,
            'url' => $url,
        ];
    }
}
