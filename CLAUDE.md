# CLAUDE.md

@AGENTS.md

## Claude Code specifics

Todo arriba viene de `AGENTS.md` (fuente de verdad compartida con Codex/Cursor/Copilot).
Lo que sigue aplica **solo** a Claude Code.

### Workflow preferences

- **Plan mode** (Shift+Tab dos veces) obligatorio antes de:
  - migrations Drizzle
  - `app/(admin)/layout.tsx` o `middleware.ts`
  - `lib/auth.ts`
  - `lib/ai/**` (cualquier cambio de prompt o modelo)
  - más de 5 archivos en un turno
- **Tests primero** en lógica no trivial.
- **Evals primero** en cambios de AI features — NO cambiar prompt productivo sin correr `pnpm eval` verde.
- Después de dos correcciones sobre lo mismo, preguntá qué falta en AGENTS.md o `.claude/rules/`.

### Slash commands del proyecto

- `/ship` — typecheck + lint + test + build. Reporta problemas sin arreglar auto.
- `/ship-ai` — ship + `pnpm eval`.
- `/migrate-page <ruta>` — traduce página legacy a Next según `docs/migration-playbook.md`.
- `/new-admin-resource <n>` — scaffold CRUD admin (página + Server Actions + schema + tests + seed).
- `/new-ai-feature <n>` — scaffold feature AI (route + prompt file + tool + eval dataset stub).
- `/db-migration <desc>` — genera migration Drizzle + tests.
- `/deploy-check` — valida build para PM2 + Nginx config.
- `/wt-status` — muestra estado de todos los git worktrees.

### Sub-agents

- `legacy-translator` — lee HTML/PHP/WordPress legacy y produce equivalente TS/Next.
- `sql-reviewer` — migrations Drizzle, índices, N+1, SQLi.
- `admin-scaffolder` — CRUDs siguiendo pipeline del proyecto (Zod → schema → page → columns → actions → form).
- `prompt-engineer` — diseña/ajusta system prompts con A/B contra evals.
- `cms-ux-reviewer` — revisa UX del admin desde perspectiva no técnica (Marketing). Chequea que no haya jerga dev en labels, placeholders, errores.

### Path-scoped rules (auto-cargan)

`.claude/rules/*.md` con frontmatter `paths:` se cargan cuando tocás archivos matching:
- `marketing.md` → `app/(marketing)/**` — SEO, ISR, CWV, conversión, copy medicina prepaga.
- `admin.md` → `app/(admin)/**` — UX para no técnicos, Server Actions, guards.
- `db.md` → `lib/db/**`, `drizzle/**` — schema, queries, índices.
- `ai.md` → `lib/ai/**`, `app/api/ai/**`, `evals/**` — multi-model patterns, evals, guardrails.
- `legacy.md` → `legacy/**` — read-only, patterns de migración.

### Extended thinking

- **ultrathink** → decisiones arquitectónicas (diseño schema planes/coberturas, estrategia multi-model, estructura de evals).
- **think hard** → bugs con race conditions, auth flows, streaming AI.

### Context hygiene

- `/clear` entre tareas no relacionadas.
- `docs/*.md` on-demand con `@docs/<file>.md`.
- **No cargues `legacy/`** sin necesidad. Abrí solo el archivo específico.
- En worktree `samap-ai/`: mantené open `lib/ai/models.ts` + el prompt activo, cerrá el resto.

### Personal preferences

@~/.claude/nico-prefs.md

### Hooks activos (`.claude/settings.json`)

- **PostToolUse(Edit|Write)** → `pnpm format` sobre archivos tocados.
- **PreToolUse(Bash)** → deny: `rm -rf /`, `git push --force`, `DROP TABLE`, `DROP DATABASE`, `TRUNCATE` en prod, comandos con credenciales plain text.
- **PreToolUse(Edit) en `lib/ai/prompts/**`** → bloquea si no existe eval dataset para esa feature.
- **SessionStart** → `docker ps | grep mysql` o aviso.

Override bloqueos **pidiendo explícito**, nunca rodear.

### Skills instaladas

- `frontend-design` (oficial Anthropic).
- `impeccable` (pbakaus) — anti-monoculture QA.
- `Shadcnblocks-Skill` — catálogo bloques shadcn.
- `vercel-labs/agent-skills` — web guidelines + React best practices + composition.
- `accesslint` — WCAG 2.1 AA.
- `php-legacy-to-modern-stack` (custom, compartida con Sanatorio).
- `mysql-drizzle-conventions` (custom, adaptada a schema SAMAP sin `tbl_`).
- `gcp-compute-engine-deploy` (custom, compartida con Sanatorio).
- `admin-panel-crud-kit` (custom, compartida).
- `authjs-credentials-patterns` (custom, compartida).
- **`multi-model-ai-features`** (custom, nueva — ver `.claude/skills/`).
- **`cms-for-non-technical-users`** (custom, nueva — UX admin para Marketing).
- **`git-worktrees-multi-agent`** (custom, nueva — workflow paralelo).
- **`medical-prepaid-compliance`** (custom, nueva — copy regulatorio, Ley 6534, Superintendencia).

### Worktree awareness

Si estás en un worktree no-default (`../samap-ai/`, `../samap-admin/`, etc.), al iniciar sesión:
1. Ejecutá `git worktree list` mental para ubicarte.
2. Tu branch es el del worktree — no cambies a `main` sin avisar.
3. Port de dev es el del `.env.local` local, no el default.
4. Revisá `.agent-lock` — si existe con PID de otra sesión activa, pará y avisá.