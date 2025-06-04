<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Attendance extends Model
{
    use HasApiTokens , SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'employee_user_id',
        'date',
        'entry_time',
        'exit_time',
        'total_time',
        'status',
    ];


    public function employeeUser() :BelongsTo{
        return $this->belongsTo(User::class , 'employee_user_id');
    }

    public function getDailyDurationAttribute()
    {
        if ($this->entry_time && $this->exit_time) {
            $entry = Carbon::parse($this->entry_time);
            $exit = Carbon::parse($this->exit_time);
            return $exit->diff($entry)->format('%H:%I:%S');
        }
        return null;
    }
}
