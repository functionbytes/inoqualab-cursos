<?php

namespace App\Http\Controllers\Managers\Seo;

use App\Http\Controllers\Controller;
use App\Models\Blog\Blog;
use App\Models\Bundle\Bundle;
use App\Models\Course\Course;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Illuminate\View\View;

class SeoPageUrlsController extends Controller
{
    private const LIMIT_PER_TYPE = 100;

    public function index(Request $request): View
    {

        $search = $request->input('search');
        $typeFilter = $request->input('type');
        $seoStatus = $request->input('seo_status');

        $items = collect();

        // type filter: view sends 'Course'/'Blog'/'Bundle'
        if (! $typeFilter || $typeFilter === 'Course') {
            $items = $items->merge($this->collectCourses($search));
        }

        if (! $typeFilter || $typeFilter === 'Blog') {
            $items = $items->merge($this->collectBlogs($search));
        }

        if (! $typeFilter || $typeFilter === 'Bundle') {
            $items = $items->merge($this->collectBundles($search));
        }

        // seo_status filter: view sends 'with_seo'/'without_seo'
        if ($seoStatus === 'with_seo') {
            $items = $items->filter(fn ($item) => $item['has_seo']);
        } elseif ($seoStatus === 'without_seo') {
            $items = $items->filter(fn ($item) => ! $item['has_seo']);
        }

        $totalPages = $items->count();
        $withSeo = $items->filter(fn ($item) => $item['has_seo'])->count();
        $withoutSeo = $totalPages - $withSeo;

        $perPage = 50;
        $page = max(1, (int) $request->input('page', 1));
        $offset = ($page - 1) * $perPage;
        $paginated = $items->slice($offset, $perPage)->values();

        $pages = new LengthAwarePaginator(
            $paginated,
            $totalPages,
            $perPage,
            $page,
            ['path' => $request->url(), 'query' => $request->query()]
        );

        return view('managers.views.seo.page-urls.index', compact(
            'pages', 'totalPages', 'withSeo', 'withoutSeo', 'search', 'typeFilter', 'seoStatus'
        ));
    }

    private function collectCourses(?string $search): Collection
    {
        return Course::query()
            ->with('seoMeta')
            ->when($search, fn ($q) => $q->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                    ->orWhere('slug', 'like', "%{$search}%");
            }))
            ->latest()
            ->limit(self::LIMIT_PER_TYPE)
            ->get()
            ->map(fn (Course $course) => $this->mapItem('Curso', $course->title, $course->url, $course->seoMeta));
    }

    private function collectBlogs(?string $search): Collection
    {
        return Blog::query()
            ->with('seoMeta')
            ->when($search, fn ($q) => $q->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                    ->orWhere('slug', 'like', "%{$search}%");
            }))
            ->latest()
            ->limit(self::LIMIT_PER_TYPE)
            ->get()
            ->map(fn (Blog $blog) => $this->mapItem('Blog', $blog->title, $blog->url, $blog->seoMeta));
    }

    private function collectBundles(?string $search): Collection
    {
        return Bundle::query()
            ->with('seoMeta')
            ->when($search, fn ($q) => $q->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                    ->orWhere('slug', 'like', "%{$search}%");
            }))
            ->latest()
            ->limit(self::LIMIT_PER_TYPE)
            ->get()
            ->map(fn (Bundle $bundle) => $this->mapItem('Bundle', $bundle->title, $bundle->url, $bundle->seoMeta));
    }

    private function mapItem(string $type, ?string $title, ?string $url, $seoMeta): array
    {
        return [
            'type' => $type,
            'title' => $title ?? '—',
            'url' => $url ?? '',
            'has_seo' => $seoMeta !== null,
            'seo_id' => $seoMeta?->id,
            'seo_robots' => $seoMeta?->robots,
            'seo_score' => $seoMeta?->seo_score,
        ];
    }
}
