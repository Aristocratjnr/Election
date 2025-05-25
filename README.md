# Election Management System (EMS)

## Overview
The **Election Management System (EMS)** is a web-based platform designed to streamline student elections, such as student council and class elections. EMS enhances efficiency, transparency, accessibility, and security in elections by leveraging modern web technologies.

## Features
- Secure user authentication
- Candidate registration and management
- Online voting system with real-time results
- Blockchain technology for tamper-proof vote security and verification
- Role-based access control (Admin, Candidates, Voters)
- SMS and email notifications for election updates
- Responsive UI for mobile and desktop users
- Audit logs and election history
- Election activation and deactivation
- Progressive Web App (PWA) integration for offline access and mobile installation

## System Summary

### **Admin Section**
1. Add, edit, and delete elections
2. Add, edit, and delete categories (positions) of elections
3. Add, edit, and delete candidates for each category
4. Activate and deactivate elections
5. View results of active elections
6. View and update profile
7. View, assign admin roles, and reset passwords for voters
8. Configure and monitor blockchain vote verification
9. Validate election integrity through blockchain verification
10. Secure login authentication

### **Voter Section**
1. Secure login authentication
2. View the list of candidates for active elections
3. Cast votes in active elections with blockchain security
4. View voting choices after submission
5. Verify personal vote through blockchain verification
6. View live results of active elections
7. Learn about blockchain voting security
8. View and update profile

## Technology Stack
### **Frontend:**
- HTML5
- CSS3
- Bootstrap
- JavaScript (ECMAScript 7)
- Progressive Web App (PWA) features
- Service Workers for offline functionality

### **Backend:**
- PHP 8.4

### **Database:**
- MySQL

### **Hosting:**
- Google Cloud

### **Security:**
- Blockchain technology for tamper-proof voting records
- Vote verification through cryptographic hash chains
- SSL Encryption
- Reset Password linked via Mail
- Google Authenticator for 2FA

### **Notifications:**
- **Email:** PHPMailer
- **Device:** Customized api for push notifications with sound.
- **SMS:** Arkesel SMS Gateway

## Installation
### **Prerequisites**
Ensure you have the following installed:
- PHP 8.2
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
   - Set up the blockchain database table:
     ```sh
     php database/blockchain_migration.php
     ```
3. **Configure Blockchain (Optional)**
   - By default, the blockchain functionality is enabled
   - You can modify blockchain settings in `admin_blockchain_setup.php`
   - The blockchain system uses SHA-256 hashing which is supported by default in PHP
   - For high-volume elections, consider optimizing your MySQL configuration:
     ```sh
     # Add to my.cnf for better blockchain performance
     innodb_buffer_pool_size = 256M
     innodb_log_file_size = 64M
     ```

4. **Run the Application**
   - Place the project folder inside `htdocs` (C:/xampp/htdocs/Election).
   - Start XAMPP and ensure Apache and MySQL are running.
   - Open `http://localhost/Election` in your browser.
   - Log in as admin and navigate to "Blockchain Setup" to verify the blockchain system is functioning correctly.

## Usage
### **Admin**
- Manage candidates, set election dates, and oversee the voting process.

### **Candidates**
- Register and view their election status.

### **Voters**
- Securely log in, cast votes, and view real-time election results.
- Install as a mobile app via the PWA functionality for convenient access.

## Blockchain Security
The Election Management System utilizes blockchain technology to secure the voting process, enhancing transparency and integrity. This cutting-edge approach provides several important benefits:

### **Features:**
- **Tamper-proof Records:** Each vote is secured as a block in an immutable chain, making it impossible to alter votes without detection
- **Vote Verification:** Voters can independently verify that their vote was correctly recorded and remains unchanged
- **Transparency:** The blockchain provides a transparent record of all votes while preserving voter anonymity
- **Cryptographic Security:** Uses SHA-256 hashing algorithm and proof of work to ensure data integrity
- **Real-time Validation:** The entire blockchain can be validated at any time to verify election integrity
- **Voter Privacy:** Uses one-way hashing with salt to protect voter identities

### **Implementation:**
- Genesis blocks are automatically created for each new election
- Votes are securely recorded in the blockchain with cryptographic validation
- Administrative interface for monitoring blockchain health and integrity
- Educational resources to help users understand blockchain security
- User-friendly verification interface for voters to verify their ballots

