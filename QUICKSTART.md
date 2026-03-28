# Quickstart: Run Automatic Question Paper Generator (AQPG)

Follow these steps to get the project running locally with XAMPP:

1. **Prerequisites**
   - XAMPP with Apache, PHP 8.x, and MySQL running.
   - A browser and phpMyAdmin access.

2. **Place the project**
   - Copy this repository into your XAMPP `htdocs` directory and name the folder `aqpg` (or update the URLs below to match your folder name).

3. **Import the database**
   - Open phpMyAdmin at `http://localhost/phpmyadmin`.
   - Create a database named `aqpg_db`.
   - Import `aqpg.sql` from the project root into `aqpg_db`.

4. **Configure credentials (if needed)**
   - Default MySQL credentials in `config/db.php` are `root` with a blank password. Update `$user` and `$pass` there if your local credentials differ.

5. **Start the app**
   - Ensure Apache and MySQL are running in XAMPP.
   - Visit the app:
     - Public site: `http://localhost/aqpg/`
     - Admin: `http://localhost/aqpg/admin/login.php`
     - Professor: `http://localhost/aqpg/users/login.php`

6. **Test with default logins**
   - Admin — Username: `admin`, Password: `admin123`
   - Professor — Email: `professor@example.com`, Password: `Avani@123`

   These credentials are for local/demo use only. Create your own accounts or change the passwords immediately before any production use.

If you see a database connection error, confirm the database name matches `aqpg_db`, MySQL is running, and the credentials in `config/db.php` are correct.
