<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserFeedback extends Model
{
    use HasFactory;

    public const STATUS_NEW = 'new';
    public const STATUS_REVIEWING = 'reviewing';
    public const STATUS_RESOLVED = 'resolved';

    protected $table = 'user_feedback';

    protected $fillable = [
        'user_id',
        'category',
        'rating',
        'subject',
        'message',
        'status',
        'reviewed_at',
        'reviewed_by',
    ];

    protected function casts(): array
    {
        return [
            'rating' => 'integer',
            'reviewed_at' => 'datetime',
        ];
    }

    public static function categories(): array
    {
        return [
            'general' => __('General'),
            'bug' => __('Problem or error'),
            'idea' => __('Improvement idea'),
            'usability' => __('Usability'),
        ];
    }

    public static function statuses(): array
    {
        return [
            self::STATUS_NEW => __('New'),
            self::STATUS_REVIEWING => __('Reviewing'),
            self::STATUS_RESOLVED => __('Resolved'),
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }
}
