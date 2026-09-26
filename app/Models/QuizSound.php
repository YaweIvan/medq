<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class QuizSound extends Model
{
    use HasFactory;

    protected $fillable = [
        'sound_type',
        'file_name',
        'file_path',
        'mime_type',
        'file_size',
        'volume',
        'uploaded_by',
    ];

    /**
     * Get the user who uploaded this sound
     */
    public function uploader()
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }

    /**
     * Get the full URL to the sound file
     */
    public function getUrlAttribute()
    {
        return asset($this->file_path);
    }

    /**
     * Get human readable file size
     */
    public function getFileSizeHumanAttribute()
    {
        $bytes = $this->file_size;
        if ($bytes >= 1048576) {
            return number_format($bytes / 1048576, 2) . ' MB';
        } elseif ($bytes >= 1024) {
            return number_format($bytes / 1024, 2) . ' KB';
        } else {
            return $bytes . ' bytes';
        }
    }
}
