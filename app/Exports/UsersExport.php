<?php

namespace App\Exports;

use App\Models\User;
use Maatwebsite\Excel\Concerns\FromCollection;

class UsersExport implements FromCollection
{
    public function collection()
    {
        return User::all();
    }

    // public function map($user): array
    // {
    //     return [
    //         $user->id,
    //         $user->name,
    //         $user->email,
    //         $user->created_at->format('Y-m-d'), // Format the date
    //     ];
    // }

}