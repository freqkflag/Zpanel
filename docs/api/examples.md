# API Examples

Practical examples for common Zpanel API operations.

## Prerequisites

- Valid API token (see [Authentication Guide](authentication.md))
- Zpanel instance URL
- Basic knowledge of REST APIs

## Quick Examples

### List All Applications

```bash
curl -H "Authorization: Bearer YOUR_TOKEN" \
     https://your-domain.com/api/v1/applications
```

**Response:**
```json
{
  "data": [
    {
      "id": 1,
      "name": "my-app",
      "fqdn": "app.example.com",
      "status": "running",
      "git_repository": "https://github.com/user/repo.git",
      "git_branch": "main"
    }
  ]
}
```

### Create Application

```bash
curl -X POST https://your-domain.com/api/v1/applications/public \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -H "Content-Type: application/json" \
  -d '{
    "project_uuid": "project-uuid-here",
    "server_uuid": "server-uuid-here",
    "environment_name": "production",
    "git_repository": "https://github.com/user/repo.git",
    "git_branch": "main",
    "ports_exposes": "3000",
    "destination_uuid": "destination-uuid"
  }'
```

### Deploy Application

```bash
curl -X POST https://your-domain.com/api/v1/applications/{id}/deploy \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -H "Content-Type: application/json"
```

## Python Examples

### Basic Client

```python
import requests
from typing import Dict, List, Optional

class ZpanelClient:
    def __init__(self, base_url: str, api_token: str):
        self.base_url = base_url.rstrip('/')
        self.headers = {
            "Authorization": f"Bearer {api_token}",
            "Content-Type": "application/json",
            "Accept": "application/json"
        }
    
    def get_applications(self) -> List[Dict]:
        """List all applications."""
        response = requests.get(
            f"{self.base_url}/applications",
            headers=self.headers
        )
        response.raise_for_status()
        return response.json()['data']
    
    def deploy_application(self, app_id: int) -> Dict:
        """Trigger application deployment."""
        response = requests.post(
            f"{self.base_url}/applications/{app_id}/deploy",
            headers=self.headers
        )
        response.raise_for_status()
        return response.json()
    
    def get_servers(self) -> List[Dict]:
        """List all servers."""
        response = requests.get(
            f"{self.base_url}/servers",
            headers=self.headers
        )
        response.raise_for_status()
        return response.json()['data']

# Usage
client = ZpanelClient(
    base_url="https://your-domain.com/api/v1",
    api_token="your-token-here"
)

# List applications
apps = client.get_applications()
for app in apps:
    print(f"App: {app['name']} - Status: {app['status']}")

# Deploy first application
if apps:
    result = client.deploy_application(apps[0]['id'])
    print(f"Deployment started: {result}")
```

## JavaScript/Node.js Examples

### Using Axios

```javascript
const axios = require('axios');

class ZpanelClient {
  constructor(baseUrl, apiToken) {
    this.client = axios.create({
      baseURL: baseUrl,
      headers: {
        'Authorization': `Bearer ${apiToken}`,
        'Content-Type': 'application/json',
        'Accept': 'application/json'
      }
    });
  }

  async getApplications() {
    const response = await this.client.get('/applications');
    return response.data.data;
  }

  async deployApplication(appId) {
    const response = await this.client.post(`/applications/${appId}/deploy`);
    return response.data;
  }

  async getServers() {
    const response = await this.client.get('/servers');
    return response.data.data;
  }
}

// Usage
const client = new ZpanelClient(
  'https://your-domain.com/api/v1',
  'your-token-here'
);

(async () => {
  try {
    const apps = await client.getApplications();
    console.log('Applications:', apps);

    if (apps.length > 0) {
      const deployment = await client.deployApplication(apps[0].id);
      console.log('Deployment:', deployment);
    }
  } catch (error) {
    console.error('Error:', error.response?.data || error.message);
  }
})();
```

## Advanced Examples

### Deploying with Environment Variables

```bash
curl -X POST https://your-domain.com/api/v1/applications/{id} \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -H "Content-Type: application/json" \
  -d '{
    "environment_variables": [
      {"key": "DATABASE_URL", "value": "postgres://..."},
      {"key": "API_KEY", "value": "secret-key", "is_secret": true}
    ]
  }'
```

### Monitoring Deployment Status

```python
import time

def wait_for_deployment(client, app_id, timeout=300):
    """Wait for deployment to complete."""
    start_time = time.time()
    
    while time.time() - start_time < timeout:
        app = client.get_application(app_id)
        status = app['status']
        
        if status == 'running':
            print("Deployment successful!")
            return True
        elif status == 'error':
            print("Deployment failed!")
            return False
        
        print(f"Status: {status}, waiting...")
        time.sleep(5)
    
    print("Deployment timeout!")
    return False
```

### Batch Operations

```python
def deploy_all_applications(client):
    """Deploy all applications in sequence."""
    apps = client.get_applications()
    
    for app in apps:
        print(f"Deploying {app['name']}...")
        try:
            client.deploy_application(app['id'])
            print(f"✅ {app['name']} deployment started")
        except Exception as e:
            print(f"❌ {app['name']} deployment failed: {e}")
```

