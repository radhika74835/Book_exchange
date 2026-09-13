USE book_exchange_db;

-- The original dump contains one book with an empty book_condition,
-- but the column only allows new/good/used.
UPDATE books
SET book_condition = 'used'
WHERE book_condition IS NULL OR book_condition = '';

-- Check the result:
SELECT id, title, book_condition FROM books;

-- Students.email is UNIQUE in the existing schema.
-- The fixed login.php requires an email during registration,
-- so new registrations will no longer insert a duplicate empty email.
SELECT id, name, email FROM students;
