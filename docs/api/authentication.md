# API Authentication

Zpanel uses **Laravel Sanctum** for API token authentication, providing secure and flexible access control.

## Authentication Methods

### 1. Bearer Token Authentication (Recommended)

API tokens provide secure, revocable access to your Zpanel instance.

#### Creating an API Token

**Via Web Interface:**
1. Log in to your Zpanel dashboard
2. Navigate to **Settings** → **API Tokens**
3. Click **Create New Token**
4. Enter a descriptive name (e.g., "CI/CD Pipeline")
5. Select token abilities/permissions
6. Click **Create**
7. **Copy the token immediately** (it won't be shown again)

**Via API:**
```bash
curl -X POST https://your-domain.com/api/v1/tokens \
  -H "Content-Type: application/json" \
  -u "your-email@example.com:your-password" \
  -d '{
    "name": "My API Token",
    "abilities": ["*"]
  }'
```

#### Using the Token

Include the token in the `Authorization` header:

```bash
curl -H "Authorization: Bearer YOUR_API_TOKEN" \
     https://your-domain.com/api/v1/applications
```

### 2. Session Authentication

For web-based integrations, you can use Laravel's session authentication (cookie-based).

## Token Abilities

Tokens can be scoped to specific abilities (permissions):

### Available Abilities

- `*` - Full access (all abilities)
- `applications:read` - Read application data
- `applications:write` - Create/update applications
- `applications:delete` - Delete applications
- `applications:deploy` - Trigger deployments
- `servers:read` - Read server data
- `servers:write` - Create/update servers
- `servers:delete` - Delete servers
- `databases:read` - Read database data
- `databases:write` - Create/update databases
- `databases:delete` - Delete databases

### Example: Limited Token

```json
{
  "name": "Read-Only Token",
  "abilities": [
    "applications:read",
    "servers:read",
    "databases:read"
  ]
}
```

## Token Management

### Listing Tokens

```bash
curl -H "Authorization: Bearer YOUR_API_TOKEN" \
     https://your-domain.com/api/v1/tokens
```

### Revoking Tokens

**Via API:**
```bash
curl -X DELETE https://your-domain.com/api/v1/tokens/{token_id} \
  -H "Authorization: Bearer YOUR_API_TOKEN"
```

**Via Web Interface:**
1. Navigate to **Settings** → **API Tokens**
2. Click **Delete** next to the token
3. Confirm deletion

## Security Best Practices

### Token Storage

- ✅ **DO**: Store tokens in environment variables
- ✅ **DO**: Use secrets management (HashiCorp Vault, AWS Secrets Manager)
- ❌ **DON'T**: Commit tokens to version control
- ❌ **DON'T**: Share tokens via email or chat

### Token Rotation

- Rotate tokens regularly (every 90 days recommended)
- Create new token before revoking old one to avoid downtime
- Use separate tokens for different environments (dev, staging, prod)

### Token Scoping

- Use minimal required abilities
- Create separate tokens for different services
- Avoid using wildcard (`*`) ability in production

## Rate Limiting

API requests are rate-limited per token:

- **Standard Rate**: 60 requests/minute
- **Deployment Operations**: 10 requests/minute
- **Webhook Endpoints**: 100 requests/minute

### Rate Limit Headers

Response includes rate limit information:

```http
X-RateLimit-Limit: 60
X-RateLimit-Remaining: 59
X-RateLimit-Reset: 1699000000
```

### Handling Rate Limits

When rate limited, you'll receive a `429 Too Many Requests` response:

```json
{
  "message": "Too Many Requests",
  "retry_after": 60
}
```

Wait for the `retry_after` seconds before retrying.

## IP Allowlisting

For enhanced security, configure IP allowlisting:

1. Navigate to **Settings** → **Security**
2. Enable **API IP Allowlist**
3. Add allowed IP addresses or CIDR ranges
4. Save configuration

**Format:**
```
192.168.1.100
10.0.0.0/24
2001:db8::/32
```

## OAuth Integration

For integrations requiring user context, Zpanel supports OAuth 2.0:

### Supported Providers
- GitHub
- GitLab
- Google
- Microsoft Azure AD
- Discord

See [OAuth Integration Guide](oauth.md) for details.

## Team-Based Access

API tokens are scoped to teams:

- Tokens have access to resources of the user's current team
- Switch teams via API to access different resources
- Team isolation enforced at database level

### Switching Teams

```bash
curl -X POST https://your-domain.com/api/v1/team/switch \
  -H "Authorization: Bearer YOUR_API_TOKEN" \
  -H "Content-Type: application/json" \
  -d '{"team_id": 5}'
```

## Example: Complete Authentication Flow

```python
import requests

# Configuration
ZPANEL_URL = "https://your-domain.com/api/v1"
API_TOKEN = "your-api-token-here"

# Create authenticated session
headers = {
    "Authorization": f"Bearer {API_TOKEN}",
    "Content-Type": "application/json",
    "Accept": "application/json"
}

# List applications
response = requests.get(f"{ZPANEL_URL}/applications", headers=headers)
applications = response.json()

# Deploy an application
app_id = applications['data'][0]['id']
response = requests.post(
    f"{ZPANEL_URL}/applications/{app_id}/deploy",
    headers=headers,
    json={"branch": "main"}
)

print(f"Deployment status: {response.status_code}")
```

## Troubleshooting

### Common Authentication Errors

**401 Unauthorized**
- Token is missing or invalid
- Token has been revoked
- Token has expired

**403 Forbidden**
- Token lacks required abilities
- Resource belongs to different team
- IP not in allowlist

**429 Too Many Requests**
- Rate limit exceeded
- Wait and retry after specified time

## Additional Resources

- [API Overview](overview.md)
- [API Examples](examples.md)
- [OpenAPI Specification](../../implementation/phase-1/Zpanel/openapi.yaml)
- [Security Best Practices](../../implementation/phase-1/Zpanel/SECURITY.md)

