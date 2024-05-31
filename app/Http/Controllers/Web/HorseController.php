<?php
<<<<<<< HEAD

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class HorseController extends Controller
{
 
=======
namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Horse;
use Illuminate\Http\Request;
use Validator;

class HorseController extends Controller
{
>>>>>>> c63b82f715b9dbcafe38d4530744d25b33228f80
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $horses = Horse::all();
<<<<<<< HEAD
        return view('admin.horse.index', compact('horses'));
    }

    public function create()
    {
        // Lógica para mostrar el formulario de creación
        return view('admin.horse.create');
=======
        return response()->json($horses);
>>>>>>> c63b82f715b9dbcafe38d4530744d25b33228f80
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
<<<<<<< HEAD
        $horse = new Horse($request ->all());
        $horse->save();
        return redirect('admin/horse');
=======
        $validator = Validator::make($request->all(), [
            'name' =>'required',
            'breed' =>'required',
            'date_of_birth' =>'required',
            'sick' =>'required',
            'observations' =>'required',
            'price' =>'required',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 400);
        }

        $horse = Horse::create($request->all());

        return response()->json($horse, 201);
>>>>>>> c63b82f715b9dbcafe38d4530744d25b33228f80
    }

    /**
     * Display the specified resource.
     */
    public function show($id)
    {
<<<<<<< HEAD
        // Verificar el rol del usuario actual
        if (auth()->user()->hasRole('admin')) {
            // Redireccionar al administrador a la página de administración de caballos
            return redirect()->route('admin.horse.index');
        } elseif (auth()->user()->hasRole('user')) {
            // Redireccionar al usuario a la página de reservas de usuario
            return redirect()->route('booking.index');
        } else {
            // Si el usuario no tiene un rol válido, redireccionar a alguna página predeterminada
            return redirect('/home');
        }
    }
    

  
    public function edit(Request $request, $id)
    {
        $horse = Horse::find($id);
        return view('admin.horse.edit', compact('horse'));

=======
        $horse = Horse::find($id);

        if (is_null($horse)) {
            return response()->json(['message' => 'Horse not found'], 404);
        }

        return response()->json($horse, 200);
>>>>>>> c63b82f715b9dbcafe38d4530744d25b33228f80
    }

    /**
     * Update the specified resource in storage.
     */
<<<<<<< HEAD
    public function update(Request $request, $id){
        $horse = Horse::find($id);
        $horse->fill($request->all());
        $horse->save();
        return redirect('/admin/horse');
    }
    
=======
    public function update(Request $request, $id)
    {
        $horse = Horse::find($id);

        if (is_null($horse)) {
            return response()->json(['message' => 'Horse not found'], 404);
        }

        $validator = Validator::make($request->all(), [
            'name' =>'required',
            'breed' =>'required',
            'date_of_birth' =>'required',
            'sick' =>'required',
            'observations' =>'required',
            'price' =>'required',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 400);
        }

        $horse->update($request->all());

        return response()->json($horse, 200);
    }

>>>>>>> c63b82f715b9dbcafe38d4530744d25b33228f80
    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
<<<<<<< HEAD
        $horse = Horse::findOrFail($id);
        $horse->delete();
        return redirect('/admin/horse');
    }
}
=======
        $horse = Horse::find($id);

        if (is_null($horse)) {
            return response()->json(['message' => 'Horse not found'], 404);
        }

        $horse->delete();

        return response()->json(['message' => 'Horse deleted successfully'], 204);
    }
}

>>>>>>> c63b82f715b9dbcafe38d4530744d25b33228f80
