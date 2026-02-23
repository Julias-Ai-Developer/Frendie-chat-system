<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ChatMessage extends Model
{
    /** @use HasFactory<\Database\Factories\ChatMessageFactory> */
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'sender_id',
        'recipient_id',
        'body',
        'message_type',
        'attachment_path',
        'attachment_name',
        'attachment_mime',
        'attachment_size',
        'view_once',
        'opened_at',
        'metadata',
        'read_at',
    ];

    /**
     * The attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'read_at' => 'datetime',
            'opened_at' => 'datetime',
            'view_once' => 'boolean',
            'attachment_size' => 'integer',
            'metadata' => 'array',
        ];
    }

    /**
     * Message sender.
     */
    public function sender(): BelongsTo
    {
        return $this->belongsTo(User::class, 'sender_id');
    }

    /**
     * Message recipient.
     */
    public function recipient(): BelongsTo
    {
        return $this->belongsTo(User::class, 'recipient_id');
    }
}
