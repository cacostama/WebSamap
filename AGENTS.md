# AGENTS.md

> Canonical instructions for **all** AI coding agents (Codex, Claude Code, Cursor, Copilot).
> Claude Code reads this via `@AGENTS.md` import from `CLAUDE.md`.
> Mantener bajo ~280 líneas. Detalle va a `docs/` y se referencia acá.

## Project overview

**SAMAP — Medicina Prepaga del Sanatorio Adventista de Asunción.** Migración de sitio legacy (template HTML5 medical + WordPress/PHP) a stack moderno.

Dos superficies:
- **Landing pública** (`app/(marketing)/`) — captación de afiliados: home, planes (Alfa/Beta/Gamma/etc.), beneficios, prestadores, blog, cotizador, formulario de contacto. SEO-first + conversión.
- **Panel de admin** (`app/(admin)/`) — CMS operado por equipo de **Marketing** (no técnicos): gestión de planes, coberturas, fotos, slider, blog, testimonios, red de prestadores, leads entrantes.

**Negocio**: empresa con 35+ años, 8.000+ socios, regulada por Superintendencia de Salud (EMPP). Cuidado con copy legal, divulgación de coberturas, LGPD/datos personales de salud.

Legacy vive en `legacy/` (read-only reference).

## Brand basics

Referencia: `Logos y colores.pdf` entregado por cliente.

- **Azul institucional** (llamitas y tipografía Red Hospitales IASD): `#274767` / variantes cercanas `#254564`, `#274266`.
- **Verde mar SAMAP** (bandas envolventes): `#6CA3AB` / variante `#74A4B0`.
- **Neutro oscuro**: `#2F2E2D`.
- Usar esta paleta como base visual. Evitar verdes genéricos, azules saturados o paletas monocromáticas no alineadas.

## Tech stack

- **Runtime**: Node.js 20 LTS
- **Framework**: Next.js 15.x (App Router, React 19, Turbopack dev)
- **Styling**: Tailwind CSS v4 + shadcn/ui
- **ORM**: Drizzle ORM con driver `mysql2`
- **DB**: MySQL 8 (**DB dedicada `samap_db`** en el mismo servidor MySQL que Sanatorio — users y grants separados)
- **Auth**: Auth.js v5 (Credentials + JWT) para admin. **NO hay login de asegurados** en esta versión.
- **AI SDK**: `ai` (Vercel AI SDK v5) + providers `@ai-sdk/anthropic`, `@ai-sdk/openai`, `@ai-sdk/google`
- **CMS primitives**: rich text con Tiptap, image editor inline con `react-image-crop`, file upload con UploadThing pattern local (FS de la VM)
- **Email**: Nodemailer + SMTP de la VM (alertas de leads al equipo comercial)
- **File storage**: filesystem VM (`/var/www/samap/uploads/`), servido por Nginx
- **Lang**: TypeScript strict + `noUncheckedIndexedAccess: true`
- **Package manager**: pnpm (NO npm/yarn)
- **Testing**: Vitest (unit) + Playwright (e2e) + evals en `evals/` para AI features
- **Lint/Format**: ESLint flat config + Prettier
- **Process manager**: PM2 cluster (2 instancias en la VM: `samap-web` y `sanatorio-web`)
- **Reverse proxy**: Nginx con vhosts separados para `samap.com.py` y `admin.samap.com.py`
- **SSL**: Let's Encrypt (certbot)
- **Deploy target**: misma VM GCP Compute Engine que Sanatorio (Ubuntu 22.04 LTS)

## Setup

```bash
pnpm install
cp .env.example .env.local           # ver sección "Env vars"
pnpm db:migrate                      # aplica migrations Drizzle a samap_db
pnpm db:seed                         # datos iniciales (planes, admin user)
pnpm dev                             # http://localhost:3100 (sanatorio usa 3000)
```

**MySQL local**: `docker compose up -d mysql` crea DB `samap_dev` con user `samap` / pass en `.env.local`.

## Env vars (nunca commitear valores)

