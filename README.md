# SiAGRI (Agricultural Information System)

SiAGRI is a localized, web-based marketplace and agricultural information platform designed to bridge local farmers with nearby agricultural kiosks. The platform is designed to overcome logistical challenges associated with heavy agricultural goods by utilizing a **"Click & Collect"** (Self-Pickup) system. 

Transaction security is guaranteed through a **Manual Kiosk Verification (KYC)** system to authenticate official kiosks, along with a **Centralized Manual Escrow** mechanism combined with a unique **OTP Verification Code** to ensure physical goods are successfully handed over before funds are disbursed to the Kiosks.

---

## Team Members & Responsibilities

* **Rinaldi Noviyanto** (Backend Developer, Database, Tailwind CSS Setup, Chart.js Integration)
* **Lalu Gede Janarung Ginawang Arkan** (Frontend Developer, UI/UX Design, Layout Templates, Interactive Components)
* **Fido Priasa Setyono Putra** (System Analyst, QA Testing, Use Case flows, Policy Documentation)

---

## Key Features by User Roles

### 1. Farmer (Customer)
* **Secure Auth**: Sign up and login with secure session storage and encrypted passwords.
* **Interactive Marketplace Catalog**: Browse, search, and filter agricultural products by categories (Fertilizer, Seeds, Tools, Pesticides) with Price and HET indicator tags.
* **Click & Collect Checkout**: Place orders online to immediately lock items' inventory stock. Pick up physical goods and pay cash at the kiosk location.
* **Countdown Cancellation Timer**: Automatic 24-hour order expiration. If items are not collected, the order is cancelled, and inventory returns to stock.
* **Direct Kiosk Communication**: Click to initiate a pre-filled WhatsApp chat with the specific Kiosk owner for collection coordination.
* **Community Forum Threading**: Create agronomy topics, read posts, post comments, and nested reply comments to engage with other farmers and consultants.
* **Profile Management**: Update account parameters, password details.

### 2. Kiosk (Retailer)
* **KYC Document Verification**: Upload legal business documents (NIB, SIUP, SPJB) for Admin review. Store features are locked until validated.
* **Performance Dashboard**: Real-time sales statistics cards (Pending orders count, Active items, Revenue) alongside weekly Chart.js revenue charts.
* **Catalog Management (CRUD)**: Create, read, update, and delete catalog items (Name, price, HET pricing, category, photo, stock level, description).
* **Incoming Order Processing**: Track collection orders. Confirm incoming orders and mark as Completed upon cash handover.

### 3. Admin (System Controller)
* **KYC Approvals Module**: Review uploaded kiosk business documents, approve store verified statuses, or reject with administrator feedback notes.
* **Category Control**: Add new product category types to the marketplace and delete unused options.
* **User Accounts Monitor**: View registration details of all platform actors, and delete user accounts to enforce system rules.
* **Expert Onboarding**: Manually register Expert Consultant credentials.

### 4. Expert (Consultant)
* **Pakar Discussion Board**: Provide professional agronomic advice and crop diagnosis inside the community forum.
* **Verified Consultation Badge**: Replies are highlighted with an official "Pakar Pertanian" indicator tag to establish credibility.

---

## Tech Stack

* **Front-End**: HTML5, CSS, Tailwind CSS, Chart.js
* **Back-End**: Native PHP
* **Database**: MySQL

---

## Web Sitemap & Role-Based Access

The table below maps the web files inside [pages/] to sitemap routes and access rights:

