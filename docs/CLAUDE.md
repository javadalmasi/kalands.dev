# Claude Code Guidelines for kalands.dev

## 🎯 Project Context

**Project:** Kalands.ir - Advanced E-Commerce Admin Dashboard
**Type:** Laravel + Vue.js Admin Panel
**Main Goal:** Professional, modular admin interface with unified help system

---

## 📚 Key Documentation

- **Admin Dashboard Redesign:** `ADMIN_DASHBOARD_REDESIGN.md`
- **Quick Admin Reference:** `ADMIN_QUICK_REFERENCE.md`
- **Help System Migration Skill:** `.claude/projects/...skills/migrate-module-to-help-system.md`

---

## 🔧 Important Systems

### 1. Module Help System (Unified)

**Status:** ✅ Complete (24 modules)

**Key Files:**
- `resources/views/components/admin/help-offcanvas.blade.php` - Reusable help sidebar
- `resources/css/help-offcanvas.css` - Light/dark mode styling
- `resources/js/admin/help-offcanvas.js` - Open/close logic
- `resources/modules/help/*.md` - Markdown help content (24 files)
- `app/Services/ModuleRegistry.php` - Central module registry
- `app/Services/MarkdownConverter.php` - Markdown → HTML conversion

**How It Works:**
1. Each module blade has `:helpModuleKey="'moduleKey'"`
2. Admin layout renders help button in topbar
3. Click opens offcanvas sidebar with markdown content
4. Content converted to HTML via MarkdownConverter

**To Add New Module:**
→ Use skill: `migrate-module-to-help-system.md`

### 2. Module Registry System

**Config:** `config/modules.php` (24 module definitions)

**Service:** `app/Services/ModuleRegistry.php`
- `all()` - Get all modules grouped by permission status
- `get(key)` - Get single module metadata
- `helpManifest(key)` - Get markdown help content as HTML
- `grouped()` - Get modules by category

### 3. Admin Dashboard Layout

**File:** `resources/views/components/layouts/admin-dashboard.blade.php`

**Features:**
- Responsive sidebar with module navigation
- Admin topbar with notifications, user menu, help button
- Dark mode support via Tailwind `.dark` class
- Mobile-optimized navigation

**Props:**
- `title` - Page title
- `helpModuleKey` - Module key for help system (optional)

### 4. Markdown Help Content

**Location:** `resources/modules/help/{moduleKey}.md`

**Format Rules:**
- `# Title` (hidden in rendering)
- `## Heading 2` (main sections, blue underline)
- `### Heading 3` (subsections)
- Bullet lists: `- item`
- Numbered lists: `1. item`
- Bold: `**text**`
- Inline code: `` `code` ``
- Code blocks: ` ```language\ncode\n``` `
- Tables: standard markdown `| header | header |`
- Blockquotes: `> quote`

---

## 🚀 Common Tasks

### Add Help to Existing Module

```bash
# 1. Extract help content from blade tab-help section
# 2. Create resources/modules/help/{moduleKey}.md
# 3. Add :helpModuleKey="'{moduleKey}'" to blade layout tag
# 4. Remove help tab button and div from blade
# 5. Test in browser
```

See: `.claude/projects/.../skills/migrate-module-to-help-system.md`

### Add New Admin Module

```bash
# 1. Create module config entry in config/modules.php
# 2. Create blade view: resources/views/dash/admin/{module}.blade.php
# 3. Create help file: resources/modules/help/{key}.md
# 4. Add helpModuleKey prop to admin-dashboard layout
# 5. Register routes in AdminDashboardController if needed
```

### Style Adjustments

**Dark Mode:** Use Tailwind's `.dark` class
- Admin UI automatically switches based on user preference
- Help offcanvas content respects dark mode via CSS

**Colors:**
- Primary: `primary` (blue accent)
- Slate: Various shades for text/background
- Use Tailwind utilities, avoid hardcoded hex

---

## ⚙️ Development Workflow

### Before Working on Admin Features

1. **Check module registry** - Is the module defined in `config/modules.php`?
2. **Check help system** - Does the module have markdown help in `resources/modules/help/`?
3. **Check layout** - Does blade have `:helpModuleKey` prop?

### When Making Changes

