<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Question extends Model
{
    use HasFactory;

    protected $fillable = [
        'quiz_id', 'subject_id', 'question', 'option_a', 'option_b', 
        'option_c', 'option_d', 'option_e', 'correct_answer', 'is_used'
    ];

    public function quiz()
    {
        return $this->belongsTo(Quiz::class);
    }

    public function subject()
    {
        return $this->belongsTo(Subject::class);
    }

    public function attempts()
    {
        return $this->hasMany(QuizAttempt::class);
    }
}