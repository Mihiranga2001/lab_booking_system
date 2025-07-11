# 🧪 Lab Booking System

This is a **database management system (DBMS) project** for managing university laboratory operations, built with **PHP**, **MySQL**, and **CSS**. The system offers role-based access for Instructors, Students, Lab Technical Officers, and Lecturers in Charge.

---

## 🔧 Technologies Used

* **PHP**
* **MySQL (phpMyAdmin)**
* **HTML/CSS**
* **XAMPP** (Apache & MySQL)

---

## 👤 User Roles & Functionalities

🧑 **Student**

* Log in
* view lab schedules

🧑‍🏫 **Instructor**

* Log in
* Request lab bookings
* View and track booking approvals
* View lab schedules

🧑‍💼 **Lab Technical Officer**

* Log in
* Approve or reject lab booking requests
* View lab and equipment details

👨‍🎓 **Lecture-in-Charge**

* Log in
* View lab schedules
* Monitor lab usage logs

---

## 🗃️ Folder Structure

```
lab_booking_system/
├── db_connect.php
├── index.php
├── index.html
├── logout.php
├── images/
├── login/
├── register/
├── labs/
├── bookings/
├── schedules/
├── usage_logs/
├── approval/
├── reports/
└── style.css
```

---

## 🚀 How to Run Locally

1. **Install XAMPP**
2. **Place the `lab_booking_system` folder** in `htdocs`
3. **Start Apache and MySQL**
4. **Create a MySQL database**

   * Open **phpMyAdmin**
   * Import your SQL schema (e.g., `lab_booking_system.sql`)
5. **Access the system:**

   ```
   http://localhost/lab_booking_system
   ```

---

## 📸 Screenshots

Screenshots of login screens, dashboards, and key modules are available in the `Screenshots` folder *(create this folder to store images)*.

---

## 📂 Database

The system uses MySQL with these main tables:

* `Student`
* `Instructor`
* `Lab_TO`
* `Lecture_in_charge`
* `Lab`
* `Lab_Booking`
* `Lab_Equipment`
* `instructor_book_booking`
* Relationship tables for bookings and schedules

---

## ✅ Features

* Role-based access control for 4 user types
* Real-time lab availability and booking
* Approval workflow for lab requests
* Lab and equipment tracking
* Usage logs
* Responsive, modern UI design

---

## 🔒 Security

* Password hashing (`password_hash()`)
* Session-based authentication
* Input validation and sanitization

---

## 📌 Future Improvements

* Email/SMS notifications for bookings and approvals
* Calendar integration for schedules
* Enhanced analytics dashboards
* Multi-lab support and grouping
* Auto-reminders for upcoming reservations

---

## 📬 Author

GitHub: [https://github.com/Mihiranga2001](https://github.com/Mihiranga2001)
University: **University of Jaffna**

