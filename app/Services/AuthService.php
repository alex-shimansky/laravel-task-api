<?php
declare(strict_types=1);

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class AuthService
{
    /**
     * Authenticates the user and returns a pair:
     *  - access token
     *  - user model
     *
     * @param string $email
     * @param string $password
     * @return array{token:string, user:User}
     *
     * @throws ValidationException
     */
    public function attemptLogin(string $email, string $password): array
    {
        if (! Auth::attempt(['email' => $email, 'password' => $password])) {
            throw ValidationException::withMessages(['email' => 'Invalid credentials.']);
        }

        /** @var User $user */
        $user  = Auth::user();
        $token = $user->createToken('api-token')->plainTextToken;

        return compact('token', 'user');
    }

    /** Logs out the user by revoking the current access token. */
    public function logout(User $user): void
    {
        $user->currentAccessToken()?->delete();
    }
}
