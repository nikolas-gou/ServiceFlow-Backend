# ServiceFlow — Backend Documentation

Τεκμηρίωση του backend της εφαρμογής **ServiceFlow** (διαχείριση συνεργείου ηλεκτρικών μοτέρ). PHP API πάνω σε **Slim Framework 4**, με **PHP-DI** για dependency injection και **raw SQL μέσω PDO** (χωρίς ORM/query builder).

> Frontend: ξεχωριστό React project στο `ServiceFlow/` — βλ. `ServiceFlow/FRONTEND.md`.

---

## 1. Επισκόπηση & Stack

| Στοιχείο | Τεχνολογία |
|---|---|
| Framework | Slim 4 (`slim/slim ^4.12`, `slim/psr7 ^1.6`) |
| Dependency Injection | PHP-DI (`php-di/php-di ^7.0`) |
| Database access | PDO (raw SQL, χειροκίνητο prepared-statement binding — **όχι ORM**, όχι Eloquent/Doctrine) |
| DB engine | MySQL (inferred από `mysql:host=...;charset=utf8mb4` DSN) |
| Response format | JSON, ενιαίο envelope μέσω `ResponseHelper` (§10) |
| Auth | **Καμία** (βλ. §13) |
| Testing | `phpunit/phpunit` δηλωμένο ως dev-dependency, αλλά **δεν υπάρχει κανένα test** (βλ. §15) |
| PHP version | Δεν δηλώνεται explicit constraint στο `composer.json` |

Δεν υπάρχει `require: php` στο `composer.json`, ούτε `scripts` section — καμία `composer test`/`composer lint` δεν είναι πραγματικά καλωδιωμένη παρότι αναφέρεται στο README.

## 2. Δομή Φακέλων

```
ServiceFlow-Backend/
├── composer.json / composer.lock
├── config/
│   ├── config.php        # DB credentials + base_url (χρησιμοποιείται από index.php)
│   ├── settings.php       # Δεύτερο, ξεχωριστό config (app/db/cors/logging) — βλ. §16 (διπλό config bug)
│   └── routes.php         # Όλα τα routes
├── public/
│   ├── index.php          # Entry point, DI container, middleware, error handling
│   └── uploads/repairs/{id}/   # Ανεβασμένες φωτογραφίες επισκευών (gitignored)
├── src/
│   ├── Config/Database.php     # ⚠️ Αχρησιμοποίητο/legacy — βλ. §16
│   ├── Contracts/Repositories/ # ⚠️ Κενό — ποτέ δεν υλοποιήθηκαν interfaces
│   ├── Controllers/            # 8 αρχεία — βλ. §6
│   ├── Helpers/                # ResponseHelper.php, ServiceHelper.php
│   ├── Middleware/              # CorsMiddleware (ενεργό) + AuthMiddleware/ValidationMiddleware (dead code)
│   ├── Models/                  # 8 αρχεία — βλ. §9
│   ├── Queries/Repair/           # ⚠️ 2 αχρησιμοποίητα .sql αρχεία (δεν φορτώνονται από PHP)
│   ├── Repositories/             # 8 αρχεία — βλ. §7
│   ├── Services/                 # 6 αρχεία — βλ. §8
│   └── pages/                    # ⚠️ Κενό, vestigial
├── tests/Feature/, tests/Integration/   # ⚠️ Κενά directories
├── logs/app.log            # Flat-file log (gitignored)
└── .htaccess                # mod_rewrite catch-all → index.php (Apache)
```

## 3. Bootstrap & Dependency Injection (`public/index.php`)

