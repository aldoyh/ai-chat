<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

final class ChatIdentityResolver
{
    public function resolve(Request $request, bool $createGuest = false): ?User
    {
        $user = $request->user();

        if ($user instanceof User) {
            return $user;
        }

        $guestUserId = $request->session()->get('guest_user_id');

        if (is_int($guestUserId) || ctype_digit((string) $guestUserId)) {
            $guestUser = User::query()->find((int) $guestUserId);

            if ($guestUser instanceof User) {
                return $guestUser;
            }

            $request->session()->forget('guest_user_id');
        }

        if (! $createGuest) {
            return null;
        }

        $sessionId = $request->session()->getId();
        $guestEmail = sprintf('guest-%s@ai-chat.local', $sessionId);

        $guestUser = User::query()->firstOrCreate(
            ['email' => $guestEmail],
            [
                'name' => 'مستخدم مجهول',
                'password' => Hash::make(Str::random(48)),
                'email_verified_at' => now(),
            ],
        );

        $request->session()->put('guest_user_id', $guestUser->id);

        return $guestUser;
    }
}
