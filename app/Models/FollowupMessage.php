<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FollowupMessage extends Model
{
	use HasFactory;

	protected $fillable = ['enquiry_id', 'followed_up_at', 'follow_up_message', 'is_sent', 'receiver_id', 'followupable_type', 'followupable_id'];

	// protected $casts = [
	//     'followed_up_at' => 'datetime',
	// ];
	protected $dates = ['followed_up_at']; // Ensures it's treated as a DateTime object

	public function enquiry()
	{
		return $this->belongsTo(Enquiry::class);
	}

	public function followupable()
	{
		return $this->morphTo();
	}
}