```php
$containerBuilder = new ContainerBuilder();
$containerBuilder->addDefinitions([
    PDO::class => function() { /* χτίζει PDO από config/config.php */ },
    // Repositories — explicit wiring
    CustomerRepository::class => DI\create()->constructor(DI\get(PDO::class)),
    // ... (όλα τα υπόλοιπα Repositories, ένα-ένα)
    RepairRepository::class => DI\create()->constructor(
        DI\get(PDO::class),
        DI\get(MotorRepository::class),
        DI\get(CustomerRepository::class),
        DI\get(RepairFaultLinksRepository::class),
        DI\get(ImageRepository::class)
    ),
    LoggerService::class => DI\create(),
    Psr\Log\LoggerInterface::class => DI\get(LoggerService::class),
]);

$app = AppFactory::createFromContainer($container);
$app->add(new CorsMiddleware());
$app->addErrorMiddleware(true, true, true);  // ⚠️ hardcoded true — βλ. §16
require __DIR__ . '/../config/routes.php';
$app->run();
```

**Κανόνας DI**: Μόνο το `PDO` και όλα τα **Repositories** δηλώνονται explicit στο container. Όλα τα **Controllers** και **Services** επιλύονται μέσω **PHP-DI autowiring** (reflection πάνω στα typed constructor arguments) — δεν χρειάζεται να προσθέσεις τίποτα στο `index.php` όταν φτιάχνεις νέο Controller/Service, αρκεί οι εξαρτήσεις του να είναι ήδη γνωστές στο container (άμεσα ή έμμεσα).

**Middleware ενεργά**: μόνο `CorsMiddleware` (custom, όχι το `slim/cors` package) + το built-in error middleware του Slim. Τίποτα άλλο δεν είναι attached (ούτε global, ούτε per-route/group) — βλ. §13 για το γιατί αυτό έχει σημασία (καμία auth).

## 4. Routing (`config/routes.php`)

Όλα τα routes είναι κάτω από `$app->group('/api', ...)`. Υπάρχει επίσης ένα καθολικό `OPTIONS /{routes:.+}` route πριν το group, για CORS preflight requests.

| Resource | Routes |
|---|---|
| **Customers** | `GET /customers`, `POST /customers`, `GET /customers/{id}` — **όχι** PUT/PATCH/DELETE (ενώ το repository έχει `updateCustomer`/`deleteCustomer` — μη-καλωδιωμένα, βλ. §16) |
| **Motors** | `GET /motors`, `GET /motors/{id}` — μόνο read-only. Δημιουργία/ενημέρωση μοτέρ γίνεται έμμεσα μέσα από `RepairController::createRepair`/`updateRepair` |
| **Repairs** | `GET /repairs`, `POST /repairs`, `GET /repairs/trash`, `GET /repairs/{id}`, `PUT /repairs/{id}`, `PATCH /repairs/{id}/soft-delete`, `PATCH /repairs/{id}/restore` — το πιο ώριμο resource (pagination + trash) |
| **Common Faults** | `GET /common_faults` |
| **Images** | `POST /images/upload/{repairId}`, `GET /images/repair/{repairId}`, `GET /images/serve/{id}`, `DELETE /images/delete` |
| **Statistics** | `GET /statistics/dashboard`, `GET /statistics/customers`, `GET /statistics/connectionism` |
| **Suggested** | `GET /suggested/form-values` (autocomplete suggestions για τη φόρμα επισκευής) |
| **Connections** | `GET /connections`, `POST /connections`, `GET /connections/{id}`, `PUT /connections/{id}`, `DELETE /connections/{id}` |

`/repairs/trash` δηλώνεται **πριν** το `/repairs/{id}` — σημαντικό, αλλιώς το FastRoute matching θα προσπαθούσε να τρέξει `getRepairById('trash')`.

## 5. Αρχιτεκτονικό pattern: Controller → (Service) → Repository → PDO

Δύο μονοπάτια, ανάλογα με το resource:

- **Απλά CRUD resources** (Customers, Motors, Repairs, Connections, Images, CommonFaults): `Controller` καλεί απευθείας το αντίστοιχο `Repository` — καμία ενδιάμεση Service layer.
- **Aggregating/statistics resources** (Statistics, Suggested): `Controller` καλεί ένα `Service`, το οποίο συνδυάζει δεδομένα από **πολλαπλά** Repositories (π.χ. `DashboardService` καλεί `MotorService` + `RepairService` + `CustomerService`, καθένα από τα οποία με τη σειρά του καλεί το δικό του Repository).