| File Path | Accessible To | Feature Description / Menu |
| :--- | :--- | :--- |
| [/index.php] | Public (Guest) | App Landing Page, statistics counters, and features description |
| [/pages/auth/login.php] | Public (Guest) | Sign-in portal for all system roles |
| [/pages/auth/register.php] | Public (Guest) | Account registration (supports Farmer and Kiosk roles) |
| [/pages/auth/forgot-password.php] | Public (Guest) | Password recovery prompt page |
| [/pages/general/profile.php] | Registered Users | Personal details update and password resets |
| [/pages/farmer/catalog.php] | Farmer | Marketplace browsing, search, categories filters, and cart triggers |
| [/pages/farmer/product-detail.php] | Farmer | Product specifics page, stock check, HET labels, and add-to-cart |
| [/pages/farmer/checkout.php] | Farmer | Cart checkout screen, order groupings by kiosk, and click & collect submit |
| [/pages/farmer/my-orders.php] | Farmer | Active orders status center (Pending, Confirmed, Completed, Cancelled) |
| [/pages/farmer/forum.php] | Farmer, Expert | Agricultural discussions forum, comments feed, and nested replies |
| [/pages/kiosk/dashboard.php] | Kiosk | Seller analytics dashboard with weekly sales charts |
| [/pages/kiosk/kyc-upload.php] | Kiosk | Upload administrative files (NIB/SIUP) for verified status center |
| [/pages/kiosk/manage-catalog.php] | Kiosk | Kiosk shop inventory CRUD center |
| [/pages/kiosk/incoming-orders.php] | Kiosk | Processing queue for incoming collection orders |
| [/pages/admin/dashboard.php] | Admin | KYC approval system, Category CRUD, Expert registration, User accounts management |
| [/pages/general/privacy-policy.php] | All Roles | Privacy conditions statement (Indonesian) |
| [/pages/general/privacy-policy-en.php] | All Roles | Privacy conditions statement (English) |
| [/pages/general/terms-of-service-id.php] | All Roles | Terms and Conditions of service usage (Indonesian) |
| [/pages/general/terms-of-service.php] | All Roles | Terms and Conditions of service usage (English) |

---

## Project Directory Structure

```
SiAGRI/
├── assets/                         # Static assets (CSS, JS, Images, Uploads)
│   ├── css/                        # Stylesheets
│   │   ├── global.css              # Core design styles and CSS custom properties
│   │   ├── input.css               # Tailwind directive input file
│   │   └── style.css               # Main stylesheet compiled/built from Tailwind
│   ├── images/                     # Visual UI elements, logos, and illustration placeholders
│   │   ├── ICON.png                # App favicon/icon
│   │   ├── LOGO.png                # Main application logo
│   │   ├── Placeholder-photo.png   # Default profile/item placeholder
│   │   ├── sawah.jpg               # Rice field banner image
│   │   └── Urea-Petro.jpg          # Sample product image
│   ├── js/                         # Client-side script files
│   │   ├── auth.js                 # Authentication handlers and validation
│   │   ├── catalog.js              # Dynamic catalog rendering and filters
│   │   ├── forum.js                # Community discussion board logic
│   │   ├── main.js                 # Base JS, dynamic modals, responsive menus
│   │   └── orders.js               # Checkout and order flow manager
│   └── uploads/                    # Dynamic file uploads (ignored in version control)
│       ├── kyc/                    # Verification documents (ID card uploads)
│       └── products/               # Kiosk catalog product images
├── component/                      # Reusable view components
│   └── layout/                     # Site layout templates
│       ├── footer.php              # Shared footer template
│       ├── head.php                # Document head meta tags & asset loads
│       └── navbar.php              # Shared responsive header and role-based navigation
├── config/                         # Server configuration scripts
│   └── koneksi.php                 # Database credentials & mysqli connection setup
├── database/                       # Database schemas & seed data
│   └── siagri.sql                  # Initial table layout and prefilled accounts script
├── pages/                          # User interface routing files by role
│   ├── admin/                      # Admin panel module
│   │   └── dashboard.php           # Admin dashboard (users, catalogs, logs verification)
│   ├── auth/                       # Sign-in/up views
│   │   ├── forgot-password.php     # Password recovery prompt
│   │   ├── login.php               # System user credentials entry
│   │   └── register.php            # New farmer or kiosk account creation
│   ├── farmer/                     # Farmer marketplace interface
│   │   ├── catalog.php             # Main product browsing gallery
│   │   ├── checkout.php            # Shopping cart checkout summary
│   │   ├── forum.php               # Discussion & agronomy queries panel
│   │   ├── my-orders.php           # Historical purchase tracking
│   │   └── product-detail.php      # Detailed single item preview & specifications
│   ├── general/                    # Shared system-wide views
│   │   ├── privacy-policy.php      # Indonesian privacy rules policy
│   │   ├── privacy-policy-en.php   # English version of privacy guidelines
│   │   ├── profile.php             # Editable settings panel for registered users
│   │   ├── terms-of-service.php    # English terms and conditions of usage
│   │   └── terms-of-service-id.php # Indonesian translation of terms of service
│   └── kiosk/                      # Seller kiosk module
│       ├── dashboard.php           # Kiosk sales dashboard & statistics overview
│       ├── incoming-orders.php     # Transaction requests processing queue
│       ├── kyc-upload.php          # ID upload verification panel
│       └── manage-catalog.php      # Add, edit, remove products panel
├── proses/                         # Server-side transaction processing handlers
│   ├── cancel-order.php            # Logic to handle user request to drop a transaction
│   └── logout.php                  # Clear PHP session context and redirect
├── index.php                       # Main portal entry point & root router
├── package.json                    # NPM dependencies & task script configurations
├── package-lock.json               # Dynamic version locked dependencies list
└── tailwind.config.js              # Tailored styling presets of Tailwind utility compiler
```

