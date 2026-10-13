# test-manager-library-3145556-api

> **Prueba técnica · Ficha ADSO 3145556**
> Entrega: **martes 13 de octubre de 2026, a las 5:00 p. m.** (17:00, hora Colombia).

Backend de *Manager Library* en **PHP 8.4 + Laravel 13**, con arquitectura hexagonal. Se construye a partir del spec de
[`test-manager-library-3145556-docs`](https://github.com/BrayanCABRERA123/test-manager-library-3145556-docs), versión `spec-laravel/`.

## Estructura (spec-laravel/architecture.md)

```text
app/
├── Domain/                     # User (normalización de nombre, rol válido)
├── Application/
│   ├── Ports/Outbound/         # UserRepository, CategoryRepository, PasswordHasher, TokenIssuer, IdGenerator
│   ├── Services/               # LoginService, BootstrapAdminService, ListCategoriesService
│   └── Errors/                 # ApplicationError (códigos del contrato)
├── Adapters/
│   ├── Inbound/Http/           # controladores y cuerpo de error único
│   └── Outbound/               # Persistence/ (PostgreSQL), Security/ (Argon2id, JWT, UUID v4)
└── Providers/PortBindingsServiceProvider.php   # único punto de composición (artículo III)
database/migrations/            # todo el DDL (ADR-001)
```

## Cómo se levanta

Con el repositorio `infra` clonado al lado de este:

```bash
cd ../test-manager-library-3145556-infra
cp .env.example .env      # y cambiar los valores
docker compose up -d --build
```

Al arrancar, el contenedor ejecuta las migraciones y crea el admin inicial desde `ADMIN_USERNAME` / `ADMIN_PASSWORD` (D-10).
No necesita PHP ni Composer instalados en el equipo.

## Pruebas

Pruebas unitarias de dominio y de aplicación con *fakes* de los puertos (sin base de datos), con PHPUnit (D-12):

```bash
cd ../test-manager-library-3145556-infra
docker compose exec api sh -c "composer install --no-interaction --no-scripts && php vendor/bin/phpunit --testsuite Unit --testdox"
```

Resultado: **OK (9 tests, 25 assertions)**: normalización del usuario, rol inválido rechazado, login válido con token de 8 h,
el mismo error para usuario o clave inválidos (CA-01.2), admin inicial creado una sola vez, y 401/403 del token (CA-01.3).

## Endpoints implementados

| Método | Ruta | Rol | Estado |
|---|---|---|---|
| GET | `/health` y `/api/v1/health` | público | ✅ `{"status":"ok"}` |
| POST | `/api/v1/auth/login` | público | ✅ JWT HS256 de 8 h con `sub` y `role` (ADR-005) |
| GET | `/api/v1/categories` | autenticado | ✅ Las cinco categorías ordenadas por nombre (§3.3) |

## Tareas y evidencia (artículo XII)

| Tarea | Estado | Evidencia |
|---|---|---|
| T-01 Esqueleto y `GET /health` | ✅ | `curl http://localhost:8000/health` → `{"status":"ok"}`; arranca con un comando |
| T-02 Migración inicial | ✅ | Seis tablas en el esquema `library`, FK, índices únicos y parciales, `ck_book_available_range`, cinco categorías con sus UUID fijos y **0 columnas con `DEFAULT`** (consultas de `data-model.md` §10) |
| T-06 `User` (normalización, rol válido) | ✅ parcial | `UserTest`: `" Ana "` queda `ana`; un rol fuera de (`admin`,`librarian`) se rechaza |
| T-07 Login contra fakes | ✅ parcial | `LoginServiceTest`: token de 8 h; usuario o clave inválidos → el mismo 401 `INVALID_CREDENTIALS` (CA-01.2). `CreateLibrarianService` (HU-02) pendiente |
| T-14 Hash y token | ✅ | La clave se guarda con Argon2id (`$argon2id$…`); ninguna respuesta incluye `password` ni `password_hash` |
| T-17 Admin inicial desde el entorno | ✅ | La base arranca vacía, se migra y se puede iniciar sesión como admin |
| CA-01.3 Token obligatorio | ✅ | Sin token o con token inválido → 401 `UNAUTHENTICATED`; rol insuficiente → 403 `FORBIDDEN_ROLE`; `/health` y el login siguen públicos (`AuthenticateTokenTest`) |
| RNF-4 Cuerpo de error único | ✅ | 401, 404, 422 y 500 responden `{ code, message, details }`, sin trazas |
| T-03 a T-05, T-08 a T-13, T-15, T-16, T-18, T-19 | ⏳ pendiente | No iniciadas por tiempo: libros, socios, préstamos, devoluciones, vencidos, reporte, portadas, pruebas de integración, contrato, concurrencia y arquitectura |

## Decisiones registradas en docs

- Códigos `UNAUTHENTICATED` (401 sin token) e `INTERNAL_ERROR` (500): incongruencia anotada en `spec-laravel/api-contract.md` §2.
- Claves primarias `pk_{tabla}`: `spec-laravel/data-model.md` §4.
- PHP 8.4 y Laravel 13: `spec-laravel/plan.md` D-01 y D-02 (Laravel 11 ya no se instala por avisos de seguridad).
- La tabla `migrations` de Laravel vive en `public`, fuera del esquema `library`, para no agregar una séptima tabla al modelo.

## Convenciones

- Código, columnas, rutas, ramas y mensajes de commit/PR: **inglés** (Conventional Commits).
- Documentación y mensajes al usuario: **español** (constitución, artículo XI).
- Flujo de ramas: `feature/*` → `develop` → `main`, siempre mediante Pull Request.
