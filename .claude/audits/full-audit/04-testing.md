# Auditoría de Cobertura de Tests — LMS Training

**Fecha:** 2026-06-13  
**Proyecto:** Monolito LMS + e-commerce (Laravel 12)  
**Escala:** 1511 PHP, 213 controllers, 108 modelos, ~9 tests reales

---

## 1. Estado Actual de Tests

### Inventario completo

| Archivo | Tipo | Traits | Factories | Calidad |
|---------|------|--------|-----------|---------|
| `Unit/WompiServiceTest.php` | Unit | ninguno | no | Buena — 2 casos, firma SHA-256 + URL |
| `Unit/CartCouponDiscountTest.php` | Unit | ninguno | no | Buena — 4 casos de función pura `cartCouponDiscount()` |
| `Unit/IncomingMail/ConfidenceScorerTest.php` | Unit | ninguno | no | Desconocido — no leído (parser lógica pura) |
| `Unit/IncomingMail/RedNacionalParserTest.php` | Unit | ninguno | no | Desconocido — parser lógica pura |
| `Feature/Checkout/CheckoutTest.php` | Feature | RefreshDatabase | CourseFactory, UserFactory | Muy buena — 8 casos; happy, empty cart, webhook APPROVED/DECLINED/bad-sig, coupon invalid |
| `Feature/Invoices/InvoiceTest.php` | Feature | RefreshDatabase | UserFactory | Buena — cubre CRUD de facturas para manager |
| `Feature/Inscriptions/InscriptionTest.php` | Feature | RefreshDatabase | UserFactory, CourseFactory | Buena — cubre auth + CRUD inscriptions |

### Problemas de calidad detectados

- `InscriptionTest` usa `Inscription::create([...])` manual en lugar de factory — viola `rules/tests.md`
- `CheckoutTest` usa `Order::create([...])` manual para webhook tests — no hay `OrderFactory`
- `InvoiceTest` usa `firstOrCreate()` para lookup tables en lugar de factories/seeders dedicados
- phpunit.xml comenta el bloque `DB_CONNECTION=sqlite` — los tests corren contra **MySQL real** (no in-memory); esto es arriesgado sin `.env.testing` o con datos de producción
- `.env.testing` SÍ existe — pero sin SQLite configurado en phpunit.xml la protección depende solo del env file
- `phpunit.xml` tiene `<source><include><directory>app</directory>` — excluye completamente el código en `database/`, `routes/` y funciones helper; cobertura de código estará subinformada
- Ningún test cubre módulo `modules/` (no existe directorio `modules/`)

### Factories existentes (5 de 108 modelos)

| Factory | Modelos cubiertos |
|---------|------------------|
| `UserFactory` | User |
| `Course/CourseFactory` | Course |
| `Course/CourseAliasFactory` | CourseAlias |
| `Enterprise/EnterpriseAliasFactory` | EnterpriseAlias |
| `Mail/IncomingMailFactory` | IncomingMail |

**Brechas de factory:** Order, Inscription, Quiz, Exam, Coupon, Invoice, Distributor, Bundle, Enterprise y ~98 modelos más no tienen factory.

---

## 2. Matriz de Riesgo — Flujos Críticos vs Cobertura

| Flujo de negocio | Riesgo | Cobertura actual | Deuda |
|------------------|--------|-----------------|-------|
| **Pago Wompi — webhook APPROVED** | CRITICO (dinero) | Parcial — webhook OK, pero no tests de doble procesamiento ni de idempotencia | Alta |
| **Pago Wompi — generación de orden** | CRITICO | Básica — un happy path + cart vacío | Alta |
| **Quiz customer — submit respuestas / finish** | ALTO (último commit corrigió bugs aquí) | 0% | URGENTE |
| **Exam customer — submit respuestas / finish** | ALTO (último commit corrigió bugs aquí) | 0% | URGENTE |
| **Navegación de cursos customer** | ALTO (último commit corrigió bugs aquí) | 0% | URGENTE |
| **Inscriptions — enroll / expire / culminated** | ALTO | Básica (auth + CRUD manager) — falta lógica de expiración | Media |
| **Cupones — aplicar / validar / consumir** | ALTO (dinero) | Unit solo para función pura; 0% para endpoint HTTP y CouponUsage DB | Alta |
| **Facturas — crear desde orden / condición** | MEDIO-ALTO | Básica para manager CRUD | Media |
| **Checkout completo — multi-item + bundle** | ALTO | Un happy path single-item; 0% para bundles | Alta |
| **Auth — login / registro / rol customer vs manager** | MEDIO | 0% — ningún test de autenticación | Alta |
| **Certificados — generar al completar curso** | MEDIO | 0% | Media |
| **IncomingMail — parseo + creación de orden/matrícula** | MEDIO | Parsers unit; 0% integración IMAP→inscripción | Media |
| **Distribuidores / Empresas — CRUD + inscripciones masivas** | BAJO-MEDIO | 0% | Baja |
| **Admin CRUD courses/chapters/lessons/quizs** | BAJO | 0% | Baja |

---

## 3. Infraestructura de Tests

### phpunit.xml — estado
```xml
<!-- DB_CONNECTION y DB_DATABASE están COMENTADAS -->
<!-- Tests corren contra MySQL definido en .env.testing -->
```
- `.env.testing` existe: protección mínima presente
- Sin SQLite in-memory: cada test con RefreshDatabase hace `migrate:fresh` en MySQL — lento y frágil
- Suite solo define `tests/Unit` y `tests/Feature` — no incluye `modules/` (no existen) ni rutas de módulos hipotéticos futuros
- Cobertura declarada en `<source>` solo apunta a `app/` — helpers en `bootstrap/`, `routes/` y `database/` quedan fuera

