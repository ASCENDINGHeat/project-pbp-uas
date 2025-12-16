# Project PBP UAS - PC Component E-Commerce

This is a full-stack e-commerce web application developed for the Platform-Based Programming (PBP) Final Exam (UAS). The application focuses on selling computer components, ready-to-use PCs, and allows users to simulate PC builds.

The project is structured into two main parts:

1. **Backend:** A RESTful API built with **Laravel**.
2. **Frontend:** A modern user interface built with **SvelteKit**.

## Features

* **User Authentication:** Registration and Login for Users and Vendors.
* **Product Management:** Browse categories (CPU, GPU, RAM, Motherboard, etc.).
* **PC Simulation:** Tools to simulate building a PC.
* **Shopping Cart & Wishlist:** Manage items before purchase.
* **Orders:** Place orders and track status.
* **Vendor System:** Vendors can manage their own products and orders.
* **Payment Integration:** Setup for Midtrans gateway.

## Tech Stack

* **Backend:** Laravel (PHP), Postgresql, Sanctum (Auth).
* **Frontend:** SvelteKit (Node.js), Vite, Tailwindcss.
* **Database:** Postgresql.

---

## Prerequisites

Before running the project, ensure you have the following installed locally:

* [PHP](https://www.php.net/downloads) (Version 8.4 minimum)
* [Composer](https://getcomposer.org/)
* [Node.js](https://nodejs.org/) (LTS Version) & NPM
* [Postgresql](https://www.postgresql.org/)

---

## Installation & Local Setup

### 1. Database Setup

1. Open your database management tool (phpMyAdmin, DBeaver, TablePlus, etc.).
2. Create a new, empty database. For this guide, we will name it `pbp_uas`.

### 2. Backend Setup (Laravel)

Navigate to the backend directory and set up the API.

```bash
# Enter the backend directory
cd backend-API

# Install PHP dependencies
composer install

# Copy the example environment file
cp .env.example .env
````

**Configuration:**
Open the `.env` file in a text editor and update the database settings to match your local setup:

```ini
DB_CONNECTION=pgsql
DB_HOST=127.0.0.1
DB_PORT=5432
DB_DATABASE=pbp_uas  # The name of the database you created
DB_USERNAME=root     # Your database username
DB_PASSWORD=         # Your database password
```

**Finish Backend Setup:**

```bash
# Generate the application key
php artisan key:generate

# Run database migrations and seeders (to populate initial data)
php artisan migrate:fresh

# Link the storage directory (for images)
php artisan storage:link

# Start the backend server
php artisan serve
```

The backend API will now be running at `http://127.0.0.1:8000`.

### 3\. Frontend Setup (SvelteKit)

Open a new terminal window, navigate to the frontend directory, and start the client application.

```bash
# Enter the frontend directory
cd frontend-user

# Install Node dependencies
npm install

# Start the development server
npm run dev
```
Create .env file for connecting to the api

```.env
PUBLIC_API_URL=YOUR_API_URL/api
PUBLIC_STORAGE_URL=YOUR_API_URL/storage
```

The frontend will usually be accessible at `http://localhost:5173` (check your terminal output for the exact URL).

-----

## Configuration Notes

### API Endpoint

Ensure the frontend knows where the backend is running. If you encounter connection issues, check inside `frontend-user` (specifically `.env` or `src/lib/` config files) to ensure the API base URL is set to `http://127.0.0.1:8000`.

### Midtrans (Payment Gateway)

This project contains configuration for Midtrans. To test payments fully, you may need to add your Midtrans Server Key and Client Key to the `backend-API/.env` file:

```ini
MIDTRANS_SERVER_KEY=your-server-key
MIDTRANS_CLIENT_KEY=your-client-key
MIDTRANS_IS_PRODUCTION=false
```

## Troubleshooting

  * **Images not loading:** Ensure you ran `php artisan storage:link` in the backend folder.
  * **CORS Errors:** If the frontend cannot talk to the backend, check `backend-API/config/cors.php` or ensure both are running on the expected ports.
  * **Database Connection Refused:** Verify your MySQL server is running and the credentials in `.env` are correct.

<!-- end list -->