Κάθε Controller method ακολουθεί το ίδιο σχήμα:

```php
public function xAction(Request $request, Response $response, $args = []): Response
{
    try {
        $result = $this->repository->doSomething($args);
        return ResponseHelper::success($response, $result, 'Μήνυμα επιτυχίας');
    } catch (\Exception $e) {
        return ResponseHelper::serverError($response, 'Μήνυμα σφάλματος: ' . $e->getMessage());
    }
}
```

Εξαίρεση: `StatisticsController` **δεν** έχει καθόλου try/catch (τα σφάλματα φτάνουν στο Slim error middleware) — αντ' αυτού βασίζεται στο ότι κάθε υποκείμενο Service field είναι ήδη τυλιγμένο σε `ServiceHelper::safeField` (§8), οπότε ένα partial failure δεν ρίχνει exception, απλά επιστρέφει `{error: ..., details: ...}` για εκείνο το field.

## 6. Controllers (`src/Controllers/`, 8 αρχεία)

| Controller | Injected | Methods |
|---|---|---|
| `CustomerController` | `CustomerRepository` | `getAll`, `getCustomerById`, `createCustomer` (χειροκίνητο validation μέσω `Customer::isValid()`) |
| `MotorController` | `MotorRepository` | `getAll`, `getMotorById` |
| `RepairController` | `RepairRepository` | `getAll` (branch σε paginated αν υπάρχουν `page`/`perPage` query params), `getRepairById`, `createRepair`, `updateRepair`, `softDelete`, `getTrash`, `restore` |
| `CommonFaultController` | `CommonFaultRepository` | `getAll` |
| `ImageController` | `ImageRepository` | `uploadImages` (parse raw `$_FILES`, όχι `$request->getUploadedFiles()`), `getImagesForRepair`, `serveImage` (binary stream), `deleteImages` |
| `StatisticsController` | `MotorService`, `DashboardService`, `CustomerService` | `getDashboardData`, `getCustomerStats`, `getConnectionism` — **χωρίς try/catch** |
| `SuggestedController` | `SuggestedService`, `LoggerInterface` | `getSuggestedData` — μοναδικό controller που κάνει structured logging (`$logger->error()`) |
| `ConnectionController` | `ConnectionRepository` | Πλήρες CRUD + `getPaginated` branch (ίδιο pattern με repairs) |

## 7. Repositories (`src/Repositories/`, 8 αρχεία)

| Repository | Πίνακας | Pagination | Soft-delete/Trash | Σημειώσεις |
|---|---|---|---|---|
| `RepairRepository` | `repairs` | ✅ (`getPaginated`) | ✅ (`getTrashPaginated`, `restore`) | Το πιο ώριμο — **template για νέα resources** |
| `ConnectionRepository` | `connections` | ✅ (`getPaginated`) | ❌ (hard `DELETE`) | Δεύτερο πιο ώριμο |
| `CustomerRepository` | `customers` | ❌ | ❌ (hard `DELETE`, ούτε καν καλωδιωμένο σε route) | Μεγάλη στατιστική επιφάνεια (`getMonthlyTrends`, `getTopCustomerByRevenue`, ...) |
| `MotorRepository` | `motors` (+ `motor_cross_section_links`) | ❌ | Έμμεσο — φιλτράρει `WHERE r.deleted_at IS NULL` μέσω JOIN στο `repairs`, δεν έχει δική του στήλη | Μεγάλη στατιστική επιφάνεια για dashboard |
| `ImageRepository` | `images` + filesystem (`public/uploads/repairs/{id}/`) | ❌ | ❌ (hard delete αρχείο+row) | MIME validation, 10MB max, `uniqid()` filenames |
| `MotorCrossSectionLinksRepository` | `motor_cross_section_links` | ❌ | ❌ | ⚠️ `getMotorCrossSectionLinksById` χρησιμοποιεί `fetch()` αντί για `fetchAll()` παρότι ένα μοτέρ μπορεί να έχει πολλαπλά links — πιθανό bug |
| `RepairFaultLinksRepository` | `repair_fault_links` (junction, χωρίς δικό του `id`) | ❌ | ❌ | Μόνο read (`getAll`, `getByRepairId`) — τα inserts/deletes γίνονται inline μέσα στο `RepairRepository` |
| `CommonFaultRepository` | `common_faults` | ❌ | ❌ | Το απλούστερο repository, μόνο `getAll()` |

