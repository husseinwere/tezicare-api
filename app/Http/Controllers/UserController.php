<?php

namespace App\Http\Controllers;

use App\Mail\UserCreated;
use App\Models\Hospital\Configuration;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;

class UserController extends Controller
{
    public function store(Request $request) {
        $fields = $request->validate([
            'first_name' => 'required|string',
            'last_name' => 'required|string',
            'email' => 'required|string|unique:users,email',
            'phone' => 'required|string',
            'roles' => 'required|string'
        ]);

        //password is a random 4 digit string that should as well be sent to email
        $password = str_pad(random_int(11, 9999), 4, '0', STR_PAD_LEFT);
        $fields['password'] = bcrypt($password);

        $user = User::create($fields);

        if($user) {
            $accountCredentials = [
                'name' => $user->first_name,
                'email' => $user->email,
                'password' => $password
            ];
            Mail::to($user->email)->send(new UserCreated($accountCredentials));

            return response(null, 201);
        }
        else {
            return response(['message' => 'An unexpected error has occurred. Please try again'], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function login(Request $request) {
        $fields = $request->validate([
            'email' => 'required|string',
            'password' => 'required|string'
        ]);

        //CHECK EMAIL
        $user = User::where('email', $fields['email'])->where('status', 'ACTIVE')->first();

        //CHECK PASSWORD
        if(!$user || !Hash::check($fields['password'], $user->password)) {
            return response([
                'message' => 'Incorrect email or password'
            ], 401);
        }

        $token = $user->createToken('okoyana')->plainTextToken;

        $configuration = Configuration::first();

        $response = [
            'user' => $user,
            'hospital' => $configuration,
            'token' => $token
        ];

        return response($response, 201);
    }

    public function logout(Request $request) {
        $request->user()->currentAccessToken()->delete();

        return response([
            'message' => 'Logged out'
        ], 201);
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $pageSize = $request->query('page_size', 20);
        $pageIndex = $request->query('page_index', 1);
        
        return User::where('status', 'ACTIVE')->paginate($pageSize, ['*'], 'page', $pageIndex);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        return User::find($id);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $data = $request->all();

        $user = User::find($id);
        $updatedUser = $user->update($data);

        if($updatedUser){
            return response(null, Response::HTTP_OK);
        }
        else {
            return response(['message' => 'An unexpected error has occurred. Please try again'], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $user = User::find($id);
        $user->status = 'DELETED';

        if($user->save()) {
            return response(null, Response::HTTP_NO_CONTENT);
        }
        else {
            return response(['message' => 'An unexpected error has occurred. Please try again'], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Search the specified resource by name.
     */
    public function search(Request $request, string $name)
    {
        $pageSize = $request->query('page_size', 20);
        $pageIndex = $request->query('page_index', 1);

        return User::where(DB::raw("CONCAT(first_name, ' ', last_name)"), 'like', '%' . $name . '%')->paginate($pageSize, ['*'], 'page', $pageIndex);
    }
}
