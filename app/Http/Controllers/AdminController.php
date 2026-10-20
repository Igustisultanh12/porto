<?php

namespace App\Http\Controllers;

use App\Http\Requests\ContentRequest;
use App\Http\Requests\SettingsRequest;
use App\Models\Activity;
use App\Models\ContactMessage;
use App\Models\Content;
use App\Models\ProfileEntry;
use App\Models\Setting;
use App\Models\Taxonomy;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;

class AdminController extends Controller
{
    public function dashboard(): Response
    {
        return Inertia::render('Admin/Dashboard', ['counts' => ['content' => Content::count(), 'draft' => Content::where('status', 'draft')->count(), 'unread' => ContactMessage::whereNull('read_at')->whereNull('archived_at')->count()],
            'activities' => Activity::latest()->limit(15)->get()]);
    }

    public function index(Request $request): Response
    {
        $filters = $request->validate(['q' => 'nullable|string|max:120', 'type' => ['nullable', Rule::in(['project', 'research', 'article'])], 'status' => ['nullable', Rule::in(['draft', 'published', 'trash'])]]);
        $query = Content::query();
        if (($filters['status'] ?? '') === 'trash') {
            $query->onlyTrashed();
        } elseif ($status = $filters['status'] ?? null) {
            $query->where('status', $status);
        }
        if ($type = $filters['type'] ?? null) {
            $query->where('type', $type);
        }
        if ($q = $filters['q'] ?? null) {
            $query->where('title', 'like', '%'.$q.'%');
        }

        return Inertia::render('Admin/Contents', ['items' => $query->orderBy('sort_order')->latest('id')->paginate(15)->withQueryString(), 'filters' => $filters]);
    }

    public function create(Request $request): Response
    {
        $type = in_array($request->query('type'), ['project', 'research', 'article'], true) ? $request->query('type') : 'project';

        return Inertia::render('Admin/Editor', ['content' => null, 'type' => $type, 'taxonomies' => Taxonomy::orderBy('kind')->orderBy('name')->get()]);
    }

    public function edit(Content $content): Response
    {
        return Inertia::render('Admin/Editor', ['content' => $content->load('media', 'taxonomies'), 'type' => $content->type, 'taxonomies' => Taxonomy::orderBy('kind')->orderBy('name')->get()]);
    }

    public function store(ContentRequest $request): RedirectResponse
    {
        Gate::authorize('create', Content::class);
        $content = DB::transaction(function () use ($request) {
            $data = $request->safe()->except('taxonomy_ids');
            $data['published_at'] = $data['status'] === 'published' ? now() : null;
            $content = Content::create($data);
            $content->taxonomies()->sync($request->validated('taxonomy_ids', []));
            Activity::record('create', 'content:'.$content->id);

            return $content;
        });

        return redirect()->route('admin.contents.edit', $content)->with('success', 'Konten disimpan.');
    }

    public function update(ContentRequest $request, Content $content): RedirectResponse
    {
        Gate::authorize('update', $content);
        DB::transaction(function () use ($request, $content) {
            $data = $request->safe()->except('taxonomy_ids');
            $data['published_at'] = $data['status'] === 'published' ? ($content->published_at ?? now()) : null;
            $content->update($data);
            $content->taxonomies()->sync($request->validated('taxonomy_ids', []));
            Activity::record('update', 'content:'.$content->id);
        });

        return back()->with('success', 'Perubahan disimpan.');
    }

    public function destroy(Content $content): RedirectResponse
    {
        Gate::authorize('delete', $content);
        $content->delete();
        Activity::record('trash', 'content:'.$content->id);

        return redirect()->route('admin.contents.index')->with('success', 'Konten dipindahkan ke sampah.');
    }

    public function bulk(Request $request): RedirectResponse
    {
        $data = $request->validate(['ids' => 'required|array|min:1|max:100', 'ids.*' => 'integer|distinct|exists:contents,id', 'action' => ['required', Rule::in(['publish', 'unpublish', 'trash', 'restore'])]]);
        DB::transaction(function () use ($data) {
            $items = Content::withTrashed()->whereIn('id', $data['ids'])->lockForUpdate()->get();
            foreach ($items as $item) {
                Gate::authorize('update', $item);
                if ($data['action'] === 'restore') {
                    $item->status = 'draft';
                    $item->published_at = null;
                    $item->restore();
                    $item->save();
                } elseif ($data['action'] === 'trash') {
                    $item->delete();
                } elseif (! $item->trashed()) {
                    $item->update(['status' => $data['action'] === 'publish' ? 'published' : 'draft', 'published_at' => $data['action'] === 'publish' ? ($item->published_at ?? now()) : null]);
                }
                Activity::record($data['action'], 'content:'.$item->id);
            }
        });

        return back()->with('success', 'Konten diperbarui.');
    }

