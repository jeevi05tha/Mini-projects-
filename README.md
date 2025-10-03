# Mini-projects-
Real Estate Explorer 🏠
A modern, responsive web application for real estate property management and transactions. Built with PHP, MySQL, and Bootstrap, this platform allows users to browse properties by city, view detailed listings, and complete property transactions seamlessly.

✨ Features:
User Features:
             •	City-based Property Browsing: Explore properties organized by popular cities
             •	Interactive Property Cards: Click on any property to view details and initiate transactions
             •	Responsive Design: Optimized for desktop, tablet, and mobile devices
             •	Real-time Availability: Properties are automatically marked as unavailable after purchase
             •	Smooth User Experience: Modern UI with hover effects and smooth scrolling
Admin Features:
             •	City Management: Add new cities to the platform
             •	Property Management: Add new property listings with details like type, mode (Sale/Rent), and pricing
             •	Data Overview: View all cities and properties in organized tables
             •	Real-time Updates: Property availability updates automatically
             
🛠️ Tech Stack:
             •	Frontend: HTML5, CSS3, JavaScript, Bootstrap 5.3.0
             •	Backend: PHP 7.4+
             •	Database: MySQL 5.7+
             •	Styling: Custom CSS with Bootstrap framework
             •	Icons & Fonts: Segoe UI font family
             
📋 Prerequisites:
             •	Web Server: Apache/Nginx with PHP support
             •	PHP: Version 7.4 or higher
             •	MySQL: Version 5.7 or higher
             •	XAMPP/WAMP/LAMP: Recommended for local development

             
🚀 Installation & Setup:

1. Clone the Repository
```bash
git clone https://github.com/yourusername/real-estate-explorer.git
cd real-estate-explorer
```

2. Database Setup:
Create a MySQL database named `dbms` and execute the provided SQL to set up tables.

-- Create database
CREATE DATABASE dbms;
USE dbms;

-- Cities table
CREATE TABLE city (
    id INT AUTO_INCREMENT PRIMARY KEY,
    city_name VARCHAR(100) NOT NULL,
    no_of_availabilities INT DEFAULT 0
);

-- Availabilities (Properties) table
CREATE TABLE availabitities (
    a_id INT AUTO_INCREMENT PRIMARY KEY,
    id INT NOT NULL,
    type VARCHAR(50) NOT NULL,
    mode VARCHAR(20) NOT NULL,
    rate DECIMAL(12,2) NOT NULL,
    available TINYINT(1) DEFAULT 1,
    FOREIGN KEY (id) REFERENCES city(id)
);

-- Transactions table
CREATE TABLE transactions (
    t_id INT AUTO_INCREMENT PRIMARY KEY,
    cust_name VARCHAR(100) NOT NULL,
    city_name VARCHAR(100) NOT NULL,
    rate DECIMAL(12,2) NOT NULL,
    a_id INT NOT NULL,
    transaction_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (a_id) REFERENCES availabitities(a_id)
);

4. Configure Database Connection: Update db_connect.php with your credentials.
   $servername = "localhost";
   $username = "your_db_username";    // Replace with your DB username
   $password = "your_db_password";    // Replace with your DB password
   $dbname = "dbms";
   
5. Add City Images: Place images like `mumbai.jpg`, `delhi.jpg`, etc. in root.
   Format: cityname.jpg (lowercase)
   Example: mumbai.jpg, delhi.jpg, bangalore.jpg
   Recommended size: 300x180px for optimal display
   
6. Background Image: Add `background.webp` for hero section.
   
7. Start the Server: Use XAMPP/WAMP or `php -S localhost:8000`.
   
8. Access the Application: Navigate to the appropriate URL.

   
📁 Project Structure:

real-estate-explorer/
├── index.php              # Main application homepage
├── admin.php              # Admin dashboard for management
├── db_connect.php         # Database connection configuration
├── transaction.php        # Transaction processing script
├── background.webp        # Hero section background image
├── [city-name].jpg        # City-specific images
└── README.md              # Project documentation

🎯 Usage:

For Users:
1. Browse Cities: View available cities on the homepage
2. Explore Properties: Click on any city to see available properties
3. Select Property: Click on a property card to auto-fill the transaction form
4. Complete Purchase: Fill in your details and submit the transaction

For Administrators (Official):
1. Official Login: Navigate to `/login.php` (credentials below)
2. Official Dashboard: After login, go to `/dashboard.php` to view KPIs and recent transactions
3. Admin Panel: Manage data at `/admin.php` (unchanged)
4. Logout: Use `/logout.php`

MCA eConsultation Mirror:
- Visit `/mca.php` to view the live MCA eConsultation page in-app. Relative links are resolved to the MCA domain via a `<base>` tag.

Official Credentials (default):
```text
username: official
password: official123
```
   
🔧 Configuration:
Database Tables:
- city: Stores city information and availability counts
- availabitities: Stores property listings with availability status
- transactions: Records all completed property transactions

Key Features:
- Automatic Availability Management: Properties are marked unavailable after purchase
- Data Validation: Server-side validation for all form inputs
- Responsive Design: Mobile-first approach with Bootstrap framework
- Security: Prepared statements to prevent SQL injection
  
🤝 Contributing:
1. Fork the repository
2. Create a feature branch (`git checkout -b feature/AmazingFeature`)
3. Commit your changes (`git commit -m 'Add some AmazingFeature'`)
4. Push to the branch (`git push origin feature/AmazingFeature`)
5. Open a Pull Request

🐛 Known Issues
- Ensure city images are properly named and placed in the root directory
- Database connection must be properly configured before first use
- Property availability is managed automatically - manual updates may require admin intervention


🚀 Future Enhancements:
- [ ] User authentication system
- [ ] Property image gallery
- [ ] Advanced search and filtering
- [ ] Email notifications for transactions
- [ ] Property comparison feature
- [ ] Integration with payment gateways
- [ ] Mobile app development

      
📞 Support:
For support, email [narayankanakuru76@gmail.com] or create an issue in this repository.
Made with ❤️ for Real Estate Management

