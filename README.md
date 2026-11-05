# 🍰 CakeCraft

**Design it. Order it. Track it — one slice at a time.**

CakeCraft is a full-stack cake ordering platform: customers design and order custom cakes, see the price live and track the order all the way to their door. The shop owner runs everything — stock, menu, prices, incoming orders and delivery riders — from a dedicated admin panel, and riders accept deliveries and post live updates from their own panel. Built as a growing school project, with a long-term dream of becoming a real cake business.

> 🎓 Built as a project for **CSE 364 – Software Development Project 1** (Level 3, Term 2)
> 👨‍💻 Developed by [Md. Manam Khan](https://github.com/Md-Manam-Khan)

---

## ✨ About the Project

CakeCraft started as a simple idea: what if ordering a custom cake felt as easy — and as delightful — as designing one? What began as a basic sign-up/sign-in system has grown into a working ordering, pricing, tracking and delivery platform, with an admin side built for the person actually baking the cakes and a rider side built for the people bringing them home.

The bigger picture:

- 🎂 Customers can **sign up, design their dream cake** (flavor, filling, size, delivery address, special notes) and place an order.
- 💰 Customers see the **total price update live** as they choose, based on prices the admin sets.
- 📦 Customers can **track their own orders**, see their live status, and read **delivery updates from the rider**.
- 👨‍🍳 An **admin panel** lets the operator (also the chef!) manage ingredient stock, edit the menu and prices, review, accept or decline incoming orders, create rider accounts and **assign each accepted order to a specific rider**.
- 🛵 A **rider panel** lets riders accept or decline the orders assigned to them, and post updates like *Received*, *Reached* and *Delivery done*.
- 🗄️ All data — users, stock, menu, prices, riders, orders and delivery updates — is stored in a relational MySQL database.
- 📱 Down the road, this project aims to expand into a **Flutter-powered mobile app**, making CakeCraft accessible anywhere.
- 💼 Beyond the classroom, CakeCraft is meant to grow into a **real business**.

---

## 🚀 Current Features

**Customer side**
- ✅ Secure **Sign Up** with hashed passwords (`password_hash`)
- ✅ Secure **Sign In** supporting login via ID Name, Email, or Phone Number
- ✅ Session-based login and duplicate-account prevention (unique email & ID name)
- ✅ **Dashboard** as a central hub after login
- ✅ **Cake customization form** — flavor, filling and size dropdowns, a **delivery address** box, and special instructions
- ✅ **Dynamic pricing** — prices shown beside each option and a live **Total** (e.g. `Total: 1000 TK`) that updates as the customer picks
- ✅ **My Orders** page with order history, address, total, live status badges (pending / accepted / declined / delivered) and **rider updates** with timestamps

**Admin side**
- ✅ Separate **admin login**, protected by its own session
- ✅ **Stock management** — add new ingredients or top up existing quantities
- ✅ **Edit Menu** dropdown with popup boxes: **Add Cake, Remove Cake, Add Filling, Remove Filling** and **Edit Price**
- ✅ **Edit Price** — choose a flavor, filling or size, type the new price and save; customers see it immediately
- ✅ **Order management** — pending orders are numbered (**Order #1, #2, …**) and show customer, details, address and total, with Accept / Decline
- ✅ **Rider management** — create a rider's ID name and password (stored hashed) and see the rider list
- ✅ **Assign Order to Rider** — pick a rider, press **Select**, choose an accepted order by its number and press **Assign**; a live list shows each assignment's state (awaiting rider / accepted / declined / delivered)
- ✅ Declined or unanswered assignments can be **re-assigned** to another rider

**Rider side**
- ✅ Separate **rider login** with the credentials created by the admin
- ✅ **New Assignments** — the rider only sees orders assigned to them and can **Accept** or **Decline**
- ✅ **Ongoing Orders** — after accepting, the rider sees the customer, address and total, and can **Send Update** (e.g. Received, Reached, On the way) or press **Delivery Done**
- ✅ A declined order disappears from the rider's panel and returns to the admin for re-assignment

**Design**
- ✅ Fully responsive layout
- ✅ Popup (overlay) boxes and a dropdown button for the admin's menu editor
- ✅ Custom "3D press" buttons and per-section themed backgrounds
- ✅ Prices displayed in Bangladeshi Taka as `850 TK`

---

## 🛠️ Tech Stack

| Layer      | Technology                                          |
|------------|-----------------------------------------------------|
| Frontend   | HTML5, CSS3, a little vanilla JavaScript            |
| Backend    | PHP (procedural, `mysqli`, sessions)                |
| Database   | MySQL                                               |
| Server     | Apache (via XAMPP)                                  |
| Future     | Dart & Flutter (planned mobile app)                 |

---

## 📁 Project Structure

```
CakeCraft/
├── index.html           # Landing page (Sign In / Sign Up / Admin Login / Rider Login)
├── signin.php           # Customer login
├── signup.php           # Registration
├── config.php           # Database connection, session start, tk() price formatter
├── dashboard.php        # Logged-in customer's hub
├── customize.php        # Order form: flavor, filling, size, address, live total
├── orders.php           # Customer's order history, status & rider updates
├── logout.php           # Ends the customer session
├── admin_login.php      # Admin login
├── admin.php            # Admin panel: stock, orders, riders, rider assignment, Edit Menu & prices
├── admin_logout.php     # Ends the admin session
├── rider_login.php      # Rider login
├── rider.php            # Rider panel: new assignments, ongoing orders & updates
├── rider_logout.php     # Ends the rider session
├── style.css            # Shared stylesheet
├── database.sql         # Full schema (all 8 tables) + starter menu and prices
├── cakes-bg.png, signin-bg.jpg, signup-bg.jpg, page1.png, admin.png,
│   Red-Velvet-Cake-Still-Life.png   # Backgrounds
├── LICENSE              # MIT License
└── README.md            # You are here
```

---

## 🗄️ Database Schema

**Database:** `CakeCraft`

| Table           | Purpose                                   | Key columns                                                                 |
|-----------------|-------------------------------------------|-----------------------------------------------------------------------------|
| `users`         | Customer accounts                         | `full_name`, `email` (unique), `phone`, `id_name` (unique), hashed `password` |
| `items`         | Ingredient stock (admin)                  | `item_name` (unique), `quantity`                                            |
| `cakes`         | Flavors on the menu                       | `cake_name` (unique), `price`                                               |
| `fillings`      | Fillings on the menu                      | `filling_name` (unique), `price`                                            |
| `sizes`         | Cake sizes                                | `size_name` (unique), `price`                                               |
| `riders`        | Delivery accounts (admin-created)         | `id_name` (unique), hashed `password`                                       |
| `orders`        | Customer orders                           | `user_id`, `cake_details`, `address`, `total_price`, `status`, `rider_id`, `rider_status` |
| `order_updates` | Rider messages shown to customers         | `order_id`, `update_text`, `created_at`                                     |

**Order flow**

- `orders.status`: `pending` → `accepted` / `declined` (admin) → `delivered` (rider)
- `orders.rider_status`: empty (not assigned) → `pending` (assigned, awaiting the rider) → `accepted` or `declined` (rider's answer)
- The total price is the sum of the chosen flavor, filling and size prices, saved with the order at the time it is placed.

Seeded with five starter cakes, three fillings and three sizes (Small, Medium, Large), each with a starting price.

---

## ⚙️ Getting Started (Local Setup)

1. Install [XAMPP](https://www.apachefriends.org/) and start **Apache** and **MySQL**.
2. Copy this project into your `htdocs` folder:
   ```
   C:\xampp\htdocs\CakeCraft
   ```
3. Open **phpMyAdmin**, go to the **SQL** tab, paste/run `database.sql`. It drops and recreates the `CakeCraft` database with all tables and starter data. ⚠️ Any existing data will be erased.
4. Visit:
   ```
   http://localhost/CakeCraft/index.html
   ```
5. Sign up as a customer, then place an order from **Customize Your Cake**.
6. Open **Admin Login** (credentials are set in `admin_login.php` — change them before deploying anywhere public):
   - Create a rider in the **Riders** box.
   - Accept the pending order (shown as **Order #1**).
   - In **Assign Order to Rider**, pick the rider, press **Select**, choose the order and press **Assign**.
   - Use **Edit Menu → Edit Price** to change prices, or add/remove flavors and fillings (new items start at 0 TK until priced).
7. Open **Rider Login**, accept the assignment and post updates; press **Delivery Done** when finished.
8. Check **My Orders** as the customer to see the status and rider updates.

---

## 🗺️ Roadmap

- [x] User authentication (sign up / sign in)
- [x] Cake design & customization form
- [x] Order placement with delivery address
- [x] Customer order tracking
- [x] Admin panel — stock management
- [x] Admin panel — order accept/decline
- [x] Admin panel — rider account creation
- [x] Admin-managed menu (flavors and fillings) with dropdown selection
- [x] Dynamic pricing set by the admin, with a live total for customers
- [x] Admin-controlled rider assignment with rider accept / decline
- [x] Delivery & riders panel with live updates for customers
- [ ] Add / remove cake sizes from the admin panel
- [ ] A separate dashboard for chefs
- [ ] Online payment with verification
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
