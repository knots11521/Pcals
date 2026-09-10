# PCALS — Planter's Cash Advance & Loan System

A PHP-based desktop application for managing cash advances and loans for planters. Built with a traditional LAMP stack (PHP, MySQL) and a Tailwind CSS frontend. Packaged as a standalone desktop app using [phpdesktop](https://github.com/coder-ltd/phpdesktop).

## Features

- **Planters Management** — Store and manage planter profiles and personal details.
- **Loans Management** — Create, track, and manage cash advance / loan records.
- **Repayments Management** — Record and track repayment schedules and history.
- **Financial Reports** — Generate financial and loan status reports.
- **Document Generation** — Export reports/documents using PHPWord (`PhpOffice/PhpWord`).
- **Dashboard** — Overview of system activity and key metrics.

## Requirements

- **PHP** >= 7.4
- **MySQL / MariaDB**
- **Composer**
- **Node.js & npm** (for asset building via Tailwind CSS)

## Installation

1. **Clone / copy the repository** into your web server directory.

2. **Install PHP dependencies** via Composer:
   ```bash
   composer install
   ```

3. **Install Node dependencies** and build CSS assets:
   ```bash
   npm install
   npm run build
   ```

4. **Configure the environment**:
   - Copy `.env.example` to `.env`.
   - Update `.env` with your database credentials:
     ```env
     APP_ENV=local
     DB_HOST=localhost
     DB_NAME=ca_loan_system
     DB_USER=root
     DB_PASS=your_password
     ```

5. **Set up the database**:
   - Import the SQL schema from the `sql/` directory into your MySQL database:
     ```bash
     mysql -u root -p ca_loan_system < sql/schema.sql
     ```

6. **Run the installer**:
   - Open `http://localhost/pcals/install.php` in your browser and follow the setup instructions.

## Usage

After installation, launch the application in your browser:
```
http://localhost/pcals/public/
```

- **Dashboard** — Overview of system activity (`public/dashboard.php`)
- **Planters** — Manage planter records (`public/planters.php`)
- **Loans** — Manage loan records (`public/loans.php`)
- **Repayments** — Manage repayments (`public/repayments.php`)
- **Reports** — View financial reports (`public/reports.php` / `public/financial_reports.php`)
- **Logout** — End your session (`public/logout.php`)

## Running with phpdesktop

This application is packaged as a standalone Windows desktop app using [phpdesktop](https://github.com/coder-ltd/phpdesktop), which runs a Chromium browser and an embedded PHP engine locally.

### Quick Start (Standalone Build)

Download the pre-built Windows package:

> **[Download pcals-windows.zip](#)** *(placeholder link — release package coming soon)*

After downloading:
1. Extract `pcals-windows.zip` to a folder of your choice.
2. Double-click `pcals.exe` to launch the application.
3. Follow the on-screen installer to set up the database connection.

### Building a Standalone Package

To create your own phpdesktop build from this source:

1. Install the [phpdesktop-chrome](https://github.com/coder-ltd/phpdesktop-chrome) package.
2. Place the contents of `www/pcals/` into the phpdesktop `www/` folder.
3. Configure `phpdesktop.json` with your PHP version and Chromium settings.
4. Ensure the bundled PHP has the required extensions: `pdo_mysql`, `mbstring`, `openssl`, `gd`, `zip`.
5. Package the folder into a distributable ZIP for Windows deployment.

## Development

- Build CSS assets in **watch mode** during development:
  ```bash
  npm run dev
  ```

## Project Structure

```
pcals/
├── composer.json          # PHP dependencies (PHP, PHPWord, PHP dotenv)
├── package.json           # Node dependencies & scripts (Tailwind CSS)
├── .env / .env.example    # Environment configuration
├── config/                # Configuration files
├── consent_system/        # Consent management module
├── includes/              # Shared PHP includes / helpers
├── install.php            # Web-based installer
├── src/                   # Frontend source assets (Tailwind input CSS)
├── public/                # Public web root (entry points)
│   ├── index.php
│   ├── dashboard.php
│   ├── planters.php
│   ├── loans.php
│   ├── repayments.php
│   ├── reports.php
│   └── financial_reports.php
├── sql/                   # Database schema & seed data
└── vendor/                # Composer packages
```

## Configuration

| Variable    | Description              | Default          |
|-------------|--------------------------|------------------|
| `APP_ENV`   | Application environment  | `local`          |
| `DB_HOST`   | Database host            | `localhost`      |
| `DB_NAME`   | Database name            | `ca_loan_system` |
| `DB_USER`   | Database username      | `root`           |
| `DB_PASS`   | Database password      | *(your password)*|

## License

This project is licensed under the MIT License. See `composer.json` for license details.
