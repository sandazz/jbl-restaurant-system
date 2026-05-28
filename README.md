# 🍽️ BYOB Restaurant POS System

A modern, full-featured **Point of Sale (POS) system** for BYOB (Bring Your Own Bottle) restaurants built with **Laravel 11** and **Tailwind CSS**. Designed for dine-in operations with real-time billing, table management, kitchen order tickets (KOT), bar order tickets (BOT), and comprehensive inventory management.

[![Laravel](https://img.shields.io/badge/Laravel-11.x-red?logo=laravel&logoColor=white)](https://laravel.com)
[![PHP](https://img.shields.io/badge/PHP-8.1%2B-777bb3?logo=php&logoColor=white)](https://php.net)
[![MySQL](https://img.shields.io/badge/MySQL-8.0%2B-00758f?logo=mysql&logoColor=white)](https://mysql.com)
[![License](https://img.shields.io/badge/License-MIT-green)](LICENSE)

## ✨ Features

### 🛎️ POS & Billing Module
- **Real-time live billing** with instant calculations
- **Table management** — 12+ color-coded table cards (available/occupied/reserved/cleaning)
- **Product search** — Live search with category filtering
- **Dynamic menu** — 6 categories: Starters, Mains, Desserts, Drinks, Bar Items, Specials
- **Order management** — Add/remove items, adjust quantities, kitchen notes
- **Hold orders** — Pause and resume orders later
- **Multiple payment methods** — Cash, Card, Bank Transfer, Mixed
- **Discount & tax** — Percentage or fixed amount discounts, 10% auto tax
- **Currency support** — LKR (Rs.) formatting throughout

### 🖨️ Kitchen & Bar Operations
- **KOT (Kitchen Order Ticket)** — Automatic separation of kitchen items
- **BOT (Bar Order Ticket)** — Separate bar/drink items for bartender
- **Print management** — Track KOT/BOT print times
- **Kitchen notes** — Special instructions per item

### 📊 Table Management
- **Visual table status** — Green (available), Red (occupied), Yellow (reserved), Gray (cleaning)
- **Real-time updates** — Auto-refresh table status
- **VIP room support** — Separate VIP section (2 tables)
- **Capacity tracking** — Display table capacity and current orders

### 🛒 Inventory & Products
- **Product database** — Name, category, cost price, selling price, barcode
- **Stock management** — Track inventory or unlimited stock options
- **Product images** — Image upload support
- **Supplier tracking** — Link products to suppliers
- **Category management** — Organize products by category
- **Barcode scanning** — Barcode support for quick product lookup

### 👥 Role-Based Access Control
- **Admin** — Full system access
- **Manager** — Most modules (no settings access)
- **Cashier** — POS only
- **Permission system** — Module-level permissions

### 📈 Order & Sales Management
- **Order history** — Complete transaction logs
- **Re-print invoices** — Reprint customer invoices
- **Order status tracking** — pending → confirmed → completed
- **Waiter assignment** — Track who created the order
- **Payment tracking** — Record payment method and amount

## 🏗️ Architecture

### Database Schema
- **14 total tables** (5 core Laravel + 9 custom)
- **users** — Authentication with roles
- **orders** — Full order lifecycle
- **order_items** — Line items with kitchen notes
- **products** — Extended product fields
- **categories** — Product categorization
- **restaurant_tables** — Table management
- **customers** — Customer profiles

### API Endpoints
```
POST   /pos/order                      # Create order
GET    /pos/order/{id}                 # Get order details
POST   /pos/order/{id}/item            # Add item to order
DELETE /pos/order/{id}/item/{item}     # Remove item
PUT    /pos/order/{id}/item/{item}     # Update item qty/notes
POST   /pos/order/{id}/hold            # Hold order
POST   /pos/order/{id}/complete        # Complete + payment
POST   /pos/order/{id}/kot             # Print KOT
POST   /pos/order/{id}/bot             # Print BOT
GET    /pos/tables                     # Get all tables
GET    /pos/products                   # Search products
GET    /pos/held-orders                # List held orders
```

## 🚀 Quick Start

### Prerequisites
- PHP 8.1+
- MySQL 8.0+
- Composer
- Node.js & npm

### Installation

```bash
# Clone repository
git clone https://github.com/VindiPerera/ByobRestaurant.git
cd ByobRestaurant

# Install PHP dependencies
composer install

# Install Node dependencies
npm install

# Copy environment file
cp .env.example .env

# Generate app key
php artisan key:generate

# Create database
mysql -u root -e "CREATE DATABASE restaurant_byob;"

# Run migrations
php artisan migrate

# Seed demo data
php artisan db:seed

# Start development server
php artisan serve --host=127.0.0.1 --port=8000

# Open in browser
# http://localhost:8000/login
```

### Demo Login Credentials

| Role | Email | Password |
|------|-------|----------|
| Admin | `admin@restaurant.local` | `password` |
| Manager | `manager@restaurant.local` | `password` |
| Cashier | `cashier@restaurant.local` | `password` |

### Demo Data
- **12 restaurant tables** (main section)
- **2 VIP rooms** (VIP section)
- **6 product categories** (Starters, Mains, Desserts, Drinks, Bar Items, Specials)
- **9 sample products** (pre-loaded for testing)

## 📁 Project Structure

```
RestaurantByob/
├── app/
│   ├── Http/
│   │   ├── Controllers/
│   │   │   ├── PosController.php          # 13 POS endpoints
│   │   │   ├── ProductController.php      # Product CRUD
│   │   │   ├── CustomerController.php     # Customer CRUD
│   │   │   └── WastageController.php      # Wastage tracking
│   │   └── Requests/
│   └── Models/
│       ├── Order.php                      # Order model
│       ├── OrderItem.php                  # Line items
│       ├── RestaurantTable.php            # Table model
│       ├── Category.php                   # Product categories
│       ├── Product.php                    # Products
│       ├── User.php                       # Authentication
│       └── Role.php                       # Roles & permissions
├── database/
│   ├── migrations/
│   │   ├── create_orders_table.php
│   │   ├── create_order_items_table.php
│   │   ├── create_restaurant_tables_table.php
│   │   └── ...
│   └── seeders/
│       ├── CategorySeeder.php
│       ├── TableSeeder.php
│       └── ...
├── resources/views/
│   └── modules/
│       └── pos.blade.php                  # Main POS UI (691 lines)
└── routes/
    └── web.php                            # All routes
```

## 🎨 UI Components

### POS Page Layout
- **Left Panel (280px)** — Table cards with status
- **Center Panel (flex)** — Product grid, search, categories
- **Right Panel (360px)** — Live bill, payment, controls

### Interactive Modals
- **Payment Modal** — Multiple payment methods
- **KOT Modal** — Kitchen items preview
- **BOT Modal** — Bar items preview
- **Held Orders Modal** — Resume or delete held orders

### Color Coding
- 🟢 **Green** — Available table
- 🔴 **Red** — Occupied table
- 🟡 **Yellow** — Reserved table
- ⚫ **Gray** — Cleaning table

## 🛠️ Technology Stack

**Backend**
- Laravel 11
- PHP 8.1+
- MySQL 8.0+
- Eloquent ORM
- Laravel Authentication

**Frontend**
- Tailwind CSS
- Vanilla JavaScript (28 functions)
- Font Awesome icons
- Responsive design

## 📝 Key Files

| File | Purpose |
|------|---------|
| `app/Http/Controllers/PosController.php` | Core POS logic (13 methods) |
| `resources/views/modules/pos.blade.php` | Interactive POS UI |
| `app/Models/Order.php` | Order model with relationships |
| `database/migrations/create_orders_table.php` | Order schema |
| `routes/web.php` | All application routes |

## 🔐 Security Features

- ✅ Laravel authentication middleware
- ✅ CSRF token protection
- ✅ Role-based access control
- ✅ Password hashing with bcrypt
- ✅ Input validation on all requests
- ✅ SQL injection protection (Eloquent)
- ✅ XSS protection (Blade templating)

## 📖 Documentation

- [QUICKSTART.md](QUICKSTART.md) — 5-minute setup guide
- [README_SETUP.md](README_SETUP.md) — Complete installation guide
- [DB_SETUP.md](DB_SETUP.md) — Database schema reference
- [PROJECT_STRUCTURE.md](PROJECT_STRUCTURE.md) — File organization
- [SEEDER_DATA.md](SEEDER_DATA.md) — Demo data details

## 🚦 Development Workflow

### Running the app
```bash
php artisan serve --host=127.0.0.1 --port=8000
```

### Creating a test order
```bash
php artisan tinker
$user = User::first();
$order = Order::create(['order_number' => 'ORD-TEST', 'user_id' => $user->id, 'status' => 'pending']);
```

### Reset database
```bash
php artisan migrate:fresh --seed
```

## 🐛 Debugging

Enable debug mode in `.env`:
```env
APP_DEBUG=true
```

View logs:
```bash
tail -f storage/logs/laravel.log
```

## 🤝 Contributing

1. Fork the repository
2. Create a feature branch (`git checkout -b feature/amazing-feature`)
3. Commit changes (`git commit -m 'Add amazing feature'`)
4. Push to branch (`git push origin feature/amazing-feature`)
5. Open a Pull Request

## 📜 License

This project is licensed under the MIT License — see the [LICENSE](LICENSE) file for details.

## 👨‍💻 Author

**Vindi Perera**
- GitHub: [@VindiPerera](https://github.com/VindiPerera)
- Email: jaanclaude.lk@gmail.com

## 🙏 Acknowledgments

- Built with [Laravel 11](https://laravel.com)
- UI with [Tailwind CSS](https://tailwindcss.com)
- Icons by [Font Awesome](https://fontawesome.com)

---

**Status**: ✅ Production Ready | **Version**: 1.0.0 | **Last Updated**: May 26, 2026
