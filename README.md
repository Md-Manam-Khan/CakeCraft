# 🍰 CakeCraft

**Design it. Order it. Track it — one slice at a time.**

CakeCraft is a cake ordering platform being built as a full-stack web application, with the long-term vision of growing into a complete cake design, ordering, and delivery-tracking experience — and eventually, a cross-platform mobile app and a real business.

> 🎓 Built as a project for **CSE 364 – Software Development Project 1** (Level 3, Term 2)
> 👨‍💻 Developed by [Md. Manam Khan](https://github.com/Md-Manam-Khan)

---

## ✨ About the Project

CakeCraft started as a simple idea: what if ordering a custom cake felt as easy — and as delightful — as designing one? This project is the foundation of that idea, currently focused on building a solid, secure user authentication system as the base for everything to come.

The bigger picture:

- 🎂 Customers will be able to **design their dream cake**, place an order, and pay online.
- 👨‍🍳 An **admin panel** will let the operator (also the chef!) view incoming orders and update their status.
- 📦 Order progress will move through clear stages: **Received → Baking → Arriving → Delivered.**
- 🗄️ All data — users, orders, statuses — will be stored and managed through a MySQL database.
- 📱 Down the road, this project aims to expand into a **Flutter-powered mobile app**, making CakeCraft accessible anywhere.
- 💼 Beyond the classroom, CakeCraft is meant to grow into a **real business** — a space to combine software craftsmanship with a genuine passion for baking.

---

## 🚀 Current Features

- ✅ Clean, responsive **landing page**
- ✅ Secure **Sign Up** with hashed passwords (`password_hash`)
- ✅ Secure **Sign In** supporting login via ID Name, Email, or Phone Number
- ✅ Duplicate-account prevention (unique email & ID name checks)
- ✅ Fully responsive design (mobile-friendly down to 600px breakpoint)
- ✅ Custom "3D press" button interactions across the site
- ✅ Per-page themed backgrounds (Home / Sign In / Sign Up)

---

## 🛠️ Tech Stack

| Layer      | Technology                          |
|------------|--------------------------------------|
| Frontend   | HTML5, CSS3                          |
| Backend    | PHP (procedural, `mysqli`)           |
| Database   | MySQL                                |
| Server     | Apache (via XAMPP)                   |
| Future     | Dart & Flutter (planned mobile app)  |

---

## 📁 Project Structure

```
CakeCraft/
├── index.html        # Landing page
├── signin.php         # Login logic + form
├── signup.php         # Registration logic + form
├── config.php         # Database connection (mysqli)
├── style.css          # Shared stylesheet
├── users.sql          # Database schema & export
├── cakes-bg.png        # Homepage background
├── signin-bg.jpg       # Sign-in page background
└── signup-bg.jpg       # Sign-up page background
```

---

## 🗄️ Database Schema

**Database:** `CakeCraft`
**Table:** `users`

| Column       | Type          | Notes                          |
|--------------|---------------|----------------------------------|
| `id`         | INT, AUTO_INCREMENT | Primary Key               |
| `full_name`  | VARCHAR(100)  | Not null                       |
| `email`      | VARCHAR(100)  | Not null, unique                |
| `phone`      | VARCHAR(20)   | Not null                       |
| `id_name`    | VARCHAR(50)   | Not null, unique                |
| `password`   | VARCHAR(255)  | Hashed via `password_hash()`    |
| `created_at` | TIMESTAMP     | Defaults to current timestamp   |

---

## ⚙️ Getting Started (Local Setup)

1. Install [XAMPP](https://www.apachefriends.org/) and start **Apache** and **MySQL**.
2. Clone or copy this project into your `htdocs` folder:
   ```
   C:\xampp\htdocs\CakeCraft
   ```
3. Open **phpMyAdmin**, create a database named `CakeCraft`, and import `users.sql`.
4. Visit the project in your browser:
   ```
   http://localhost/CakeCraft/index.html
   ```
5. Sign up for a new account, then sign in to test the flow!

---

## 🗺️ Roadmap

- [x] User authentication (sign up / sign in)
- [ ] Cake design & customization interface
- [ ] Order placement & online payment
- [ ] Admin panel for order management
- [ ] Order status tracking (Received → Baking → Arriving → Delivered)
- [ ] Customer order history
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