# 🍰 CakeCraft

**Design it. Order it. Track it — one slice at a time.**

CakeCraft is a full-stack cake ordering platform: customers design and order custom cakes and track their status, while the shop owner runs everything — stock and incoming orders — from a dedicated admin panel. Built as a growing school project, with a long-term dream of becoming a real cake business.

> 🎓 Built as a project for **CSE 364 – Software Development Project 1** (Level 3, Term 2)
> 👨‍💻 Developed by [Md. Manam Khan](https://github.com/Md-Manam-Khan)

---

## ✨ About the Project

CakeCraft started as a simple idea: what if ordering a custom cake felt as easy — and as delightful — as designing one? What began as a basic sign-up/sign-in system has grown into a working ordering and tracking platform, with an admin side built for the person actually baking the cakes.

The bigger picture:

- 🎂 Customers can **sign up, design their dream cake** (flavor, size, special notes), and place an order.
- 📦 Customers can **track their own orders** and see their live status.
- 👨‍🍳 An **admin panel** lets the operator (also the chef!) manage ingredient stock and review, accept, or decline incoming orders.
- 🗄️ All data — users, stock, and orders — is stored and managed through a relational MySQL database.
- 📱 Down the road, this project aims to expand into a **Flutter-powered mobile app**, making CakeCraft accessible anywhere.
- 💼 Beyond the classroom, CakeCraft is meant to grow into a **real business** — a space to combine software craftsmanship with a genuine passion for baking.

---

## 🚀 Current Features

**Customer side**
- ✅ Secure **Sign Up** with hashed passwords (`password_hash`)
- ✅ Secure **Sign In** supporting login via ID Name, Email, or Phone Number
- ✅ Session-based login — no need to sign in again on every page
- ✅ Duplicate-account prevention (unique email & ID name checks)
- ✅ **Dashboard** as a central hub after login
- ✅ **Cake customization form** (flavor, size, special instructions) that places an order
- ✅ **My Orders** page showing order history with live status badges

**Admin side**
- ✅ Separate **admin login**, protected by its own session
- ✅ **Stock management** — add new ingredients or top up existing stock quantities
- ✅ **Order management** — view all pending orders and Accept / Decline each one
- ✅ Manual-only quantity input (no accidental spinner clicks — typed values only)

**Design**
- ✅ Fully responsive design (mobile-friendly down to 600px breakpoint)
- ✅ Custom "3D press" button interactions across the site
- ✅ Per-section themed backgrounds (Home / Sign In / Sign Up / Dashboard / Admin)

---

## 🛠️ Tech Stack

| Layer      | Technology                          |
|------------|--------------------------------------|
| Frontend   | HTML5, CSS3                          |
| Backend    | PHP (procedural, `mysqli`, sessions) |
| Database   | MySQL                                |
| Server     | Apache (via XAMPP)                   |
| Future     | Dart & Flutter (planned mobile app)  |

---

## 📁 Project Structure

```
CakeCraft/
├── index.html          # Landing page (Sign In / Sign Up / Admin Login)
├── signin.php           # Login logic + form
├── signup.php           # Registration logic + form
├── config.php           # Database connection (mysqli) + session start
├── dashboard.php        # Logged-in user's hub
├── customize.php        # Cake order form → places an order
├── orders.php           # Customer's own order history + status
├── logout.php           # Ends the customer session
├── admin_login.php      # Admin login form
├── admin.php            # Admin panel — stock & order management
├── admin_logout.php     # Ends the admin session
├── style.css            # Shared stylesheet
├── database.sql         # Full database schema
├── cakes-bg.png          # Homepage background
├── signin-bg.jpg         # Sign-in page background
├── signup-bg.jpg         # Sign-up page background
├── page1.png             # Dashboard / customize / orders background
└── admin.png             # Admin section background
```

---

## 🗄️ Database Schema

**Database:** `CakeCraft`

**`users`**

| Column       | Type          | Notes                          |
|--------------|---------------|----------------------------------|
| `id`         | INT, AUTO_INCREMENT | Primary Key               |
| `full_name`  | VARCHAR(100)  | Not null                       |
| `email`      | VARCHAR(100)  | Not null, unique                |
| `phone`      | VARCHAR(20)   | Not null                       |
| `id_name`    | VARCHAR(50)   | Not null, unique                |
| `password`   | VARCHAR(255)  | Hashed via `password_hash()`    |
| `created_at` | TIMESTAMP     | Defaults to current timestamp   |

**`items`** (ingredient stock, managed by admin)

| Column       | Type          | Notes                          |
|--------------|---------------|----------------------------------|
| `id`         | INT, AUTO_INCREMENT | Primary Key               |
| `item_name`  | VARCHAR(100)  | Not null, unique                |
| `quantity`   | INT           | Defaults to 0                   |

**`orders`**

| Column         | Type          | Notes                                       |
|----------------|---------------|-----------------------------------------------|
| `id`           | INT, AUTO_INCREMENT | Primary Key                             |
| `user_id`      | INT           | Foreign key → `users(id)`                     |
| `cake_details` | TEXT          | Flavor, size, and notes combined              |
| `status`       | VARCHAR(20)   | `pending` / `accepted` / `declined` (default: `pending`) |
| `created_at`   | TIMESTAMP     | Defaults to current timestamp                 |

---

## ⚙️ Getting Started (Local Setup)

1. Install [XAMPP](https://www.apachefriends.org/) and start **Apache** and **MySQL**.
2. Clone or copy this project into your `htdocs` folder:
   ```
   C:\xampp\htdocs\CakeCraft
   ```
3. Open **phpMyAdmin**, create a database named `CakeCraft`, and run `database.sql` to set up all three tables.
4. Visit the project in your browser:
   ```
   http://localhost/CakeCraft/index.html
   ```
5. Sign up for a new account, place a cake order, then check its status under **My Orders**.
6. Visit **Admin Login** from the homepage to manage stock and orders (default credentials are set in `admin_login.php` — change them before deploying anywhere public).

---

## 🗺️ Roadmap

- [x] User authentication (sign up / sign in)
- [x] Cake design & customization form
- [x] Order placement
- [x] Customer order tracking
- [x] Admin panel — stock management
- [x] Admin panel — order accept/decline
- [ ] Online payment integration
- [ ] More granular order status stages (Received → Baking → Arriving → Delivered)
- [ ] Customer notifications on status change
- [ ] Mobile app using Flutter & Dart
- [ ] Launch as a real, independent cake business 🎂

---

## 💌 A Note from the Developer

CakeCraft isn't just a class project — it's the first slice of a bigger dream. What starts here as a Software Development Project submission is meant to grow into something that connects good code with good cake, and eventually, into a business built on both craftsmanship and passion.

---

## 📬 Contact

**Developer:** Md. Manam Khan
**GitHub:** [github.com/Md-Manam-Khan](https://github.com/Md-Manam-Khan)

---

<p align="center">Made with 🧁 and a lot of semicolons.</p>
