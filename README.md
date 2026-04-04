# Vacation Register

A modern vacation management application built with the Laravel TALL-ish stack (Laravel, Inertia.js v2, Vue 3, and Tailwind CSS). This application helps Company to organize vacations, track team availability, and streamline the approval process.

## 🚀 Features

- **Vacation Requests:** Users can submit requests for time off, specifying dates, reasons, and a potential substitute.
- **Approval Workflow:** Managers can review, approve, or reject vacation requests with comments.
- **Team Calendar:** A centralized view of all approved and pending vacations to ensure team coverage.
- **User Management:** Administrator tools to manage users and their roles within the application.
- **Real-time UI:** Powered by Inertia.js v2 and Vue 3 for a seamless, SPA-like experience.

## 🛠️ Tech Stack

- **Backend:** Laravel 13 (PHP 8.4)
- **Frontend:** Vue 3 via Inertia.js v2
- **Styling:** Tailwind CSS v3
- **Testing:** Pest PHP v4
- **Database:** SQLite (default)

## 📦 Installation

1. **Clone the repository:**
   ```bash
   git clone [repository-url]
   cd vacationregister
   ```

2. **Install dependencies:**
   ```bash
   composer install
   npm install
   ```

3. **Set up the environment:**
   ```bash
   cp .env.example .env
   php artisan key:generate
   ```

4. **Prepare the database:**
   ```bash
   touch database/database.sqlite
   php artisan migrate --seed
   ```

5. **Build assets:**
   ```bash
   npm run build
   ```

6. **Start the application:**
   ```bash
   php artisan serve
   ```

## 🧪 Testing

The application uses [Pest PHP](https://pestphp.com/) for testing. You can run the test suite with:

```bash
php artisan test
```
