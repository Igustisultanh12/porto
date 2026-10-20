<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class Content extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = ['type', 'title', 'slug', 'summary', 'body', 'status', 'featured', 'sort_order', 'year', 'publication_type', 'metadata', 'seo_title', 'seo_description', 'published_at'];

    protected $appends = ['url', 'body_html'];

    protected function casts(): array
    {
        return ['metadata' => 'array', 'featured' => 'boolean', 'published_at' => 'datetime'];
    }

    public function taxonomies(): BelongsToMany
    {
        return $this->belongsToMany(Taxonomy::class);
    }

    public function media(): HasMany
    {
        return $this->hasMany(Media::class);
    }

    public function scopePublished(Builder $query): Builder
    {
        return $query->where('status', 'published')->whereNotNull('published_at')->where('published_at', '<=', now());
    }

    public function scopeCards(Builder $query): Builder
    {
        return $query->select(['id', 'type', 'title', 'slug', 'summary', 'status', 'featured', 'sort_order', 'year', 'publication_type', 'published_at'])
            ->with(['media' => fn (HasMany $media) => $media->where('slot', 'cover'), 'taxonomies']);
    }

    public function isPublished(): bool
    {
        return ! $this->trashed() && $this->status === 'published' && $this->published_at?->lte(now());
    }

    public function getUrlAttribute(): string
    {
        return route('content.show', ['type' => $this->type, 'slug' => $this->slug], false);
    }

    public function getBodyHtmlAttribute(): string
    {
        return (string) Str::markdown($this->body ?? '', ['html_input' => 'strip', 'allow_unsafe_links' => false]);
    }

    public function citation(): string
    {
        $m = $this->metadata ?? [];
        $authors = implode(', ', array_column($m['authors'] ?? [], 'name'));

        return implode('. ', array_filter([$authors, $this->year ? '('.$this->year.')' : null, $this->title, $m['venue'] ?? null, ! empty($m['doi']) ? 'https://doi.org/'.$m['doi'] : null]));
    }

    public function bibtex(): string
    {
        $m = $this->metadata ?? [];
        $fields = ['title' => $this->title, 'author' => implode(' and ', array_column($m['authors'] ?? [], 'name')), 'year' => $this->year, 'journal' => $m['venue'] ?? null, 'doi' => $m['doi'] ?? null, 'url' => url($this->url)];
        $lines = [];
        foreach ($fields as $key => $value) {
            if ($value !== null && $value !== '') {
                $value = strtr((string) $value, ['\\' => '\\textbackslash{}', '{' => '\\{', '}' => '\\}', '%' => '\\%', '&' => '\\&', '_' => '\\_', '#' => '\\#', "\n" => ' ', "\r" => ' ']);
                $lines[] = '  '.$key.' = {'.$value.'}';
            }
        }

        return '@misc{porto'.$this->id.",\n".implode(",\n", $lines)."\n}\n";
    }
}
