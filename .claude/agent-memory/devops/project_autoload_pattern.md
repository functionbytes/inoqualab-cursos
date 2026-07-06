---
name: project-architecture-monolith
description: Project is a monolith in app/ — no modules/ directory, no nwidart. This replaces the stale nwidart memory.
metadata:
  type: project
---

This project uses a **domain monolith** architecture. There is no `modules/` directory and `nwidart/laravel-modules` is NOT installed.

Code lives in `app/Http/Controllers/{Domain}/` organized by domain (Managers, Customers, Distributors, Enterprises, Supports, Accountings, Auth, Pages).

**Why:** The project was confirmed to never have used nwidart. The old memory referenced a previous investigation of a different codebase context. CLAUDE.md explicitly states "No existe `modules/`".

**How to apply:** Never suggest `php artisan module:*` commands, never reference `Modules\` namespace, never look for `modules_statuses.json`. Route files live in `routes/{domain}.php`. Views live in `resources/views/`.