---

## Installation & Database Import Guide

### Quick Start Guide (Clone & Setup)

Follow these initial steps to clone the repository, install styles watcher dependencies, and prepare the database:

1. **Clone the Repository**
   Open your command prompt or terminal and run:
   ```bash
   git clone https://github.com/rinaldinvt67/SiAGRI.git
   cd SiAGRI
   ```

2. **Install Assets Compiler & Dev Server (Optional)**
   If you plan to modify or recompile the Tailwind CSS layout styling:
   ```bash
   # Install developer styling utility packages
   npm install

   # Run styling compile-on-save watcher
   npm run dev
   ```

3. **Configure Your Web Server Webroot**
   Move or copy the cloned `SiAGRI` project directory to your local server's web root:
   - For **Laragon**: copy folder to `C:\laragon\www\SiAGRI`
   - For **XAMPP**: copy folder to `C:\xampp\htdocs\SiAGRI`

4. **Initialize & Setup Database via MySQL CLI**
   Instead of using phpMyAdmin, you can initialize and seed the database schemas directly using the terminal commands:
   ```bash
   # 1. Login to your MySQL server (defaults user root, password empty/prompt)
   mysql -u root -p

   # 2. Inside the MySQL prompt, create the database
   CREATE DATABASE siagri;
   EXIT;

   # 3. Import and seed the sql dump file from the project database folder
   mysql -u root -p siagri < database/siagri.sql
   ```

---

### Technical Prerequisites & Versions
- **PHP**: Version 8.1 or higher (Recommended: 8.1.10+)
- **MySQL**: Version 8.0 or higher (Recommended: 8.0.30+)
- **Node.js** (Optional, for compiling styles): Version 16.0 or higher

---

### Method A: Local Setup Using Laragon (Recommended)
*Laragon is the recommended local development environment for Windows because it handles automatic Virtual Hosts.*

#### Prerequisites:
- **Laragon**: Version 6.0 or higher (Full edition with PHP 8.1+ and MySQL 8.0+)

#### Configuration Steps:
1. Clone or copy the project folder into the Laragon web root directory:
   `C:\laragon\www\SiAGRI`
2. Start Laragon and click **"Start All"** to boot Apache and MySQL.
3. Open your browser and navigate to **`http://localhost/phpmyadmin`** (or access database via Laragon database utility).
4. Create a new database named **`siagri`**.
5. Import the SQL file located in the project's database folder:
   [SiAGRI/database/siagri.sql]
