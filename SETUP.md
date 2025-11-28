                Setup Guide – PROJECT-A (Admission Portal System)


This guide explains how to set up PROJECT-A on your local system using XAMPP, WAMP, or LAMP. The project includes a ready-made database inside the DB folder, and no manual configuration changes are required.
________________________________________
1. Requirements
Ensure the following software is installed:
Software
•	XAMPP / WAMP / LAMP (Recommended)
•	PHP 7.4 or above
•	MySQL (phpMyAdmin)
•	Any modern web browser
Services Needed
•	Apache (Running)
•	MySQL (Running)
________________________________________
2. Download the Project
Option 1: Git Clone
git clone  https://github.com/anankush/PROJECT-A.git
Option 2: Download ZIP
•	Click Code → Download ZIP
•	Extract the ZIP anywhere
________________________________________
3. Move Project to Server Directory
Move the project folder to your server’s root directory:
XAMPP (Windows)
C:\xampp\htdocs\
WAMP
C:\wamp64\www\
LAMP (Linux)
/var/www/html/
After moving, path will be:
htdocs/PROJECT-A/
________________________________________
4. Create the Database
1.	Start Apache and MySQL
2.	Open phpMyAdmin:
 	http://localhost/phpmyadmin
3.	Click New
4.	Create a new database with the exact name used inside your SQL file (For example: college portal — including the space)
Important:
•	If the SQL file name contains a space, the database name can also contain a space.
•	Use the exact same name so that the import works cleanly.
________________________________________
5. Import the Database
1.	Open the folder:
 	PROJECT-A/DB/
2.	Find the SQL file (example: college portal.sql)
3.	Go to phpMyAdmin → Select your database
4.	Click Import
5.	Select the SQL file
6.	Click Go
All tables will be created automatically.
________________________________________
6. No Configuration Changes Required
This project uses built-in database configuration, so:
•	No need to edit any config.php file
•	No need to set username or password manually
•	The system will automatically connect after import
Just import the database and run the project.
________________________________________
7. Run the Application
Open your browser and go to:
http://localhost/PROJECT-A/
If the database import was successful, the homepage will load.
________________________________________
8. Admin Panel (If Available)
If the project includes an admin dashboard, access it using:
http://localhost/PROJECT-A/admin/
Default credentials (if included in DB):
Username: admin
Password: admin
________________________________________
9. Project Structure Overview
PROJECT-A/

│

├── DB/                  → SQL database file (import this)

├── admin/               → Admin panel files

├── user/                → Student/user pages

├── assets/              → CSS, JS, images

├── index.php            → Home page

└── setup.md             → Setup Instructions

________________________________________
10. Deploying on Hosting Server
To deploy online:
1.	Upload entire project to public_html/
2.	Create a new database in your hosting panel
3.	Import the SQL file from the DB folder
4.	The system will run automatically (no config editing needed)
________________________________________
11. License
This project is distributed under the MIT License, allowing free use, modification, and distribution.
