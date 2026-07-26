<?php

namespace App\Models;

use App\Models\Schedule;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Train extends Model
{
    protected $primaryKey = 'train_id';
    public $timestamps = false;
    protected $fillable = ['train_code','train_name','train_class','total_seats'];

    public function schedules(): HasMany
    {
        return $this->hasMany(Schedule::class, 'train_id');
    }
}