6. Ensure the database connection settings in [SiAGRI/config/koneksi.php] match:
   ```php
   $host = "localhost";
   $user = "root";
   $password = "";
   $database = "siagri";
   ```

#### How to Access via Browser:
- Open your browser and navigate to: **`http://siagri.test/`**
*(Laragon automatically maps the `C:\laragon\www\SiAGRI` folder to `siagri.test`)*.

---

### Method B: Local Setup Using XAMPP
*XAMPP is a standard cross-platform local server solution containing Apache, PHP, and MariaDB/MySQL.*

#### Prerequisites:
- **XAMPP**: Version 8.1 or higher (incorporating PHP 8.1+)

#### Configuration Steps:
1. Clone or copy the project folder into the XAMPP web root directory:
   `C:\xampp\htdocs\SiAGRI`
2. Open the **XAMPP Control Panel** and start both **Apache** and **MySQL** modules.
3. Open your browser and navigate to **`http://localhost/phpmyadmin/`**.
4. Create a new database named **`siagri`**.
5. Import the SQL file located in the project's database folder:
   [SiAGRI/database/siagri.sql]
6. Ensure the database connection settings in [SiAGRI/config/koneksi.php] match:
   ```php
   $host = "localhost";
   $user = "root";
   $password = "";
   $database = "siagri";
   ```

#### How to Access via Browser:
- Open your browser and navigate to: **`http://localhost/SiAGRI/`**

---

## Default Test Accounts (Credentials) Imported directly from `siagri.sql`):

|    Role    | Username |        Email        |   Password   |
|------------|----------|---------------------|--------------|
| **Admin**  | `admin`  | `admin@siagri.com`  | `Admin123#`  |
| **Kiosk**  | `kiosk`  | `kiosk@siagri.com`  | `Kiosk123#`  |
| **Farmer** | `farmer` | `farmer@siagri.com` | `Farmer123#` |
| **Expert** | `expert` | `expert@siagri.com` | `Expert123#` |

---

## DBMS Table Specification

The database (default: `siagri`) schema contains **11 tables** with the following technical specifications:

### 1. `users`
Stores all account authentication data, emails, and roles.

| Column | Type | Key | Null | Default | Description |
| :--- | :--- | :---: | :---: | :--- | :--- |
| `user_id` | `int` | PK | NO | *None (AUTO_INCREMENT)* | Unique user identifier |
| `username` | `varchar(50)` | UNIQUE | NO | *None* | Login username |
| `email` | `varchar(100)` | UNIQUE | NO | *None* | Registered email address |
| `password` | `varchar(255)` | - | NO | *None* | Hashed password |
| `role` | `enum('Farmer','Kiosk','Expert','Admin')` | - | NO | *None* | User system authorization level |

---

### 2. `kiosk_profiles`
Manages kiosk store parameters, locations, and KYC verification status.

| Column | Type | Key | Null | Default | Description |
| :--- | :--- | :---: | :---: | :--- | :--- |
| `kiosk_id` | `int` | PK | NO | *None (AUTO_INCREMENT)* | Unique kiosk identifier |
| `user_id` | `int` | FK | NO | *None* | References `users(user_id)` ON DELETE CASCADE |
| `store_name` | `varchar(100)` | - | NO | *None* | Commercial name of the agricultural store |
| `full_address` | `varchar(300)` | - | NO | *None* | Physical pickup address details |
| `whatsapp_number`| `varchar(20)` | - | NO | *None* | Phone contact for WhatsApp Click-to-Chat |
| `kyc_status` | `enum('unverified','pending','verified','rejected')` | - | NO | `unverified` | Current KYC validation status |
| `kyc_doc_path` | `varchar(255)` | - | YES | `NULL` | Relative path to uploaded PDF/Image document |
| `kyc_note` | `varchar(500)` | - | YES | `NULL` | Rejection feedback from Admin |
| `verified_at` | `datetime` | - | YES | `NULL` | Time stamp of KYC approval |

