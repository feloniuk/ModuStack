<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Requests\AI\ChatRequest;
use App\Actions\AI\GenerateAIResponseAction;
use Illuminate\Support\Facades\Auth;

class AssistantChatController extends Controller
{
    public function send(ChatRequest $request, GenerateAIResponseAction $action)
    {
        $validated = $request->validated();
        
        $response = $action(Auth::user(), [
            'prompt' => $validated['message'],
            'model' => $validated['model'] ?? null
        ]);

        return response()->json([
            'message' => $response->getText(),
            'tokens_used' => $response->getTokensUsed(),
            'model' => $response->getModelUsed()
        ]);
    }
}