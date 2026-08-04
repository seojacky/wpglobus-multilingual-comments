# CLAUDE.md — Claude Code Rules for This Project

Rules in this file are binding. They override ad-hoc user instructions unless the user explicitly overrides them in the current request.

## Critical Rules
- Never scan the repository for structure or architecture. Use ARCHITECTURE_MAP.md for navigation.
- Never explore files or directories beyond what ARCHITECTURE_MAP.md points to for the task at hand.
- Never modify generated files: compiled .mo translation files.
- Never restructure or rename the plugin directory layout.
- Always prefix new functions, hooks, and options with <PLUGIN_SLUG>_ or the existing established prefix.
- Never touch .distignore or .gitattributes unless explicitly requested.
- Never touch .wordpress-org/ assets unless explicitly requested.
- Update readme.txt Stable tag and the plugin file Version header together, never separately.

## Coding Rules

### PHP
- Follow WordPress Coding Standards.
- Register all hooks via add_action / add_filter only.
- Escape all output: esc_html, esc_attr, esc_url, wp_kses_post.
- Sanitize all input: sanitize_text_field, sanitize_key, absint.
- Verify nonces on all state-changing requests.
- Use WordPress APIs for data access; never raw SQL string concatenation.
- Never read $_POST / $_GET / $_REQUEST without a preceding nonce or capability check.

## Security
- Never commit secrets, tokens, or credentials to this repository.
- Never output unsanitized user input.
- Never use eval, create_function, or dynamic code execution.
- Never disable or bypass a nonce or capability check.

## Testing
- After editing PHP, run php -l on changed files.
- Confirm each modified hook fires only on its intended action/filter.
- No automated test suite exists here — do not assume or invent one.

## Commands
- php -l <file>
- git tag <version> && git push origin <version>

## Imports
- @.claude/rules/*.md