```bash
# .env.example — template
DATABASE_URL="mysql://samap_user:PASS@localhost:3306/samap_db"
AUTH_SECRET=""                       # openssl rand -base64 32
AUTH_URL="http://localhost:3100"     # prod: https://admin.samap.com.py
PUBLIC_URL="http://localhost:3100"   # prod: https://samap.com.py

# AI providers (multi-model)
ANTHROPIC_API_KEY=""
OPENAI_API_KEY=""
GOOGLE_GENERATIVE_AI_API_KEY=""      # opcional, fallback
AI_DEFAULT_MODEL="claude-sonnet-4-5-20250929"
AI_FAST_MODEL="gpt-4o-mini"          # clasificación, routing barato
AI_FALLBACK_MODEL="gemini-2.0-flash"

# Email (leads comerciales)
SMTP_HOST=""
SMTP_PORT="587"
SMTP_USER=""
SMTP_PASS=""
SMTP_FROM="web@samap.com.py"
LEADS_NOTIFICATION_TO="comercial@samap.com.py,ventas@samap.com.py"

# Uploads
UPLOAD_DIR="/var/www/samap/uploads"
PUBLIC_UPLOAD_URL="https://samap.com.py/uploads"

# Negocio
WHATSAPP_PHONE="595..."              # confirmar con cliente
WHATSAPP_COMERCIAL="595..."          # para CTA "Cotizar ahora"
TZ="America/Asuncion"
LOCALE="es-PY"
```

**CRÍTICO**: valores reales solo en `.env.local` (gitignored) y `/etc/samap/env` en el server (chmod 600). **Nunca** hardcodear en código, comentarios, ni este AGENTS.md.

## Commands

| Qué | Comando |
|---|---|
| Dev | `pnpm dev` (puerto 3100) |
| Build | `pnpm build` |
| Start prod local | `pnpm start` |
| Type check | `pnpm typecheck` |
| Lint / fix / format | `pnpm lint` / `lint:fix` / `format` |
| Unit / watch / e2e | `pnpm test` / `test:watch` / `test:e2e` |
| AI evals | `pnpm eval` (corre tests del `evals/` dir) |
| Drizzle new migration | `pnpm drizzle-kit generate` |
| Drizzle apply | `pnpm db:migrate` |
| Drizzle studio | `pnpm db:studio` |
| Deploy a prod | `pnpm deploy` |

**Antes de declarar "done"**: `pnpm typecheck && pnpm lint && pnpm test`. Si tocaste AI features: además `pnpm eval`.

## Code style

- **TypeScript strict**. Sin `any` salvo con comentario ESLint justificando.
- **Funciones antes que clases**.
- **Named exports** por default. `export default` solo páginas/layouts.
- Single quotes, no semicolons (Prettier).
- Imports ordenados: `node → external → @/ aliases → relative`.
- **Server Components by default**. `'use client'` solo si hace falta — documentar motivo.
- **No client-side secrets**: `process.env.X` en cliente debe ser `NEXT_PUBLIC_*`.
- Timezone: `America/Asuncion`. Fechas en UI con `Intl.DateTimeFormat('es-PY', { timeZone: 'America/Asuncion' })`.
- Montos en **guaraníes**: formateo con `Intl.NumberFormat('es-PY', { style: 'currency', currency: 'PYG', maximumFractionDigits: 0 })`. Ejemplo: "Gs. 150.000".

## Folder structure

