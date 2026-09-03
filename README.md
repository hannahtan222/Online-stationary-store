**Best Stationary -- Online Stationary Store**

This is a group assignment project for Web Application Development at Universiti Tunku Abdul Rahman (UTAR). This project is a web-based e-commerce application that allows customers to browse, search, and purchase stationery products online, built using PHP, HTML, CSS, JavaScript, and MySQL.

**User Features**

User registration and login (with secure password hashing using password_hash() and password_verify()).
Product browsing with category filtering and keyword search.
Product details page with an "Add to Cart" function.
Shopping cart management (add, update, remove items).
Checkout with shipping address.
Order history.
Profile management.
Contact form for customer enquiries.

**Admin Features**

Admin dashboard with an overview of products, categories, orders, messages, and users.
Product management (Create, Read, Update, Delete).
Category management.
User management.
Order management.
View customer contact messages.

**Tech Stack**
-Frontend:	HTML, CSS, JavaScript

-Backend: PHP

-Database: MySQL

-Local Server:	XAMPP (Apache + MySQL)

**Getting Started**

1. Requirements
XAMPP (provides Apache and MySQL), or any server that supports PHP and MySQL.

A modern web browser (Google Chrome, Microsoft Edge, Firefox, or Safari).

2. Clone this repository

git clone https://github.com/hannahtan222/Online-stationary-store.git
Extract/place the project folder into the XAMPP htdocs directory, for example:
C:\xampp\htdocs\Online-stationary-store

3. Start Apache and MySQL

Open the XAMPP Control Panel and start both Apache and MySQL.

4. Set up the database

Open http://localhost/phpmyadmin.
Create a new database named stationery_store.
Select the database and use the Import function to import the project's SQL file (database_stationery_store.sql).
The script automatically creates the required tables (categories, products, users, cart, orders, order_items, contact_messages) and pre-populates sample category and product data.

5. Configure the database connection

Update config/db_connection.php with your local MySQL settings:
php
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "stationery_store";

6. Configure the base URL

Update BASE_URL in includes/config.php to match your project folder, for example:
php
define('BASE_URL', 'http://localhost/Online-stationary-store/');

7. Run the application

Open a browser and go to the configured URL, e.g.: http://localhost/Online-stationary-store/

**Admin Access**
Default admin login credentials:
Username: admin
Password: admin123
Admin login page: admin/admin_login.php

**Verification Checklist**
After installation, confirm the following work correctly:
-Website loads without errors and navigation menu displays correctly
-Category pages show products, product details pages display properly
-User registration and login work
-Profile page shows user information
-Shopping cart accepts items and checkout completes successfully
-Contact form submits messages
-Admin panel is accessible with the default credentials above

**Project Structure**
Online-Stationary-Store/
│
├── admin/
│   ├── add_product.php
│   ├── admin_login.php
│   ├── dashboard.php
│   ├── edit_product.php
│   ├── logout.php
│   ├── manage_categories.php
│   ├── manage_messages.php
│   ├── manage_orders.php
│   ├── manage_product.php
│   └── manage_users.php
│
├── assets/
│   ├── css/
│   └── js/
│
├── config/
│   ├── auth.php
│   └── db_connection.php
│
├── front-end/
│   ├── cart.php
│   ├── contact.php
│   └── index.php
│
├── includes/
│   ├── config.php
│   ├── footer.php
│   ├── header.php
│   └── navigation.php
│
├── product_module/
│   ├── product_details.php
│   └── products.php
│
├── user/
│   ├── add_to_cart.php
│   ├── cart.php
│   ├── categories.php
│   ├── checkout.php
│   ├── db_connect.php
│   ├── login.php
│   ├── logout.php
│   ├── order_history.php
│   ├── profile.php
│   ├── register.php
│   ├── remove_cart.php
│   ├── update_cart.php
│   └── update_profile.php
│
├── database_stationery_store.sql
├── index.php
└── README.md
