# SiAGRI (Agricultural Information System)

SiAGRI is a localized, web-based marketplace and agricultural information platform designed to bridge local farmers with nearby agricultural kiosks. The platform is designed to overcome logistical challenges associated with heavy agricultural goods by utilizing a **"Click & Collect"** (Self-Pickup) system. 

Transaction security is guaranteed through a **Manual Kiosk Verification (KYC)** system to authenticate official kiosks, along with a **Centralized Manual Escrow** mechanism combined with a unique **OTP Verification Code** to ensure physical goods are successfully handed over before funds are disbursed to the Kiosks.

---

## Team Members
Rinaldi Noviyanto - Full Stack Dev
Lalu Gede Janarung Ginawang Arkan - Front-End Developer & UI/UX Designer
Fido Priasa Setyono Putra - System Analyst & QA Tester

---

## Key Features & User Roles

### 1. Farmer
* **Secure Auth**: Sign up and login to a secure account with encrypted passwords.
* **Product Catalog**: Browse, search, and filter the agricultural product catalog via a premium, responsive interface.
* **Click & Collect Ordering**: Book products online and pick them up directly from the physical kiosk.
* **Centralized Escrow**: Upload transfer payment proofs to the Admin's centralized escrow account prior to picking up items.
* **Pickup OTP Code**: Receive a unique pickup OTP once the payment proof is verified by the Admin to present to the kiosk during physical retrieval.
* **Community Forum**: Ask questions and participate in discussions in the community agricultural forum.

### 2. Kiosk (Retailer)
* **KYC Verification**: Upload legal business documents (NIB, SIUP, SPJB) for manual verification by the Admin to unlock store management.
* **Performance Dashboard**: Access real-time sales statistics (active products, incoming orders, revenue) and interactive charts powered by Chart.js (7-Day Revenue Trends and Top Selling Products).
* **Catalog Management (CRUD)**:
  * Add and remove product items.
  * Edit product details including Name, Category, Price, Stock, Subsidy/HET status, Description, and replace Product Images.
* **Escrow Release**: Input the Farmer's OTP to validate the physical handover of goods and trigger fund disbursement.

### 3. Admin (System Controller)
* **KYC Verification**: Review legal kiosk documents and approve/reject verified merchant status.
* **Escrow Validator**: Verify farmer transfer receipts and activate pickup OTP codes.
* **Expert Onboarding**: Manually register agricultural Expert accounts.

### 4. Expert (Consultant)
* **Professional Consultation**: Answer queries, diagnose plant pests/diseases, and provide professional agronomic advice in the community forum.

---

## Tech Stack

* **Front-End**: HTML5, Vanilla CSS (Premium Glassmorphism styling), Tailwind CSS, Chart.js (Dashboard visualizations).
* **Back-End**: Native PHP (Semi-modular MVC-like architecture).
* **Database**: MySQL.

---

## Project Directory Structure

```
SiAGRI/
├── assets/             # Static assets (CSS, JS, Images, Uploads)
├── component/          # Shared layout components (Header, Footer, Navbar)
├── config/             # Database configuration (koneksi.php)
├── database/           # SQL database schema (siagri.sql)
├── pages/              # Role-based user interface pages
│   ├── admin/          # Admin dashboard & management
│   ├── auth/           # Login, registration, and forgot password pages
│   ├── farmer/         # Farmer catalog & checkout flow
│   ├── general/        # Profile & Terms of Service documents
│   └── kiosk/          # Kiosk dashboard & inventory management
├── proses/             # Backend handler scripts (logout, cancel order)
├── index.php           # Main entry point of the application
├── package.json        # NPM dependencies and scripts configuration
├── package-lock.json   # Locked versions of npm packages
└── tailwind.config.js  # Tailwind CSS configuration file
```

---

## Installation & Database Import Guide

Follow either of the two web server setup methods below to configure and run SiAGRI locally:

### Technical Prerequisites & Versions
- **PHP**: Version 8.1 or higher (Recommended: 8.1.10+)
- **MySQL**: Version 8.0 or higher (Recommended: 8.0.30+)

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
   [database/siagri.sql](file:///C:/laragon/www/SiAGRI/database/siagri.sql)
6. Ensure the database connection settings in [config/koneksi.php](file:///C:/laragon/www/SiAGRI/config/koneksi.php) match:
   * **Host**: `localhost`
   * **User**: `root`
   * **Password**: `""` (empty string)
   * **Database**: `siagri`

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
   [database/siagri.sql](file:///C:/xampp/htdocs/SiAGRI/database/siagri.sql)
6. Ensure the database connection settings in [config/koneksi.php](file:///C:/xampp/htdocs/SiAGRI/config/koneksi.php) match:
   * **Host**: `localhost`
   * **User**: `root`
   * **Password**: `""` (empty string)
   * **Database**: `siagri`

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

## Database Schema Configuration

The **`siagr_`** database structure contains the following tables:

1. **`users`**
   Stores user authentication credentials, email contacts, and role permissions.
   * *Fields*: `user_id`, `username`, `email`, `password`, `role`.

2. **`kiosk_profiles`**
   Manages Kiosk store details, address locations, contact numbers, and KYC verification status.
   * *Fields*: `kiosk_id`, `user_id`, `store_name`, `full_address`, `whatsapp_number`, `kyc_status`, `kyc_doc_path`, `kyc_note`, `verified_at`.

3. **`farmer_profiles`**
   Stores farmer land sizes to determine eligible quotas for subsidized fertilizers.
   * *Fields*: `farmer_id`, `user_id`, `total_land_area`, `land_area`.

4. **`expert_profiles`**
   Contains agricultural expert details, bios, and whatsapp consultation contacts.
   * *Fields*: `expert_id`, `user_id`, `full_name`, `specialization`, `whatsapp_number`, `expert_photo`.

5. **`categories`**
   Categorization master list for store products (Subsidized Fertilizer, Seeds, Pesticides, etc.).
   * *Fields*: `category_id`, `category_name`, `created_at`.

6. **`products`**
   Central product catalog containing pricing, inventory stock level, and subsidy/HET tags.
   * *Fields*: `product_id`, `kiosk_id`, `category_id`, `product_name`, `description`, `product_image`, `selling_price`, `stock`, `is_subsidized`, `het_price`, `created_at`.

7. **`cart`**
   Holds temporary shopping cart items for farmers before checkout.
   * *Fields*: `cart_id`, `user_id`, `product_id`, `quantity`, `added_at`.

8. **`orders`**
   Tracks escrow transactions and collection states.
   * *Fields*: `order_id`, `user_id`, `kiosk_id`, `total_price`, `status`, `expired_at`, `created_at`.

9. **`order_items`**
   Stores details of products linked to each order transaction.
   * *Fields*: `item_id`, `order_id`, `product_id`, `quantity`, `price`.

10. **`forum_discussions`**
    Stores farmer discussion topics and pakar answers in the community forum.
    * *Fields*: `forum_id`, `user_id`, `post_title`, `post_content`, `created_at`.
