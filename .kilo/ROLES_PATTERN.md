# Kilo System Role Templates

This directory contains role-specific templates following a **consistent YAML frontmatter + content layout pattern**.

## Pattern Overview

All role files share the same structural layout with these sections:

```yaml
---
# Required metadata fields (vary by role type)
---

# Content body with operational details
```

## Role Types and Layouts

### 1. Commands (`.kilo/command/*.md`)

**Layout Pattern:**
```yaml
---
description: Brief description of what this command does
agent: [agent-name]      # Optional: route to specific agent
model: [provider/model]  # Optional: override model
subtask: [true|false]    # Optional: run as subtask
---

# Content: What to do and how
# Use template variables:
# - $1, $2, ... $N for positional arguments
# - $ARGUMENTS for full argument string
# - @file for referencing file contents
# - !`cmd` for shell output
```

**Example:** See `command/run-tests.md`, `command/build-project.md`

---

### 2. Agents (`.kilo/agent/*.md`)

**Layout Pattern:**
```yaml
---
description: What this agent specializes in
type: [primary|subagent|all]  # How agent can be invoked
mode: [primary|subagent|all]  # Same as type for backwards compatibility
model: [provider/model]       # Optional model override
steps: [number]               # Max agentic iterations
hidden: [true|false]          # Hide from @ menu (subagent only)
color: "#[hex-color]"         # Visual identification
permission:                   # Optional permissions override
  bash: [allow|ask|deny|pattern-map]
  edit: [allow|ask|deny|pattern-map]
  read: [allow|ask|deny]
  skill: { skill-name: [allow|ask|deny] }
  external_directory: [allow|deny]
---

# Agent persona and behavioral instructions
# - Role definition and expertise
# - Key priorities and values
# - Operational guidelines
# - Decision-making framework
```

**Example:** See `agent/code.md`, `agent/explore.md`, `agent/general.md`

---

### 3. Skills (`.kilo/skills/*/SKILL.md`)

**Layout Pattern:**
```yaml
---
name: [Skill Name]
description: What this skill does
version: [x.y.z]
---

# Skill purpose and when to use it

## Usage Pattern
```
Action: /skill [skill-name] "[task description]"
```

## Available Functions
1. **[function-name]** - Description
   ```
   format: example usage
   ```

Guidelines for using this skill effectively.
```

**Example:** See `skills/database-migration/SKILL.md`, `skills/api-documentation/SKILL.md`

---

### 4. Modes (`.kilo/mode/*.md`)

**Layout Pattern:**
```yaml
---
name: [Mode Name]
description: Brief description
model: [provider/model]  # Optional model override
---

# Mode behavior and focus areas
# - What to prioritize in this mode
# - How to approach tasks
# - Key differences from other modes
```

**Example:** See `mode/development.md`, `mode/review.md`

---

### 5. Workflows (`.kilo/workflows/*.md`)

**Layout Pattern:**
```yaml
---
description: What this workflow accomplishes
agent: [agent-name]      # Optional: which agent to use
model: [provider/model]  # Optional: model override
---

# Step-by-step process
# Use !`cmd` to execute and show shell commands
# Use @file to reference configuration
# Reference template variables if accepting arguments
```

**Example:** See `workflows/setup-dev.md`

---

## Creating New Roles

### Step 1: Choose the Role Type
- **Command** - For invocable actions with arguments
- **Agent** - For specialized AI personas
- **Skill** - For domain-specific capabilities
- **Mode** - For behavioral modes
- **Workflow** - For multi-step processes

### Step 2: Create Directory Structure
```bash
# Commands
mkdir -p .kilo/command
# Agents  
mkdir -p .kilo/agent
# Skills
mkdir -p .kilo/skills/[skill-name]
# Modes
mkdir -p .kilo/mode
# Workflows
mkdir -p .kilo/workflows
```

### Step 3: Follow the YAML Frontmatter Pattern
Copy the appropriate template above, filling in your specific values.

### Step 4: Write Content Body
- Be clear and concise
- Use template variables (commands, workflows)
- Include examples where helpful
- Reference files with @file syntax
- Execute commands with !`cmd` syntax

### Step 5: Validate
The system validates `.kilo/command/*.md` files automatically.

## Template Variables Reference

| Variable | Description | Used In |
|----------|-------------|----------|
| `$1`, `$2`, ... `$N` | Positional arguments | Commands, Workflows |
| `$ARGUMENTS` | Full argument string | Commands, Workflows |
| `@file` | Reference file contents | All role types |
| `!\`cmd\`` | Execute and embed shell output | All role types |

## Key Principles

1. **Consistent Structure** - Same YAML frontmatter pattern across all roles
2. **Clear Separation** - Metadata vs. content clearly divided
3. **Template-Driven** - Use variables for dynamic behavior
4. **Self-Documenting** - Each file explains its own purpose
5. **Composable** - Roles can reference and build on each other

## Best Practices

- Keep YAML frontmatter minimal but descriptive
- Use consistent field ordering across similar roles
- Document permissions explicitly for agents
- Include version numbers for skills
- Provide usage examples in skills
- Make modes behaviorally distinct
- Keep workflows focused on single objectives

## File Naming Conventions

- **Commands**: kebab-case descriptive name (`run-tests.md`, `build-project.md`)
- **Agents**: singular noun describing role (`code.md`, `explore.md`)
- **Skills**: directory name = skill ID, file = `SKILL.md`
- **Modes**: adjective describing mode (`development.md`, `review.md`)
- **Workflows**: kebab-case action description (`setup-dev.md`)

## Loading Order and Precedence

Files are loaded from multiple locations with this precedence (highest to lowest):

1. `KILO_CONFIG_CONTENT` (env variable)
2. Project `.kilo/`, `.kilocode/`, `.opencode/` directories
3. `~/.kilo/`, `~/.kilocode/`, `~/.opencode/` directories  
4. `~/.config/kilo/` directory
5. Managed config (enterprise)

Later entries override earlier ones for the same role name.

## See Also

- [Kilo CLI Configuration Reference](#) - Overall config system
- [Commands Documentation](#) - Command-specific details
- [Agents Documentation](#) - Agent behavior and permissions
- [Skills Documentation](#) - Skill development guide