```
app/
├── (marketing)/                 # Landing pública, ISR, SEO
│   ├── page.tsx                 # home (hero + planes destacados + CTA cotizar)
│   ├── planes/
│   │   ├── page.tsx             # listado/comparador
│   │   └── [slug]/page.tsx      # detalle de plan
│   ├── beneficios/page.tsx
│   ├── prestadores/page.tsx     # red de prestadores con filtros (ciudad, especialidad)
│   ├── blog/[slug]/page.tsx
│   ├── cotizar/page.tsx         # wizard multi-step
│   ├── contacto/page.tsx
│   └── layout.tsx
├── (admin)/                     # Panel CMS (marketing opera acá)
│   ├── layout.tsx               # requireAuth('editor' | 'admin') + sidebar
│   ├── dashboard/page.tsx       # leads recientes, stats
│   ├── planes/(list,new,[id]/edit)
│   ├── coberturas/...
│   ├── prestadores/...
│   ├── blog/...
│   ├── slider/...
│   ├── testimonios/...
│   ├── galeria/...
│   ├── leads/page.tsx           # inbox de contactos entrantes
│   └── usuarios/...             # solo rol admin
├── api/
│   ├── auth/[...nextauth]/route.ts
│   ├── ai/
│   │   ├── chat/route.ts        # streaming chat asesor virtual
│   │   ├── classify-lead/route.ts
│   │   └── suggest-plan/route.ts
│   └── cron/<job>/route.ts
└── layout.tsx

components/{ui,marketing,admin,ai}/
lib/
├── db/
│   ├── index.ts
│   ├── schema.ts
│   └── migrations/
├── auth.ts
├── ai/
│   ├── models.ts                # registry central de modelos
│   ├── prompts/                 # system prompts versionados
│   └── tools/                   # tool definitions para AI SDK
├── uploads.ts
├── whatsapp.ts
├── validations/
├── constants.ts
└── utils.ts
evals/                           # datasets + tests para AI features
scripts/
├── deploy.sh
├── backup-db.sh
└── migrate-legacy-content.ts
legacy/                          # sitio viejo read-only
```

## Database conventions (MySQL + Drizzle)

- **DB dedicada `samap_db`**. User MySQL `samap_user` con `GRANT ALL` solo a esa DB. **Nunca** cruzar a `sanatorio` DB.
- **Naming**: tablas `snake_case` plural (`planes`, `coberturas`, `prestadores`, `leads`, `blog_posts`, `testimonios`, `slider_items`, `galeria_items`, `users`). **Sin prefijo `tbl_`** — proyecto nuevo, schema limpio.
- Columnas `snake_case`, FKs `<tabla>_id`.
- **Charset**: `utf8mb4` + `utf8mb4_unicode_ci` siempre.
- **Timestamps**: `created_at`, `updated_at`, `deleted_at NULL` en todas las tablas.
- **Soft deletes**: nunca DELETE físico salvo scripts de mantenimiento.
- **Migrations Drizzle append-only**. Revertir = nueva migration que deshace.
- **SQL directo**: permitido con `db.execute(sql\`...\`)` solo si Drizzle no lo expresa. Siempre parametrizado.
- **Transactions** para operaciones multi-tabla.
- **Connection pool**: `mysql2/promise` con `connectionLimit: 10` en prod.

### Core tables (outline — ver `lib/db/schema.ts` para detalle)

- `planes` — id, slug, nombre, descripcion_corta, precio_guaranies, orden, destacado, activo, imagen_url, ...
- `coberturas` — plan_id, tipo (consultas, analisis, urgencias, internacion), detalle, limite_anual, orden
- `prestadores` — id, nombre, especialidad, ciudad, direccion, telefono, lat, lng, activo
- `leads` — id, nombre, telefono, email, plan_interes_id, origen (cotizador|contacto|chat_ai), estado (nuevo|contactado|cerrado), notas, clasificacion_ai (json), created_at
- `blog_posts` — id, slug, titulo, contenido_html, contenido_tiptap_json, autor, publicado, publicado_at
- `testimonios` — id, nombre, texto, foto_url, plan_id, consentimiento_firmado_at
- `slider_items`, `galeria_items`, `usuarios`

## Auth conventions (Auth.js v5)

- **Strategy**: JWT stateless.
- **Provider**: Credentials + bcrypt cost 12.
- **Roles**: `admin` (full) | `editor` (CMS content pero no usuarios) | `comercial` (solo lee leads).
- **Guard**: helper `requireAuth(role?)` en RSC y Server Actions.
- **Middleware** (`middleware.ts`): matcher `['/admin/:path*']`.
- **Rate limit**: 5 intentos / 15min por IP en login.

## AI features (multi-model, Vercel AI SDK v5)

### Principios

1. **Model routing por caso de uso**, no "un modelo para todo":
   - `claude-sonnet-4-5` → chat conversacional largo (asesor virtual de planes), redacción de respuestas a leads.
   - `gpt-4o-mini` → clasificación de leads (urgencia, intención), extracción estructurada, resumen corto.
   - `gemini-2.0-flash` → fallback si los otros caen + batch processing nocturno.
