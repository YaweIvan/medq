<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Subject extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'max_questions', 'marks_per_question', 'sort_order'];

    protected static function boot()
    {
        parent::boot();
        
        static::creating(function ($subject) {
            if (is_null($subject->sort_order)) {
                $subject->sort_order = static::max('sort_order') + 1;
            }
        });
    }

    public function scopeOrdered($query)
    {
        return $query->orderBy('sort_order', 'asc')
                    ->orderBy('created_at', 'asc');
    }

    public function questions()
    {
        return $this->hasMany(Question::class);
    }

    public function attempts()
    {
        return $this->hasMany(QuizAttempt::class);
    }
}