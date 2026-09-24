<?php

namespace App\Http\Controllers;

use App\Services\AiCopilotService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AiAssistantController extends Controller
{
    /**
     * 💬 Chat with AI Copilot
     */
    public function chat(Request $request, AiCopilotService $copilotService)
    {
        $request->validate([
            'message' => 'required|string|max:2000',
            'context' => 'nullable|array',
        ]);

        $user = Auth::user();
        $message = trim($request->input('message'));
        $context = $request->input('context', []);
        $history = $request->input('history', []);

        $result = $copilotService->chat($message, $context, $history, $user ? $user->id : 1);

        return response()->json($result);
    }
}
