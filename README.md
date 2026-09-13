# Smart Online Book Exchange - Fixed Version

## Requirements
- XAMPP
- Apache
- MySQL
- PHP 8.x

## Database
Database name: `book_exchange_db`

Use the existing database and run `database_fix.sql` once in phpMyAdmin.

## Important
The original project had PHP/database column mismatches:
- `students` uses `name`, but `login.php` used `student_name`.
- `students.email` is UNIQUE, but registration did not provide an email.
- `books` uses `student_id` and `book_condition`, but several pages used `owner_id`, `user_id` and `condition`.
- `messages` uses `content` and `sent_at`, but the old code used `message`, `book_id` and `timestamp`.
- `notifications` uses `student_id`, not `user_id`.
- `exchange_request` uses `current_book_id` and `desired_book_id`.
- The database stores image paths such as `uploads/...`; the old pages incorrectly prefixed them with `assets/images/`.

The files in this folder use the actual database schema.

## Run
1. Copy the fixed PHP files into your project folder:
   `C:\xampp\htdocs\bookexchange\`
2. Keep your CSS files.
3. Create/keep this folder:
   `C:\xampp\htdocs\bookexchange\uploads\`
4. Start Apache and MySQL in XAMPP.
5. Open:
   `http://localhost/bookexchange/`
6. Register using a new student name, a valid email and a password.
7. Upload a book.
8. Browse/search the book.
9. Use Book Details to send a message or exchange request.

## Database connection
`db_connect.php` assumes:
- Host: localhost
- User: root
- Password: empty
- Database: book_exchange_db

If your MySQL password is different, change it in `db_connect.php`.
