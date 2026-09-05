# Best Stationary -- Online Stationary Store

This is a group assignment project for Web Application Development at Universiti Tunku Abdul Rahman (UTAR). This project is a web-based e-commerce application that allows customers to browse, search, and purchase stationery products online, built using PHP, HTML, CSS, JavaScript, and MySQL.

## User Features

User registration and login (with secure password hashing using password_hash() and password_verify()).
Product browsing with category filtering and keyword search.
Product details page with an "Add to Cart" function.
Shopping cart management (add, update, remove items).
Checkout with shipping address.
Order history.
Profile management.
Contact form for customer enquiries.

## Admin Features

Admin dashboard with an overview of products, categories, orders, messages, and users.
Product management (Create, Read, Update, Delete).
Category management.
User management.
Order management.
View customer contact messages.

## Tech Stack
-Frontend:	HTML, CSS, JavaScript

-Backend: PHP

-Database: MySQL

-Local Server:	Wampserver

## Getting Started

### 1. Requirements
WampServer (which provides Apache, MySQL, and PHP).

A modern web browser (Google Chrome, Microsoft Edge, Firefox, or Safari).

### 2. Clone this repository

git clone https://github.com/hannahtan222/Online-stationary-store.git
Extract/place the project folder into the WAMPSERVER htdocs directory.

### 3. Start WampServer

Launch WampServer and ensure the icon in the system tray turns green (indicating Apache and MySQL are running).

### 4. Set up the database

Open http://localhost/phpmyadmin.
Create a new database named stationery_store.
Select the database and use the Import function to import the project's SQL file (database_stationery_store.sql).
The script automatically creates the required tables (categories, products, users, cart, orders, order_items, contact_messages) and pre-populates sample category and product data.

### 5. Configure the database connection

Update config/db_connection.php with your local MySQL settings:
php
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "stationery_store";

### 6. Configure the base URL

Update BASE_URL in includes/config.php to match your project folder, for example:
php
define('BASE_URL', 'http://localhost/Online-stationary-store/');

### 7. Run the application

Open a browser and go to the configured URL, e.g.: http://localhost/Online-stationary-store/

---

## Admin Access
1. Default admin login credentials:
2. Username: admin
3. Password: admin123
4. Admin login page: admin/admin_login.php

---

## Verification Checklist
After installation, confirm the following work correctly:
1. Website loads without errors and navigation menu displays correctly
2.Category pages show products, product details pages display properly
3. User registration and login work
4. Profile page shows user information
5. Shopping cart accepts items and checkout completes successfully
6. Contact form submits messages
7. Admin panel is accessible with the default credentials above

---

### Demo video ：https://drive.google.com/file/d/11BZ6R6cGBgsnW0JI-IJfiTtLZscbKjLa/view?usp=sharing
