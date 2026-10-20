<?php

namespace App\Http\Controllers;

use App\Models\Content;
use App\Models\ProfileEntry;
use App\Models\Taxonomy;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Inertia\Response;

class PublicController extends Controller
{
    public function home(): Response
    {
        return Inertia::render('Home', [
            'featured' => Content::published()->cards()->where('featured', true)->orderBy('sort_order')->latest('published_at')->limit(6)->get(),
            'entries' => ProfileEntry::whereIn('kind', ['skill', 'experience', 'education'])->orderBy('sort_order')->limit(12)->get(),
            'counts' => Content::published()->selectRaw('type, count(*) as total')->groupBy('type')->pluck('total', 'type'),
        ]);
    }

    public function about(): Response
    {
        return Inertia::render('About', ['entries' => ProfileEntry::orderBy('sort_order')->get()]);
    }

    public function contact(): Response
    {
        return Inertia::render('Contact', ['submissionId' => (string) Str::uuid()]);
    }

    public function index(Request $request, string $type): Response
    {
        abort_unless(in_array($type, ['project', 'research', 'article'], true), 404);
        $filters = $request->validate(['q' => 'nullable|string|max:120', 'taxonomy' => 'nullable|integer', 'year' => 'nullable|integer|min:1900|max:2100', 'publication_type' => 'nullable|string|max:40']);
        $query = Content::published()->where('type', $type)->cards();
        if ($search = $filters['q'] ?? null) {
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', '%'.$search.'%')->orWhere('summary', 'like', '%'.$search.'%');
            });
        }
        if ($taxonomy = $filters['taxonomy'] ?? null) {
            $query->whereHas('taxonomies', fn ($q) => $q->where('taxonomies.id', $taxonomy));
        }
        if ($year = $filters['year'] ?? null) {
            $query->where('year', $year);
        }
        if ($publication = $filters['publication_type'] ?? null) {
            $query->where('publication_type', $publication);
        }

        return Inertia::render('Listing', ['type' => $type, 'items' => $query->orderBy('sort_order')->latest('published_at')->paginate(9)->withQueryString(),
            'filters' => $filters, 'taxonomies' => Taxonomy::whereHas('contents', fn ($q) => $q->published()->where('type', $type))->orderBy('name')->get(),
            'years' => Content::published()->where('type', $type)->whereNotNull('year')->distinct()->orderByDesc('year')->pluck('year')]);
    }

    public function show(string $type, string $slug): Response
    {
        $content = Content::published()->where('type', $type)->where('slug', $slug)->firstOrFail();

        return $this->detail($content);
    }

    public function preview(Content $content): Response
    {
        return $this->detail($content, true);
    }

    private function detail(Content $content, bool $preview = false): Response
    {
        return Inertia::render('Detail', ['content' => $content->load('media', 'taxonomies'), 'citation' => $content->citation(), 'preview' => $preview]);
    }

    public function bibtex(string $slug): \Illuminate\Http\Response
    {
        $content = Content::published()->where('type', 'research')->where('slug', $slug)->firstOrFail();

        return response($content->bibtex(), 200, ['Content-Type' => 'application/x-bibtex; charset=UTF-8', 'Content-Disposition' => 'attachment; filename="'.$content->slug.'.bib"']);
    }

    public function sitemap(): \Illuminate\Http\Response
    {
        $urls = collect([route('home'), route('about'), route('contact'), route('content.index', 'project'), route('content.index', 'research'), route('content.index', 'article')]);
        Content::published()->select('id', 'type', 'slug')->orderBy('id')->chunk(500, function ($items) use ($urls) {
            foreach ($items as $item) {
                $urls->push(url($item->url));
            }
        });
        $xml = '<?xml version="1.0" encoding="UTF-8"?><urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">';
        foreach ($urls as $url) {
            $xml .= '<url><loc>'.htmlspecialchars($url, ENT_XML1, 'UTF-8').'</loc></url>';
        }

        return response($xml.'</urlset>', 200, ['Content-Type' => 'application/xml']);
    }
}
