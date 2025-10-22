<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
use Carbon\Carbon;

class ApiToken extends Model
{
    protected $fillable = ['user_id', 'token', 'expires_at'];

    public static function generateFor($user)
    {
        return self::create([
            'user_id' => $user->id,
            'token' => hash('sha256', Str::random(60)),
            'expires_at' => Carbon::now()->addHours(2),
        ]);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