### **Technical Details:**
- **Proof of Work:** Implements mining algorithm to secure each block
- **Block Structure:** Contains vote data, previous hash, timestamp, and nonce
- **Chain Validation:** Complete chain integrity checking with O(n) complexity
- **Database Integration:** Blockchain blocks stored in dedicated MySQL table
- **Vote Anonymization:** Student IDs are never directly stored in the blockchain
- **IP Tracking:** Securely hashes IP addresses for audit purposes without exposing raw data

## Progressive Web App (PWA)
The Election Management System implements PWA technology, providing the following benefits:

### **Features:**
- **Offline Access:** Access key features even with poor or no internet connection.
- **Installation:** Install SmartVote as an app on mobile devices and desktops.
- **App-like Experience:** Enjoy a native app-like experience with full-screen mode and home screen access.
- **Automatic Updates:** Always get the latest version without manual updates.
- **Improved Performance:** Faster loading times and smoother interactions through cached resources.

### **How to Install:**
1. Open the application in a modern browser (Chrome, Edge, Safari, etc.)
2. Look for the "Install App" button in the interface
3. Follow the on-screen prompts to install the application
4. Access SmartVote directly from your device's home screen or app drawer

## Blockchain FAQs and Troubleshooting

### **Frequently Asked Questions**

**Q: How does blockchain ensure vote security?**  
A: Each vote is recorded as a block in the chain with a unique cryptographic hash. The hash is dependent on the vote data and the previous block's hash, creating an immutable chain. Any attempt to modify a vote would invalidate the entire chain.

**Q: Is my vote anonymous?**  
A: Yes. Your identity is protected through one-way hashing with a random salt. This means your vote is verifiable by you but cannot be traced back to your identity by others.

**Q: How can I verify my vote was counted correctly?**  
A: After voting, you'll receive a vote ID. You can use this ID on the blockchain verification page to confirm your vote was recorded correctly and remains unmodified.

**Q: Does implementing blockchain slow down the voting process?**  
A: The blockchain operations occur in the background after your vote is submitted, so there's no noticeable delay in the voting experience.

### **Troubleshooting**

**Blockchain Verification Issues:**
- If you cannot verify your vote, ensure you're using the correct vote ID
- Check that you're selecting the correct election in the verification interface
- Try clearing your browser cache and trying again

**For Administrators:**
- If blockchain validation fails, check the database connection
- Ensure PHP has sufficient memory allocation for handling large blockchains
- Regular database backups are recommended to preserve blockchain integrity
- Use the admin blockchain setup page to monitor system health

## Blockchain Architecture
The blockchain implementation in the Election Management System follows a carefully designed architecture to ensure security, performance, and maintainability.

### **Core Components:**
1. **Blockchain Class (`classes/Blockchain.php`):**
   - Core implementation of blockchain functionality
   - Methods for blockchain creation, validation, and vote addition
   - Proof of work algorithm implementation
   - Block integrity verification

2. **Blockchain Database (`blockchain_blocks` Table):**
   - Stores all blocks in the blockchain
   - Indexes for efficient querying and validation
   - Maintains chain references through previous_hash field

3. **Verification Interface (`blockchain_verify.php`):**
   - User interface for blockchain exploration and validation
   - Tools to verify individual votes
   - Blockchain statistics and health monitoring
   - Chain integrity validation tools

4. **Admin Management (`admin_blockchain_setup.php`):**
   - Enable/disable blockchain functionality
   - Monitor blockchain performance
   - Perform integrity checks and system diagnostics
   - Educational resources for administrators

5. **Educational Resources (`blockchain_learn.php`):**
   - Explanations of blockchain technology for users
   - Visual aids and diagrams
   - FAQs and benefits explanation
   - Security measures documentation

### **Data Flow:**
1. User casts vote through the standard interface
2. Vote is recorded in the traditional database tables
3. Vote data is simultaneously passed to the blockchain system
4. Blockchain system creates a new block with vote data
5. Block is secured with proof of work algorithm
6. Block is added to the blockchain with links to previous blocks
7. User receives verification information for their vote

### **Security Measures:**
- Vote data is anonymized through one-way hashing
- Each block is secured with SHA-256 cryptographic hashing
- Proof of work algorithm prevents tampering
- Full chain validation available at any time
- Vote verification without compromising voter privacy

## Contact
For inquiries, reach out to:
- **Email:** ayimobuobi@gmail.com
- **GitHub:** [aristocratjnr](https://github.com/aristocratjnr)
- **Website:** [My Portfolio](https://mynextjs-portfolio-nu.vercel.app)

