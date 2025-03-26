# Election Management System (EMS)

## Overview
The **Election Management System (EMS)** is a web-based platform designed to streamline the election process for student representatives, such as student council and class elections. By leveraging technology, EMS enhances efficiency, transparency, accessibility, and security in elections.

## Features
- Secure user authentication
- Candidate registration and management
- Online voting system with real-time results
- Role-based access control (Admin, Candidates, Voters(Students)
- SMS and email notifications for election updates
- Responsive UI for mobile and desktop users
- Audit logs and election history

## Technology Stack
### Frontend:
- HTML5
- CSS3
- JavaScript (ECMAScript 7)

### Backend:
- PHP 8.4

### Database:
- MySQL

### Hosting:
- Google Cloud

### Security:
- SSL Encryption

### Notifications:
- **Email:** PHPMailer
- **SMS:** Arkesel SMS Gateway

## Installation
### Prerequisites
Ensure you have the following installed:
- PHP 8.4
- MySQL Server
- Apache/Nginx Server
- Composer (for dependency management)

### Steps
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

5. **Run the Application**
   -Place the project folder inside htdocs (C:/xampp/htdocs/Election).
   -Start XAMPP and ensure Apache and MySQL are running.
   - Open http://localhost/Election in your browser.

## Usage
1. **Admin Login:** Manage candidates, set election dates, and oversee the voting process.
2. **Candidates:** Register and view their election status.
3. **Voters:** Cast votes securely and view real-time election results.



## Contact
For any inquiries, reach out to:
- **Email:** ayimobuobi@gmail.com
- **GitHub:** https://github.com/aristocratjnr
- **Website:** https://mynextjs-portfolio-nu.vercel.app
