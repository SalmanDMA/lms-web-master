<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Config;
use Inertia\Inertia;
use GuzzleHttp\Client;
use Illuminate\Http\Request;
use App\Http\Traits\ApiHelperTrait;
use GuzzleHttp\Exception\RequestException;

class AuthViewController extends Controller
{
    protected $client;
    use ApiHelperTrait;

    public function __construct()
    {
        $this->client = new Client([
            'base_uri' => env('API_URL')
        ]);
    }

    public function v_dashboard($role = 'student')
    {
        return view('dashboard', [
            'title' => 'Login',
            'role' => $role,
        ]);
    }

    public function v_login($role = 'student')
    {
        $customThemeData = $this->client->get('/api/v1/mobile/cms');
        $customTheme = json_decode($customThemeData->getBody()->getContents())->data ?? null;

        if (isset($customTheme)) {
            Config::set('app.name', $customTheme->title);
        }

        return view('auth.login', [
            'title' => 'Login',
            'role' => $role,
            'customTheme' => $customTheme,
        ]);
    }

    public function v_adminLogin()
    {
        return view('auth.admin-login', [
            'title' => 'Login',
        ]);
    }

    public function onLogin($role, Request $request)
    {
        $credentials = $request->all();

        try {
            $response = $this->client->post('/api/v1/auth/login', [
                'form_params' => $credentials,
            ]);
            $response_data = json_decode($response->getBody()->getContents());

            if (!$response_data->success) {
                return back()->withErrors($response_data->message);
            }

            if ($response_data->data->user->role === "TEACHER") {
                session([
                    'role' => strtoupper($role),
                    'token' => $response_data->data->token,
                    'user' => $response_data->data->user,
                    'is_premium_school' => $response_data->data->user->is_premium_school,
                    'role_teacher' => $response_data->data->user->role_teacher,
                ]);
            } else {
                session([
                    'role' => strtoupper($role),
                    'token' => $response_data->data->token,
                    'user' => $response_data->data->user,
                ]);
            }

            return redirect("/{$role}/dashboard");
        } catch (RequestException $e) {
            if ($e->hasResponse()) {
                $response     = $e->getResponse();
                $responseBody = $response->getBody()->getContents();
                return response()->json(['error' => $responseBody], $response->getStatusCode());
            }

            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function onLogout()
    {
        if (session()->has('token')) {
            $response = $this->client->get('/api/v1/auth/logout', [
                'headers' => ['Authorization' => 'Bearer ' . session('token')]
            ]);
            $response_data = json_decode($response->getBody()->getContents());

            if ($response_data->success) {
                session()->flush();
                return redirect('/login');
            }

            return back();
        } else {
            return redirect('/login');
        }
    }

    public function onAdminLogin(Request $request)
    {
        $credentials = $request->only('email', 'password');

        try {
            $response = $this->client->post('/api/v1/auth/login', [
                'form_params' => $credentials,
            ]);

            $response_data = json_decode($response->getBody()->getContents());
        } catch (RequestException $e) {
            if ($e->hasResponse()) {
                $response     = $e->getResponse();
                $responseBody = $response->getBody()->getContents();
                return response()->json(['error' => $responseBody], $response->getStatusCode());
            }

            return response()->json(['error' => $e->getMessage()], 500);
        }

        if (!$response_data->success) {
            return back()->withErrors($response_data->message);
        }

        if (isset($response_data->data->user->is_staff)) {
            if ($response_data->data->user->role !== 'STAFF') {
                return back()->withErrors('Email ini bukan akun staff. Coba Lagi Ya');
            }

            session([
                'role' => $response_data->data->user->role,
                'token' => $response_data->data->token,
                'user' => [
                    'name' => $response_data->data->user->fullname,
                    'email' => $response_data->data->user->email,
                    'authority' => $response_data->data->user->is_staff->authority,
                ],
                'is_premium_school' => $response_data->data->user->is_premium_school,
            ]);

            if ($response_data->data->user->is_staff->authority === 'ADMIN') {
                return redirect('/staff-administrator/dashboard');
            } elseif ($response_data->data->user->is_staff->authority === 'KURIKULUM') {
                return redirect('/staff-curriculum/dashboard');
            } else {
                return redirect('/admin/login');
            }
        }

        session([
            'role' => 'ADMIN',
            'token' => $response_data->data->token,
            'user' => [
                'name' => $response_data->data->user->admin_name,
                'email' => $response_data->data->user->admin_email,
                'is_premium' => $response_data->data->user->is_premium
            ],
        ]);

        return redirect('/admin/dashboard');
    }

    public function v_signup($role = 'siswa')
    {
        return Inertia::render('auth/register', [
            'role' => $role,
            'title' => 'Register',
        ]);
    }

    public function v_forgot($role = 'siswa')
    {
        return Inertia::render('auth/forgot-password', [
            'role' => $role,
            'title' => 'Forgot Password',
        ]);
    }
}
