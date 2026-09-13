# Smart Online Book Exchange

##  Project Overview

Smart Online Book Exchange is a web-based application developed to help students exchange and manage books online.

The system allows students to register, upload books, browse and search available books, send exchange requests, communicate with other students, and track exchange activities.

An administrator can manage students, books, and exchange requests through the admin dashboard.

##  Features

### Student Module
- Student registration and login
- Secure session-based authentication
- Upload books
- Edit and delete uploaded books
- View personal books
- Browse available books
- Search books
- Browse books by category
- View book details
- Send book exchange requests
- View and manage exchange requests
- Track exchange status
- Send and receive messages
- Receive notifications

### Admin Module
- Admin login
- Admin dashboard
- View and manage students
- View and manage books
- View and manage exchange requests
- Process exchange activities

##  Technologies Used

- PHP 8.x
- MySQL
- HTML
- CSS
- JavaScript
- XAMPP
- Apache
- phpMyAdmin

##  Database

Database name:

`book_exchange_db`

The project uses MySQL for storing student, book, exchange request, message, notification, and review information.

##  Project Structure

Online_BookExchange/
│
├── admin_dashboard.php
├── admin_login.php
├── admin_books.php
├── admin_students.php
├── admin_requests.php
│
├── index.php
├── register.php
├── login.php
├── logout.php
│
├── add_book.php
├── upload_book.php
├── edit_book.php
├── delete_book.php
├── my_books.php
├── browse_books.php
├── book_details.php
│
├── search_book.php
├── search_books.php
├── book_categories.php
├── category_books.php
│
├── exchange.php
├── exchange_request.php
├── exchange_status.php
├── my_requests.php
├── process_exchange.php
│
├── messages.php
├── send_message.php
├── notifications.php
├── notify_user.php
│
├── db_connect.php
├── database_fix.sql
├── README.md
└── uploads/
