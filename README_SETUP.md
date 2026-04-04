# Xbox Gamertag Generator – Local Setup Guide

This guide explains how to run the project locally on Windows using **Laragon**, **MySQL**, **Laravel**, and **Astro**.

## Project Structure

```bash
project-root/
  frontend/   # Astro frontend
  backend/    # Laravel backend
```

---

## What You Need

Please install these first:

- **Laragon** (recommended for PHP, MySQL, Composer)
- **Node.js LTS**
- **Git**
- **VS Code** (optional, but recommended)

### Recommended
- Laragon Full
- Node.js 20+
- Git latest version

---

# 1. Install Laragon

## Step 1
Download and install **Laragon Full**.

During installation, a simple path like this is recommended:

```bash
C:\laragon
```

## Step 2
Open Laragon.

## Step 3
Click:

```bash
Start All
```

This should start:
- Apache or Nginx
- MySQL

## Step 4
In Laragon, add PHP/Composer to PATH:

- Menu
- Tools
- Path
- **Add Laragon to Path**

After that:
- close terminal
- close VS Code
- reopen them

## Step 5
Check if PHP and Composer work:

```bash
php -v
composer -V
```

If both show a version, Laragon is set up correctly.

---

# 2. Install Node.js

Install Node.js LTS.

Check it with:

```bash
node -v
npm -v
```

---

# 3. Clone or Open the Project

If using Git:

```bash
git clone <repository-url>
cd <project-folder>
```

If you already have the project folder, just open it in VS Code.

---

# 4. Backend Setup (Laravel)

Go to the backend folder:

```bash
cd backend
```

## Step 1: Install PHP dependencies

```bash
composer install
```

## Step 2: Create environment file

If `.env` does not exist:

```bash
copy .env.example .env
```

or manually duplicate `.env.example` as `.env`.

## Step 3: Generate app key

```bash
php artisan key:generate
```

## Step 4: Configure database in `.env`

Open `backend/.env` and update this:

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=xbox_gamertag
DB_USERNAME=root
DB_PASSWORD=
```

> If your MySQL password is different, replace `DB_PASSWORD=` with your actual password.

---

# 5. Create the Database in phpMyAdmin

## Step 1
Open Laragon.

## Step 2
Go to:

- Menu
- MySQL
- phpMyAdmin

## Login Details

By default (Laragon):

Username: root  
Password: (leave blank)

If this does not work, use your MySQL credentials configured in Laragon.

## Step 3
Create a database named:

```sql
xbox_gamertag
```

---

# 6. Import the SQL File

## Recommended location for the SQL file in the project

Put the SQL file here:

```bash
backend/database/sql/xbox_gamertag.sql
```

This is a clean place because it keeps database-related files inside the Laravel backend.

## If the folder does not exist
Create it:

```bash
backend/database/sql/
```

## How to import in phpMyAdmin

1. Open **phpMyAdmin**
2. Select the database:

```sql
xbox_gamertag
```

3. Click **Import**
4. Choose the file:

```bash
backend/database/sql/xbox_gamertag.sql
```

5. Click **Go**



---

# 7. Run the Laravel Backend

From the `backend` folder:

```bash
php artisan serve
```

Laravel should run at:

```bash
http://127.0.0.1:8000
```

---

# 8. Frontend Setup (Astro)

Open a new terminal and go to the frontend folder:

```bash
cd frontend
```

## Step 1: Install dependencies

```bash
npm install
```

## Step 2: Run Astro

```bash
npm run dev
```

Astro should run at something like:

```bash
http://localhost:4321
```

---

# 9. How the Project Works Locally

- **Frontend (Astro)** runs on `localhost:4321`
- **Backend (Laravel)** runs on `127.0.0.1:8000`
- **Database (MySQL)** runs through Laragon/phpMyAdmin

The frontend sends requests to the Laravel API.
The Laravel backend reads generator data from MySQL and returns the results.

---

# 10. How to Test the Generator

## Step 1
Make sure all three are running:

- Laragon / MySQL
- Laravel backend
- Astro frontend

## Step 2
Open the frontend in the browser.

## Step 3
Use the generator form and click **Generate**.

## Expected behavior
- If backend is running, generation works
- If backend is stopped, generation fails

This confirms the frontend depends on the backend API.

---

# 11. Common Issues

## Problem: `php artisan serve` does not work
Make sure:
- Laragon is installed
- PHP is added to PATH
- you reopened terminal after adding PATH

## Problem: Database connection error
Check:
- MySQL is running in Laragon
- database name is correct in `.env`
- username/password are correct

## Problem: Frontend loads but generator fails
Check:
- Laravel backend is running
- API URL is correct
- database is imported properly

## Problem: `composer install` fails
Make sure Composer is available:

```bash
composer -V
```

---

# 12. Recommended Workflow

## Start services in this order

### 1. Start Laragon
Make sure MySQL is running.

### 2. Start Laravel backend

```bash
cd backend
php artisan serve
```

### 3. Start Astro frontend

```bash
cd frontend
npm run dev
```

---

# 13. Notes

- `node_modules`, `vendor`, and `.env` should not be committed to GitHub
- The SQL file can be kept in:

```bash
backend/database/sql/
```

- If the database is already managed by migrations/seeders, that method is preferred over manual SQL import

---

# 14. Summary

To run the project successfully:

1. Install Laragon
2. Start MySQL in Laragon
3. Create `xbox_gamertag` database
4. Import SQL file or run migrations/seeders
5. Configure Laravel `.env`
6. Run Laravel backend
7. Run Astro frontend

---

If any issue comes up during setup, check the terminal error first, then verify:
- MySQL is running
- `.env` is correct
- backend is running
- frontend is running
