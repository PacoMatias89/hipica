<?php

namespace App\Orchid\Resources;

use Orchid\Crud\Resource;
use Orchid\Screen\TD;
use Orchid\Screen\Fields\Input;
use Orchid\Screen\Fields\TextArea;
use Orchid\Screen\Fields\DateTimer;
use Orchid\Screen\Fields\Relation;



use App\Models\Bokking;

use App\Models\Horse;

use App\Models\User;


class BookingResource extends Resource
{
    /**
     * The model the resource corresponds to.
     *
     * @var string
     */
    public static $model = Bokking::class;

    /**
     * Get the fields displayed by the resource.
     *
     * @return array
     */
    public function fields(): array
    {
        return [
            DateTimer::make('date')
            ->title('Fecha')
            ->format('Y-m-d'),

            Input::make('time')
                ->title('Hora'),

            TextArea::make('comments')
                ->title('Comentario'),

            Relation::make('user_id')
                ->title('Nombre del usuario')
                ->fromModel(User::class, 'name'),

            Relation::make('horse_id')
                ->title('Nombre del caballo')
                ->fromModel(Horse::class, 'name'),
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

            TD::make('date'),
    
            TD::make('time'),
    
            TD::make('comments'),
    
            TD::make('user_id', 'Nombre del usuario')
                ->render(function ($model) {
                    return $model->user->name;
                }),
    
            TD::make('horse_id', 'Nombre del caballo')
                ->render(function ($model) {
                    return $model->horse->name;
                }),
    
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
     * Get the sights displayed by the resource.
     *
     * @return Sight[]
     */
    public function legend(): array
    {
        return [
            'title' => 'Legend for BookingResource'

        ];
    }


    
}
