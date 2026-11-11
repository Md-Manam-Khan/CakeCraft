# 🍰 CakeCraft

**Design it. Order it. Track it — one slice at a time.**

CakeCraft is a full-stack cake ordering platform: customers design and order custom cakes, see the price live and track the order all the way to their door. The shop owner runs everything — stock, menu, prices, incoming orders and delivery riders — from a dedicated admin panel, and riders accept deliveries and post live updates from their own panel. Built as a growing school project, with a long-term dream of becoming a real cake business.

> 🎓 Built as a project for **CSE 364 – Software Development Project 1** (Level 3, Term 2)
> 👨‍💻 Developed by [Md. Manam Khan](https://github.com/Md-Manam-Khan)

---

## ✨ About the Project

CakeCraft started as a simple idea: what if ordering a custom cake felt as easy — and as delightful — as designing one? What began as a basic sign-up/sign-in system has grown into a working ordering, pricing, tracking and delivery platform, with an admin side built for the person actually baking the cakes and a rider side built for the people bringing them home. A quick **food search bar** lets anyone check whether a cake is on the menu, even before signing in. A later security update hardened the whole system: admin accounts now live in the database, every form is CSRF-protected and repeated failed logins are blocked.

The bigger picture:

- 🎂 Customers can **sign up, design their dream cake** (flavor, filling, size, delivery address, special notes) and place an order.
- 💰 Customers see the **total price update live** as they choose, based on prices the admin sets.
- 🔍 Anyone can use the **search bar** to check whether a cake is currently **Available** on the menu — on the landing page and on the customer dashboard.
- 📦 Customers can **track their own orders**, see their live status, and read **delivery updates from the rider**.
- 👨‍🍳 An **admin panel** lets the operator (also the chef!) manage ingredient stock, edit the menu and prices, review, accept or decline incoming orders, create rider accounts, **assign each accepted order to a specific rider** and change the admin's own login credentials.
- 🛵 A **rider panel** lets riders accept or decline the orders assigned to them, and post updates like *Received*, *Reached* and *Delivery done*.
- 🔒 **Security built in** — hashed passwords, CSRF tokens, login-attempt limiting, validated input and automatic idle logout.
- 🗄️ All data — users, admins, stock, menu, prices, riders, orders and delivery updates — is stored in a relational MySQL database.
- 📱 Down the road, this project aims to expand into a **Flutter-powered mobile app**, making CakeCraft accessible anywhere.
- 💼 Beyond the classroom, CakeCraft is meant to grow into a **real business**.

---

## 🚀 Current Features

**Food search (landing page + dashboard)**
- ✅ **"Search for food" bar** at the top of the landing page and the customer dashboard
- ✅ Answers **Available** or **Not available** by checking the live menu — only cakes with a price above 0 TK count as available
- ✅ Smart matching: filler words such as *cake*, *the*, *of* and *with* are ignored, so `chocolate cake` finds *Chocolate Truffle*; up to 5 words are matched, in any order
- ✅ Works without logging in: the landing page asks `search.php` in the background (JSON) and shows the answer instantly with no page reload; the dashboard uses the same logic on the server
- ✅ Reflects admin changes immediately — add, remove or re-price a cake and the search result changes with it

**Customer side**
- ✅ Secure **Sign Up** with hashed passwords (`password_hash`) and server-side validation of every field
- ✅ Secure **Sign In** supporting login via ID Name, Email, or Phone Number
- ✅ Session-based login and duplicate-account prevention (unique email & ID name)
- ✅ **Dashboard** as a central hub after login
- ✅ **Cake customization form** — flavor, filling and size dropdowns, a **delivery address** box, and special instructions
- ✅ **Dynamic pricing** — prices shown beside each option and a live **Total** (e.g. `Total: 1000 TK`) that updates as the customer picks; the final price is always recalculated on the server
- ✅ **My Orders** page with order history, address, total, live status badges (pending / accepted / declined / delivered) and **rider updates** with timestamps

**Admin side**
- ✅ Separate **admin login**, protected by its own session; admin accounts are stored in the database with hashed passwords
- ✅ **Edit Credentials** — change the Admin Profile (username) and Admin Password from a popup, each confirmed twice
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

**Security**
- ✅ Passwords stored only as bcrypt hashes (customers, riders and admins)
- ✅ **Prepared statements** for every query that touches user input, and output escaping against XSS
- ✅ **CSRF tokens** on every form
- ✅ **Login-attempt limiting** — 5 wrong attempts per ID or 20 per IP within 15 minutes are blocked (tracked in the `login_attempts` table)
- ✅ New session id after every login, **HttpOnly / SameSite** session cookies and automatic logout after 30 minutes of inactivity
- ✅ Security headers (`X-Frame-Options`, `X-Content-Type-Options`, `Referrer-Policy`) and generic error messages for visitors
- ✅ The search uses a **prepared statement**, escapes `%` and `_` wildcards, caps the query at 100 characters and returns only a yes/no answer (no menu data is exposed)
- ✅ Separate session variables per role, so a page of one role cannot be opened by another

**Design**
- ✅ Fully responsive layout
- ✅ Popup (overlay) boxes and a dropdown button for the admin's menu editor
- ✅ Custom "3D press" buttons and per-section themed backgrounds
- ✅ Prices displayed in Bangladeshi Taka as `850 TK`
- ✅ Search bar with green **Available** / red **Not available** result badge

---

## 🛠️ Tech Stack

| Layer      | Technology                                          |
|------------|-----------------------------------------------------|
| Frontend   | HTML5, CSS3, a little vanilla JavaScript (search, popups, live total) |
| Backend    | PHP (procedural, `mysqli`, sessions)                |
| Database   | MySQL                                               |
| Server     | Apache (via XAMPP)                                  |
| Future     | Dart & Flutter (planned mobile app)                 |

---

## 📁 Project Structure

```
CakeCraft/
├── index.html           # Landing page (Sign In / Sign Up / Admin Login / Rider Login) + food search bar
├── signin.php           # Customer login
├── signup.php           # Registration
├── config.php           # Database connection, secure session start, security headers,
│                        #   helpers (tk() price formatter, CSRF tokens, validation, login limiting)
├── dashboard.php        # Logged-in customer's hub, with the food search bar
├── search.php           # Food search logic: included by dashboard.php, or called by index.html (returns JSON)
├── customize.php        # Order form: flavor, filling, size, address, live total
├── orders.php           # Customer's order history, status & rider updates
├── logout.php           # Ends the customer session
├── admin_login.php      # Admin login (accounts stored in the admins table)
├── admin.php            # Admin panel: stock, orders, riders, rider assignment, Edit Menu & prices, Edit Credentials
├── admin_logout.php     # Ends the admin session
├── rider_login.php      # Rider login
├── rider.php            # Rider panel: new assignments, ongoing orders & updates
├── rider_logout.php     # Ends the rider session
├── style.css            # Shared stylesheet
├── database.sql         # Full schema (all 10 tables) + starter menu, prices and default admin
├── Screenshots/         # 21 numbered screenshots of the project (see the Screenshots section)
├── Manam(014)_Project_Report.pdf   # Academic project report (updated version)
├── cakes-bg.png, signin-bg.jpg, signup-bg.jpg, page1.png, admin.png,
│   Red-Velvet-Cake-Still-Life.png   # Backgrounds (AI-generated, see Credits)
├── LICENSE              # MIT License
└── README.md            # You are here
```

---

## 🗄️ Database Schema

**Database:** `CakeCraft`

| Table            | Purpose                                   | Key columns                                                                 |
|------------------|-------------------------------------------|-----------------------------------------------------------------------------|
| `users`          | Customer accounts                         | `full_name`, `email` (unique), `phone`, `id_name` (unique), hashed `password` |
| `admins`         | Admin accounts (editable by the admin)    | `username` (unique), hashed `password`                                      |
| `login_attempts` | Failed logins, used to block guessing     | `scope`, `ident`, `ip`, `created_at`                                        |
| `items`          | Ingredient stock (admin)                  | `item_name` (unique), `quantity`                                            |
| `cakes`          | Flavors on the menu                       | `cake_name` (unique), `price`                                               |
| `fillings`       | Fillings on the menu                      | `filling_name` (unique), `price`                                            |
| `sizes`          | Cake sizes                                | `size_name` (unique), `price`                                               |
| `riders`         | Delivery accounts (admin-created)         | `id_name` (unique), hashed `password`                                       |
| `orders`         | Customer orders                           | `user_id`, `cake_details`, `address`, `total_price`, `status`, `rider_id`, `rider_status` |
| `order_updates`  | Rider messages shown to customers         | `order_id`, `update_text`, `created_at`                                     |

**Order flow**

- `orders.status`: `pending` → `accepted` / `declined` (admin) → `delivered` (rider)
- `orders.rider_status`: empty (not assigned) → `pending` (assigned, awaiting the rider) → `accepted` or `declined` (rider's answer)
- The total price is the sum of the chosen flavor, filling and size prices, calculated on the server and saved with the order at the time it is placed.

Seeded with five starter cakes, three fillings, three sizes (Small, Medium, Large), each with a starting price, and one default admin account.

---

## ⚙️ Getting Started (Local Setup)

1. Install [XAMPP](https://www.apachefriends.org/) and start **Apache** and **MySQL**.
2. Copy this project into your `htdocs` folder:
   ```
   C:\xampp\htdocs\CakeCraft
   ```
3. Open **phpMyAdmin**, go to the **SQL** tab, paste/run `database.sql`. It drops and recreates the `CakeCraft` database with all tables and starter data. ⚠️ Any existing data will be erased.
   - By default the app connects to `localhost` as `root` with an empty password (the XAMPP defaults). To use other settings, set the `DB_HOST`, `DB_USER`, `DB_PASS` and `DB_NAME` environment variables.
4. Visit:
   ```
   http://localhost/CakeCraft/index.html
   ```
5. Try the **Search for food** bar on the landing page (for example `chocolate` or `red velvet`), then sign up as a customer and place an order from **Customize Your Cake**.
6. Open **Admin Login** with the default account created by `database.sql` (the username and password are written at the top of that file), then **immediately change them with the "Edit Credentials" button** before using or deploying the project anywhere public:
   - Create a rider in the **Riders** box.
   - Accept the pending order (shown as **Order #1**).
   - In **Assign Order to Rider**, pick the rider, press **Select**, choose the order and press **Assign**.
   - Use **Edit Menu → Edit Price** to change prices, or add/remove flavors and fillings (new items start at 0 TK until priced and cannot be ordered until then).
7. Open **Rider Login**, accept the assignment and post updates; press **Delivery Done** when finished.
8. Check **My Orders** as the customer to see the status and rider updates.

---

## 📸 Screenshots

All screenshots are in the [`Screenshots/`](Screenshots) folder, numbered in the order of the user journey:

| #  | Screen                                   | File                                                   |
|----|------------------------------------------|--------------------------------------------------------|
| 01 | Front page                               | `Screenshots/01_Front_Page.png`                        |
| 02 | GitHub page                              | `Screenshots/02_Github_Page.png`                       |
| 03 | Customer sign up                         | `Screenshots/03_User_Signup_Page.png`                  |
| 04 | Customer sign in                         | `Screenshots/04_User_Signin_Page.png`                  |
| 05 | Customer dashboard with food search      | `Screenshots/05_User_Dashboard_Page_With_Search.png`   |
| 06 | Cake customization                       | `Screenshots/06_Cake_Customization_Page.png`           |
| 07 | Customization done, with live price      | `Screenshots/07_Cake_Customization_Done_With_Prize.png`|
| 08 | Order placed                             | `Screenshots/08_Order_Placed.png`                      |
| 09 | Order tracking                           | `Screenshots/09_Order_Tracking_Page.png`               |
| 10 | Order accepted                           | `Screenshots/10_Order_Accepted.png`                    |
| 11 | Admin login                              | `Screenshots/11_Admin_Login_Page.png`                  |
| 12 | Admin dashboard                          | `Screenshots/12_Admin_Dashboard_Page.png`              |
| 13 | Admin credentials edit                   | `Screenshots/13_Admin_Credentials_Edit.png`            |
| 14 | Rider assigned                           | `Screenshots/14_Rider_Assigned.png`                    |
| 15 | Menu edit                                | `Screenshots/15_Menu_Edit.png`                         |
| 16 | Price edit                               | `Screenshots/16_Price_Edit.png`                        |
| 17 | Rider login                              | `Screenshots/17_Rider_Login_Page.png`                  |
| 18 | Rider dashboard                          | `Screenshots/18_Rider_Dashboard_Page.png`              |
| 19 | Rider messaging panel                    | `Screenshots/19_Rider_Messaging_Panel.png`             |
| 20 | Delivery confirmation                    | `Screenshots/20_Delivery_Confirmation.png`             |
| 21 | Database                                 | `Screenshots/21_Database.png`                          |

The full walkthrough, with explanations, is in the project report: `Manam(014)_Project_Report.pdf`.

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
- [x] Admin accounts stored in the database, with an Edit Credentials popup
- [x] Security hardening — CSRF tokens, login-attempt limiting, input validation, session protection
- [x] Food search bar on the landing page and dashboard (Available / Not available)
- [ ] Add / remove cake sizes from the admin panel
- [ ] Search suggestions and searching by filling or size
- [ ] A separate dashboard for chefs
- [ ] Online payment with verification
- [ ] Customer notifications on status change
- [ ] HTTPS deployment, password reset and two-step verification for the admin
- [ ] Mobile app using Flutter & Dart
- [ ] Launch as a real, independent cake business 🎂

---

## 🙏 Credits

- 👨‍💻 **Code** — the entire code of CakeCraft was written by me, [Md. Manam Khan](https://github.com/Md-Manam-Khan).
- 🎨 **Background images** — generated with **ChatGPT**.
- 🛡️ **Security loopholes and bugs** — found by **Claude** (an AI assistant by Anthropic), and fixed by me to make the system better.

---

## 💌 A Note from the Developer

CakeCraft isn't just a class project — it's the first slice of a bigger dream. What starts here as a Software Development Project submission is meant to grow into something that connects good code with good cake, and eventually, into a business built on both craftsmanship and passion.

---

## 📬 Contact

**Developer:** Md. Manam Khan
**GitHub:** [github.com/Md-Manam-Khan](https://github.com/Md-Manam-Khan)

---

<p align="center">Made with 🧁 and a lot of semicolons.</p>