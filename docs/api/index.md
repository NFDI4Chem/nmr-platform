# API Reference

The NMR Platform provides a comprehensive RESTful API for programmatic access to all platform features.

## Overview

The API is designed to be:
- **RESTful**: Following REST principles and conventions
- **Secure**: Token-based authentication with role-based access
- **Versioned**: Backward compatibility through API versioning
- **Well-documented**: Complete OpenAPI/Swagger documentation
- **Rate-limited**: Proper rate limiting to ensure stability

## Base URL

All API requests should be made to:
```
https://your-domain.com/api/v1/
```

## Authentication

The API uses token-based authentication. You need to include your API token in the Authorization header:

```http
Authorization: Bearer your-api-token
```

### Obtaining API Tokens

1. **Via Web Interface**:
   - Log into the platform
   - Go to Profile → API Tokens
   - Generate a new token

2. **Via API Endpoint**:
   ```http
   POST /api/v1/auth/token
   Content-Type: application/json

   {
     "email": "user@example.com",
     "password": "password"
   }
   ```

### Token Types

- **Personal Access Tokens**: For individual user access
- **Application Tokens**: For application-to-application access
- **Read-only Tokens**: Limited to GET requests only

## Core Endpoints

### Projects

| Method | Endpoint | Description |
|--------|----------|-------------|
| GET    | `/projects` | List all accessible projects |
| POST   | `/projects` | Create a new project |
| GET    | `/projects/{id}` | Get project details |
| PUT    | `/projects/{id}` | Update project |
| DELETE | `/projects/{id}` | Delete project |

### Datasets

| Method | Endpoint | Description |
|--------|----------|-------------|
| GET    | `/projects/{id}/datasets` | List project datasets |
| POST   | `/projects/{id}/datasets` | Upload new dataset |
| GET    | `/datasets/{id}` | Get dataset details |
| PUT    | `/datasets/{id}` | Update dataset metadata |
| DELETE | `/datasets/{id}` | Delete dataset |

### Spectra

| Method | Endpoint | Description |
|--------|----------|-------------|
| GET    | `/datasets/{id}/spectra` | Get spectrum data |
| GET    | `/datasets/{id}/peaks` | Get peak data |
| POST   | `/datasets/{id}/peaks` | Create/update peaks |
| GET    | `/datasets/{id}/integrations` | Get integration data |

### Analysis

| Method | Endpoint | Description |
|--------|----------|-------------|
| POST   | `/datasets/{id}/analyze` | Run analysis workflow |
| GET    | `/datasets/{id}/analysis/{id}` | Get analysis results |
| POST   | `/datasets/{id}/compare` | Compare multiple datasets |

## Request/Response Format

### Content Types

- **Request**: `application/json`
- **Response**: `application/json`
- **File Upload**: `multipart/form-data`

### Standard Response Format

```json
{
  "data": {
    // Response data
  },
  "meta": {
    "pagination": {
      "current_page": 1,
      "per_page": 20,
      "total": 100
    }
  },
  "links": {
    "first": "https://api.example.com/projects?page=1",
    "last": "https://api.example.com/projects?page=5",
    "prev": null,
    "next": "https://api.example.com/projects?page=2"
  }
}
```

### Error Response Format

```json
{
  "error": {
    "code": "VALIDATION_ERROR",
    "message": "The given data was invalid.",
    "details": {
      "name": ["The name field is required."]
    }
  }
}
```

## Pagination

All list endpoints support pagination:

**Query Parameters:**
- `page`: Page number (default: 1)
- `per_page`: Items per page (default: 20, max: 100)

**Example:**
```http
GET /api/v1/projects?page=2&per_page=50
```

## Filtering and Sorting

### Filtering

Use query parameters to filter results:

```http
GET /api/v1/datasets?experiment_type=1H_NMR&nucleus=1H
```

**Common Filters:**
- `experiment_type`: Type of NMR experiment
- `nucleus`: Observed nucleus
- `solvent`: Solvent used
- `created_after`: ISO 8601 date
- `created_before`: ISO 8601 date

### Sorting

Use the `sort` parameter:

```http
GET /api/v1/datasets?sort=-created_at,name
```

- Prefix with `-` for descending order
- Multiple fields separated by commas

## Rate Limiting

The API implements rate limiting:

- **Default**: 1000 requests per hour per token
- **Burst**: 100 requests per minute

Rate limit headers are included in responses:
```http
X-RateLimit-Limit: 1000
X-RateLimit-Remaining: 999
X-RateLimit-Reset: 1640995200
```

## File Uploads

### Upload Dataset

```http
POST /api/v1/projects/{id}/datasets
Content-Type: multipart/form-data

{
  "name": "Sample Dataset",
  "description": "Description of the dataset",
  "files[]": [file1, file2, ...],
  "metadata": {
    "experiment_type": "1H_NMR",
    "nucleus": "1H",
    "solvent": "CDCl3"
  }
}
```

### Large File Uploads

For large files, use chunked uploads:

1. **Initiate Upload**:
   ```http
   POST /api/v1/uploads/initiate
   {
     "filename": "large_dataset.zip",
     "filesize": 1073741824,
     "chunk_size": 1048576
   }
   ```

2. **Upload Chunks**:
   ```http
   PUT /api/v1/uploads/{upload_id}/chunks/{chunk_number}
   Content-Type: application/octet-stream
   Content-Range: bytes 0-1048575/1073741824

   [binary data]
   ```

3. **Complete Upload**:
   ```http
   POST /api/v1/uploads/{upload_id}/complete
   ```

## Webhooks

Configure webhooks to receive real-time notifications:

### Event Types

- `dataset.created`: New dataset uploaded
- `dataset.processed`: Dataset processing completed
- `analysis.completed`: Analysis workflow finished
- `project.shared`: Project shared with user

### Webhook Configuration

```http
POST /api/v1/webhooks
{
  "url": "https://your-app.com/webhook",
  "events": ["dataset.created", "analysis.completed"],
  "secret": "your-webhook-secret"
}
```

### Webhook Payload

```json
{
  "event": "dataset.created",
  "timestamp": "2024-01-15T10:30:00Z",
  "data": {
    "dataset_id": 123,
    "project_id": 456,
    "name": "New Dataset"
  }
}
```

## SDK and Libraries

### Official SDKs

**Python SDK:**
```bash
pip install nmr-platform-sdk
```

```python
from nmr_platform import Client

client = Client(api_token="your-token")
projects = client.projects.list()
```

**JavaScript SDK:**
```bash
npm install @nmr-platform/sdk
```

```javascript
import { NMRPlatformClient } from '@nmr-platform/sdk';

const client = new NMRPlatformClient({
  apiToken: 'your-token'
});

const projects = await client.projects.list();
```

**R Package:**
```r
install.packages("nmrplatform")
library(nmrplatform)

client <- nmr_client(token = "your-token")
projects <- nmr_projects(client)
```

## OpenAPI Documentation

Complete API documentation is available in OpenAPI format:

- **Interactive Docs**: `https://your-domain.com/api/docs`
- **OpenAPI Spec**: `https://your-domain.com/api/openapi.json`
- **Redoc**: `https://your-domain.com/api/redoc`

## Next Sections

- [Authentication Details](/api/authentication)
- [Complete Endpoint Reference](/api/endpoints)
- [Data Models and Schemas](/api/models)
- [Code Examples and Tutorials](/api/examples)
