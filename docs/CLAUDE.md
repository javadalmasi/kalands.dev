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

