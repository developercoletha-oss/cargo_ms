---
description: Search for specific code patterns in the codebase
agent: explore
model: anthropic/claude-sonnet
---
Search for $1 pattern in the codebase.
Use $2 as file filter if provided ($ARGUMENTS).
Reference results with @file paths found.

## Search Process
1. Define search pattern from $1
2. Apply file filter from $2 if given
3. Execute search across codebase
4. Return matching files and line numbers