## 8. Services (`src/Services/`, 6 αρχεία)

Οι Services υπάρχουν **αποκλειστικά** για να συνδυάζουν πολλαπλά repositories σε aggregate στατιστικά (dashboard, suggestions) — δεν υπάρχει Service layer για τα βασικά CRUD resources.

| Service | Depends on | Σκοπός |
|---|---|---|
| `DashboardService` | `MotorService`, `RepairService`, `CustomerService` | Composition root του `GET /statistics/dashboard` — χτίζει Ελληνικά μηνιαία chart labels + `customer`/`motor`/`repair`/`revenue` blocks |
| `CustomerService` | `CustomerRepository` | `getCustomerStats()`, `getSuggestedData()` |
| `MotorService` | `MotorRepository`, `MotorCrossSectionLinksRepository` | `getMotorStats()`, `getConnectionism()`, `getSuggestedData()` |
| `RepairService` | `RepairRepository` | `getRepairStats()`, `getRevenueStats()` |
| `SuggestedService` | `MotorService`, `CustomerService` | Composition root του `GET /suggested/form-values` — συγκεντρώνει errors μέσω private `collectErrors()` |
| `LoggerService` | — (implements `Psr\Log\LoggerInterface`) | Flat-file logger → `logs/app.log` |

### `ServiceHelper::safeField()` — το κεντρικό resilience pattern

```php
public static function safeField(callable $fn, string $errorMsg) {
    try {
        return $fn();
    } catch (\Throwable $e) {
        return ['error' => $errorMsg, 'details' => $e->getMessage()];
    }
}
```

Κάθε στατιστικό πεδίο στο dashboard τυλίγεται ξεχωριστά σε `safeField(fn() => $repo->someQuery(), 'Ελληνικό μήνυμα σφάλματος')`. Αν μία query αποτύχει, **μόνο εκείνο το field** επιστρέφει `{error, details}` αντί να ρίξει exception που θα γκρέμιζε ολόκληρο το dashboard response. Το frontend (`useErrorSnackbar` hook) ελέγχει αυτά τα `error` keys για να δείξει partial-failure toast.

⚠️ **Edge case**: αν μια επιτυχημένη query επιστρέψει legitimately ένα associative array που περιέχει το key `'error'`, το `formatSuggestedList()` θα το παρεξηγήσει ως αποτυχία. Χαμηλό ρίσκο με τα σημερινά call sites (πάντα scalar/flat list), αλλά κράτα το υπόψη αν προστεθεί νέο resource με πεδίο literally named `error`.

## 9. Models (`src/Models/`, 8 αρχεία)

Όλα τα Models ακολουθούν **αυστηρά** την ίδια σύμβαση:

```php
class X {
    public $snake_case_field;               // = DB column
    public function __construct(array $data = []) { $this->snake_case_field = $data['snake_case_field'] ?? default; }
    public static function fromFrontendFormat(array $frontendData): self { /* camelCase → snake_case */ }
    public function toFrontendFormat(): array { /* snake_case → camelCase, ό,τι στέλνεται στο React */ }
    public function toArray(): array { /* snake_case passthrough, εσωτερική/DB-facing μορφή */ }
}
```

**Σύμβαση ονομασίας**: τα foreign key/relation ids μετατρέπονται σε **πλήρη κεφαλαία `ID`** στο frontend format (`repairID`, `customerID`, `motorID`, `commonFaultID`) — **όχι** `repairId`/`customerId`. Αν προσθέσεις νέο Model, ακολούθησε το ίδιο, το frontend το περιμένει έτσι.

