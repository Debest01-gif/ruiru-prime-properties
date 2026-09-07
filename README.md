# Ruiru Prime Properties — Real Estate Portal & CMS

An ultra-premium, dark-glassmorphism **Real Estate Website & Management System** tailored for the property market in **Ruiru, Kiambu County, Kenya**. Built with pure **PHP 8.2, MySQL, HTML5, CSS3, and Vanilla JavaScript**.

---

## 🌟 Key Features

### Public Portal
- **Interactive Ruiru Search:** Filter by neighborhoods (*Kimbo, Membley, Ruiru East, Ruiru CBD, Murera, Gitambaya, Toll*), price, bedrooms, and listing type (*Sale / Rent*).
- **Property Showcase:** High-resolution galleries, amenities checklist (borehole, solar, garage, 24hr security, pool), coordinates, and neighborhood landmarks.
- **Mortgage Calculator:** Dynamic sliders for KES property price, down payment, interest rates, and loan terms, accompanied by a Kenyan bank benchmark guide (KCB, Co-op, Stanbic, Equity, NCBA).
- **Direct WhatsApp Inquiries:** 1-click WhatsApp buttons with prefilled property details for immediate buyer-to-agent connection.
- **Agents Directory:** Agent sales metrics, star ratings, and listed portfolios.
- **Market Insights Blog:** Guides and analysis on Ruiru real estate growth, land title diligence, and investment trends.

### Admin Dashboard (`/admin`)
- **Dashboard Analytics:** Visual KPI counters and Chart.js analytics for property types and lead conversion pipelines.
- **Listing Management:** Add/edit listings with image uploads, amenities checklists, coordinates, and status toggles (*Available, Reserved, Sold, Rented*).
- **Leads & Inquiries Tracker:** Direct WhatsApp reply integration and status pipelines (*New, Read, Replied, Closed*).
- **Agent Team Profiles:** Consultant bio management, photos, and performance metrics.
- **Blog & Testimonials CMS:** Moderation and publishing tools.
- **Site Configuration:** Manage branding, phone numbers, WhatsApp lines, office location, and admin password security.

---

## 🚀 Quick Local Setup (XAMPP)

1. Clone or copy into your XAMPP web directory:
   ```bash
   cd C:\xampp\htdocs\
   git clone https://github.com/Debest01-gif/ruiru-prime-properties.git "real estate"
   ```
2. Start **Apache** and **MySQL** in your XAMPP Control Panel.
3. Run the automated database installer:
   ```bash
   php database/setup.php
   ```
   *(Or import `database/schema.sql` directly into phpMyAdmin)*
4. Visit the website in your browser:
   - **Frontend:** [http://localhost/real estate/index.php](http://localhost/real%20estate/index.php)
   - **Admin Login:** [http://localhost/real estate/admin/login.php](http://localhost/real%20estate/admin/login.php)

### ⚡ Instant Zero-Config Dev (Built-in PHP Server)
No MySQL or Apache needed! The system automatically detects offline MySQL and falls back seamlessly to an included SQLite database with all demo properties, agents, and settings:
```bash
cd "real estate"
php database/setup.php
php -S localhost:8000
```
Then visit:
- **Frontend:** [http://localhost:8000](http://localhost:8000)
- **Admin Panel:** [http://localhost:8000/admin/login.php](http://localhost:8000/admin/login.php)

### Default Admin Credentials
- **Email:** `admin@ruiruprimeproperties.co.ke`
- **Password:** `admin123`

---

## ☁️ Deploying on Render

This repository includes a production-ready `Dockerfile` and `render.yaml`.

### Steps:
1. Push this repository to your GitHub account (`Debest01-gif`).
2. Log in to [Render.com](https://render.com) and click **New +** -> **Web Service**.
3. Connect your GitHub repository `ruiru-prime-properties`.
4. Select **Docker** as the Runtime.
5. In **Environment Variables**, provide your MySQL credentials:
   - `DB_HOST`: Your MySQL host (e.g., from Aiven, PlanetScale, Railway, or Render MySQL)
   - `DB_USER`: Database user
   - `DB_PASS`: Database password
   - `DB_NAME`: Database name (e.g. `ruiru_realestate`)
   - `DB_PORT`: `3306` (or provide full `DATABASE_URL`)
6. Click **Deploy Web Service**!
