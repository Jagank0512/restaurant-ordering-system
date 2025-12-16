# Restaurant QR Menu Ordering System

A proper Web App for Restaurant QR Ordering.

## Features
- **Customer**: Mobile-first menu, Add to Cart, Checkout, Track Order.
- **Admin**: Dashboard with live orders, Status updates, Menu Management.
- **Tech Stack**: HTML, CSS, JS (Frontend), PHP (Backend), MySQL (Database).

## Setup Instructions

### 1. Database Setup
1. Open your MySQL Database (phpMyAdmin or CLI).
2. Create a database named `restaurant_qr_db`.
3. Import the `database.sql` file provided in the root directory.

### 2. Configuration
1. Open `db_connect.php`.
2. Update the credentials (`$username`, `$password`) if they differ from the defaults.

### 3. Running the Project
1. Move the project folder to your web server directory (e.g., `htdocs` for XAMPP).
2. Start Apache and MySQL.
3. Access the customer app at: `http://localhost/Resturant/index.php`
4. Access the admin panel at: `http://localhost/Resturant/admin/login.php`

### 4. Admin Credentials
- **Username**: `admin`
- **Password**: `admin123` (Ensure you hash this if you use the production code provided in `database.sql`)

## Testing (Mock Mode)
This project includes a **Mock Mode** for demonstration purposes without a backend.
- Opening `index.html` or `admin/login.html` directly (via `file://`) activates Mock Mode automatically.
- To use the real PHP backend, ensure you are running on a server (`http://`).