| Model | Πίνακας | Ειδικά |
|---|---|---|
| `Repair` | `repairs` | Έχει hydrated relations (`customer`, `motor`, `repairFaultLinks`, `images`) — defensive `is_object()`/`method_exists()` checks πριν καλέσει `toFrontendFormat()` σε αυτά |
| `Motor` | `motors` | Hydrated relation `motorCrossSectionLinks` (array) |
| `Customer` | `customers` | `isValid()` — non-empty trimmed `name`+`phone` |
| `Connection` | `connections` | — |
| `Image` | `images` | — |
| `MotorCrossSectionLinks` | `motor_cross_section_links` | — |
| `RepairFaultLinks` | `repair_fault_links` | Junction table, χωρίς δικό του `id` |
| `CommonFault` | `common_faults` | Απλούστερο (1:1 mapping, χωρίς μετονομασία) |

## 10. Response Format (`src/Helpers/ResponseHelper.php`)

Ενιαίο JSON envelope σε **όλα** τα endpoints:

```json
// Επιτυχία
{ "status": "success", "message": "...", "data": {...}, "pagination": {...} }  // pagination μόνο όταν δίνεται

// Σφάλμα
{ "status": "error", "message": "...", "errors": [...] }  // errors μόνο σε validation errors
```

| Method | Status | Χρήση |
|---|---|---|
| `success($response, $data, $message, $statusCode=200, $pagination=null)` | 200/201 | Το default happy-path |
| `error($response, $message, $statusCode=400, $errors=null)` | οποιοδήποτε | Βάση για όλα τα παρακάτω |
| `notFound($response, $message)` | 404 | |
| `validationError($response, array $errors)` | 422 | |
| `serverError($response, $message)` | 500 | Το default σε κάθε `catch` block |
| `badRequest($response, $message)` | 400 | |
| `binary($response, $data, $contentType, $statusCode=200, $headers=[])` | οποιοδήποτε | Μόνο για `ImageController::serveImage` — raw bytes, όχι JSON |

**Pagination shape** (RepairRepository/ConnectionRepository):
```json
{ "currentPage": 1, "perPage": 10, "totalItems": 162, "totalPages": 17, "from": 1, "to": 10, "hasNextPage": true, "hasPrevPage": false }
```

## 11. Error Handling & Validation

- **Global**: `$app->addErrorMiddleware(true, true, true)` — Slim's built-in error middleware, `displayErrorDetails`/`logErrors`/`logErrorDetails` **hardcoded `true`** ανεξαρτήτως environment (βλ. §16 — security concern).
- **Καμία validation library** (no `respect/validation`, no `symfony/validator`). Υπάρχει ένα χειροποίητο `src/Middleware/ValidationMiddleware.php` (rules: `required`, `email`, `min:N`) αλλά **δεν είναι ποτέ attached** σε κανένα route/group — dead code.
- Όλο το πραγματικό validation είναι **ad-hoc, μέσα σε κάθε controller**: `isset()`/`empty()` checks χειροκίνητα (π.χ. `RepairController::createRepair` ελέγχει `isset($data['repair']['customer'])`, `CustomerController::createCustomer` καλεί `Customer::isValid()`).

## 12. Database Schema (inferred — δεν υπάρχει authoritative schema file)

⚠️ **Δεν υπάρχει κανένα `.sql`/migration αρχείο για το πραγματικό schema.** Τα μόνα `.sql` αρχεία στο repo (`src/Queries/Repair/*.sql`) δεν φορτώνονται από κανένα PHP αρχείο — φαίνονται σαν παλιά reference queries. Το παρακάτω είναι **συναγόμενο από τον κώδικα**, όχι επίσημο:

