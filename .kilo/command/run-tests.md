---
description: Run tests in specified directory and fix failures
agent: code
model: anthropic/claude-sonnet
subtask: true
---
Run all tests in $1 and fix any failures or errors.
Use $ARGUMENTS for the full argument string.
Reference files with @file and shell output with !`cmd`.

## Testing Strategy
1. Run the specified test suite
2. Analyze any failures
3. Fix failing tests
4. Re-run to verify fixes