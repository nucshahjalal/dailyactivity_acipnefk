<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Task extends Model
{
    use HasFactory;
    protected $table = 'task';

    // Task.php (Model)
    public function user()
    {
        return $this->belongsTo(\App\Models\User::class, 'userid'); // Replace 'userid' with the actual foreign key in the Task table
    }

}
