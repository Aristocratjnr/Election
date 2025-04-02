# Election Management System (EMS)

## Overview
The **Election Management System (EMS)** is a web-based platform designed to streamline student elections, such as student council and class elections. EMS enhances efficiency, transparency, accessibility, and security in elections by leveraging modern web technologies.

## Features
- Secure user authentication
- Candidate registration and management
- Online voting system with real-time results
- Role-based access control (Admin, Candidates, Voters)
- SMS and email notifications for election updates
- Responsive UI for mobile and desktop users
- Audit logs and election history
- Election activation and deactivation

## System Summary

### **Admin Section**
1. Add, edit, and delete elections
2. Add, edit, and delete categories (positions) of elections
3. Add, edit, and delete candidates for each category
4. Activate and deactivate elections
5. View results of active elections
6. View and update profile
7. View, assign admin roles, and reset passwords for voters
8. Secure login authentication

### **Voter Section**
1. Secure login authentication
2. View the list of candidates for active elections
3. Cast votes in active elections
4. View voting choices after submission
5. View live results of active elections
6. View and update profile

## Technology Stack
### **Frontend:**
- HTML5
- CSS3
- JavaScript (ECMAScript 7)

### **Backend:**
- PHP 8.4

### **Database:**
- MySQL

### **Hosting:**
- Google Cloud

### **Security:**
- SSL Encryption

### **Notifications:**
- **Email:** PHPMailer
- **SMS:** Arkesel SMS Gateway

## Installation
### **Prerequisites**
Ensure you have the following installed:
- PHP 8.4
- MySQL Server
- Apache/Nginx Server

### **Steps**
1. **Clone the Repository**
   ```sh
   git clone https://github.com/aristocratjnr/Election.git
   cd Election
   ```
2. **Set Up the Database**
   - Create a MySQL database named `ems`.
   - Import the SQL file:
     ```sh
     mysql -u root -p ems < database/ems.sql
     ```
3. **Run the Application**
   - Place the project folder inside `htdocs` (C:/xampp/htdocs/Election).
   - Start XAMPP and ensure Apache and MySQL are running.
   - Open `http://localhost/Election` in your browser.

## Usage
### **Admin**
- Manage candidates, set election dates, and oversee the voting process.

### **Candidates**
- Register and view their election status.

### **Voters**
- Securely log in, cast votes, and view real-time election results.

## Contact
For inquiries, reach out to:
- **Email:** ayimobuobi@gmail.com
- **GitHub:** [aristocratjnr](https://github.com/aristocratjnr)
- **Website:** [My Portfolio](https://mynextjs-portfolio-nu.vercel.app)

