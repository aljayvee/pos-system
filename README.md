# VeraPOS

A modern, robust Point of Sale (POS) system built with Laravel and Vue.js, designed for high-performance retail management.

## Key Features

-   **POS Interface**: Optimized for both touch and desktop, with barcode scanning and session management.
-   **Inventory Management**: Track stock, handling unit conversions, and low-stock alerts.
-   **Multi-Buy Pricing**: Flexible pricing strategies (Buy X Get Y, Tiered Discounts).
-   **User Roles**: Granular permissions for Admins, Managers, and Cashiers.
-   **Reporting**: Real-time sales analytics and detailed reports.
-   **Mobile Native Experience**: Responsive design with bottom sheets and touch-friendly controls.

## Development Setup

1.  **Clone the repository**
2.  **Install dependencies**:
    ```bash
    composer install
    npm install
    ```
3.  **Environment Setup**:
    ```bash
    cp .env.example .env
    php artisan key:generate
    ```
4.  **Database Migration**:
    ```bash
    php artisan migrate --seed
    ```
5.  **Run Development Server**:
    ```bash
    php artisan serve
    npm run dev
    ```

## Testing

This project utilizes PHPUnit for automated testing.

### Running Tests

To run the full test suite, execute:

```bash
php artisan test
```

Or directly via PHPUnit:

```bash
./vendor/bin/phpunit
```

### Test Suites

#### Unit Tests (`tests/Unit`)
-   **`CashRegisterServiceTest`**: Verifies the core logic of the Cashier module.
    -   `test_open_session_creates_record`: Ensures cash register sessions are correctly initialized with opening amounts.
    -   `test_calculate_expected_cash`: Validates the financial calculations, ensuring that opening cash + cash sales match the expected total (ignoring non-cash methods).
    -   *Note*: These tests use specific database transactions (`DB::beginTransaction()` / `DB::rollBack()`) to ensure no test data persists in your local database.

#### Feature Tests (`tests/Feature`)
-   Includes standard HTTP tests to verify route accessibility and controller responses.

## Security

If you discover any security related issues, please email the administrator instead of using the issue tracker.

## License

Private / Proprietary.