- **No inline help content** - Use markdown files instead
- **No custom help components** - Use `help-offcanvas` component
- **Keep blade files focused** - Move help to markdown, keep blade for logic/forms
- **Test dark mode** - Check both light and dark themes

---

## 🔍 Code Standards

### Blade Components

**Location:** `resources/views/components/`

**Patterns:**
- Admin forms: Use `admin-btn`, `admin-card`, `admin-toggle` classes
- Help button: Use `:helpModuleKey` prop on admin-dashboard layout
- Dark mode: Use Tailwind `dark:` prefix (not @media prefers-color-scheme)

### PHP Services

**Location:** `app/Services/`

- `ModuleRegistry` - Module metadata and help manifest
- `MarkdownConverter` - Convert markdown to HTML with proper styling

### CSS

**Location:** `resources/css/`

- **Global:** `app.css`
- **Admin:** `admin.css`
- **Help:** `help-offcanvas.css` (light + dark modes)

### JavaScript

**Location:** `resources/js/admin/`

- `admin-app.js` - Main admin entry point
- `help-offcanvas.js` - Offcanvas sidebar logic
- Individual module scripts: `admin-{module}.js`

---

## 🧪 Testing Checklist

When modifying admin features:

```
✓ Help button appears in topbar
✓ Help offcanvas opens on button click
✓ Markdown content renders correctly
✓ Light mode text colors are visible
✓ Dark mode text colors are visible
✓ Offcanvas closes on ESC key
✓ Offcanvas closes on backdrop click
✓ Sidebar pointer-events restored after close
✓ All form elements are accessible
✓ Mobile layout is responsive
```

---

## 📋 Project Structure (Admin)

```
app/
├── Services/
│   ├── ModuleRegistry.php
│   └── MarkdownConverter.php

config/
└── modules.php (24 module definitions)

resources/
├── css/
│   ├── help-offcanvas.css
│   └── admin.css
├── js/
│   ├── admin-app.js
│   └── admin/
│       ├── help-offcanvas.js
│       └── admin-{module}.js
├── modules/
│   └── help/
│       ├── analytics.md
│       ├── email_settings.md
│       ├── sms_settings.md
│       ├── ... (24 markdown files total)
│       └── visitor_intelligence.md
└── views/
    ├── components/
    │   ├── admin/
    │   │   └── help-offcanvas.blade.php
    │   └── layouts/
    │       └── admin-dashboard.blade.php
    └── dash/
        └── admin/
            ├── *-hub.blade.php (18 files)
            ├── email-settings.blade.php
            ├── sms-settings.blade.php
            ├── email-templates.blade.php
            ├── home-items.blade.php
            ├── affiliate-settings.blade.php
            └── queues.blade.php
```

---

## 🎓 Tips & Tricks

**Q: Help content not showing?**
A: Check `helpModuleKey` spelling matches `config/modules.php` key exactly

**Q: Dark mode colors wrong?**
A: Use `.dark .markdown-content` in CSS, not `@media (prefers-color-scheme: dark)`

**Q: Offcanvas stuck open?**
A: Check if `pointer-events-none` is properly removed from sidebar on close

**Q: Markdown tables not rendering?**
A: Tables must use `| header |` format; MarkdownConverter parses them manually

**Q: Need to add help to new module?**
A: Follow skill: `migrate-module-to-help-system.md` - exact step-by-step

---

## 📞 Quick References

- **Module Keys:** See `config/modules.php` lines 1-200
- **Help Markdown Examples:** See `resources/modules/help/analytics.md`
- **Component Props:** See `resources/views/components/admin/help-offcanvas.blade.php` line 1
- **Styling:** See `resources/css/help-offcanvas.css` for complete theme

---

## 🚦 Current Status

| Component | Status | Notes |
|-----------|--------|-------|
| Module Registry | ✅ Complete | 24 modules configured |
| Help Offcanvas | ✅ Complete | Light + dark mode |
| Markdown Files | ✅ Complete | All 24 help files |
| Admin Dashboard | ✅ Complete | Help button in topbar |
| Blade Layouts | ✅ Complete | All 24 modules migrated |

**Next Steps:** Monitor for new modules; use skill for migration.

---

**Last Updated:** 2026-07-14
**Created By:** Claude Code
**For Questions:** Refer to documentation files above

===

<laravel-boost-guidelines>
=== foundation rules ===

# Laravel Boost Guidelines