```
customers            (id, type, name, email, phone, created_at)
motors                (id, customer_id FK, serial_number, description, manufacturer, kw, hp, rpm,
                        step/half_step/helper_step/helper_half_step, spiral/half_spiral/helper_spiral/helper_half_spiral,
                        connectionism, volt, amps, poles, coils_count/half_coils_count/helper_coils_count/helper_half_coils_count,
                        type_of_motor, type_of_volt, type_of_step, created_at)
                       -- ΧΩΡΙΣ δικό του deleted_at· κληρονομεί soft-delete μέσω JOIN στο repairs
motor_cross_section_links  (id, motor_id FK, cross_section, type)
repairs                (id, customer_id FK, motor_id FK, description, repair_status, cost,
                         created_at, is_arrived, estimated_is_complete, deleted_at)
repair_fault_links     (repair_id FK, common_fault_id FK)  -- pure junction, χωρίς δικό του id
common_faults          (id, name)
images                 (id, repair_id FK, path, type, size, created_at)
connections            (id, connection_type, type_of_volt, poles, rpm, step, half_step,
                         type_of_step, coils, caves, description, created_at)
```

**`deleted_at` υπάρχει μόνο στο `repairs`.** Κανένας άλλος πίνακας δεν έχει soft-delete στήλη — το trash feature είναι αποκλειστικό των επισκευών σήμερα.

## 13. CORS & Authentication

### CORS ⚠️
Δύο **ξεχωριστά, ασύγχρονα** config αρχεία ορίζουν CORS-σχετικά δεδομένα:
- `config/settings.php` → `cors.allowed_origins = ['http://localhost:3000', 'https://motorserviceflow.vercel.app', 'http://192.168.2.240:3000']` — **αυτό** διαβάζει το `CorsMiddleware.php` (exact-match στο `Origin` header, όχι wildcard).
- `config/config.php` → `base_url` (δεν καταναλώνεται πουθενά στο cors logic, dead/άσχετο πεδίο) — έχει μάλιστα typo (`https://https://serviceflow.42web.io/api`, διπλό protocol).

Κανένα από τα δύο **δεν** αναφέρει το `localhost:8000`/`192.168.2.240:8000` που χτυπάει το frontend (βλ. `ServiceFlow/src/config.js`) — αν frontend/backend ποτέ τρέξουν σε διαφορετικά origins με αυτά τα ports, το CORS θα αποτύχει σιωπηλά στο browser. **Χρήζει επιβεβαίωσης/διόρθωσης.**

### Authentication — Δεν υπάρχει, καθόλου
- `src/Middleware/AuthMiddleware.php` υπάρχει σαν **εντελώς άδειο αρχείο** (0 bytes, ούτε καν class declaration) — σκελετός που περιμένει να γραφτεί.
- Κανένα middleware δεν είναι attached για auth, πουθενά.
- Κανένα login/register/token endpoint.
- Καμία βιβλιοθήκη JWT/session στο `composer.json`.
- **Κάθε route κάτω από `/api/*` είναι πλήρως ανοιχτό, χωρίς κανέναν έλεγχο ταυτότητας.**

## 14. Deployment

- **Τοπικά**: `php -S localhost:8000 -t public` (PHP built-in server) — matches το hardcoded `localhost:8000` του frontend dev config.
- **Production**: Ενδείξεις (`config.php`) για InfinityFree shared hosting (`sql112.infinityfree.com`, `serviceflow.42web.io`) — δωρεάν hosting provider, όχι VPS/cloud deployment.
- Κανένα Dockerfile/docker-compose/CI pipeline στο repo.
- Ένα μόνο root-level `.htaccess` (mod_rewrite όλα → `index.php`) — τυπικό για Apache. Αν το document root δείχνει σε `public/` (συνηθισμένο για Slim), αυτό το root `.htaccess` μπορεί να είναι στο λάθος σημείο/περιττό — χρήζει επιβεβαίωσης ανάλογα με το πώς είναι στημένο το πραγματικό hosting.

## 15. Testing

`phpunit/phpunit ^9.0` είναι δηλωμένο dev-dependency, αλλά:
- `tests/Feature/` και `tests/Integration/` είναι **εντελώς κενά** directories.
- Δεν υπάρχει `phpunit.xml`/`phpunit.xml.dist`.
- Καμία `composer test` script.

