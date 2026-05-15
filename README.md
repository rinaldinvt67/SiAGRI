**Website Name**
SiAGRI (Sistem Informasi Agrikultur)

## Short Desc
SiAGRI is a localized, web-based marketplace and agricultural information platform designed to bridge local farmers with nearby agricultural kiosks. To overcome logistical challenges of heavy goods, it features a "Click & Collect" (Self-Pickup) system. The platform ensures secure transactions through a Manual Kiosk Verification (KYC) system to filter out unofficial agents, and a Centralized Manual Escrow system combined with a unique OTP to protect both buyers and sellers during the transaction process.

## Team, Roles
* **Rinaldi Noviyanto** - System Analyst & Full Stack Dev
* **Lalu Gede Janarung Ginawang Arkan** - Front-End Developer & UI/UX Designer
* **Fido Priasa Setyono Putra** - System Analyst & QA Tester

## List the User/Actor Website and Features
* **Farmer (Default User)**
    * Register and Login to a secure personal account.
    * Browse, search, and filter the agricultural product catalog using a mobile-friendly interface.
    * Book products using the "Click & Collect" (Self-Pickup) method to avoid unnecessary travel.
    * Upload payment proof to the Admin's centralized escrow account for secure transaction handling.
    * Receive and use a unique OTP code to claim goods at the physical Kiosk location.
    * Participate in community discussions and ask questions in the Forum.

* **Kiosk (Retailer)**
    * Register and upload legal business documents (NIB, SIUP, SPJB) for the Manual KYC verification process.
    * Access a dedicated Kiosk Dashboard to manage store inventory (unlocked after Admin approval).
    * Manage product catalog including CRUD operations for stock, prices, and product images.
    * Input the Farmer's OTP to validate the physical handover of goods and trigger fund disbursement.

* **Admin (System Controller)**
    * Verify Kiosk legal documents and assign the "Verified Kiosk" trust badge.
    * Validate Farmer payment proofs and manage the manual escrow fund flow.
    * Generate Pickup OTPs and oversee the completion of transaction cycles.
    * Manually register "Expert" accounts to maintain the quality of information in the forum.

* **Expert (Consultant)**
    * Login using credentials exclusively provided by the Admin.
    * Provide professional agricultural advice and resolve farmers' technical issues within the Forum feature.

## Techstack Using?
* **Front-End:** HTML5, CSS3 (Vanilla CSS for Glassmorphism effects), and Tailwind CSS for rapid responsive layouting.
* **Back-End:** Native PHP with Semi-Modular MVC architecture.
* **Methodology:** Rapid Application Development (RAD) / Prototyping for efficient 1-month development.

## DBMS: Configuration Table
* **Database Management System:** MySQL (Relational Database).
* **Configuration Tables (Core Entities):**
    * `users`: Handles authentication and roles (`user_id`, `username`, `email`, `password`, `role`).
    * `kiosk_profiles`: Stores store details and KYC verification status (`kiosk_id`, `user_id`, `store_name`, `legal_document`, `verification_status`).
    * `products`: Centralized master catalog of agricultural goods (`product_id`, `product_name`, `category`, `is_subsidized`).
    * `kiosk_catalogs`: Individual kiosk inventory and real-time stock status (`catalog_id`, `kiosk_id`, `product_id`, `actual_selling_price`, `stock_status`).
    * `orders`: Manages the Escrow transaction cycle (`order_id`, `user_id`, `total_price`, `payment_proof`, `pickup_code`, `escrow_status`).
    * `order_items`: Specific transaction details (`id`, `order_id`, `product_id`, `quantity`).
    * `forums`: Community discussion posts (`forum_id`, `user_id`, `post_title`, `post_content`).
    * `experts`: Professional consultant profiles (`expert_id`, `expert_name`, `specialization`).
