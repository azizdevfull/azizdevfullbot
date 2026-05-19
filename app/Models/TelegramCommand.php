<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TelegramCommand extends Model
{
    protected $fillable = ['command', 'reply'];
}
