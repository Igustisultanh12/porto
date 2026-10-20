<?php

namespace Database\Seeders;

use App\Models\Content;
use Illuminate\Database\Seeder;

class DemoSeeder extends Seeder
{
    public function run(): void
    {
        foreach (['project' => 'Proyek', 'research' => 'Riset', 'article' => 'Artikel'] as $type => $label) {
            Content::firstOrCreate(['slug' => 'demo-'.$type], ['type' => $type, 'title' => '[DEMO] Contoh '.$label, 'summary' => 'Data ilustrasi untuk mencoba editor. Bukan karya atau prestasi pemilik.', 'body' => '## Data demo'."\n\n".'Ganti dengan konten milikmu sebelum menerbitkan.', 'status' => 'draft', 'metadata' => []]);
        }
    }
}
