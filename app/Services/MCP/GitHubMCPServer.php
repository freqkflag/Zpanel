<?php

namespace App\Services\MCP;

use App\Services\GitHub\ActionsService;
use App\Services\GitHub\GitHubService;
use App\Services\GitHub\IssueService;
use App\Services\GitHub\PullRequestService;
use Illuminate\Support\Facades\Log;

/**
 * GitHub MCP Server
 *
 * Exposes GitHub operations through Model Context Protocol.
 * Supports repository management, PRs, issues, and GitHub Actions.
 */
class GitHubMCPServer
{
    public function __construct(
        private GitHubService $githubService,
        private PullRequestService $prService,
        private IssueService $issueService,
        private ActionsService $actionsService
    ) {}

    /**
     * Get available MCP tools
     */
    public function getTools(): array
    {
        return [
            'github_list_repos' => [
                'name' => 'list_repositories',
                'description' => 'List user repositories',
                'parameters' => [
                    'username' => ['type' => 'string', 'required' => false],
                    'type' => ['type' => 'string', 'enum' => ['all', 'owner', 'public', 'private'], 'required' => false],
                ],
            ],
            'github_get_repo' => [
                'name' => 'get_repository',
                'description' => 'Get repository details',
                'parameters' => [
                    'owner' => ['type' => 'string', 'required' => true],
                    'repo' => ['type' => 'string', 'required' => true],
                ],
            ],
            'github_create_repo' => [
                'name' => 'create_repository',
                'description' => 'Create a new repository',
                'parameters' => [
                    'name' => ['type' => 'string', 'required' => true],
                    'description' => ['type' => 'string', 'required' => false],
                    'private' => ['type' => 'boolean', 'required' => false],
                    'auto_init' => ['type' => 'boolean', 'required' => false],
                ],
            ],
            'github_fork_repo' => [
                'name' => 'fork_repository',
                'description' => 'Fork a repository',
                'parameters' => [
                    'owner' => ['type' => 'string', 'required' => true],
                    'repo' => ['type' => 'string', 'required' => true],
                ],
            ],
            'github_list_prs' => [
                'name' => 'list_pull_requests',
                'description' => 'List pull requests for a repository',
                'parameters' => [
                    'owner' => ['type' => 'string', 'required' => true],
                    'repo' => ['type' => 'string', 'required' => true],
                    'state' => ['type' => 'string', 'enum' => ['open', 'closed', 'all'], 'required' => false],
                ],
            ],
            'github_create_pr' => [
                'name' => 'create_pull_request',
                'description' => 'Create a pull request',
                'parameters' => [
                    'owner' => ['type' => 'string', 'required' => true],
                    'repo' => ['type' => 'string', 'required' => true],
                    'title' => ['type' => 'string', 'required' => true],
                    'head' => ['type' => 'string', 'required' => true],
                    'base' => ['type' => 'string', 'required' => true],
                    'body' => ['type' => 'string', 'required' => false],
                ],
            ],
            'github_merge_pr' => [
                'name' => 'merge_pull_request',
                'description' => 'Merge a pull request',
                'parameters' => [
                    'owner' => ['type' => 'string', 'required' => true],
                    'repo' => ['type' => 'string', 'required' => true],
                    'pull_number' => ['type' => 'integer', 'required' => true],
                    'merge_method' => ['type' => 'string', 'enum' => ['merge', 'squash', 'rebase'], 'required' => false],
                ],
            ],
            'github_list_issues' => [
                'name' => 'list_issues',
                'description' => 'List issues for a repository',
                'parameters' => [
                    'owner' => ['type' => 'string', 'required' => true],
                    'repo' => ['type' => 'string', 'required' => true],
                    'state' => ['type' => 'string', 'enum' => ['open', 'closed', 'all'], 'required' => false],
                ],
            ],
            'github_create_issue' => [
                'name' => 'create_issue',
                'description' => 'Create an issue',
                'parameters' => [
                    'owner' => ['type' => 'string', 'required' => true],
                    'repo' => ['type' => 'string', 'required' => true],
                    'title' => ['type' => 'string', 'required' => true],
                    'body' => ['type' => 'string', 'required' => false],
                    'labels' => ['type' => 'array', 'required' => false],
                ],
            ],
            'github_close_issue' => [
                'name' => 'close_issue',
                'description' => 'Close an issue',
                'parameters' => [
                    'owner' => ['type' => 'string', 'required' => true],
                    'repo' => ['type' => 'string', 'required' => true],
                    'issue_number' => ['type' => 'integer', 'required' => true],
                ],
            ],
            'github_list_workflows' => [
                'name' => 'list_workflows',
                'description' => 'List GitHub Actions workflows',
                'parameters' => [
                    'owner' => ['type' => 'string', 'required' => true],
                    'repo' => ['type' => 'string', 'required' => true],
                ],
            ],
            'github_trigger_workflow' => [
                'name' => 'trigger_workflow',
                'description' => 'Trigger a workflow dispatch event',
                'parameters' => [
                    'owner' => ['type' => 'string', 'required' => true],
                    'repo' => ['type' => 'string', 'required' => true],
                    'workflow_id' => ['type' => 'integer', 'required' => true],
                    'ref' => ['type' => 'string', 'required' => true],
                    'inputs' => ['type' => 'object', 'required' => false],
                ],
            ],
            'github_get_workflow_run' => [
                'name' => 'get_workflow_run',
                'description' => 'Get workflow run details',
                'parameters' => [
                    'owner' => ['type' => 'string', 'required' => true],
                    'repo' => ['type' => 'string', 'required' => true],
                    'run_id' => ['type' => 'integer', 'required' => true],
                ],
            ],
        ];
    }

