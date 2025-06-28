# ServiceFlow Backend API

## Επισκόπηση

Το ServiceFlow Backend είναι ένα RESTful API που χρησιμοποιεί το Slim Framework 4 και PHP-DI για dependency injection.

## Αρχιτεκτονική

```
src/
├── Config/          # Database configuration
├── Controllers/     # Request handlers
├── Helpers/         # Utility classes (ResponseHelper)
├── Middleware/      # Request/Response middleware
├── Models/          # Data models
├── Repositories/    # Data access layer
├── Services/        # Business logic services
└── Queries/         # SQL queries
```

## Εγκατάσταση

1. Εγκαταστήστε τις εξαρτήσεις:

```bash
composer install
```

2. Δημιουργήστε το αρχείο `.env` (βλ. `.env.example`)

3. Ρυθμίστε τη βάση δεδομένων στο `config/settings.php`

## Χρήση

### Εκκίνηση Development Server

```bash
php -S localhost:8000 -t public
```

### API Endpoints

#### Πελάτες

-   `GET /api/customers` - Λήψη όλων των πελατών
-   `POST /api/customers` - Δημιουργία νέου πελάτη
-   `GET /api/customers/{id}` - Λήψη πελάτη με ID

#### Κινητήρες

-   `GET /api/motors` - Λήψη όλων των κινητήρων
-   `POST /api/motors` - Δημιουργία νέου κινητήρα
-   `GET /api/motors/brands` - Λήψη όλων των μάρκων
-   `GET /api/motors/{id}` - Λήψη κινητήρα με ID

#### Επισκευές

-   `GET /api/repairs` - Λήψη όλων των επισκευών
-   `POST /api/repairs` - Δημιουργία νέας επισκευής
-   `GET /api/repairs/{id}` - Λήψη επισκευής με ID

#### Στατιστικά

-   `GET /api/statistics/overview` - Γενικά στατιστικά
-   `GET /api/statistics/dashboard` - Δεδομένα dashboard
-   `GET /api/statistics/customers` - Στατιστικά πελατών

## Best Practices

### 1. Dependency Injection

Όλες οι κλάσεις χρησιμοποιούν dependency injection μέσω του PHP-DI container.

### 2. Repository Pattern

Η πρόσβαση στη βάση δεδομένων γίνεται μέσω repositories.

### 3. Response Standardization

Όλες οι απαντήσεις ακολουθούν το ίδιο format μέσω του `ResponseHelper`.

### 4. Error Handling

Κεντρική διαχείριση σφαλμάτων με try-catch blocks.

### 5. Validation

Χρήση middleware για validation των δεδομένων.

### 6. Logging

Κεντρικό logging system για debugging και monitoring.

## Configuration

### Environment Variables

-   `DB_HOST` - Database host
-   `DB_NAME` - Database name
-   `DB_USER` - Database username
-   `DB_PASS` - Database password
-   `APP_DEBUG` - Debug mode (true/false)
-   `CORS_ALLOWED_ORIGINS` - Allowed CORS origins

### Database

Η σύνδεση στη βάση δεδομένων γίνεται μέσω PDO με τις εξής ρυθμίσεις:

-   UTF-8 encoding
-   Exception error mode
-   Prepared statements
-   Associative fetch mode

## Security

-   CORS protection
-   SQL injection prevention (prepared statements)
-   Input validation
-   Error message sanitization

## Logging

Τα logs αποθηκεύονται στο `logs/app.log` και περιλαμβάνουν:

-   HTTP requests
-   Exceptions
-   Application events

## Testing

Για testing χρησιμοποιήστε:

```bash
# Unit tests (αν υπάρχουν)
composer test

# API testing
curl -X GET http://localhost:8000/api/customers
```
