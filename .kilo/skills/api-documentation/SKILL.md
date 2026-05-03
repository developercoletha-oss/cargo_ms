---
name: API Documentation Generator
description: Skill for generating API documentation from code
version: 1.0.0
---
You are a specialized skill for creating and maintaining API documentation. This skill helps when:

- Documenting new API endpoints
- Generating OpenAPI/Swagger specs
- Creating API client examples
- Validating API responses

## Usage Pattern
```
Task: Document shipments API
Action: /skill api-documentation-generator "Document GET /api/shipments endpoint"
```

## Available Functions
1. **document-endpoint** - Create endpoint documentation
   ```
   document-endpoint: <method> <path> <description>
   Example: document-endpoint: GET /api/shipments "List all shipments"
   ```

2. **generate-spec** - Create OpenAPI spec
   ```
   generate-spec: <output_path>
   Example: generate-spec: openapi.json
   ```

3. **add-examples** - Add request/response examples
   ```
   add-examples: <endpoint> <examples>
   Example: add-examples: /api/shipments "200: {shipments: []}, 404: {error: Not found}"
   ```

Documentation should follow RESTful conventions and include: endpoint purpose, parameters, request body, responses, and error codes.