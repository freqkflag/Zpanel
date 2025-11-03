

🧠 Cursor Prompt — Create Secure Autonomous MCP Supervisor System

Prompt:

Build a complete Node.js (ESM) project called mcp-supervisor for macOS that implements a Model Context Protocol (MCP) Supervisor which can manage, orchestrate, and monitor reusable agent workers locally.

System Goals:
	1.	The Supervisor exposes an HTTP API for ChatGPT or other MCP clients to call.
	2.	It can launch, monitor, and terminate pre-approved reusable agent templates (each in its own process).
	3.	Each agent runs inside a sandboxed environment and communicates with the Supervisor via JSON messages (IPC).
	4.	Supervisor maintains strict safety rules:
	•	No access to passwords, Keychain, or sudo commands.
	•	Enforces an explicit manifest.json with allowed directories, whitelisted apps, and a blocklist for dangerous commands.
	•	Requires environment variable ALLOW_AUTONOMY=true to permit destructive or autonomous actions.
	5.	Every action, message, and exit code is logged to logs/actions.log.
	6.	Include basic monitoring (CPU, memory, runtime) and timeouts for runaway agents.
	7.	Provide an easy way to add new reusable agent templates that the Supervisor can deploy and monitor.

Technical Requirements:
	•	Use Node.js (ESM) with dependencies: express, body-parser, pidusage, uuid.
	•	Folder structure:

mcp-supervisor/
├─ package.json  
├─ .env.example  
├─ index.js (Supervisor entry)  
├─ manifest.json (safety rules)  
├─ registry.json (registered agents)  
├─ tools/monitor.js (spawns agents + monitors them)  
├─ tools/logger.js  
├─ agents/example-task/agent.js (demo worker)  
├─ agents/image-optimize/agent.js (demo worker)  
└─ logs/actions.log  


	•	The Supervisor should provide routes:
	•	POST /run-agent → starts an agent by ID with parameters
	•	GET /registry → lists all registered agents
	•	GET /manifest → returns safety policy
	•	tools/monitor.js uses child_process.fork() to spawn workers, enforce runtime limits, capture logs, and return lifecycle status.
	•	Each agent (worker) listens for { cmd: "run", params } via process.on("message") and responds with process.send() updates.
	•	Include .env.example with variables:

ALLOW_AUTONOMY=false  
SUPERVISOR_PORT=3001  
LOG_PATH=./logs/actions.log  
MAX_AGENT_RUNTIME_MS=300000  
MAX_AGENT_RETRIES=2  


	•	Add clear inline comments explaining how to:
	•	Add new agent templates
	•	Approve new agents via registry.json
	•	Extend to containerized (Docker/Colima) execution for stronger sandboxing
	•	Include a README.md detailing installation, setup, running, safety controls, and integration with ChatGPT’s Model Context Protocol integration.

Behavioral requirements:
	•	Agents are reusable and can be deployed concurrently; Supervisor tracks their status.
	•	Supervisor restarts failed agents up to MAX_AGENT_RETRIES then logs failure.
	•	Supervisor monitors agents with pidusage for CPU/memory.
	•	All actions and logs are stored locally; nothing leaves the machine.
	•	Default mode is safe: ALLOW_AUTONOMY=false.

Deliverables:
Generate the entire project folder tree with all necessary files fully implemented and ready to run via:

npm install
npm run dev

The result should be a secure local automation engine that can orchestrate, monitor, and safely manage reusable agents on macOS under the Model Context Protocol.

⸻