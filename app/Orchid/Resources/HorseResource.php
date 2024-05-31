<?php

namespace App\Orchid\Resources;

use Orchid\Crud\Resource;
use Orchid\Screen\TD;
use Orchid\Screen\Fields\Input;
use Orchid\Screen\Fields\TextArea;
use Orchid\Screen\Fields\DateTimer;
use Orchid\Screen\Fields\CheckBox;

use App\Models\Horse;

class HorseResource extends Resource
{
    /**
     * The model the resource corresponds to.
     *
     * @var string
     */
    public static $model = Horse::class;

    /**
     * Get the fields displayed by the resource.
     *
     * @return array
     */
    public function fields(): array
    {
        return [
            Input::make('name')
                ->title('Nombre'),

            Input::make('breed')
                ->title('Raza'),

            DateTimer::make('date_of_birth')
                ->title('Fecha de nacimiento')
                ->format('Y-m-d'),

            CheckBox::make('sick')
                ->value(1)
                ->title('Enfermo')
                ->placeholder('Is the horse sick?'),

            TextArea::make('observations')
                ->title('Observaciones'),

            Input::make('price')
                ->title('Precio'),
        ];
    }

    /**
     * Get the columns displayed by the resource.
     *
     * @return TD[]
     */
    public function columns(): array
    {
        return [
            TD::make('id'),

            TD::make('name'),

            TD::make('breed'),

            TD::make('date_of_birth'),

            TD::make('sick'),

            TD::make('observations'),

            TD::make('price'),

            TD::make('created_at', 'Date of creation')
                ->render(function ($model) {
                    return $model->created_at->toDateTimeString();
                }),

            TD::make('updated_at', 'Update date')
                ->render(function ($model) {
                    return $model->updated_at->toDateTimeString();
                }),
        ];
    }

    /**
     * Get the filters available for the resource.
     *
     * @return array
     */
    public function filters(): array
    {
        return [];
    }
    
    /**
     * Get the legend for the resource.
     *
     * @return array
     */
    public function legend(): array
    {
        return [
            'title' => 'Legend for HorseResource'
        ];
    }
<<<<<<< HEAD
}
=======
}
>>>>>>> c63b82f715b9dbcafe38d4530744d25b33228f80
