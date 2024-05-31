<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Orchid\Attachment\Attachable;
use Orchid\Filters\Filterable;
use Orchid\Screen\AsSource;

class Bokking extends Model
{
    use HasFactory;
    use AsSource, Filterable, Attachable;

    protected $fillable = [
        'date',
        'time',
        'comments',
        'user_id',
        'horse_id',
    ];

    public function user(){
        return $this -> belongsTo(User::class);
    }
    public function horse(){
        return $this -> belongsTo(Horse::class);
    }
}