### Factories — cobertura
- **5 factories para 108 modelos = 4.6% de cobertura**
- Modelos de riesgo crítico sin factory: `Quiz`, `QuizQuestion`, `QuizAnswer`, `Exam`, `ExamQuestion`, `ExamAnswer`, `Coupon`, `CouponUsage`, `Order`, `OrderItem`, `Inscription`, `Invoice`, `Bundle`, `Certificate`

### Convenciones vs `rules/tests.md`
- RefreshDatabase: usado correctamente en Feature tests
- `actingAs()`: usado en Inscriptions + Invoices
- `Mail::fake()`: usado en Checkout — correcto
- `Queue::fake()` / `Notification::fake()`: no usados en ningún test (procesos queued sin coverage)
- Factories con estados: no existen estados (`inactive()`, `expired()`, etc.)
- Manual DB inserts (`::create([...])`): usados en lugar de factories en varios tests — viola regla

---

## 4. Plan de Tests — Priorizado por Riesgo

### FASE 1 — Urgente: Flujos Reparados en el Último Commit (quiz/exam customer)

Estos bugs fueron corregidos pero ningún test los cubre. Sin tests, pueden regresionar.

**Suite: `tests/Feature/Customer/QuizTest.php`**
- `test_customer_can_start_quiz_for_enrolled_course`
- `test_customer_cannot_start_quiz_without_inscription`
- `test_quiz_submit_saves_answers_and_returns_result`
- `test_quiz_finish_shows_result_when_quiz_again_is_zero` (bug corregido: resultado ocultado)
- `test_quiz_can_retry_when_quiz_again_is_one`

**Suite: `tests/Feature/Customer/ExamTest.php`**
- `test_customer_can_start_exam_for_enrolled_course`
- `test_exam_submit_saves_answers_correctly`
- `test_exam_finish_hides_retry_button_when_quiz_again_is_zero` (bug corregido)
- `test_exam_first_question_prev_button_is_hidden` (bug corregido)

**Factories requeridas antes:** `QuizFactory`, `QuizQuestionFactory`, `QuizAnswerFactory`, `ExamFactory`, `ExamQuestionFactory`, `ExamAnswerFactory`, `InscriptionFactory`

### FASE 2 — Crítico: Pagos y Dinero

**Suite: `tests/Feature/Checkout/WompiWebhookIdempotencyTest.php`**
- `test_webhook_approved_twice_does_not_duplicate_inscription`
- `test_webhook_creates_inscription_for_each_order_item`
- `test_webhook_with_bundle_creates_inscriptions_for_all_courses`
- `test_webhook_amount_mismatch_is_rejected`

**Suite: `tests/Feature/Checkout/CouponIntegrationTest.php`**
- `test_valid_coupon_applies_discount_and_creates_usage`
- `test_coupon_cannot_be_used_more_than_max_uses`
- `test_expired_coupon_is_rejected`
- `test_coupon_restricted_to_course_does_not_apply_to_other_course`

**Factories requeridas:** `OrderFactory`, `OrderItemFactory`, `CouponFactory`

### FASE 3 — Alto: Auth y Roles

**Suite: `tests/Feature/Auth/AuthenticationTest.php`**
- `test_customer_can_login_with_valid_credentials`
- `test_inactive_user_cannot_login` (available=0)
- `test_unvalidated_user_cannot_login` (validation=0)
- `test_manager_is_redirected_to_manager_dashboard`
- `test_customer_is_redirected_to_customer_dashboard`
- `test_manager_cannot_access_customer_routes`
- `test_customer_cannot_access_manager_routes`

### FASE 4 — Importante: Inscripciones y Progreso

**Suite: `tests/Feature/Inscription/InscriptionLifecycleTest.php`**
- `test_order_approved_creates_inscription`
- `test_completing_all_lessons_marks_culminated`
- `test_expired_inscription_blocks_course_access`
- `test_lesson_progress_is_tracked`

**Factory requerida:** `InscriptionFactory` con estados `active()`, `expired()`, `culminated()`

### FASE 5 — Mantenimiento: CRUD Admin

**Suite: `tests/Feature/Manager/CourseManagementTest.php`**
- CRUD básico de courses, chapters, lessons
- Tests de autorización (customer no puede acceder a rutas manager)

---

## 5. Deuda Técnica de Infraestructura (hacer antes de Fase 1)

1. **Configurar SQLite in-memory en phpunit.xml** — descomentar `DB_CONNECTION=sqlite` + `DB_DATABASE=:memory:` — acelera tests 10x y elimina riesgo sobre MySQL
2. **Crear 14 factories críticas** antes de escribir los tests de Fases 1-2
3. **Agregar estados a factories existentes** — `CourseFactory::withPayment()`, `UserFactory::asManager()`, `UserFactory::inactive()`
4. **Separar seeder de lookups** en `tests/TestCase.php::setUp()` o trait `SeedsLookups` — evitar duplicación entre `CheckoutTest`, `InvoiceTest` y futuros tests

---

## Resumen Ejecutivo

**Cobertura efectiva actual: ~3-4% de la superficie de código.**  
Los 9 tests existentes cubren bien el flujo Wompi webhook + generación de orden (código estable), pero dejan sin cobertura los dos flujos modificados en el último commit (quiz/exam customer), toda la capa de autenticación y roles, y el 95%+ de los 213 controllers. La infraestructura tiene .env.testing pero usa MySQL en lugar de SQLite in-memory, lo que hace los tests lentos y con riesgo potencial. La mayor deuda es la ausencia de factories para los modelos de negocio core.
