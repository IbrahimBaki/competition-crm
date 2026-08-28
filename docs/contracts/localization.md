# Localization Architecture

## Overview

This document describes the bilingual (Arabic/English) localization strategy for the Support CRM system.

## Admin-Authored Bilingual Fields

All administrator-authored short text fields (names, labels, descriptions) are stored as JSON objects with `ar` and `en` keys:

```json
{
  "ar": "النص بالعربية",
  "en": "Text in English"
}
```

### Affected Entities

- `branches.name`
- `departments.name`
- `teams.name`
- `branch_holidays.name`

## Request Locale Negotiation

The active request locale is determined in priority order:

1. **Accept-Language header** (q-weighted list)
   - Example: `Accept-Language: ar-EG,ar;q=0.9,en;q=0.5` → resolves to `ar`
   - Language variants (ar-EG) fall back to base language (ar)

2. **Authenticated user's `locale` column** (if set)
   - User preference takes precedence over org default

3. **Organisation default locale** (`auth_settings.default_locale`)
   - Currently defaults to `'ar'`

4. **Fallback to English** (`'en'`)
   - Absolute fallback when nothing else is set

**How it works**: The `NegotiateLocale` middleware runs after authentication and sets `App::getLocale()` for the entire request. All API responses include the `Content-Language` header.

## Bilingual Object Response Format

Bilingual fields are returned in API responses as objects with a fallback marker:

```json
{
  "name": {
    "ar": "المقر الرئيسي",
    "en": "Headquarters",
    "__fallback": null
  }
}
```

When the requested locale is missing, it falls back to the other locale:

```json
{
  "name": {
    "ar": "Headquarters",
    "en": "Headquarters",
    "__fallback": "en"
  }
}
```

The `__fallback` field indicates:
- `null`: Both locales present
- `"ar"` or `"en"`: Missing locale; value was copied from the opposite locale

**Client responsibility**: Frontends should display a "translated from..." badge when `__fallback` is not null.

## Server-Side Translation Rules

- **Server translates only**: validation messages, business error messages, system emails
- **Server does NOT emit**: UI chrome copy (button labels, menu text, static labels)
- **Client owns**: all UI chrome text; it never comes from the server

### Exception Messages

All user-facing exception messages use translation keys:

```php
throw new BranchHasActiveDepartmentsException($count);
// Message is resolved from lang/en/errors.php or lang/ar/errors.php
```

Translation key format: `errors.<domain>.<specific_error>`

Example: `errors.organisation.branch_has_active_departments`

### Validation Messages

Custom validation rules are translated via `lang/en/validation.php` and `lang/ar/validation.php`.

## Mail Localization

Email messages are sent in the recipient's locale:

```php
Mail::to($user)->send(
    (new UserInvitedMail($invitation, $token))->locale($user->locale ?? 'ar')
);
```

The Blade template resolves subject and body text from translated strings.

## Database Charset

All database connections are configured for UTF-8MB4:

- **Character set**: `utf8mb4`
- **Collation**: `utf8mb4_unicode_ci`
- **Default**: Set in `config/database.php` (MySQL)
- **SQLite**: Unicode support by default

This ensures Arabic and other multi-byte characters are stored and queried correctly.

## Search and Indexing

When searching bilingual JSON fields:

```sql
SELECT * FROM branches
WHERE JSON_UNQUOTE(JSON_EXTRACT(name, '$.ar')) LIKE '%keyword%' COLLATE utf8mb4_unicode_ci
```

Always use `JSON_UNQUOTE` and `utf8mb4_unicode_ci` collation for consistent behavior.

## Exports and CSV

Export operations must respect the target locale:

```php
$name = $bilingual->forLocale($exportLocale);
// Returns ['ar' => '...', 'en' => '...', '__fallback' => null|'ar'|'en']
```

CSV files must be encoded as UTF-8 with BOM:

```php
// File encoding: UTF-8 with BOM
// Prevents Excel from misinterpreting Arabic text
```

## Audit Logs

Audit log messages are **not** translated:

- Audit entries capture action descriptions in a consistent language
- Designed for administrative review, not end-user display
- Example: `"user.invited"` event with payload `['email' => 'user@example.com']`

## API Contract

### GET /auth/me

Response includes locale information:

```json
{
  "data": {
    "id": "uuid-here",
    "email": "user@example.com",
    "locale": "ar",
    "available_locales": ["ar", "en"],
    "..."
  }
}
```

### All API Error Responses

Include `Content-Language` header and translated messages:

```
HTTP/1.1 422 Unprocessable Entity
Content-Language: ar

{
  "error": {
    "code": "validation_failed",
    "message": "البيانات المعطاة غير صحيحة",
    "field_errors": { "..." }
  }
}
```

## Extension Points

Future bilingual features (if needed):

- Audit log translations (would require separate audit_i18n table)
- RTL layout support (CSS classes, flexbox direction)
- Frontend translation bundles (separate from server)
- Multi-language support beyond AR/EN (add to `LocaleResolver::SUPPORTED`)
