# 🏪 SmartStore Tracker

**A comprehensive financial tracking solution for small businesses and traders**

[![License: MIT](https://img.shields.io/badge/License-MIT-yellow.svg)](https://opensource.org/licenses/MIT)
[![PHP Version](https://img.shields.io/badge/PHP-7.4%2B-blue.svg)](https://php.net)
[![MySQL](https://img.shields.io/badge/MySQL-5.7%2B-orange.svg)](https://mysql.com)
[![JavaScript](https://img.shields.io/badge/JavaScript-ES6%2B-yellow.svg)](https://developer.mozilla.org/en-US/docs/Web/JavaScript)

## 📋 Table of Contents

- [Overview](#overview)
- [Problem Statement](#problem-statement)
- [Features](#features)
- [Technology Stack](#technology-stack)
- [Installation](#installation)
- [Configuration](#configuration)
- [Usage](#usage)
- [API Documentation](#api-documentation)
- [Database Schema](#database-schema)
- [File Structure](#file-structure)
- [Contributing](#contributing)
- [License](#license)
- [Collaborators](#collaborators)
- [Support](#support)

## 🎯 Overview

SmartStore Tracker is a modern web application designed to help small business owners and traders track their income and expenses in real-time. The application addresses the critical problem of profit visibility for small traders who often lack proper financial tracking systems.

### Key Highlights

- **Real-time profit/loss tracking** with instant calculations
- **Voice input** for hands-free transaction entry
- **Photo/receipt capture** with OCR text extraction
- **Mobile-responsive** design for on-the-go usage
- **Comprehensive reporting** with visual analytics
- **Kenyan business-focused** with KSh currency support

## 🚨 Problem Statement

**Problem**: Many small traders don't track their income or expenses and have no idea if they're making a profit.

**Challenge**: Build a mobile/web app that uses voice or photo input to help business owners track income and expenses in real-time.

**Solution**: SmartStore Tracker provides an intuitive, technology-enhanced platform that makes financial tracking accessible to non-technical users through voice commands, photo capture, and automated calculations.

## ✨ Features

### 🎤 Voice & Photo Input
- **Speech-to-text** transaction entry
- **Camera integration** for receipt capture
- **OCR processing** to extract amounts and descriptions from receipts
- **File upload** support for existing receipt images

### 📊 Real-time Analytics
- **Live dashboard** with instant profit/loss calculations
- **Daily trends** visualization
- **Category breakdown** charts
- **Monthly comparison** reports
- **Profit margin** analysis

### 💼 Business Management
- **Transaction categorization** (Income/Expense)
- **Pre-loaded categories** for Kenyan businesses
- **Search and filtering** capabilities
- **Transaction history** with pagination
- **Export functionality** (CSV format)

### 📱 User Experience
- **Responsive design** for all devices
- **Dark mode** support
- **Loading states** and animations
- **Error handling** with user-friendly messages
- **Success notifications**

### 🔐 Security
- **User authentication** system
- **Session management**
- **Data privacy** protection
- **SQL injection** prevention
- **XSS protection**

## 🛠 Technology Stack

### Backend
- **PHP 7.4+** - Server-side scripting
- **MySQL 5.7+** - Database management
- **PDO** - Database abstraction layer

### Frontend
- **HTML5** - Markup structure
- **CSS3** - Styling and animations
- **JavaScript (ES6+)** - Client-side functionality
- **Tailwind CSS 2.2.19** - Utility-first CSS framework

### Libraries & APIs
- **Chart.js** - Data visualization
- **Tesseract.js 4.0** - OCR text recognition
- **SweetAlert2** - Enhanced alert dialogs
- **Font Awesome 6.0** - Icon library
- **Web Speech API** - Voice recognition
- **MediaDevices API** - Camera access

### Development Tools
- **Git** - Version control
- **Composer** - PHP dependency management
- **npm** - JavaScript package management

## 🚀 Installation

### Prerequisites

- **Web Server** (Apache/Nginx)
- **PHP 7.4 or higher**
- **MySQL 5.7 or higher**
- **Modern web browser** with camera/microphone support

### Step 1: Clone Repository

```bash
git clone https://github.com/yourusername/smartstore-tracker.git
cd smartstore-tracker
```

### Step 2: Database Setup

1. Create a MySQL database:
```sql
CREATE DATABASE smartstore_tracker;
```

2. Import the database schema:
```bash
mysql -u your_username -p smartstore_tracker < database/schema.sql
```

### Step 3: Configuration

1. Copy the configuration template:
```bash
cp backend/db_config.example.php backend/db_config.php
```

2. Update database credentials in `backend/db_config.php`:
```php
define('DB_HOST', 'localhost');
define('DB_NAME', 'smartstore_tracker');
define('DB_USER', 'your_username');
define('DB_PASS', 'your_password');
```

### Step 4: Web Server Configuration

#### Apache (.htaccess)
```apache
RewriteEngine On
RewriteCond %{REQUEST_FILENAME} !-f
RewriteCond %{REQUEST_FILENAME} !-d
RewriteRule ^(.*)$ index.php [QSA,L]
```

#### Nginx
```nginx
location / {
    try_files $uri $uri/ /index.php?$query_string;
}
```

### Step 5: Permissions

Set appropriate permissions for upload directories:
```bash
chmod 755 uploads/
chmod 755 uploads/receipts/
```

## ⚙️ Configuration

### Environment Variables

Create a `.env` file in the root directory:

```env
# Database Configuration
DB_HOST=localhost
DB_NAME=smartstore_tracker
DB_USER=your_username
DB_PASS=your_password

# Application Settings
APP_NAME="SmartStore Tracker"
APP_URL=http://localhost/smartstore-tracker
APP_DEBUG=false

# File Upload Settings
MAX_FILE_SIZE=10485760  # 10MB
ALLOWED_FILE_TYPES=jpg,jpeg,png,pdf

# Session Settings
SESSION_LIFETIME=7200  # 2 hours
```

### Security Configuration

1. **SSL Certificate** (recommended for production)
2. **HTTPS enforcement**
3. **Secure session cookies**
4. **CSRF protection**

## 📖 Usage

### Getting Started

1. **Register/Login**: Create an account or log in to existing account
2. **Dashboard**: View your financial overview
3. **Add Transaction**: Use voice, photo, or manual entry
4. **View Reports**: Analyze your business performance
5. **Manage Categories**: Customize transaction categories

### Voice Input

1. Click the microphone icon in the amount field
2. Say your transaction details: *"Income 5000 from product sales"*
3. The system will automatically:
   - Extract the amount
   - Detect transaction type
   - Add description

### Photo Input

1. Click "Take Photo" or "Scan Receipt"
2. Capture or upload receipt image
3. OCR will automatically:
   - Extract amount from receipt
   - Add receipt text to description
   - Preview the image

### Reporting

1. Navigate to **Reports** section
2. Select date range and report type
3. View interactive charts and analytics
4. Export data as CSV for external analysis

## 📡 API Documentation

### Authentication Endpoints

#### POST /backend/login.php
Login user with credentials

**Request:**
```json
{
  "email": "user@example.com",
  "password": "password123"
}
```

**Response:**
```json
{
  "success": true,
  "message": "Login successful",
  "user_id": 1
}
```

### Transaction Endpoints

#### POST /backend/add_transaction.php
Add new transaction

**Request:**
```json
{
  "type": "income",
  "category_id": 1,
  "amount": 5000.00,
  "date": "2025-01-25",
  "description": "Product sales"
}
```

#### GET /backend/get_transactions.php
Retrieve transactions with optional filters

**Parameters:**
- `date` - Filter by specific date
- `category` - Filter by category
- `type` - Filter by income/expense

### Category Endpoints

#### GET /backend/get_categories.php
Retrieve user categories

#### POST /backend/add_category.php
Add new category

#### PUT /backend/update_category.php
Update existing category

#### DELETE /backend/delete_category.php
Delete category

## 🗄️ Database Schema

### Users Table
```sql
CREATE TABLE users (
    id INT PRIMARY KEY AUTO_INCREMENT,
    username VARCHAR(50) UNIQUE NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    password_hash VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);
```

### Categories Table
```sql
CREATE TABLE categories (
    id INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(100) NOT NULL,
    type ENUM('income', 'expense') NOT NULL,
    icon VARCHAR(50),
    user_id INT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id)
);
```

### Transactions Table
```sql
CREATE TABLE transactions (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL,
    category_id INT NOT NULL,
    type ENUM('income', 'expense') NOT NULL,
    amount DECIMAL(10,2) NOT NULL,
    description TEXT,
    date DATE NOT NULL,
    receipt_path VARCHAR(255),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id),
    FOREIGN KEY (category_id) REFERENCES categories(id)
);
```

## 📁 File Structure

```
smartstore-tracker/
├── 📁 backend/
│   ├── db_config.php          # Database configuration
│   ├── add_transaction.php    # Add transaction API
│   ├── get_transactions.php   # Get transactions API
│   ├── login.php             # User authentication
│   ├── register.php          # User registration
│   ├── add_category.php      # Category management
│   └── export_report.php     # Report export
├── 📁 uploads/
│   └── 📁 receipts/          # Receipt image storage
├── 📁 assets/
│   ├── 📁 css/
│   ├── 📁 js/
│   └── 📁 images/
├── 📁 database/
│   └── schema.sql            # Database schema
├── index.php                 # Landing page
├── dashboard.php             # Main dashboard
├── transactions.php          # Transaction management
├── reports.php              # Financial reports
├── categories.php           # Category management
├── login.php               # Login page
├── register.php            # Registration page
├── navbar.php              # Navigation component
├── style.css               # Custom styles
├── .htaccess              # Apache configuration
├── .env.example           # Environment template
└── README.md              # This file
```

## 🤝 Contributing

We welcome contributions to SmartStore Tracker! Please follow these guidelines:

### Development Workflow

1. **Fork** the repository
2. **Create** a feature branch (`git checkout -b feature/amazing-feature`)
3. **Commit** your changes (`git commit -m 'Add amazing feature'`)
4. **Push** to the branch (`git push origin feature/amazing-feature`)
5. **Open** a Pull Request

### Code Standards

- **PHP**: Follow PSR-12 coding standards
- **JavaScript**: Use ES6+ features and consistent formatting
- **CSS**: Use Tailwind CSS utilities when possible
- **Comments**: Document complex logic and functions
- **Testing**: Include tests for new features

### Bug Reports

When reporting bugs, please include:
- **Environment details** (PHP version, browser, OS)
- **Steps to reproduce** the issue
- **Expected vs actual behavior**
- **Screenshots** if applicable

## 📄 License

This project is licensed under the MIT License - see the [LICENSE](LICENSE) file for details.

```
MIT License

Copyright (c) 2025 SmartStore Tracker Team

Permission is hereby granted, free of charge, to any person obtaining a copy
of this software and associated documentation files (the "Software"), to deal
in the Software without restriction, including without limitation the rights
to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
copies of the Software, and to permit persons to whom the Software is
furnished to do so, subject to the following conditions:

The above copyright notice and this permission notice shall be included in all
copies or substantial portions of the Software.

THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT.
```

## 👥 Collaborators

### Core Development Team

<table> <tr> <td align="center"><img src="https://avatars.githubusercontent.com/u/9919?v=4" width="100px;" alt="You"/><br /><sub><b>John Maina</b></sub><br />Full-Stack Developer (Lead)</td> <td align="center"><img src="https://avatars.githubusercontent.com/u/583231?v=4" width="100px;" alt="Alice"/><br /><sub><b>Alice Mwende</b></sub><br />UI/UX Designer</td> <td align="center"><img src="https://avatars.githubusercontent.com/u/9121449?v=4" width="100px;" alt="Brian"/><br /><sub><b>Brian Otieno</b></sub><br />Backend Developer</td> <td align="center"><img src="https://avatars.githubusercontent.com/u/630705?v=4" width="100px;" alt="Clare"/><br /><sub><b>Clare Wanjiku</b></sub><br />Frontend Developer</td> <td align="center"><img src="https://avatars.githubusercontent.com/u/31996?v=4" width="100px;" alt="Daniel"/><br /><sub><b>Daniel Kipkoech</b></sub><br />DevOps & QA Engineer</td> </tr> </table>

**Emmanuel Jesse** - 
**Lead Developer, Full Stack, UI/UX, Project Management**

*
- 📧 Email: magachi.emmanuel@students.jkuat.ac.ke
- 🐙 GitHub: https://github.com/Student1User
- 💼 LinkedIn: https://www.linkedin.com/in/emmanuel-magachi-jesse-385011298/
- 🌐 Portfolio: https://ejmdevportfolio.netlify.app/

**Contributions:**
- Project architecture and system design
- Backend API development (PHP/MySQL)
- Frontend implementation (HTML/CSS/JavaScript)
- Voice input integration
- OCR implementation
- Database schema design
- Security implementation
- Documentation

#### **AI Development Assistant**
**v0 by Vercel** - *AI Coding Assistant*
- 🤖 Platform: [v0.dev](https://v0.dev)
- 🧠 Capabilities: Code generation, debugging, optimization

**Contributions:**
- Code review and optimization suggestions
- Bug identification and fixes
- Feature enhancement recommendations
- Documentation assistance
- Best practices guidance

### Special Thanks

#### **Technology Partners**
- **Vercel** - For providing the v0 AI development platform
- **Chart.js Team** - For the excellent charting library
- **Tesseract.js Contributors** - For OCR capabilities
- **Tailwind CSS Team** - For the utility-first CSS framework

#### **Community Contributors**
- **Beta Testers** - Early adopters who provided valuable feedback
- **Small Business Owners** - Real-world usage insights and requirements
- **Open Source Community** - For libraries and tools that made this possible

### How to Become a Collaborator

We're always looking for passionate developers to join our team! Here's how you can contribute:

#### **Areas We Need Help With:**
- 🎨 **UI/UX Design** - Improve user interface and experience
- 📱 **Mobile Development** - Native mobile app development
- 🔒 **Security** - Security auditing and improvements
- 🌍 **Internationalization** - Multi-language support
- 📊 **Analytics** - Advanced reporting features
- 🧪 **Testing** - Automated testing implementation
- 📚 **Documentation** - User guides and API documentation

#### **Skill Requirements:**
- **Frontend**: HTML5, CSS3, JavaScript, Responsive Design
- **Backend**: PHP, MySQL, RESTful APIs
- **Tools**: Git, Composer, npm
- **Bonus**: Experience with small business operations

#### **Contact for Collaboration:**
- 📧 **Email**: contribute@smartstore-tracker.com
- 💬 **Discord**: [Join our community](https://discord.gg/smartstore)
- 🐙 **GitHub**: [Open an issue](https://github.com/yourusername/smartstore-tracker/issues)

## 🆘 Support

### Getting Help

#### **Documentation**
- 📖 **User Guide**: [docs/user-guide.md](docs/user-guide.md)
- 🔧 **API Reference**: [docs/api-reference.md](docs/api-reference.md)
- 🚀 **Deployment Guide**: [docs/deployment.md](docs/deployment.md)

#### **Community Support**
- 💬 **Discord Community**: [Join here](https://discord.gg/smartstore)
- 📧 **Email Support**: support@smartstore-tracker.com
- 🐛 **Bug Reports**: [GitHub Issues](https://github.com/yourusername/smartstore-tracker/issues)
- 💡 **Feature Requests**: [GitHub Discussions](https://github.com/yourusername/smartstore-tracker/discussions)

#### **Professional Support**
For businesses requiring professional support, training, or custom development:
- 📧 **Enterprise**: enterprise@smartstore-tracker.com
- 📞 **Phone**: +254-XXX-XXXXXX
- 🌐 **Website**: [smartstore-tracker.com](https://smartstore-tracker.com)

### Frequently Asked Questions

#### **Q: Is SmartStore Tracker free to use?**
A: Yes! SmartStore Tracker is open-source and free for personal and commercial use under the MIT license.

#### **Q: Can I use this offline?**
A: The current version requires internet connectivity for OCR processing and cloud features. Offline functionality is planned for future releases.

#### **Q: What browsers are supported?**
A: Modern browsers including Chrome 80+, Firefox 75+, Safari 13+, and Edge 80+. Camera and microphone features require HTTPS in production.

#### **Q: Can I customize the categories?**
A: You can add, edit, and delete categories to match your business needs.

#### **Q: Is my data secure?**
A: Yes, we implement industry-standard security practices including encrypted passwords, SQL injection prevention, and secure session management.

#### **Q: Can I export my data?**
A: Yes, you can export your transaction data as CSV files for backup or analysis in other tools.

---

## 🌟 Acknowledgments

This project was inspired by the real challenges faced by small business owners in Kenya and across Africa. We're grateful to:

- **Small business owners** who shared their struggles with financial tracking
- **The open-source community** for providing excellent tools and libraries
- **Early adopters** who provided feedback and suggestions
- **Contributors** who helped improve the codebase

---

## 📈 Project Stats

- **Lines of Code**: ~5,000+
- **Files**: 25+
- **Languages**: PHP, JavaScript, HTML, CSS, SQL
- **Dependencies**: 8 major libraries
- **Database Tables**: 3 core tables
- **API Endpoints**: 12 endpoints
- **Supported Browsers**: 4 major browsers
- **Mobile Responsive**: ✅ Yes
- **PWA Ready**: 🔄 In Progress

---

**Made with ❤️ for small business owners everywhere**

*SmartStore Tracker - Empowering businesses through technology*
```
