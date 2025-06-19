<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

class AuthController extends Controller
{
    public function index()
    {
        if (Auth::check()) {
            return redirect()->route('tasks.index');
        }

        return view('index');
    }

    public function register(Request $request): RedirectResponse
    {
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'password' => ['required', 'string', 'min:4', 'confirmed'],
        ]);

        try {
            User::create([
                'name' => $request->name,
                'email' => $request->name,
                'password' => Hash::make($request->password),
            ]);
        } catch (\Exception $e) {
            return redirect()->route('index')->with('error', 'Something went wrong during registration. Please try again.');
        }

        return redirect()->route('tasks.index')->with('success', 'Registration successful!');
    }

    public function login(Request $request): RedirectResponse
    {
        $credentials = $request->validate([
            'name' => ['required'],
            'password' => ['required'],
        ]);
        $credentials['email'] = $credentials['name'];
        $authenticated = Auth::attempt(['email' => $credentials['email'], 'password' => $credentials['password']], $request->remember);

        if ($authenticated) {
            $request->session()->regenerate();
            return redirect()->route('tasks.index')->with('success', 'Login successful!');
        }

        return redirect()->route('index')->with('error', 'Invalid credentials. Please try again.');
    }

    public function logout(Request $request): RedirectResponse
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/')->with('success', 'You have been logged out.');
    }

    public function registerApi(Request $request): JsonResponse
    {
        try {
            $request->validate([
                'name' => ['required', 'string', 'max:255'],
                'password' => ['required', 'string', 'min:4', 'confirmed'],
            ]);
        } catch (ValidationException $e) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $e->errors(),
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        try {
            $user = User::create([
                'name' => $request->name,
                'email' => $request->name, // For simple auth
                'password' => Hash::make($request->password)
            ]);

            return response()->json([
                'message' => 'Registration successful',
                'token' => $user->createToken('api_token')->plainTextToken,
                'name' => $user->name,
            ], Response::HTTP_CREATED);

        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Could not register user. Please try again.',
                'error' => $e->getMessage(),
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function loginApi(Request $request): JsonResponse
    {
        try {
            $request->validate([
                'name' => ['required', 'string'],
                'password' => ['required', 'string'],
            ]);
        } catch (ValidationException $e) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $e->errors(),
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        $user = User::where('name', $request->name)->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            return response()->json([
                'message' => 'Invalid credentials',
            ], Response::HTTP_UNAUTHORIZED);
        }

        return response()->json([
            'message' => 'Login successful',
            'name' => $user->name,
            'token' => $user->createToken('api_token')->plainTextToken,
        ], Response::HTTP_OK);
    }

    public function logoutApi(Request $request): JsonResponse
    {
        if ($request->user() && $request->user()->currentAccessToken()) {
            $request->user()->currentAccessToken()->delete();
            return response()->json(['message' => 'Successfully logged out (token revoked).'], Response::HTTP_OK);
        }

        return response()->json(['message' => 'No active token found or user not authenticated.'], Response::HTTP_UNAUTHORIZED);
    }

    public function user(Request $request): JsonResponse
    {
        return response()->json($request->user(), Response::HTTP_OK);
    }
}
