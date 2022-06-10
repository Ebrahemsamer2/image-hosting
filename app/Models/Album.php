<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

use App\Models\User;

class Album extends Model
{
    use HasFactory;
    protected $fillable = ['name', 'user_id'];

    public static function getAll()
    {
        return self::orderBy('created_at', 'DESC')->get();
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}