Μηδενικό test coverage σε όλο το backend.

## 16. Γνωστά κενά / τεχνικό χρέος (checklist)

| # | Θέμα | Λεπτομέρεια |
|---|---|---|
| 1 | **Καμία authentication** | Βλ. §13 — μεγαλύτερο ανοιχτό θέμα, action item παρακάτω (§17) |
| 2 | **CORS allow-list mismatch** | `settings.php` δεν έχει τα ports που όντως χρησιμοποιεί το frontend· `config.php`'s `base_url` έχει διπλό-protocol typo και δεν καταναλώνεται πουθενά |
| 3 | **`displayErrorDetails=true` hardcoded** | Stack traces εκτίθενται σε HTTP responses ανεξαρτήτως env — security risk σε production |
| 4 | **Διπλό, ασυνεπές config** | `config.php` vs `settings.php` — δύο πηγές αλήθειας για DB/CORS, ασύγχρονες μεταξύ τους |
| 5 | **Dead code** | `ValidationMiddleware.php`, `AuthMiddleware.php` (άδειο), `Config/Database.php` (legacy), `src/Queries/*.sql` (δεν φορτώνονται), `src/Contracts/Repositories/` + `src/pages/` (κενά) |
| 6 | **Μηδενικό test coverage** | Βλ. §15 |
| 7 | **Ασύμμετρη ωριμότητα resources** | Μόνο Repairs (+ μερικώς Connections) έχουν pagination· Customers/Motors/Images δεν έχουν καν pagination — θα γίνει πρόβλημα performance όταν μεγαλώσει το dataset |
| 8 | **Μόνο Repairs έχει soft-delete/trash** | Customers/Motors/Connections/Images κάνουν hard delete — μη αναστρέψιμο data loss αν γίνει λάθος από τον χρήστη |
| 9 | **Ασύγχρονη λείπουσα route** | `CustomerRepository::updateCustomer`/`deleteCustomer` υπάρχουν αλλά δεν είναι καλωδιωμένα σε κανένα route |
| 10 | **`MotorCrossSectionLinksRepository::getMotorCrossSectionLinksById`** | Χρησιμοποιεί `fetch()` αντί `fetchAll()` — πιθανό bug, ένα μοτέρ έχει συνήθως πολλαπλά links |
| 11 | **`ServiceHelper::formatSuggestedList` edge case** | Θα παρεξηγήσει μια νόμιμη τιμή με key `'error'` ως αποτυχία (§8) |
| 12 | **Χωρίς PHP version constraint** | `composer.json` δεν δηλώνει ελάχιστη PHP version |

## 17. Roadmap — Σχεδιαζόμενες προσθήκες

Βάσει τρεχουσών προθέσεων για το προϊόν:

### 17.1 Authentication (σχεδόν σίγουρο ότι θα μπει)
Το `AuthMiddleware.php` υπάρχει ήδη σαν κενό σκελετό — δείχνει ότι είχε προβλεφθεί από την αρχή. Βήματα όταν μπει στην ατζέντα:
1. Πίνακας `users` (id, email, password_hash, role, created_at) + migration
2. Login/register endpoints (`POST /api/auth/login`, πιθανόν `POST /api/auth/register`) — JWT (π.χ. `firebase/php-jwt`) ή session-based
3. Ενεργοποίηση του `AuthMiddleware.php` — attach σε group/route level (Slim το υποστηρίζει ήδη μέσω `->add()` σε `RouteCollectorProxy`)
4. Αν χρειαστούν ρόλοι (π.χ. admin-only οριστική διαγραφή, κάτι που ήδη αναβλήθηκε στο frontend recycle-bin feature) — προσθήκη `role` column + role-check middleware/guard
5. **Frontend side**: `src/utils/api.js` χρειάζεται request interceptor για token injection· θα χρειαστεί login page + protected-route wrapper στο `routes/index.js` (βλ. `ServiceFlow/FRONTEND.md` §Roadmap)

