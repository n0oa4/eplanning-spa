<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class RevisionNote extends Model
{
    use HasFactory;

    public const STATUS_TERBUKA = 'terbuka';
    public const STATUS_DIKONFIRMASI_OPERATOR = 'dikonfirmasi_operator';
    public const STATUS_SELESAI = 'selesai';

    protected $fillable = [
        'program_id',
        'notable_type',
        'notable_id',
        'catatan',
        'status',
        'created_by',
        'confirmed_by',
        'confirmed_at',
        'resolved_by',
        'resolved_at',
    ];

    protected $casts = [
        'confirmed_at' => 'datetime',
        'resolved_at' => 'datetime',
    ];

    protected $attributes = [
        'status' => self::STATUS_TERBUKA,
    ];

    /**
     * Target catatan ini: bisa berupa Program, Activity (Kegiatan), atau SubActivity (Sub Kegiatan).
     */
    public function notable(): MorphTo
    {
        return $this->morphTo();
    }

    /**
     * Referensi cepat ke Program induk (walau catatan menempel ke Activity/SubActivity).
     */
    public function program(): BelongsTo
    {
        return $this->belongsTo(Program::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function confirmer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'confirmed_by');
    }

    public function resolver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'resolved_by');
    }

    public function scopeTerbuka($query)
    {
        return $query->where('status', self::STATUS_TERBUKA);
    }

    /**
     * Catatan yang belum benar-benar ditutup Kabid (masih terbuka atau menunggu review ulang).
     */
    public function scopeBelumSelesai($query)
    {
        return $query->whereIn('status', [
            self::STATUS_TERBUKA,
            self::STATUS_DIKONFIRMASI_OPERATOR,
        ]);
    }
}