<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rules\Password;

class CreateAdmin extends Command
{
    protected $signature = 'app:create-admin';

    protected $description = 'Buat admin tanpa password bawaan (interaktif)';

    public function handle(): int
    {
        $data = ['name' => $this->ask('Nama'), 'email' => $this->ask('Email'), 'password' => $this->secret('Password (12+ karakter, huruf besar/kecil dan angka)')];
        $data['password_confirmation'] = $this->secret('Ulangi password');
        $validator = Validator::make($data, ['name' => 'required|string|max:100', 'email' => 'required|email|unique:users,email', 'password' => ['required', 'confirmed', Password::min(12)->mixedCase()->numbers()]]);
        if ($validator->fails()) {
            foreach ($validator->errors()->all() as $error) {
                $this->error($error);
            }

return self::FAILURE;
        }
        $user = new User;
        $user->name = $data['name'];
        $user->email = $data['email'];
        $user->password = Hash::make($data['password']);
        $user->is_admin = true;
        $user->save();
        $this->info('Admin dibuat. Masuk melalui /admin/login.');

        return self::SUCCESS;
    }
}
