<?php

namespace App\Http\Controllers\Api;
   
use Illuminate\Http\Request;
use App\Http\Controllers\Api\BaseController as BaseController;
use Illuminate\Support\Facades\Auth;
use Validator;
use App\Models\User;

class AuthController extends BaseController
{
    public function login(Request $request)
    {
        // Validar las credenciales proporcionadas por el usuario
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
            'password' => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => 'Validation Error', 'message' => $validator->errors()], 422);
        }

        // Intentar autenticar al usuario
        if (Auth::attempt(['email' => $request->email, 'password' => $request->password])) {
            $user = Auth::user();

            // Verificar si el correo electrónico del usuario está verificado
            if ($user->email_verified_at) {
                // Generar un token para el usuario autenticado
                $token = $user->createToken('MyAuthApp')->plainTextToken;

                // Retornar la respuesta exitosa con el token y el nombre del usuario
                return response()->json(['success' => ['token' => $token, 'name' => $user->name]], 200);
            } else {
                // El correo electrónico no está verificado
                return response()->json(['error' => 'Unverified', 'message' => 'El correo electrónico no está verificado'], 401);
            }
        } else {
            // Las credenciales son inválidas
            return response()->json(['error' => 'Unauthorized', 'message' => 'Credenciales inválidas'], 401);
        }
    }
    
    
    public function register(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required',
            'email' => 'required|email',
            'password' => 'required',
            'confirm_password' => 'required|same:password',
        ]);
   
        if($validator->fails()){
            return $this->sendError('Error validation', $validator->errors());       
        }
   
        try {
            $input = $request->all();
            $input['password'] = bcrypt($input['password']);
            $user = User::create($input);
            $success['token'] =  $user->createToken('MyAuthApp')->plainTextToken;
            $success['name'] =  $user->name;
       
            return $this->sendResponse($success, 'User created successfully.');
        } catch (\Exception $e) {
            return $this->sendError('Registration Error' , $e->getMessage());
        }
    }
    public function logout(Request $request)
    {
        
        // Get user who requested the logout
        $user = request()->user(); //or Auth::user()
        // Revoke current user token
        $user->tokens()->where('id', $user->currentAccessToken()->id)->delete();
        $success['name'] =  $user->name;
        // return response()->json(['message' => 'User successfully signed out']);
        return $this->sendResponse($success, 'User successfully signed out.');
    }

    
    public function updateProfile(Request $request)
    {
        // Obtener el usuario autenticado a través del token
        $user = Auth::user();

        // Validar los datos proporcionados por el usuario
        $validator = Validator::make($request->all(), [
            'name' => 'required',
            'email' => 'required|email',
        ]);

        if ($validator->fails()) {
            return $this->sendError('Error de validación', $validator->errors());
        }

        try {
            // Actualizar los detalles del usuario
            $user->name = $request->name;
            $user->email = $request->email;
            $user->save();

            // Crear un array con los datos del usuario
            $userData = [
                'name' => $user->name,
                'email' => $user->email,
            ];

            // Retornar una respuesta exitosa
            return $this->sendResponse($userData, 'Perfil de usuario actualizado correctamente.');
        } catch (\Exception $e) {
            // Retornar una respuesta de error
            return $this->sendError('Error de actualización', $e->getMessage());
        }
    }
    

    public function userDetails(Request $request)
    {
        // Obtener el usuario autenticado a través del token
        $user = Auth::user();

        // Retornar los detalles del usuario como respuesta
        return $this->sendResponse($user, 'Detalles del usuario obtenidos correctamente.');
    }
}