---

### 3. `farmer_profiles`
Stores agricultural farmer land details for subsidized purchase quota calculations.

| Column | Type | Key | Null | Default | Description |
| :--- | :--- | :---: | :---: | :--- | :--- |
| `farmer_id` | `int` | PK | NO | *None (AUTO_INCREMENT)* | Unique farmer profile identifier |
| `user_id` | `int` | FK | NO | *None* | References `users(user_id)` ON DELETE CASCADE |
| `total_land_area`| `int` | - | NO | `0` | Calculated land size integer representation |
| `land_area` | `decimal(10,2)`| - | NO | `0.00` | Land size representation in hectares |

---

### 4. `expert_profiles`
Holds specialization details and contacts of agronomy Expert consultants.

| Column | Type | Key | Null | Default | Description |
| :--- | :--- | :---: | :---: | :--- | :--- |
| `expert_id` | `int` | PK | NO | *None (AUTO_INCREMENT)* | Unique expert profile identifier |
| `user_id` | `int` | FK | NO | *None* | References `users(user_id)` ON DELETE CASCADE |
| `full_name` | `varchar(100)` | - | NO | *None* | Display name of the agronomy expert |
| `specialization`| `varchar(100)` | - | YES | `NULL` | Fields of expertise (e.g., Crop Pest Control) |
| `whatsapp_number`| `varchar(20)` | - | YES | `NULL` | Public contact for farmer consulting |
| `expert_photo` | `varchar(255)` | - | YES | `NULL` | File path to profile photo |

---

### 5. `categories`
Product category master table.

| Column | Type | Key | Null | Default | Description |
| :--- | :--- | :---: | :---: | :--- | :--- |
| `category_id` | `int` | PK | NO | *None (AUTO_INCREMENT)* | Unique category identifier |
| `category_name` | `varchar(100)` | UNIQUE | NO | *None* | Name (e.g. Pupuk Subsidi, Benih) |
| `created_at` | `datetime` | - | YES | `CURRENT_TIMESTAMP` | Category creation timestamp |

---

### 6. `products`
Central listing of catalog items offered by kiosks.

| Column | Type | Key | Null | Default | Description |
| :--- | :--- | :---: | :---: | :--- | :--- |
| `product_id` | `int` | PK | NO | *None (AUTO_INCREMENT)* | Unique product identifier |
| `kiosk_id` | `int` | FK | NO | *None* | References `kiosk_profiles(kiosk_id)` ON DELETE CASCADE |
| `category_id` | `int` | FK | NO | *None* | References `categories(category_id)` ON DELETE RESTRICT |
| `product_name` | `varchar(150)` | - | NO | *None* | Display name of product |
| `description` | `varchar(3000)`| - | YES | `NULL` | Item details, instructions, specifications |
| `product_image` | `varchar(255)` | - | YES | `NULL` | Product photo file path |
| `selling_price` | `decimal(15,2)`| - | NO | `0.00` | Catalog unit price |
| `stock` | `int` | - | NO | `0` | Available quantity level in store |
| `is_subsidized` | `enum('Yes','No')`| - | YES | `No` | Subsidized item tag flag |
| `het_price` | `decimal(15,2)`| - | YES | `0.00` | Government HET (Harga Eceran Tertinggi) ceiling price |
| `created_at` | `datetime` | - | YES | `CURRENT_TIMESTAMP` | Item registration timestamp |

---

### 7. `cart`
Shopping cart storage for Farmer items pending checkout.