The Laravel Boost guidelines are specifically curated by Laravel maintainers for this application. These guidelines should be followed closely to ensure the best experience when building Laravel applications.

## Foundational Context

This application is a Laravel application and its main Laravel ecosystems package & versions are below. You are an expert with them all. Ensure you abide by these specific packages & versions.

- php - 8.5
- laravel/framework (LARAVEL) - v13
- laravel/octane (OCTANE) - v2
- laravel/prompts (PROMPTS) - v0
- laravel/sanctum (SANCTUM) - v4
- laravel/socialite (SOCIALITE) - v5
- livewire/livewire (LIVEWIRE) - v4
- laravel/boost (BOOST) - v2
- laravel/mcp (MCP) - v0
- laravel/pint (PINT) - v1
- phpunit/phpunit (PHPUNIT) - v12
- tailwindcss (TAILWINDCSS) - v4

## Skills Activation

This project has domain-specific skills available in `**/skills/**`. You MUST activate the relevant skill whenever you work in that domain—don't wait until you're stuck.

## Conventions

- You must follow all existing code conventions used in this application. When creating or editing a file, check sibling files for the correct structure, approach, and naming.
- Use descriptive names for variables and methods. For example, `isRegisteredForDiscounts`, not `discount()`.
- Check for existing components to reuse before writing a new one.

## Verification Scripts

- Do not create verification scripts or tinker when tests cover that functionality and prove they work. Unit and feature tests are more important.

## Application Structure & Architecture

- Stick to existing directory structure; don't create new base folders without approval.
- Do not change the application's dependencies without approval.

## Frontend Bundling

- If the user doesn't see a frontend change reflected in the UI, it could mean they need to run `npm run build`, `npm run dev`, or `composer run dev`. Ask them.

## Documentation Files

- You must only create documentation files if explicitly requested by the user.

## Replies

- Be concise in your explanations - focus on what's important rather than explaining obvious details.

=== boost rules ===

# Laravel Boost

## Tools

- Laravel Boost is an MCP server with tools designed specifically for this application. Prefer Boost tools over manual alternatives like shell commands or file reads.
- Use `database-query` to run read-only queries against the database instead of writing raw SQL in tinker.
- Use `database-schema` to inspect table structure before writing migrations or models.
- Use `get-absolute-url` to resolve the correct scheme, domain, and port for project URLs. Always use this before sharing a URL with the user.
- Use `browser-logs` to read browser logs, errors, and exceptions. Only recent logs are useful, ignore old entries.

## Searching Documentation (IMPORTANT)

- Always use `search-docs` before making code changes. Do not skip this step. It returns version-specific docs based on installed packages automatically.
- Pass a `packages` array to scope results when you know which packages are relevant.
- Use multiple broad, topic-based queries: `['rate limiting', 'routing rate limiting', 'routing']`. Expect the most relevant results first.
- Do not add package names to queries because package info is already shared. Use `test resource table`, not `filament 4 test resource table`.

### Search Syntax

1. Use words for auto-stemmed AND logic: `rate limit` matches both "rate" AND "limit".
2. Use `"quoted phrases"` for exact position matching: `"infinite scroll"` requires adjacent words in order.
3. Combine words and phrases for mixed queries: `middleware "rate limit"`.
4. Use multiple queries for OR logic: `queries=["authentication", "middleware"]`.

## Artisan

- Run Artisan commands directly via the command line (e.g., `php artisan route:list`). Use `php artisan list` to discover available commands and `php artisan [command] --help` to check parameters.
- Inspect routes with `php artisan route:list`. Filter with: `--method=GET`, `--name=users`, `--path=api`, `--except-vendor`, `--only-vendor`.
- Read configuration values using dot notation: `php artisan config:show app.name`, `php artisan config:show database.default`. Or read config files directly from the `config/` directory.

## Tinker

- Execute PHP in app context for debugging and testing code. Do not create models without user approval, prefer tests with factories instead. Prefer existing Artisan commands over custom tinker code.
- Always use single quotes to prevent shell expansion: `php artisan tinker --execute 'Your::code();'`
  - Double quotes for PHP strings inside: `php artisan tinker --execute 'User::where("active", true)->count();'`

=== php rules ===

# PHP

