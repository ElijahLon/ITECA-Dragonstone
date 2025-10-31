
# DragonStone E-Commerce Platform

**Final Year Computer Science Project**

A comprehensive PHP/MySQL web application demonstrating advanced web development concepts, database design, and e-commerce functionality. Built as a capstone project showcasing full-stack development skills.

## Project Overview

This project implements a complete e-commerce platform from scratch, covering the entire software development lifecycle from requirements analysis to deployment. It demonstrates proficiency in:

- **Backend Development**: PHP 8.0+ with object-oriented programming
- **Database Design**: MySQL schema with relationships and optimization
- **Frontend Development**: Responsive HTML5/CSS3 with Bootstrap 5
- **Security Implementation**: Authentication, authorization, and data protection
- **API Integration**: Payment gateway integration with Paystack
- **User Experience**: Modern UI/UX design principles

## Core Features Implemented

### 🛍️ E-Commerce Functionality
- **Product Catalog**: Dynamic product listings with categories and search
- **Shopping Cart**: Session-based cart with AJAX updates
- **User Authentication**: Secure login/register system with password hashing
- **Checkout Process**: Multi-step checkout with delivery options
- **Order Management**: Complete order lifecycle tracking
- **Payment Integration**: Real payment processing with Paystack API

### 👨‍💼 Admin Management System
- **Dashboard**: Analytics and system overview
- **User Management**: CRUD operations with role-based access
- **Product Management**: Full inventory control system
- **Order Processing**: Admin order management interface

### 🎨 Technical Implementation
- **MVC Architecture**: Separation of concerns with includes
- **Database Layer**: PDO/MySQLi with prepared statements
- **Security Layer**: Input validation, XSS protection, CSRF prevention
- **Session Management**: Secure user sessions with regeneration
- **File Uploads**: Image handling for products
- **AJAX Integration**: Dynamic content updates

## Technology Stack

| Component | Technology | Justification |
|-----------|------------|---------------|
| **Backend** | PHP 8.0+ | Server-side processing, extensive library support |
| **Database** | MySQL 5.7+ | Relational data management, ACID compliance |
| **Frontend** | HTML5, CSS3, JavaScript | Modern web standards, responsive design |
| **Framework** | Bootstrap 5 | Rapid UI development, mobile-first approach |
| **JavaScript** | jQuery | DOM manipulation, AJAX requests |
| **Payment** | Paystack API | South African payment processing |

## Database Design

### Schema Overview
```
users (id, email, password, role, created_at)
├── user_addresses (user_id, address, city, postal_code)
├── orders (user_id, total, status, transaction_ref)
│   └── order_items (order_id, product_id, quantity, price)
└── subscriptions (user_id, plan, next_charge, active)

products (id, name, price, category_id, stock, image)
├── categories (id, name, slug)
├── promos (code, discount_percent, expires_at)
└── remember_tokens (user_id, token, expires)
```

### Key Design Decisions
- **Normalization**: 3NF to reduce redundancy
- **Indexing**: Optimized for common queries
- **Relationships**: Foreign keys for data integrity
- **Security**: Hashed passwords, secure tokens

## Security Implementation

### Authentication & Authorization
- **Password Security**: bcrypt hashing with salt
- **Session Security**: Secure cookies, session regeneration
- **Role-Based Access**: Admin vs customer permissions
- **Remember Me**: Secure token-based persistent login

### Data Protection
- **SQL Injection**: Prepared statements throughout
- **XSS Prevention**: Input sanitization and output escaping
- **CSRF Protection**: Token validation on forms
- **Input Validation**: Server-side validation with regex

## Installation & Setup

### Prerequisites
- PHP 8.0+ with mysqli extension
- MySQL 5.7+ server
- Apache/Nginx web server
- Composer (optional, for dependencies)

### Quick Start
```bash
# 1. Clone repository
git clone https://github.com/yourusername/dragonstone.git
cd dragonstone

# 2. Set up database
mysql -u root -p
CREATE DATABASE dragonstone;
exit;

# 3. Import database structure
mysql -u root -p dragonstone < sql/schema.sql
mysql -u root -p dragonstone < sql/data.sql

# 4. Configure database connection
# Edit public/inc/db.php with your credentials

# 5. Start web server
# Point document root to public/ directory
```

### Configuration
```php
// public/inc/db.php
define('DB_HOST', 'localhost');
define('DB_USER', 'your_username');
define('DB_PASS', 'your_password');
define('DB_NAME', 'dragonstone');
```

## Project Structure

```
dragonstone/
├── admin/              # Administrative interface
│   ├── index.php       # Dashboard
│   ├── users.php       # User management
│   └── products.php    # Product management
├── public/             # Public web interface
│   ├── assets/         # Static resources
│   │   ├── css/        # Stylesheets
│   │   ├── js/         # JavaScript files
│   │   └── img/        # Images
│   ├── inc/            # PHP includes
│   │   ├── db.php      # Database connection
│   │   ├── auth.php    # Authentication functions
│   │   └── header.php  # Site header
│   ├── index.php       # Homepage
│   ├── login.php       # User login
│   ├── register.php    # User registration
│   ├── cart.php        # Shopping cart
│   ├── checkout.php    # Checkout process
│   └── product.php     # Product details
├── scripts/            # Database utilities
├── sql/               # Database files
│   ├── schema.sql     # Table structure
│   └── data.sql       # Sample data
└── README.md          # This documentation
```

## Development Process

### Methodology
- **Agile Development**: Iterative development with regular commits
- **Version Control**: Git with feature branches
- **Testing**: Manual testing of all user flows
- **Documentation**: Inline code comments and this README

### Key Challenges Solved
1. **Session Management**: Implementing secure persistent sessions
2. **Payment Integration**: Handling real payment processing
3. **Database Design**: Creating efficient, normalized schema
4. **Security**: Implementing comprehensive security measures
5. **Responsive Design**: Ensuring mobile compatibility

## Learning Outcomes

This project demonstrates mastery of:
- **Full-Stack Development**: End-to-end web application
- **Database Design**: Complex relational schemas
- **Security Best Practices**: Web application security
- **API Integration**: Third-party service integration
- **User Experience**: Modern web design principles
- **Project Management**: Complete software lifecycle

## Future Enhancements

Potential improvements for further development:
- **REST API**: Backend API for mobile app integration
- **Docker**: Containerized deployment
- **Unit Testing**: Automated test suite
- **Caching**: Redis for performance optimization
- **Multi-language**: Internationalization support
- **Advanced Analytics**: Detailed reporting system

## Academic Context

This project serves as a comprehensive demonstration of computer science concepts:
- **Data Structures**: Arrays, sessions, database relationships
- **Algorithms**: Search, sort, CRUD operations
- **Software Engineering**: Design patterns, MVC architecture
- **Web Technologies**: HTTP, sessions, cookies, AJAX
- **Security**: Cryptography, authentication, authorization

---

**Author**: [Your Name]  
**Course**: Computer Science  
**Year**: Final Year Project  
**Technologies**: PHP, MySQL, HTML5, CSS3, JavaScript  
**Grade Level**: Advanced Undergraduate
