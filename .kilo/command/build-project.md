---
description: Build the project with proper compilation steps
agent: code
model: anthropic/claude-sonnet
subtask: true
---
Build and compile the project ensuring all dependencies are properly resolved.
Use $ARGUMENTS for build arguments.
Reference configuration with @file and check output with !`cmd`.

## Build Steps
1. Install dependencies
2. Compile source code
3. Run pre-build checks
4. Generate artifacts
5. Validate build output