2. **Registry central** en `lib/ai/models.ts`:
   ```ts
   export const models = {
     default: anthropic('claude-sonnet-4-5-20250929'),
     fast: openai('gpt-4o-mini'),
     fallback: google('gemini-2.0-flash'),
   } as const
   ```
3. **Prompts versionados** en `lib/ai/prompts/` como archivos `.md` con frontmatter (version, modelo, cambios). Nunca inline en el código.
4. **Tool calling** definido en `lib/ai/tools/`. Cada tool con Zod schema de input/output + test unitario.
5. **Streaming** por default en UI. Non-streaming solo para jobs background.
6. **Cost tracking**: cada llamada loguea `model`, `tokens_in`, `tokens_out`, `cost_usd_cents` en tabla `ai_calls_log`. Dashboard admin muestra gasto semanal.
7. **Evals antes de cambiar prompts**: carpeta `evals/<feature>/` con dataset JSONL + script que corre contra los 3 modelos y compara outputs. NO cambiar prompt productivo sin eval verde.
8. **Guardrails**:
   - Nunca dar consejo médico: el asesor virtual sugiere planes y explica coberturas, NO diagnostica.
   - Disclaimer obligatorio en cada respuesta del chat: "Esta información es orientativa. Para casos de salud concretos, consultá con un médico."
   - Filtro de PII en logs: teléfonos y cédulas se hashean antes de loguear.
   - Rate limit por sesión: 20 mensajes / hora / IP.

### Casos de uso implementados

- **`/api/ai/chat`** — asesor virtual (Claude Sonnet, streaming). Sistema: conoce los planes SAMAP, puede buscar coberturas por nombre, agendar callback con comercial.
- **`/api/ai/classify-lead`** — al entrar un lead, GPT-4o-mini extrae: urgencia (alta|media|baja), plan interés inferido, idioma (es|gn|mixto), intención (cotizar|queja|información).
- **`/api/ai/suggest-plan`** — dado un perfil (edad, familia, presupuesto), sugiere top 2 planes con justificación. Claude Sonnet.

## Uploads (CMS para Marketing)

- **Importante**: el admin lo usan personas de Marketing **sin conocimientos técnicos**. UX debe ser drop-zone + preview + crop inline. Nunca pedir que elijan formato o calidad.
- **Pipeline**: drop → valida MIME real (magic bytes) → resize a variantes (original, 1920, 1024, 640, 320, AVIF + WebP) → guarda en `/var/www/samap/uploads/<categoria>/<yyyy>/<mm>/<uuid>-<size>.<ext>` → devuelve URL con placeholder blur base64.
- **Librerías**: `sharp` para procesamiento, `file-type` para validar MIME.
- **Límites**: 10 MB por archivo, max 2000×2000 después de resize, rechaza si width < 600 para imagen de plan/slider.
- **Reemplazos**: subir imagen nueva a mismo recurso → marca vieja como `deleted_at` en tabla `uploads` pero mantiene archivo (recuperable 30 días) → cron limpia.

## Testing

- **Unit**: Vitest, meta >70% en `lib/`.
- **E2E**: Playwright contra `pnpm dev`.
- **DB integration**: testcontainers MySQL.
- **AI evals**: `evals/` con datasets JSONL. Corre `pnpm eval` antes de merge a `main` si tocaste `lib/ai/`.

## Commits & PRs

- Conventional Commits: `feat(marketing): ...`, `fix(admin): ...`, `feat(ai): ...`.
- Branches `feat/*`, `fix/*`.
- PR scopes: `marketing`, `admin`, `ai`, `db`, `auth`, `infra`, `legacy-migration`.
- Squash merge.

## Deploy (misma VM que Sanatorio)

Detalle en `docs/deploy.md`. Resumen:

- **Misma VM Ubuntu 22.04**, puerto interno `3100` (Sanatorio usa `3000`).
- **Path**: `/var/www/samap/current/` (symlink a release timestamp).
- **PM2 process**: `samap-web`, cluster 2 instancias, `ecosystem.config.js`.
- **Nginx vhost**: `samap.com.py` (marketing) y `admin.samap.com.py` (panel) → ambos proxy a `localhost:3100`. Separación por `server_name` + route matching.
- **SSL**: certbot con ambos dominios.
- **MySQL**: misma instancia, DB `samap_db`, user `samap_user` con grants solo a esa DB.
- **Backups**: cron diario `mysqldump samap_db` + tar `/var/www/samap/uploads/` → bucket GCS `samap-backups` con retención 30 días. Separado del backup de Sanatorio.
- **Log rotation**: PM2 logs + Nginx logs rotados semanal.

