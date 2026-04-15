<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class VolunteerApplication extends Model
{
    protected $fillable = [
        'full_name',
        'email',
        'phone',
        'city',
        'role_interest',
        'message',
        'experience',
        'motivation',
        'availability',
        'status',
        'admin_notes',
    ];
}
