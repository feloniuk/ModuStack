<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Requests\AI\ChatRequest;
use App\Actions\AI\GenerateAIResponseAction;
use App\Models\Assistant;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

class AssistantChatController extends Controller
{
    public function send(ChatRequest $request, GenerateAIResponseAction $action, int $assistantId)
    {
        $validated = $request->validated();
        $assistant = Assistant::findOrFail($assistantId);

        // Проверка доступа к ассистенту
        if ($assistant->user_id !== Auth::id()) {
            throw new AccessDeniedHttpException('У вас нет доступа к этому ассистенту');
        }

        $response = $action(Auth::user(), [
            'prompt' => $validated['message'],
            'model' => $assistant->model,
            'system_prompt' => $assistant->system_prompt,
            'assistant_id' => $assistantId
        ]);

        return response()->json([
            'message' => $response->getText(),
            'tokens_used' => $response->getTokensUsed(),
            'model' => $response->getModelUsed()
        ]);
    }
}