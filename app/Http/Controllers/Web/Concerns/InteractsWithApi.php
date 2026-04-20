<?php

namespace App\Http\Controllers\Web\Concerns;

use App\Services\ApiException;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;

trait InteractsWithApi
{
    protected function rememberApiUser(array $user): void
    {
        session(['api_user' => $user]);
    }

    protected function clearApiSession(): void
    {
        session()->forget(['api_token', 'api_user', 'known_auctions', 'known_invitations']);
    }

    protected function handleApiException(ApiException $exception): never
    {
        if ($exception->status === 401) {
            $this->clearApiSession();

            throw new HttpResponseException(
                redirect()
                    ->route('auth.login')
                    ->with('error', 'Your session has expired. Please log in again.')
            );
        }

        if ($exception->status === 403) {
            abort(403);
        }

        if ($exception->status === 404) {
            abort(404);
        }

        throw $exception;
    }

    protected function redirectBackWithApiErrors(
        Request $request,
        ApiException $exception,
        array $exceptInput = [],
    ): RedirectResponse {
        $redirect = redirect()
            ->back()
            ->withInput(Arr::except($request->input(), $exceptInput))
            ->with('error', $exception->getMessage());

        if ($exception->errors() !== []) {
            $redirect = $redirect->withErrors($exception->errors());
        }

        return $redirect;
    }
}