    /**
     * Handle MCP tool call
     */
    public function handleToolCall(string $tool, array $params): array
    {
        try {
            return match ($tool) {
                'github_list_repos' => $this->githubService->listRepositories($params['username'] ?? null, $params),
                'github_get_repo' => $this->githubService->getRepository($params['owner'], $params['repo']),
                'github_create_repo' => $this->githubService->createRepository($params),
                'github_fork_repo' => $this->githubService->forkRepository($params['owner'], $params['repo']),

                'github_list_prs' => $this->prService->list($params['owner'], $params['repo'], $params),
                'github_create_pr' => $this->prService->create($params['owner'], $params['repo'], $params),
                'github_merge_pr' => $this->prService->merge($params['owner'], $params['repo'], $params['pull_number'], $params),

                'github_list_issues' => $this->issueService->list($params['owner'], $params['repo'], $params),
                'github_create_issue' => $this->issueService->create($params['owner'], $params['repo'], $params),
                'github_close_issue' => $this->issueService->close($params['owner'], $params['repo'], $params['issue_number']),

                'github_list_workflows' => $this->actionsService->listWorkflows($params['owner'], $params['repo']),
                'github_trigger_workflow' => $this->actionsService->triggerWorkflow(
                    $params['owner'],
                    $params['repo'],
                    $params['workflow_id'],
                    $params['ref'],
                    $params['inputs'] ?? []
                ) ? ['success' => true] : ['success' => false],
                'github_get_workflow_run' => $this->actionsService->getWorkflowRun($params['owner'], $params['repo'], $params['run_id']),

                default => throw new \InvalidArgumentException("Unknown tool: {$tool}"),
            };
        } catch (\Exception $e) {
            Log::error('GitHub MCP tool error', [
                'tool' => $tool,
                'params' => $params,
                'error' => $e->getMessage(),
            ]);

            return [
                'error' => $e->getMessage(),
                'tool' => $tool,
            ];
        }
    }

    /**
     * Get server configuration for .mcp.json
     */
    public function getConfig(): array
    {
        return [
            'command' => 'php',
            'args' => ['artisan', 'mcp:github'],
            'env' => [
                'GITHUB_TOKEN' => config('services.github.token'),
            ],
        ];
    }
}
