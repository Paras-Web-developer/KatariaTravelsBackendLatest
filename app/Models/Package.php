<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\SoftDeletes;

class Package extends Model
{
	use HasFactory, Notifiable, SoftDeletes;

	/**
	 * The attributes that are mass assignable.
	 *
	 * @var array<int, string>
	 */
	protected $fillable = [
		'enquiry_id',
		'package_type',
		'departure_date',
		'from',
		'to',
		'parent_id',
	];

	function parent(): BelongsTo
	{
		return $this->belongsTo(Package::class, 'parent_id');
	}

	function children(): HasMany
	{
		return $this->hasMany(Package::class, 'parent_id');
	}

	public function enquiry(): BelongsTo
	{
		return $this->belongsTo(Enquiry::class, 'enquiry_id');
	}

	public function fromAirport(): BelongsTo
	{
		return $this->belongsTo(WorldAirport::class, 'from');
	}

	public function toAirport(): BelongsTo
	{
		return $this->belongsTo(WorldAirport::class, 'to');
	}
}