### 17.2 Απόθεμα/Inventory (τι υπάρχει σε ένα κατάστημα)
Δεν υπάρχει σήμερα καμία έννοια αποθέματος/ανταλλακτικών. Θα χρειαστεί νέο domain module:
- Πίνακας `stock_items` (id, name, sku/κωδικός, ποσότητα, τιμή, κατώφλι επαναπαραγγελίας, ...)
- Πιθανή σύνδεση με `repairs` — ποια ανταλλακτικά καταναλώθηκαν σε ποια επισκευή (νέος junction πίνακας, ίδιο σχήμα με `repair_fault_links`)
- `StockController`/`StockRepository`/`StockItem` model — ίδιο established pattern (§5-9)
- Θα χρειαστεί να διευκρινιστεί: ένα κατάστημα ή πολλά (multi-location); αν πολλά, το inventory θα χρειαστεί `location_id`/`store_id` foreign key από την αρχή

### 17.3 Καταγραφή παραγγελιών (order tracking)
Χρειάζεται περαιτέρω αποσαφήνιση domain πριν σχεδιαστεί (παραγγελίες προς προμηθευτές για ανταλλακτικά; ή παραγγελίες πελατών;) — προτείνεται συζήτηση scope πριν την υλοποίηση, ώστε να μη χτιστεί λάθος μοντέλο.

### 17.4 Γενίκευση entity: πέρα από μοτέρ (π.χ. ιστορικό επισκευών αυτοκινήτων)
Αυτό είναι το πιο ριζικό πιθανό αλλαγή αρχιτεκτονικής. Σήμερα: `repairs.motor_id` είναι tightly-coupled 1:1 σε `motors` — το domain model υποθέτει ότι *κάθε* επισκευή αφορά μοτέρ. Για να υποστηριχτούν και άλλοι τύποι επισκευάσιμων αντικειμένων (π.χ. αυτοκίνητα), δύο βασικές προσεγγίσεις:

- **(Α) Polymorphic relation** (`repairable_type` + `repairable_id` στο `repairs`, αντί για σταθερό `motor_id`) — μία κοινή ροή/UI για όλα τα είδη επισκευών, αλλά χρειάζεται σημαντικό refactor του `RepairRepository`/`Repair` model (τα joins δεν μπορούν πια να υποθέτουν πάντα `motors`), και το frontend (`RepairRow`, `repairsColumns.js`, `Filter.js`) θα χρειαστεί conditional rendering ανά τύπο.
- **(Β) Ξεχωριστό domain module ανά τύπο** (π.χ. `vehicle_repairs` παράλληλα με `repairs`, με δικό του Controller/Repository/Model/σελίδα) — λιγότερο invasive βραχυπρόθεσμα, αλλά διπλασιάζει κώδικα/UI patterns μακροπρόθεσμα αν προστεθούν κι άλλοι τύποι.

Δεν υπάρχει ακόμα απόφαση ποια προσέγγιση — άξιζε να καταγραφεί εδώ ως ανοιχτό αρχιτεκτονικό ερώτημα πριν ξεκινήσει η υλοποίηση, ώστε να μη χρειαστεί δύο φορές δουλειά.

### 17.5 Θεμελιώδεις βελτιώσεις πριν προστεθούν νέα domains
Πριν μπουν Auth/Inventory/Orders (που όλα θα προσθέσουν επιφάνεια), αξίζει πρώτα:
1. Ενοποίηση `config.php`/`settings.php` σε ένα config (§16.4) — αλλιώς κάθε νέο module θα κληρονομήσει τη σύγχυση
2. Διόρθωση CORS allow-list (§16.2)
3. `displayErrorDetails` να εξαρτάται από environment, όχι hardcoded `true` (§16.3)
4. Migrations folder (π.χ. `phinx` ή απλά ένα versioned `.sql` directory) — σήμερα δεν υπάρχει κανένας τρόπος να αναπαραχθεί το schema από την αρχή
5. Τουλάχιστον βασικό test coverage στα Repositories πριν αρχίσουν να πολλαπλασιάζονται (§15)
