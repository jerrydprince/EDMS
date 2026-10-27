<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class SupportTicket extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'support_tickets';

    protected $fillable = [
        'ticket_no',
        'user_id',
        'category',
        'subject',
        'description',
        'priority',
        'status',
        'assigned_to',
        'resolution_notes',
        'attachment_path',
        'attachment_name',
        'resolved_at',
    ];

    protected $casts = [
        'resolved_at' => 'datetime',
    ];

    /**
     * The staff officer who submitted the ticket.
     */
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * The IT officer/admin assigned to investigate.
     */
    public function assignedTo()
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }
}
