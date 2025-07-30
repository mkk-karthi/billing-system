
# 🧾 Laravel Billing System

A simple billing system built with **Laravel** and **MySQL**. This application includes product management with variant and image support, a billing system that calculates discounts, and an e-receipt generator with a notification system.

---

## 🚀 Features

- ✅ **Product CRUD** with variant and image upload support  
- 🛒 **Billing multiple products** in a single invoice  
- 💸 **Discount application** and tax calculations  
- 📄 **E-Receipt generation** after billing  
- 🔔 **Notification system** Email

---

## ⚙️ Installation & Setup

Follow these steps to set up the project on your local machine.

### 1. Clone the repository

```bash
git clone https://github.com/mkk-karthi/laravel-billing-system.git
cd laravel-billing-system
```

### 2. Install dependencies
```bash
composer install
```

### 3. Environment Configuration
```bash
cp .env.example .env
```
Edit the `.env` file with your DB config:

```env
DB_DATABASE=your_database
DB_USERNAME=your_username
DB_PASSWORD=your_password
```

### 4. Run migrations and seeders
```bash
php artisan migrate --seed
```

### 5. Run the application
```bash
php artisan serve
```

---
## 🖼 Product Management

-   Create products with:
    -   Title,  price, tax
    -   Variants (size, color, etc.)
    -   Image uploads

## 🧮 Billing & Discounts

-   Add multiple products to a bill
-   Apply for discounts and Tax calculation
-   Save and review previous bills

## 📩 E-Receipt & Notifications

-   Automatically generate a receipt after checkout
-   Send notifications via:
    -   Email (configured in `.env`)

---

## 🖼️ Gallery

<p align="center">
Products
<img src="https://raw.githubusercontent.com/mkk-karthi/laravel-billing-system/master/public/screenshots/products.png" alt="Billing System using laravel"><br>
Product Create
<img src="https://raw.githubusercontent.com/mkk-karthi/laravel-billing-system/master/public/screenshots/product-create.png" alt="Billing System using laravel"><br>
Billing
<img src="https://raw.githubusercontent.com/mkk-karthi/laravel-billing-system/master/public/screenshots/billing.png" alt="Billing System using laravel"><br>
Receipt
<img src="https://raw.githubusercontent.com/mkk-karthi/laravel-billing-system/master/public/screenshots/receipt.png" alt="Billing System using laravel"><br>
Mail Receipt
<img src="https://raw.githubusercontent.com/mkk-karthi/laravel-billing-system/master/public/screenshots/mail.png" alt="Billing System using laravel"><br>
</p>