- Always use curly braces for control structures, even for single-line bodies.
- Use PHP 8 constructor property promotion: `public function __construct(public GitHub $github) { }`. Do not leave empty zero-parameter `__construct()` methods unless the constructor is private.
- Use explicit return type declarations and type hints for all method parameters: `function isAccessible(User $user, ?string $path = null): bool`
- Use TitleCase for Enum keys: `FavoritePerson`, `BestLake`, `Monthly`.
- Prefer PHPDoc blocks over inline comments. Only add inline comments for exceptionally complex logic.
- Use array shape type definitions in PHPDoc blocks.

=== deployments rules ===

# Deployment

- Laravel can be deployed using [Laravel Cloud](https://cloud.laravel.com/), which is the fastest way to deploy and scale production Laravel applications.

=== laravel/core rules ===

# Do Things the Laravel Way

- Use `php artisan make:` commands to create new files (i.e. migrations, controllers, models, etc.). You can list available Artisan commands using `php artisan list` and check their parameters with `php artisan [command] --help`.
- If you're creating a generic PHP class, use `php artisan make:class`.
- Pass `--no-interaction` to all Artisan commands to ensure they work without user input. You should also pass the correct `--options` to ensure correct behavior.

### Model Creation

- When creating new models, create useful factories and seeders for them too. Ask the user if they need any other things, using `php artisan make:model --help` to check the available options.

## APIs & Eloquent Resources

- For APIs, default to using Eloquent API Resources and API versioning unless existing API routes do not, then you should follow existing application convention.

## URL Generation

- When generating links to other pages, prefer named routes and the `route()` function.

## Testing

- When creating models for tests, use the factories for the models. Check if the factory has custom states that can be used before manually setting up the model.
- Faker: Use methods such as `$this->faker->word()` or `fake()->randomDigit()`. Follow existing conventions whether to use `$this->faker` or `fake()`.
- When creating tests, make use of `php artisan make:test [options] {name}` to create a feature test, and pass `--unit` to create a unit test. Most tests should be feature tests.

## Vite Error

- If you receive an "Illuminate\Foundation\ViteException: Unable to locate file in Vite manifest" error, you can run `npm run build` or ask the user to run `npm run dev` or `composer run dev`.

=== octane/core rules ===

# Octane

- Octane boots the application once and reuses it across requests, so singletons persist between requests.
- The Laravel container's `scoped` method may be used as a safe alternative to `singleton`.
- Never inject the container, request, or config repository into a singleton's constructor; use a resolver closure or `bind()` instead:

```php
// Bad
$this->app->singleton(Service::class, fn (Application $app) => new Service($app['request']));

// Good
$this->app->singleton(Service::class, fn () => new Service(fn () => request()));
```

- Never append to static properties, as they accumulate in memory across requests.

=== livewire/core rules ===

# Livewire

- Livewire allow to build dynamic, reactive interfaces in PHP without writing JavaScript.
- You can use Alpine.js for client-side interactions instead of JavaScript frameworks.
- Keep state server-side so the UI reflects it. Validate and authorize in actions as you would in HTTP requests.

=== pint/core rules ===

# Laravel Pint Code Formatter

- If you have modified any PHP files, you must run `vendor/bin/pint --dirty --format agent` before finalizing changes to ensure your code matches the project's expected style.
- Do not run `vendor/bin/pint --test --format agent`, simply run `vendor/bin/pint --format agent` to fix any formatting issues.

=== phpunit/core rules ===

# PHPUnit

- This application uses PHPUnit for testing. All tests must be written as PHPUnit classes. Use `php artisan make:test --phpunit {name}` to create a new test.
- If you see a test using "Pest", convert it to PHPUnit.
- Every time a test has been updated, run that singular test.
- When the tests relating to your feature are passing, ask the user if they would like to also run the entire test suite to make sure everything is still passing.
- Tests should cover all happy paths, failure paths, and edge cases.
- You must not remove any tests or test files from the tests directory without approval. These are not temporary or helper files; these are core to the application.

## Running Tests

- Run the minimal number of tests, using an appropriate filter, before finalizing.
- To run all tests: `php artisan test --compact`.
- To run all tests in a file: `php artisan test --compact tests/Feature/ExampleTest.php`.
- To filter on a particular test name: `php artisan test --compact --filter=testName` (recommended after making a change to a related file).

</laravel-boost-guidelines>
