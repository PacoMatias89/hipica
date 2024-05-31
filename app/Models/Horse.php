<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

use Orchid\Attachment\Attachable;
use Orchid\Filters\Filterable;
use Orchid\Screen\AsSource;
class Horse extends Model
{
    use HasFactory;
    use AsSource, Filterable, Attachable;

    protected $fillable = [
        'name',
        'breed',
        'date_of_birth',
        'sick',
        'observations',
        'price'
    ];

    public function bookings()
    {
        return $this->hasMany(Booking::class);
    }
<<<<<<< HEAD
}
=======
}
>>>>>>> c63b82f715b9dbcafe38d4530744d25b33228f80