## Multi-agent development (git worktrees)

Ver `docs/multi-agent-workflow.md`. **Resumen crítico**:

- Branch principal `main`. Worktrees separados para trabajo paralelo:
  - `../samap-landing/` (branch `feat/landing`)
  - `../samap-admin/` (branch `feat/admin`)
  - `../samap-ai/` (branch `feat/ai-features`)
  - `../samap-infra/` (branch `feat/infra`)
- Cada worktree tiene su **propio `.env.local`** y **puerto dev distinto** (3100, 3101, 3102, 3103).
- **MySQL schema**: una sola DB compartida entre worktrees — `samap_db`. Pero cada worktree puede hacer migrations en su branch; **merge conflicts de migrations se resuelven renumerando** con `drizzle-kit migrate:rename`.
- Script `scripts/wt-new.sh <nombre> <puerto>` automatiza creación de worktree + setup.
- Script `scripts/wt-sync.sh` hace `git fetch && pull main && rebase` en todos los worktrees.
- **Agentes por worktree**:
  - `samap-landing/` → **Claude Code** (Claude Sonnet 4.5, mejor en RSC/SEO/UI)
  - `samap-admin/` → **Claude Code** (buena en forms complejos + shadcn)
  - `samap-ai/` → **Codex** (GPT-5, mejor en prompts + evals)
  - `samap-infra/` → cualquiera, tareas cortas
- **Nunca** dos agentes en el mismo worktree simultáneamente — lockfile en `.agent-lock`.

## Security & gotchas

- **Datos de salud = datos sensibles**. Respeto de Ley 6534/20 (Paraguay protección datos personales).
- Leads con teléfono/email → encriptar en DB columnas sensibles con `AES_ENCRYPT` de MySQL o app-level con `node:crypto`. Decidir en `docs/architecture.md`.
- Consentimiento explícito en formularios (checkbox "Acepto política de privacidad").
- **Nunca** loggear PII en plain text. Hashear teléfonos y emails antes de loguear.
- **Superintendencia de Salud**: copy de planes/coberturas debe coincidir con lo registrado. NO inventar coberturas en AI chat — solo citar lo que está en DB.
- **Testimonios**: columna `consentimiento_firmado_at` obligatoria. No publicar sin eso.
- Upload validation: magic bytes obligatorio, no confiar en `Content-Type`.
- CSRF: Server Actions built-in.
- Legacy en `legacy/` solo referencia. Nunca editar.

## Migration from legacy

Ver `docs/migration-playbook.md`. Resumen:
1. Inventario de rutas legacy → mapeo a Next (preservar URL o 301).
2. Contenido de planes/coberturas del sitio viejo → seed inicial de DB.
3. Imágenes del sitio viejo → migrar a `/uploads` local con script.
4. Forms de contacto → Server Actions + Zod + notificación email a comercial.
5. No migrar "por las dudas": cada página legacy migrada justifica por tráfico/SEO.

## Constants centralizadas

Todo valor fijo en `lib/constants.ts`:

```ts
export const WHATSAPP_COMERCIAL = process.env.WHATSAPP_COMERCIAL ?? ''
export const WHATSAPP_URL = `https://api.whatsapp.com/send?phone=${WHATSAPP_COMERCIAL}`
export const TIMEZONE = 'America/Asuncion'
export const LOCALE = 'es-PY'
export const CURRENCY = 'PYG'
export const EMPRESA_NOMBRE = 'SAMAP S.A.'
export const EMPRESA_ANIOS_TRAYECTORIA = 35
export const EMPRESA_SOCIOS_APROX = 8000
```

## When stuck

- Chequear `docs/architecture.md`.
- Si una convención no está acá, **preguntá antes de inventar**.
- Legacy **no es fuente de verdad arquitectónica**.