### Creating Application from Template

```bash
curl -X POST https://your-domain.com/api/v1/applications/public \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -H "Content-Type: application/json" \
  -d '{
    "project_uuid": "project-123",
    "server_uuid": "server-456",
    "environment_name": "production",
    "git_repository": "https://github.com/laravel/laravel.git",
    "git_branch": "main",
    "build_pack": "nixpacks",
    "ports_exposes": "80",
    "destination_uuid": "dest-789"
  }'
```

## Error Handling

### Handling Common Errors

```python
import requests
from requests.exceptions import HTTPError

def safe_api_call(client, method, endpoint, **kwargs):
    """Make API call with proper error handling."""
    try:
        response = method(endpoint, **kwargs)
        response.raise_for_status()
        return response.json()
    except HTTPError as e:
        if e.response.status_code == 401:
            print("Authentication failed - check your token")
        elif e.response.status_code == 403:
            print("Permission denied - check token abilities")
        elif e.response.status_code == 404:
            print("Resource not found")
        elif e.response.status_code == 422:
            print("Validation error:", e.response.json())
        elif e.response.status_code == 429:
            print("Rate limit exceeded - wait and retry")
        else:
            print(f"API error: {e}")
        raise
```

### Retry Logic with Exponential Backoff

```python
import time
from typing import Callable

def retry_with_backoff(
    func: Callable,
    max_retries: int = 3,
    initial_delay: float = 1.0
):
    """Retry API call with exponential backoff."""
    for attempt in range(max_retries):
        try:
            return func()
        except requests.exceptions.HTTPError as e:
            if e.response.status_code == 429:
                delay = initial_delay * (2 ** attempt)
                print(f"Rate limited, waiting {delay}s...")
                time.sleep(delay)
            else:
                raise
    
    raise Exception(f"Max retries ({max_retries}) exceeded")
```

## Webhook Examples

### GitHub Webhook Handler

```python
from flask import Flask, request, jsonify
import hmac
import hashlib

app = Flask(__name__)

WEBHOOK_SECRET = 'your-webhook-secret'

@app.route('/webhook/github', methods=['POST'])
def github_webhook():
    # Verify signature
    signature = request.headers.get('X-Hub-Signature-256')
    body = request.get_data()
    
    expected_signature = 'sha256=' + hmac.new(
        WEBHOOK_SECRET.encode(),
        body,
        hashlib.sha256
    ).hexdigest()
    
    if not hmac.compare_digest(signature, expected_signature):
        return jsonify({'error': 'Invalid signature'}), 403
    
    # Process webhook
    event = request.headers.get('X-GitHub-Event')
    data = request.json
    
    if event == 'push':
        # Trigger deployment via Zpanel API
        branch = data['ref'].split('/')[-1]
        print(f"Push to {branch} - triggering deployment")
    
    return jsonify({'status': 'ok'}), 200
```

## CI/CD Integration Examples

### GitHub Actions

```yaml
name: Deploy to Zpanel

on:
  push:
    branches: [main]

jobs:
  deploy:
    runs-on: ubuntu-latest
    steps:
      - name: Trigger Zpanel Deployment
        run: |
          curl -X POST https://your-domain.com/api/v1/applications/${{ secrets.APP_ID }}/deploy \
            -H "Authorization: Bearer ${{ secrets.ZPANEL_TOKEN }}" \
            -H "Content-Type: application/json"
```

### GitLab CI

```yaml
deploy:
  stage: deploy
  script:
    - >
      curl -X POST https://your-domain.com/api/v1/applications/$APP_ID/deploy
      -H "Authorization: Bearer $ZPANEL_TOKEN"
      -H "Content-Type: application/json"
  only:
    - main
```

## Database Management Examples

### Create PostgreSQL Database

```bash
curl -X POST https://your-domain.com/api/v1/databases \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -H "Content-Type: application/json" \
  -d '{
    "name": "production-db",
    "type": "postgresql",
    "version": "15",
    "server_uuid": "server-uuid",
    "environment_name": "production"
  }'
```

### Backup Database

```bash
curl -X POST https://your-domain.com/api/v1/databases/{id}/backup \
  -H "Authorization: Bearer YOUR_TOKEN"
```

## Server Management Examples

### Add New Server

```bash
curl -X POST https://your-domain.com/api/v1/servers \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -H "Content-Type: application/json" \
  -d '{
    "name": "production-server-01",
    "ip": "192.168.1.100",
    "port": 22,
    "user": "root",
    "private_key_uuid": "key-uuid"
  }'
```

## Additional Resources

- [API Overview](overview.md)
- [Authentication Guide](authentication.md)
- [OpenAPI Specification](../../implementation/phase-1/Zpanel/openapi.yaml)
- [Rate Limiting](overview.md#rate-limiting)

## Contributing Examples

Have a useful API example? Contribute via pull request!

