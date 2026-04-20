<?php

namespace App\Http\Controllers\Web;

use App\Services\ApiClient;
use App\Services\ApiException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AuthController extends Controller
{
    public function __construct(
        private readonly ApiClient $apiClient,
    ) {}

    public function loginForm(): View
    {
        return view('pages.auth.login');
    }

    public function login(Request $request): RedirectResponse
    {
        try {
            $response = $this->apiClient->login([
                'email' => $request->string('email')->toString(),
                'password' => $request->string('password')->toString(),
                'device_name' => 'web',
            ]);
        } catch (ApiException $exception) {
            if ($exception->status === 422) {
                return $this->redirectBackWithApiErrors($request, $exception, ['password']);
            }

            $this->handleApiException($exception);
        }

        $this->storeAuthenticatedUser($request, $response);

        return redirect()->route('dashboard.index');
    }

    public function registerForm(): View
    {
        return view('pages.auth.register');
    }

    public function register(Request $request): RedirectResponse
    {
        try {
            $response = $this->apiClient->register([
                'name' => $request->string('name')->toString(),
                'email' => $request->string('email')->toString(),
                'password' => $request->string('password')->toString(),
                'password_confirmation' => $request->string('password_confirmation')->toString(),
                'device_name' => 'web',
            ]);
        } catch (ApiException $exception) {
            if ($exception->status === 422) {
                return $this->redirectBackWithApiErrors($request, $exception, ['password', 'password_confirmation']);
            }

            $this->handleApiException($exception);
        }

        $this->storeAuthenticatedUser($request, $response);

        return redirect()->route('dashboard.index');
    }

    public function logout(Request $request): RedirectResponse
    {
        try {
            if (session('api_token')) {
                $this->apiClient->logout();
            }
        } catch (ApiException $exception) {
            if ($exception->status !== 401) {
                $this->handleApiException($exception);
            }
        }

        $this->clearApiSession();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('landing');
    }

    private function storeAuthenticatedUser(Request $request, array $response): void
    {
        $request->session()->regenerate();
        session([
            'api_token' => data_get($response, 'data.token'),
        ]);

        $user = data_get($response, 'data.user');

        if (is_array($user)) {
            $this->rememberApiUser($user);
        }
    }
}