    public function reorder(Request $request, Content $content): RedirectResponse
    {
        $data = $request->validate(['sort_order' => 'required|integer|min:0|max:100000']);
        $content->update($data);

        return back()->with('success', 'Urutan diperbarui.');
    }

    public function settings(): Response
    {
        return Inertia::render('Admin/Settings', ['settings' => Setting::values(), 'entries' => ProfileEntry::orderBy('kind')->orderBy('sort_order')->get()]);
    }

    public function saveSettings(SettingsRequest $request): RedirectResponse
    {
        Setting::updateOrCreate(['id' => 1], ['data' => $request->validated()]);
        Activity::record('update', 'settings');

        return back()->with('success', 'Profil dan pengaturan disimpan.');
    }

    public function saveEntry(Request $request): RedirectResponse
    {
        $data = $request->validate(['id' => 'nullable|integer|exists:profile_entries,id', 'kind' => ['required', Rule::in(['skill', 'experience', 'education', 'social'])], 'title' => 'required|string|max:180', 'subtitle' => 'nullable|string|max:255', 'body' => 'nullable|string|max:5000', 'url' => 'nullable|url:http,https|max:2048', 'sort_order' => 'required|integer|min:0|max:100000']);
        $id = $data['id'] ?? null;
        unset($data['id']);
        $entry = ProfileEntry::updateOrCreate(['id' => $id], $data);
        Activity::record('save', 'entry:'.$entry->id);

        return back()->with('success', 'Entri profil disimpan.');
    }

    public function deleteEntry(ProfileEntry $entry): RedirectResponse
    {
        $entry->delete();
        Activity::record('delete', 'entry:'.$entry->id);

        return back()->with('success', 'Entri dihapus.');
    }

    public function taxonomies(): Response
    {
        return Inertia::render('Admin/Taxonomies', ['items' => Taxonomy::orderBy('kind')->orderBy('name')->get()]);
    }

    public function saveTaxonomy(Request $request): RedirectResponse
    {
        $data = $request->validate(['id' => 'nullable|integer|exists:taxonomies,id', 'kind' => ['required', Rule::in(['category', 'tag', 'technology', 'topic'])], 'name' => ['required', 'string', 'max:100', Rule::unique('taxonomies')->where('kind', $request->input('kind'))->ignore($request->input('id'))]]);
        $id = $data['id'] ?? null;
        unset($data['id']);
        $item = Taxonomy::updateOrCreate(['id' => $id], $data);
        Activity::record('save', 'taxonomy:'.$item->id);

        return back()->with('success', 'Taksonomi disimpan.');
    }

    public function deleteTaxonomy(Taxonomy $taxonomy): RedirectResponse
    {
        $taxonomy->delete();
        Activity::record('delete', 'taxonomy:'.$taxonomy->id);

        return back()->with('success', 'Taksonomi dihapus.');
    }

    public function messages(Request $request): Response
    {
        $filters = $request->validate(['status' => ['nullable', Rule::in(['unread', 'archive'])], 'q' => 'nullable|string|max:120']);
        $query = ContactMessage::query();
        if (($filters['status'] ?? '') === 'archive') {
            $query->whereNotNull('archived_at');
        } else {
            $query->whereNull('archived_at');
        }
        if (($filters['status'] ?? '') === 'unread') {
            $query->whereNull('read_at');
        }
        if ($q = $filters['q'] ?? null) {
            $query->where(fn ($query) => $query->where('subject', 'like', '%'.$q.'%')->orWhere('name', 'like', '%'.$q.'%'));
        }

        return Inertia::render('Admin/Messages', ['items' => $query->latest()->paginate(15)->withQueryString(), 'filters' => $filters]);
    }

    public function messageAction(Request $request, ContactMessage $message): RedirectResponse
    {
        $action = $request->validate(['action' => ['required', Rule::in(['read', 'unread', 'archive', 'unarchive', 'delete'])]])['action'];
        if ($action === 'delete') {
            $message->delete();
        } else {
            $message->update(match ($action) {
                'read' => ['read_at' => now()],'unread' => ['read_at' => null],'archive' => ['archived_at' => now()],'unarchive' => ['archived_at' => null]
            });
        }
        Activity::record($action,'message:'.$message->id);

        return back()->with('success','Pesan diperbarui.');
    }
}