| Column | Type | Key | Null | Default | Description |
| :--- | :--- | :---: | :---: | :--- | :--- |
| `cart_id` | `int` | PK | NO | *None (AUTO_INCREMENT)* | Unique cart row identifier |
| `user_id` | `int` | FK | NO | *None* | References `users(user_id)` ON DELETE CASCADE |
| `product_id` | `int` | FK | NO | *None* | References `products(product_id)` ON DELETE CASCADE |
| `quantity` | `int` | - | NO | `1` | Cart unit count |
| `added_at` | `datetime` | - | YES | `CURRENT_TIMESTAMP` | Addition timestamp |
| *Constraint* | `UNIQUE KEY` | - | - | - | Composite unique key on (`user_id`, `product_id`) |

---

### 8. `orders`
Master sales records for Click & Collect transactions.

| Column | Type | Key | Null | Default | Description |
| :--- | :--- | :---: | :---: | :--- | :--- |
| `order_id` | `int` | PK | NO | *None (AUTO_INCREMENT)* | Unique order identifier |
| `user_id` | `int` | FK | NO | *None* | References `users(user_id)` ON DELETE CASCADE |
| `kiosk_id` | `int` | FK | NO | *None* | References `kiosk_profiles(kiosk_id)` ON DELETE CASCADE |
| `total_price` | `decimal(15,2)`| - | NO | *None* | Total checkout price for this kiosk |
| `status` | `enum('pending','confirmed','completed','cancelled')` | - | YES | `pending` | State: Pending collection, Confirmed by kiosk, Handed over, or Expired/Cancelled |
| `expired_at` | `datetime` | - | NO | *None* | Deadline stamp for pickup (checkout time + 24 Hours) |
| `created_at` | `datetime` | - | YES | `CURRENT_TIMESTAMP` | Order creation timestamp |

---

### 9. `order_items`
Detailed lines detailing product counts in orders.

| Column | Type | Key | Null | Default | Description |
| :--- | :--- | :---: | :---: | :--- | :--- |
| `item_id` | `int` | PK | NO | *None (AUTO_INCREMENT)* | Unique order line identifier |
| `order_id` | `int` | FK | NO | *None* | References `orders(order_id)` ON DELETE CASCADE |
| `product_id` | `int` | FK | NO | *None* | References `products(product_id)` ON DELETE CASCADE |
| `quantity` | `int` | - | NO | `1` | Quantity units purchased |
| `price` | `decimal(15,2)`| - | NO | *None* | Snapped unit price at purchase time |

---

### 10. `forum_discussions`
Discussion topics posted by farmers.

| Column | Type | Key | Null | Default | Description |
| :--- | :--- | :---: | :---: | :--- | :--- |
| `forum_id` | `int` | PK | NO | *None (AUTO_INCREMENT)* | Unique forum topic identifier |
| `user_id` | `int` | FK | NO | *None* | References `users(user_id)` ON DELETE CASCADE |
| `post_title` | `varchar(200)`| - | NO | *None* | Title of the discussion |
| `post_content` | `varchar(10000)`| - | NO | *None* | Main message content |
| `created_at` | `datetime` | - | YES | `CURRENT_TIMESTAMP` | Topic creation timestamp |

---

### 11. `forum_comments`
Nested comment reply rows associated with forum discussions.

| Column | Type | Key | Null | Default | Description |
| :--- | :--- | :---: | :---: | :--- | :--- |
| `comment_id` | `int` | PK | NO | *None (AUTO_INCREMENT)* | Unique comment identifier |
| `forum_id` | `int` | FK | NO | *None* | References `forum_discussions(forum_id)` ON DELETE CASCADE |
| `user_id` | `int` | FK | NO | *None* | References `users(user_id)` ON DELETE CASCADE |
| `comment_content` | `text` | - | NO | *None* | Comment text message |
| `parent_comment_id`| `int` | FK | YES | `NULL` | Self-referential comment reply reference (`forum_comments(comment_id)` ON DELETE CASCADE) |
| `created_at` | `datetime` | - | YES | `CURRENT_TIMESTAMP` | Comment creation timestamp |
