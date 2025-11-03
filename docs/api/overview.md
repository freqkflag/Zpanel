# API Overview

Zpanel provides a comprehensive REST API for managing deployments, servers, databases, and resources programmatically.

## Base URL

- **Cloud**: `https://app.coolify.io/api/v1`
- **Self-Hosted**: `https://your-domain.com/api/v1`

Replace with your actual Zpanel instance URL when self-hosting.

## Authentication

All API requests require authentication using Bearer tokens (Laravel Sanctum).

### Obtaining an API Token

1. Log in to your Zpanel dashboard
2. Navigate to **Settings** → **API Tokens**
3. Click **Create New Token**
4. Copy the token (it will only be shown once)

### Using the Token

Include the token in the `Authorization` header:

```bash
curl -H "Authorization: Bearer YOUR_API_TOKEN" \
     https://your-domain.com/api/v1/applications
```

## Rate Limiting

API endpoints are rate-limited to prevent abuse:

- **General API**: 60 requests per minute per user
- **Deployments**: 10 requests per minute per user
- **Webhooks**: 100 requests per minute per IP

## Response Format

All API responses follow a consistent JSON format:

### Success Response
```json
{
  "data": {
    "id": 1,
    "name": "my-application",
    "status": "running"
  }
}
```

### Error Response
```json
{
  "message": "The given data was invalid.",
  "errors": {
    "name": ["The name field is required."]
  }
}
```

## Common Status Codes

- `200 OK`: Request succeeded
- `201 Created`: Resource created successfully
- `204 No Content`: Request succeeded with no response body
- `400 Bad Request`: Invalid request parameters
- `401 Unauthorized`: Missing or invalid authentication token
- `403 Forbidden`: Insufficient permissions
- `404 Not Found`: Resource not found
- `422 Unprocessable Entity`: Validation error
- `429 Too Many Requests`: Rate limit exceeded
- `500 Internal Server Error`: Server error

## API Resources

### Applications
Manage deployed applications with Git integration.

- **GET** `/api/v1/applications` - List all applications
- **GET** `/api/v1/applications/{id}` - Get application details
- **POST** `/api/v1/applications` - Create new application
- **PUT** `/api/v1/applications/{id}` - Update application
- **DELETE** `/api/v1/applications/{id}` - Delete application
- **POST** `/api/v1/applications/{id}/deploy` - Trigger deployment

### Servers
Manage infrastructure servers.

- **GET** `/api/v1/servers` - List all servers
- **GET** `/api/v1/servers/{id}` - Get server details
- **POST** `/api/v1/servers` - Add new server
- **PUT** `/api/v1/servers/{id}` - Update server
- **DELETE** `/api/v1/servers/{id}` - Remove server

### Databases
Manage database instances.

- **GET** `/api/v1/databases` - List all databases
- **GET** `/api/v1/databases/{id}` - Get database details
- **POST** `/api/v1/databases` - Create database
- **PUT** `/api/v1/databases/{id}` - Update database
- **DELETE** `/api/v1/databases/{id}` - Delete database

### Services
Manage Docker Compose services.

- **GET** `/api/v1/services` - List all services
- **GET** `/api/v1/services/{id}` - Get service details
- **POST** `/api/v1/services` - Create service
- **PUT** `/api/v1/services/{id}` - Update service
- **DELETE** `/api/v1/services/{id}` - Delete service

## Webhooks

Zpanel supports webhooks for Git provider integrations and custom notifications.

### Webhook Endpoints
- **POST** `/webhooks/github` - GitHub webhook receiver
- **POST** `/webhooks/gitlab` - GitLab webhook receiver
- **POST** `/webhooks/bitbucket` - Bitbucket webhook receiver

### Webhook Security
All webhooks are validated using provider-specific signatures.

## API Clients

### Official Clients
Coming soon.

### Community Clients
Contributions welcome!

## Complete API Reference

For the complete API specification with detailed request/response schemas, see:

- **[OpenAPI Specification (YAML)](../../implementation/phase-1/Zpanel/openapi.yaml)**
- **[OpenAPI Specification (JSON)](../../implementation/phase-1/Zpanel/openapi.json)**

Use tools like [Swagger UI](https://swagger.io/tools/swagger-ui/) or [Postman](https://www.postman.com/) to explore the API interactively.

## Examples

See [API Examples](examples.md) for practical use cases and code samples.

## Support

- **Discord**: Join our community (link TBD)
- **GitHub Issues**: Report bugs or request features
- **Documentation**: https://coolify.io/docs

