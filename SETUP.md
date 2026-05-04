                Setup Guide – PROJECT-A (Admission Portal System)

## 🛠️ Setup & Installation Guide

This guide explains how to set up **PROJECT-A** on your local system using XAMPP, WAMP, or LAMP. The project includes a ready-made database inside the `DB` folder, and **no manual configuration changes are required** to get it running!

### 📋 Requirements

Before you begin, ensure you have the following installed and running:

| Software Requirements | Services Needed |
| :--- | :--- |
| • XAMPP / WAMP / LAMP (Recommended)<br>• PHP 7.4 or above<br>• MySQL (via phpMyAdmin)<br>• Any modern web browser | • **Apache:** Running<br>• **MySQL:** Running |

---

### 🚀 Step-by-Step Installation

#### 1. Download the Project
**Option A: Git Clone (Recommended)**
```bash
git clone https://github.com/anankush/PROJECT-A.git
```
**Option B: Download ZIP**
* Click **Code** → **Download ZIP** on the GitHub repository.
* Extract the ZIP file anywhere on your computer.

#### 2. Move Project to Server Directory
Move the extracted `PROJECT-A` folder to your local server’s root directory:
* **XAMPP (Windows):** `C:\xampp\htdocs\`
* **WAMP (Windows):** `C:\wamp64\www\`
* **LAMP (Linux):** `/var/www/html/`

*(Your project path should now look like: `.../htdocs/PROJECT-A/`)*

#### 3. Create the Database
1. Open your browser and go to **phpMyAdmin**: `http://localhost/phpmyadmin`
2. Click on **New** to create a database.
3. **Name the database exactly as it is named in your SQL file** (e.g., `college portal`).

> **⚠️ Important Note:** If the provided SQL file name contains a space (e.g., `college portal`), your database name must also contain that exact space. Matching the name exactly ensures the import process works flawlessly without requiring manual config edits.

#### 4. Import the Database
1. In phpMyAdmin, click on your newly created database.
2. Go to the **Import** tab at the top.
3. Click **Choose File** and locate the SQL file inside the project folder: `PROJECT-A/DB/college portal.sql` (or similar).
4. Click **Go** at the bottom of the page. All tables will be generated automatically.

> **🎉 Zero Configuration Needed!**
> This project uses a built-in database configuration. 
> * No need to edit any `config.php` file.
> * No need to set usernames or passwords manually.
> * The system will automatically connect once the database is imported!

#### 5. Run the Application
Open your browser and navigate to:
**👉 `http://localhost/PROJECT-A/`**
If the database import was successful, the user homepage will load immediately.

---

### 🔐 Admin Panel Access

If your project includes an administrative dashboard, you can access it here:
**👉 `http://localhost/PROJECT-A/admin/`**

**Default Credentials:**
* **Username:** `admin`
* **Password:** `admin`
* **Admin Authorization Code:** `NAYAN@123` *(If prompted)*

---

### 📂 Project Structure Overview

```text
PROJECT-A/
│
├── DB/              # SQL database file (Import this via phpMyAdmin)
├── admin/           # Admin panel files and backend logic
├── user/            # Student and user-facing pages
├── assets/          # CSS, JavaScript, and Image files
├── index.php        # Application Home page
└── setup.md         # Setup and installation instructions
```

---

### 🌐 Deploying on a Live Hosting Server

To deploy this project online via cPanel or similar hosting:
1. Upload the entire project folder to your `public_html/` directory.
2. Create a new MySQL database and user in your hosting panel.
3. Open phpMyAdmin on your live server and **Import** the SQL file from the `/DB/` folder.
4. The system will run automatically—no configuration editing needed!

---

### 📄 License

This project is distributed under the **MIT License**. You are free to use, modify, and distribute this software as you see fit.
