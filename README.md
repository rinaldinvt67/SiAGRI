**Website Name**
SiAGRI (Sistem Informasi Agrikultur)

**Short Desc**
SiAGRI is a localized, web-based marketplace and agricultural information platform designed to bridge local farmers with nearby agricultural kiosks. To overcome logistical challenges, it features a "Click & Collect" (Self-Pickup) system. The platform ensures secure transactions through a Manual Kiosk Verification (KYC) system to filter out unofficial agents, and a Centralized Manual Escrow system combined with a unique OTP to protect both buyers and sellers during the transaction process.


**Team, Roles**

* **Rinaldi Noviyanto** - System Analyst & Full Stack Dev
* **Lalu Gede Janarung Ginawang Arkan** - Front-End Developer & UI/UX Designer
* **Fido Priasa Setyono Putra** - System Analyst & QA Tester


**List the User/Actor Website and Features**

* **Farmer (Default User)**
* Register and Login.
* Browse, search, and filter the agricultural product catalog (using the mobile-friendly Floating Action Panel).
* Book products using the "Click & Collect" (Self-Pickup) method.
* Upload payment proof to the Admin's centralized escrow account.
* Receive and use a unique OTP to claim goods at the physical Kiosk.
* Ask questions and participate in community discussions in the Forum.


* **Kiosk (Retailer)**
* Register and Login.
* Upload legal business documents (NIB, SIUP, etc.) for the KYC verification process.
* Access a dedicated Kiosk Dashboard (unlocked after Admin verification).
* Manage product catalog (CRUD operations for stock and actual prices).
* Input the Farmer's OTP to validate the handover of goods and trigger fund disbursement.


* **Admin (System Controller)**
* Access the central Admin Dashboard.
* Verify Kiosk legal documents (Manual KYC) and assign the "Verified Kiosk" badge.
* Validate Farmer payment proofs and generate the Pickup OTP (Escrow System).
* Disburse/release held funds to the Kiosk's bank account once the transaction is completed.
* Manually create and register accounts for the "Expert" role.


* **Expert (Consultant)**
* Login using credentials exclusively provided by the Admin.
* Provide professional agricultural advice and answer technical questions within the Forum.



**Techstack Using?**

* **Front-End:** HTML5, CSS3 and Tailwind CSS (for rapid responsive layouting).
* **Back-End:** Native PHP.
* **Architecture:** Semi-Modular MVC (Separating Views like `header`/`footer` from database logic).
* **Methodology:** Rapid Application Development (RAD).


**DBMS: Configuration Table**

* **Database Management System:** MySQL (Relational Database).
* **Configuration Tables (Core Entities):**
* `users`: Handles authentication (`user_id`, `username`, `email`, `password`, `role`).
* `kiosk_profiles`: Stores store details and KYC status (`kiosk_id`, `user_id`, `store_name`, `legal_document`, `verification_status`).
* `products`: Central master catalog (`product_id`, `product_name`, `category`, `is_subsidized`).
* `kiosk_catalogs`: Individual kiosk inventory (`catalog_id`, `kiosk_id`, `product_id`, `actual_selling_price`, `stock_status`).
* `orders`: Manages transactions and Escrow (`order_id`, `user_id`, `total_price`, `payment_proof`, `pickup_code`, `escrow_status`).
* `order_items`: Details of purchased items (`id`, `order_id`, `product_id`, `quantity`).
* `forums`: Community discussions (`forum_id`, `user_id`, `post_title`, `post_content`).
* `experts`: Professional consultants (`expert_id`, `expert_name`, `specialization`).
