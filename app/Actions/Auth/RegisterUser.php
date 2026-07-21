<?php

namespace App\Actions\Auth;

use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class RegisterUser
{
    public function execute(array $data): User
    {
        return DB::transaction(function () use ($data) {
            $user = User::create([
                'name' => $data['name'],
                'email' => $data['email'],
                'phone' => $data['phone'] ?? null,
                'gender' => $data['gender'],
                'password' => Hash::make($data['password']),
            ]);

            // Pokud se nepodaří přiřadit roli, vyvolá to výjimku a transakce se vrátí (rollback)
            $user->assignRole('user');

            return $user;
        });
    }
}