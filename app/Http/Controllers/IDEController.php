<?php

namespace App\Http\Controllers;

use App\Services\IDEService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class IDEController extends Controller
{
    public function __construct(
        private IDEService $ideService
    ) {
        $this->middleware('auth');
    }

    /**
     * Display IDE interface
     */
    public function index(Request $request)
    {
        $user = Auth::user();
        $workspace = $request->get('workspace', 'default');
        $projectId = $request->get('project_id');

        $token = $this->ideService->generateToken($user->id);
        $workspacePath = $this->ideService->getWorkspacePath($user->id, $projectId);
        $ideUrl = $this->ideService->getIDEUrl($token, $workspacePath);

        return view('ide.index', [
            'ideUrl' => $ideUrl,
            'workspace' => $workspace,
            'token' => $token,
        ]);
    }

    /**
     * List user workspaces
     */
    public function workspaces()
    {
        $user = Auth::user();
        $workspaces = $user->workspaces()->with('project')->get();

        return response()->json($workspaces);
    }

    /**
     * Create new workspace
     */
    public function createWorkspace(Request $request)
    {
        $user = Auth::user();

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'type' => 'string|in:default,project',
            'project_id' => 'nullable|exists:projects,id',
        ]);

        $workspacePath = $this->ideService->getWorkspacePath($user->id, $validated['project_id'] ?? null);

        $workspace = $user->workspaces()->create([
            'name' => $validated['name'],
            'path' => $workspacePath,
            'type' => $validated['type'] ?? 'default',
            'project_id' => $validated['project_id'] ?? null,
        ]);

        return response()->json([
            'message' => 'Workspace created successfully',
            'workspace' => $workspace,
        ], 201);
    }
}
