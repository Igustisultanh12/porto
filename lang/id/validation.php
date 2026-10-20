<?php

return [
    'required' => ':attribute wajib diisi.', 'email' => ':attribute harus berupa email yang valid.',
    'string' => ':attribute harus berupa teks.', 'integer' => ':attribute harus berupa bilangan bulat.',
    'boolean' => ':attribute harus bernilai benar atau salah.', 'array' => ':attribute tidak valid.',
    'url' => ':attribute harus berupa URL HTTP atau HTTPS yang valid.', 'uuid' => ':attribute tidak valid. Muat ulang halaman.',
    'unique' => ':attribute sudah digunakan. Gunakan nilai lain.', 'exists' => ':attribute tidak ditemukan.',
    'in' => ':attribute tidak termasuk pilihan yang tersedia.', 'regex' => 'Format :attribute tidak valid.',
    'confirmed' => 'Konfirmasi :attribute tidak cocok.', 'current_password' => 'Kata sandi saat ini tidak cocok.',
    'date_format' => ':attribute harus menggunakan format :format.', 'file' => ':attribute harus berupa berkas.',
    'mimes' => ':attribute harus berupa berkas: :values.', 'distinct' => ':attribute tidak boleh duplikat.',
    'max' => ['string' => ':attribute maksimal :max karakter.', 'numeric' => ':attribute maksimal :max.', 'array' => ':attribute maksimal :max item.', 'file' => ':attribute maksimal :max KB.'],
    'min' => ['string' => ':attribute minimal :min karakter.', 'numeric' => ':attribute minimal :min.', 'array' => ':attribute minimal :min item.', 'file' => ':attribute minimal :min KB.'],
    'password' => ['letters' => ':attribute harus mengandung huruf.', 'mixed' => ':attribute harus mengandung huruf besar dan kecil.', 'numbers' => ':attribute harus mengandung angka.'],
    'attributes' => ['title' => 'Judul', 'slug' => 'Slug', 'name' => 'Nama', 'email' => 'Email', 'password' => 'Kata sandi', 'message' => 'Pesan', 'subject' => 'Subjek', 'file' => 'Berkas', 'status' => 'Status', 'type' => 'Jenis', 'website' => 'Website', 'sort_order' => 'Urutan'],
];
