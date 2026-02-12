# GEMINI.md

Repository guidance for coding agents working on `vendor-integration-woo`.

## Project Snapshot

- Type: WordPress plugin (WooCommerce integration)
- Entry point: `WooCatalogo.php`
- Main runtime areas:
  - Bootstrap and hook registration: `WooCatalogo.php`, `includes/class.woocatalogo.php`
  - Admin UI and AJAX endpoints: `includes/class.woocatalogo.admin.php`
  - Catalog sync logic: `includes/class.woocatalogo.catalog.php`
  - Product create/update/delete logic: `includes/class.woocatalogo.product.php`
  - Provider abstraction and Nexsys provider: `includes/interfaces/*`, `includes/abstracts/*`, `includes/providers/class.provider.nexsys.php`
  - Admin JavaScript: `admin/js/admin-woocatalogo.js`, `admin/js/actualizacion-stock.js`

## Rules Files Check

- Checked `.cursor/rules/`: not present
- Checked `.cursorrules`: not present
- Checked `.github/copilot-instructions.md`: not present
- Result: no Cursor/Copilot instruction files to inherit from in this repository.

### Skills Registry

Auto-generated from `./.agents/skills` (repo) and `~/.agents/skills` (global).

| Skill | Source | Description |
|-------|--------|-------------|
| `agents-gemini-sync` | repo | Sync `AGENTS.md` and `GEMINI.md` skill registry sections from both repository-local skills (`./.agents/skills`) and global skills (`~/.agents/skills`). Use when creating, renaming, deleting, or updating skills and you need agent docs to reflect current available skills. |
| `error-handling-patterns` | global | Master error handling patterns across languages including exceptions, Result types, error propagation, and graceful degradation to build resilient applications. Use when implementing error handling, designing APIs, or improving application reliability. |
| `find-skills` | global | Helps users discover and install agent skills when they ask questions like "how do I do X", "find a skill for X", "is there a skill that can...", or express interest in extending capabilities. This skill should be used when the user is looking for functionality that might exist as an installable skill. |
| `frontend-design` | global | Create distinctive, production-grade frontend interfaces with high design quality. Use this skill when the user asks to build web components, pages, artifacts, posters, or applications (examples include websites, landing pages, dashboards, React components, HTML/CSS layouts, or when styling/beautifying any web UI). Generates creative, polished code and UI design that avoids generic AI aesthetics. |
| `skill-creator` | global | Guide for creating effective skills. This skill should be used when users want to create a new skill (or update an existing skill) that extends Claude's capabilities with specialized knowledge, workflows, or tool integrations. |
| `skill-sync` | global | Syncs skill metadata to AGENTS.md Auto-invoke sections. Trigger: When updating skill metadata (metadata.scope/metadata.auto_invoke), regenerating Auto-invoke tables, or running ./skills/skill-sync/assets/sync.sh (including --dry-run/--scope). |
| `wordpress-plugin-core` | repo | Build secure WordPress plugins with hooks, database interactions, Settings API, custom post types, and REST API. Covers Simple, OOP, and PSR-4 architecture patterns plus the Security Trinity. Includes WordPress 6.7-6.9 breaking changes. Use when creating plugins or troubleshooting SQL injection, XSS, CSRF, REST API vulnerabilities, wpdb::prepare errors, nonce edge cases, or WordPress 6.8+ bcrypt migration. |## Build, Lint, and Test Commands

This repo has no `composer.json`, no `package.json`, and no built-in PHPUnit config.
Use the commands below as the default validation workflow.

### Quick Validation (recommended before commit)

1. PHP syntax check on files you changed
2. Optional whole-plugin syntax sweep
3. Manual WordPress/WooCommerce smoke test

### Syntax / Lint Commands

- Lint a single PHP file:
  - `php -l "includes/class.woocatalogo.catalog.php"`
- Lint all PHP files in repo (PowerShell):
  - `Get-ChildItem -Recurse -Filter *.php | ForEach-Object { php -l $_.FullName }`
- Lint only plugin PHP directories (faster, PowerShell):
  - `Get-ChildItem includes,admin -Recurse -Filter *.php | ForEach-Object { php -l $_.FullName }`

### Test Commands

- No automated test suite is configured in this repository today.
- If a local WordPress test stack exists, run manual tests through wp-admin.
- Suggested manual smoke scenarios:
  - Activate plugin with WooCommerce active
  - Open plugin admin pages and confirm DataTables loads
  - Run catalog JSON update
  - Run stock update and price update
  - Insert one product and verify SKU/meta/category/tag/image assignment

### Single-Test Guidance (important)

Because there is no PHPUnit (or JS test runner) configured, a "single test" should be interpreted as one targeted validation action:

- Single-file syntax check:
  - `php -l "path/to/changed-file.php"`
- Single behavior smoke test:
  - Execute one AJAX flow from the admin UI (for example, only "Actualizar Stock en Woocommerce")
- Single endpoint check with WP-CLI (if available):
  - `wp eval "do_action('wp_ajax_update_stock_product_vendor_integration');"`

## Code Style and Conventions

Follow existing repository conventions first, then tighten security and consistency when touching code.

### PHP Version / Style Baseline

- Code is written in classic WordPress plugin style (non-namespaced classes, static methods, procedural hook functions).
- Prefer compatibility with common WordPress hosting PHP versions (avoid bleeding-edge syntax unless project policy changes).
- Use 4-space indentation in PHP.
- Keep braces and spacing consistent with nearby file style.

### Imports and File Organization

- No Composer autoloading is used in plugin runtime.
- Include dependencies with `require_once` from `WooCatalogo.php`.
- Add new provider-related code under:
  - `includes/interfaces/`
  - `includes/abstracts/`
  - `includes/providers/`
- Add admin-only assets under `admin/` and enqueue them conditionally.

### Naming Conventions

- Existing class naming uses prefix `cVendorIntegration*`; keep consistency in touched files.
- Existing method naming uses prefix `viw*`; keep for public AJAX/admin methods.
- AJAX action names use snake_case with suffix `_vendor_integration`.
- Option/transient keys use `vendor_integration_*` (prefer this over legacy `woocatalogo_*`).
- Constants use uppercase snake case (for example `VENDOR_INTEGRATION_PLUGIN_DIR`).

### WordPress Hook and AJAX Patterns

- Register hooks in centralized init/hook files (`WooCatalogo.php` and `includes/class.woocatalogo.php`).
- For privileged AJAX handlers:
  - Verify nonce (`wp_verify_nonce` or `check_ajax_referer`)
  - Check capability (`current_user_can('manage_options')`)
  - Sanitize all user input (`sanitize_text_field`, `sanitize_email`, `intval`, etc.)
  - End with `wp_die()` or `wp_send_json_*`
- Do not add public `wp_ajax_nopriv_*` handlers for admin data endpoints unless explicitly required.

### Data Access and SQL

- Prefer `$wpdb->prepare()` for queries with dynamic input.
- Use typed placeholders (`%d`, `%f`, `%s`) correctly.
- Prefer `$wpdb->insert()`, `$wpdb->update()`, `$wpdb->delete()` where possible.
- Keep table names prefixed with `$wpdb->prefix`.

### Security and Secrets

- Never commit real credentials or tokens.
- `.env` is gitignored; keep secrets there when needed.
- Escape output by context:
  - HTML text: `esc_html()`
  - Attributes: `esc_attr()`
  - URLs: `esc_url()`
- Sanitize input at boundaries, not deep inside business logic only.

### Error Handling and Logging

- Use defensive checks for remote API calls and empty responses.
- For provider HTTP failures, follow existing retry/re-auth pattern in `VendorIntegrationNexsysProvider`.
- Use `error_log()` only for actionable debug information.
- Guard noisy logs behind `VENDOR_INTEGRATION_DEBUG_MODE` where practical.
- Prefer fail-safe behavior (skip item, continue loop) over fatal exits in batch sync operations.

### API / Provider Layer

- Keep provider return shape normalized before reaching catalog/product classes.
- Preserve backward-compatible keys expected by existing product sync logic.
- When adding a provider, implement `VendorIntegrationProviderInterface` and extend `VendorIntegrationProviderAbstract`.

### JavaScript Style (Admin)

- Existing JS is jQuery-based; continue with jQuery for consistency unless a migration is planned.
- Reuse localized globals created with `wp_localize_script`.
- Keep AJAX action names and nonce usage aligned with PHP handlers.
- Avoid introducing build steps for JS unless the repo adopts a frontend toolchain.

### Files and Paths

- Use plugin path/url constants for internal file references and assets.
- Keep admin data files under `admin/dataWooCatalogo/` when required by existing flows.
- Avoid hardcoding absolute system paths.

## Practical Agent Workflow

1. Inspect related PHP + JS flow end-to-end before editing.
2. Make smallest safe change that matches existing naming/style.
3. Run `php -l` on every changed PHP file.
4. If behavior changed, perform one focused manual smoke test in wp-admin.
5. Report any discovered legacy inconsistencies (do not silently refactor unrelated areas).

## Known Repository Realities

- No CI and no automated tests are configured today.
- Mixed legacy/new naming exists (`woocatalogo_*` and `vendor_integration_*`).
- Spanish/English strings are mixed; preserve user-facing language unless asked to standardize.
