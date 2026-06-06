# PHP Form Builder

## Overview

PHP Form Builder is a dynamic web application built using Core PHP and MySQL that allows administrators to create, manage, and publish custom forms without writing code. Users can access public form URLs and submit responses, while administrators can view and manage submissions through an admin panel.

## Features

### Admin Panel

* Create forms with name and description
* Add dynamic fields:

  * Text
  * Email
  * Number
  * Textarea
  * Dropdown
  * Radio Buttons
  * Checkboxes
* Configure labels, placeholders, and required validation
* Edit and delete fields
* Reorder fields

### User Side

* Dynamic form rendering
* Public form URLs
* Client-side validation
* Server-side validation
* Secure form submission

### Submission Management

* Store submissions in MySQL database
* View submissions in admin panel
* Submission date and time tracking
* Export submissions to CSV (if implemented)

### Security

* PDO Prepared Statements
* Input Validation and Sanitization
* SQL Injection Protection
* XSS Prevention
* Proper Error Handling

## Technology Stack

* Backend: Core PHP
* Database: MySQL
* Frontend: HTML, CSS, JavaScript

## Installation

### 1. Clone Repository

```bash
git clone https://github.com/Thehimanshuu01/form_builder
```

### 2. Import Database

* Open phpMyAdmin
* Create a database
* Import the provided SQL file

### 3. Configure Database

Update database credentials in:

```php
config/database.php
```

```php
define('DB_HOST', 'localhost');
define('DB_NAME', 'form_builder');
define('DB_USER', 'if0_42115770');
define('DB_PASS', 'Tiwariexe01');
```

### 4. Run Application

Place the project inside your web server directory:

* XAMPP → htdocs


## Database Structure

* forms
* fields
* submissions
* submission_values

## Project Structure

```text
project/
│
├── admin/
├── assets/
├── config/
├── includes/
├── forms/
├── uploads/
├── database/
│   └── schema.sql
├── index.php
└── README.md
```

## Live Demo

Live Link :- https://formbuilder.infinityfree.me/

Admin Portal Username :- admin
Admin Portal Password :- admin123


