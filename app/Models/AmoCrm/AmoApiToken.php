<?php

namespace App\Models\AmoCrm;

use Illuminate\Database\Eloquent\Model;

class AmoApiToken extends Model
{
    protected $table = 'amo_api_access_tokens';
    protected $guarded = [];

    public static function getLastToken(): ?Model
    {
        return self::orderBy('created_at', 'desc')->first();
    }
}
