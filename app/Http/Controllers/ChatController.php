<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Chat;
use App\Models\User;
use Inertia\Inertia;
use Inertia\Response;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Http\RedirectResponse;
use App\Http\Requests\StoreChatRequest;
use App\Http\Requests\UpdateChatRequest;
use App\Services\ChatIdentityResolver;

final class ChatController extends Controller
{
    public function __construct(
        private readonly ChatIdentityResolver $chatIdentityResolver,
    ) {
    }

    public function index(Request $request): Response
    {
        $chatHistory = null;

        $actor = $this->chatIdentityResolver->resolve($request);

        if ($actor instanceof User) {
            $chatHistory = $actor->chats()->orderBy('updated_at', 'desc')->paginate(25);
        }

        return Inertia::render('Chat/Index', [
            'chatHistory' => Inertia::deepMerge($chatHistory),
        ]);
    }

    public function store(StoreChatRequest $request): RedirectResponse
    {
        $actor = $this->chatIdentityResolver->resolve($request, true);

        Gate::forUser($actor)->authorize('create', Chat::class);

        $validated = $request->validated();

        $chat = $actor->chats()->create([
            'user_id' => $actor->id,
            'title' => $validated['message'],
            'visibility' => $validated['visibility'],
        ]);

        return to_route('chats.show', ['chat' => $chat]);
    }

    public function show(Request $request, Chat $chat): Response
    {
        $chatHistory = null;

        $actor = $this->chatIdentityResolver->resolve($request);

        if ($actor instanceof User) {
            $chatHistory = $actor->chats()->orderBy('updated_at', 'desc')->paginate(25);
            Gate::forUser($actor)->authorize('view', $chat);
        } else {
            Gate::authorize('view', $chat);
        }

        return Inertia::render('Chat/Show', [
            'chat' => fn () => $chat->load('messages'),
            'chatHistory' => Inertia::deepMerge($chatHistory),
        ]);
    }

    public function update(UpdateChatRequest $request, Chat $chat): RedirectResponse
    {
        $actor = $this->chatIdentityResolver->resolve($request);
        abort_unless($actor instanceof User, 403);

        Gate::forUser($actor)->authorize('update', $chat);

        $validated = $request->validated();

        if (isset($validated['message_id'])) {
            $messageId = $validated['message_id'];

            $message = $chat->messages()->find($messageId);

            if ($message && isset($validated['is_upvoted'])) {
                $upvoteValue = (bool) $validated['is_upvoted'];
                $message->update(['is_upvoted' => $upvoteValue]);
            }
        }

        $updates = [];

        if (isset($validated['title'])) {
            $updates['title'] = $validated['title'];
        }

        if (isset($validated['visibility'])) {
            $updates['visibility'] = $validated['visibility'];
        }

        if ($updates !== []) {
            $chat->update($updates);
        }

        return to_route('chats.show', ['chat' => $chat]);
    }

    public function destroy(Request $request, Chat $chat): RedirectResponse
    {
        $actor = $this->chatIdentityResolver->resolve($request);
        abort_unless($actor instanceof User, 403);

        Gate::forUser($actor)->authorize('delete', $chat);

        $chat->messages()->delete();
        $chat->delete();

        return to_route('chats.index');
    }
}
