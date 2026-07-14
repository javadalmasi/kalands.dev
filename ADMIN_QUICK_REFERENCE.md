# Admin Dashboard Quick Reference

## Module System Quick Start

### Check All Modules
```php
$registry = app(\App\Services\ModuleRegistry::class);
$all = $registry->all();           // Returns all 24 modules
$grouped = $registry->grouped();   // Grouped by category
```

### Get Single Module
```php
$module = $registry->get('analytics');
// Returns: ['key', 'label', 'description', 'icon', 'permission', 'category']
```

### Load Module Help
```php
$help = $registry->helpManifest('analytics');
// Returns: ['module', 'version', 'help' => ['title', 'sections']]
```

### Get Localized Names
```php
$labels = $registry->categoryLabels();
// ['communication' => 'ارتباطات', 'content' => 'مدیریت محتوا', ...]

$icons = $registry->categoryIcons();
// ['communication' => 'hub', 'analytics' => 'analytics', ...]
```

---

## Dashboard Home Features

### Service Status Indicators
Located at: `resources/views/dash/admin/index.blade.php`

Each service card shows:
- Real-time status (✓ OK, ⚠️ Warning, ✗ Error)
- Key metric (e.g., "Database mode" for Queue)
- Additional details (e.g., "Last run 2 hours ago")

### Available Services
1. **Queue** - Job processing status
2. **Cache** - Cache driver and clear time
3. **GeoIP** - Database version and updates
4. **Object Cache** - Redis/Memcached connection
5. **Analytics** - Live visitors and events
6. **Sitemap** - Generated URLs and timing

---

## Help Component Usage

### In Any Module View
```blade
<div id="tab-help">
    <x-admin.module-help moduleKey="analytics" />
</div>
```

### Supported Content Types
- **text**: Regular paragraph
- **tip**: Green callout with lightbulb icon
- **warning**: Yellow callout with warning icon  
- **code**: Code block with syntax highlighting
- **table**: Responsive table with headers and rows

### Example Help JSON
```json
{
  "module": "my_module",
  "version": "1.0",
  "help": {
    "title": "ماژول من",
    "sections": [
      {
        "heading": "معرفی",
        "type": "text",
        "content": "توضیح..."
      },
      {
        "heading": "نکات مهم",
        "type": "tip",
        "content": "اگر X، سپس Y"
      }
    ]
  }
}
```

---

## Adding a New Module

### 1. Add to Config
**File**: `config/modules.php`

```php
'my_module' => [
    'key' => 'my_module',
    'label' => 'ماژول من',
    'description' => 'توضیح کوتاه',
    'icon' => 'settings',  // Material Icon name
    'permission' => 'my_module.view',
    'category' => 'technical',  // or 'communication', 'content', 'data', 'analytics'
],
```

### 2. Create Help Manifest
**File**: `resources/modules/help/my_module.json`

```json
{
  "module": "my_module",
  "version": "1.0",
  "help": {
    "title": "راهنمای ماژول",
    "sections": [
      { "heading": "معرفی", "type": "text", "content": "..." },
      { "heading": "ویژگی‌ها", "type": "text", "content": "..." }
    ]
  }
}
```

### 3. Clear Cache
```bash
php artisan config:clear
```

### 4. Module automatically appears in:
- `/dash/admin/{authkey}/modules` - Module list
- Permission system (if `permission` is set)
- Navigation sidebar (if configured)

---

## Module Categories

| Category | Label | Icon | Usage |
|----------|-------|------|-------|
| `communication` | ارتباطات | hub | Email, SMS, Contact |
| `content` | مدیریت محتوا | article | Products, Comments, Posts |
| `data` | داده‌ها و فایل‌ها | folder_open | Files, Categories, Data |
| `technical` | فنی و بهینه‌سازی | settings | Cache, Queue, GeoIP |
| `analytics` | تحلیل و آمار | analytics | Analytics, Reports |

---

## Material Icons Used

```
dashboard, analytics, group, inventory_2, contact_support
hub, link, folder, drafts, queue, forum, confirmation_number
quiz, language, settings_suggest, search, menu, report_problem
bolt, memory, psychology, terminal, category, map, publish
storage, sensors, public, touch_app, filter_alt, timeline
manage_search, bug_report, group_work, compare_arrows
explore, cleaning_services, help_outline, home_repair_service
```

---

## Troubleshooting

### Module not appearing in list
1. Check permission: User must have `module.permission` granted
2. Verify `config/modules.php` syntax
3. Run `php artisan config:clear`
4. Check `hasPermission()` in User model

### Help not loading
1. Check JSON file exists at `resources/modules/help/{moduleKey}.json`
2. Validate JSON syntax (use jsonlint.com)
3. Verify component uses correct moduleKey

### Service status showing error
1. Check if service is actually installed (Cache, GeoIP, etc.)
2. Verify database tables exist (QueueExecutionLog, SitemapRunLog)
3. Check SettingsRepository for related config

---

## Controller Methods

### AdminDashboardController

#### `index(SettingsRepository $settingsRepository)`
- Returns dashboard home page
- Gathers 6 service statuses
- Shows stat cards and service indicators

#### `modules(Request $request, ...)`
- Lists all modules grouped by category
- Filters by user permissions
- Returns module list view

#### `moduleSettings(string $authkey, string $moduleKey, ...)`
- Loads specific module settings page
- Routes to correct hub view
- Checks user permissions

---

## Database Models Used

- `User` - For user count
- `Admin` - For admin count
- `Comment` - For pending comments
- `Ticket` - For open tickets
- `QueueExecutionLog` - For queue status
- `AnalyticsEvent` - For analytics events
- `AnalyticsLiveVisitor` - For live visitor count
- `SitemapRunLog` - For sitemap generation

---

## Performance Tips

✅ **Good**:
- Use `ModuleRegistry::grouped()` for categorized lists
- Cache help manifests if loading frequently
- Use service status in dashboard sparingly

❌ **Avoid**:
- Calling `ModuleRegistry` in loops
- Loading help for all modules at once
- Running heavy queries in service status methods

---

## Next Steps

### Phase 3: Analytics Rewrite
- Split 1,488-line analytics view
- Remove Cohort analysis (unused feature)
- Refactor 2,155-line service class

### Phase 4: Sidebar Enhancements
- Add module category badges
- Implement collapsed mode
- Improve navigation tooltips

---

For more details, see: `ADMIN_DASHBOARD_REDESIGN.md`
