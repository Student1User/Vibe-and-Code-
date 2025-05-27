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

## 👥 Collaborators  
Core Development Team
## 👥 Collaborators  
Core Development Team

| Avatar | Name & Role |
|--------|-------------|
| [![](https://github.com/Student1User.png?size=100)](https://github.com/Student1User) | **Emmanuel Jesse**  <br> Lead Developer, Full Stack, UI/UX, Project Management|
| [![](https://github.com/Isaac-mutuma.png?size=100)](https://github.com/Isaac-mutuma) | **Isaac Mutuma**  <br> Backend Developer, API Intergrations Specilist |
| [![](/https://github.com/Hlawutelo.png?size=100)](https://github.com/Hlawutelo) | **Hlawutelo Ntsanwisi**  <br> Technical Writer & Product Researcher |
| [![](https://github.com/WinnieMwangi-dot.png?size=100)](https://github.com/WinnieMwangi-dot) | **Winnie Nyambura**  <br> Frontend Developer, Tailwind CSS Expert |
| [![](https://github.com/Q-TNess.png?size=100)](https://github.com/Q-TNess) | **Qaqamba**  <br> QA Engineer, Testing and Automation |



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


**HERE ARE THE PROMPTS USED IN PRODUCTION OF THIS PROJECT**
Here are all the prompts you provided, refined into professional versions:

## 1. **Professional PHP Retail & E-commerce Tracker Specification**

```plaintext
Develop a comprehensive PHP-based Retail & E-commerce Financial Tracker Web Application with the following professional specifications:

**Core Requirements:**
- Multi-user authentication system with secure session management
- Real-time transaction tracking for income and expense management
- Advanced reporting and analytics dashboard
- Voice input integration for hands-free transaction entry
- OCR receipt scanning and automatic data extraction
- File upload capabilities for receipt management
- Responsive design optimized for desktop and mobile devices

**Technical Stack:**
- Backend: PHP 7.4+ with PDO for database operations
- Database: MySQL with normalized schema design
- Frontend: Modern HTML5, CSS3, JavaScript (ES6+)
- UI Framework: Tailwind CSS for responsive design
- Additional: Chart.js for data visualization

**Key Features:**
- Secure user registration and authentication
- Transaction categorization with custom categories
- Real-time financial dashboard with KPIs
- Advanced filtering and search capabilities
- Export functionality (CSV, PDF reports)
- Multi-currency support (Kenyan Shillings primary)
- Voice-to-text transaction input
- Camera integration for receipt capture
- Comprehensive audit trails

**Security Requirements:**
- Password hashing with bcrypt
- SQL injection prevention via prepared statements
- XSS protection with input sanitization
- CSRF token implementation
- Secure session management

**Performance Specifications:**
- Optimized database queries with proper indexing
- Lazy loading for large datasets
- Responsive design with mobile-first approach
- Progressive Web App capabilities

Deliver a production-ready application with clean, maintainable code following PSR standards and best practices.
```

## 2. **Professional AI Image Generation Prompt for Transaction Form**

```plaintext
Generate a high-fidelity mockup of a professional financial transaction form interface with the following exact specifications:

**Visual Design Requirements:**
- Clean, modern web application interface design
- Minimalist aesthetic with professional typography
- Consistent spacing and visual hierarchy
- Enterprise-grade user experience design

**Layout Specifications:**
- Centered form container with subtle elevation shadow
- White background form card with rounded corners (8px radius)
- Light gray (#F8F9FA) page background
- Generous padding (24px) within form container
- Form width: approximately 400px

**Header Section:**
- Title: "Add Transaction" in dark gray (#374151)
- Font weight: Semi-bold, size: 24px
- Top margin: 16px from container edge

**Form Elements (Sequential Layout):**

1. **Transaction Type Field:**
   - Label: "Transaction Type" (14px, medium weight)
   - Dropdown with "Select type" placeholder
   - Gray border (#D1D5DB), rounded corners
   - Chevron down icon (right-aligned)

2. **Category Field:**
   - Label: "Category" (14px, medium weight)
   - Dropdown with "Select category" placeholder
   - Consistent styling with transaction type

3. **Amount Field:**
   - Label: "Amount" (14px, medium weight)
   - Input field with "KSh" prefix (left-aligned)
   - Blue microphone icon (#3B82F6) right-aligned
   - Clean input styling with focus states

4. **Date Field:**
   - Label: "Date" (14px, medium weight)
   - Input displaying "26/05/2025"
   - Calendar icon (right-aligned, gray)

5. **Description Field:**
   - Label: "Description (Optional)" (14px, medium weight)
   - Large textarea (120px height)
   - Placeholder: "Enter transaction details..."
   - Subtle border styling

6. **Receipt Upload Section:**
   - Label: "Receipt (Optional)" (14px, medium weight)
   - File upload area (partially visible)

**Technical Specifications:**
- Form elements: 16px vertical spacing
- Input height: 44px (touch-friendly)
- Border radius: 6px for all form elements
- Border color: #D1D5DB
- Focus state: Blue border (#3B82F6)
- Font family: System UI, sans-serif

**Quality Requirements:**
- Pixel-perfect rendering
- Professional web application aesthetic
- Consistent with modern SaaS interfaces
- High contrast for accessibility compliance
- Responsive design principles

Generate as a realistic web interface screenshot with production-quality visual fidelity.
```

## 3. **Professional Development Request for Responsive Navigation**

```plaintext
Implement responsive navigation optimization for the SmartStore Tracker application with the following professional requirements:

**Objective:**
Enhance user experience by implementing adaptive navigation that hides authentication elements on mobile devices while maintaining full functionality on desktop platforms.

**Technical Requirements:**

**Mobile Implementation (≤ 768px):**
- Hide "Sign In" link from primary navigation
- Hide "Get Started" button from primary navigation
- Implement collapsible hamburger menu
- Maintain theme toggle visibility
- Provide mobile-optimized touch targets (minimum 44px)

**Desktop Implementation (≥ 769px):**
- Display full navigation with all authentication elements
- Maintain existing desktop user experience
- Preserve current styling and interactions

**Implementation Specifications:**
- Utilize Tailwind CSS responsive utilities
- Implement `hidden md:flex` for desktop-only elements
- Use `md:hidden` for mobile-only components
- Ensure smooth transitions and animations
- Maintain accessibility standards (WCAG 2.1 AA)

**Code Quality Standards:**
- Clean, semantic HTML structure
- Efficient CSS class organization
- JavaScript best practices for menu interactions
- Cross-browser compatibility
- Performance optimization

**Testing Requirements:**
- Verify functionality across multiple device sizes
- Test touch interactions on mobile devices
- Validate keyboard navigation accessibility
- Ensure consistent behavior across browsers

**Deliverables:**
- Updated navigation component with responsive behavior
- Mobile hamburger menu implementation
- Comprehensive testing across device breakpoints
- Documentation of responsive breakpoint strategy

This enhancement should improve mobile user experience while maintaining the professional desktop interface standards.
```

## 4. **Professional User Experience Enhancement Request**

```plaintext
Implement user flow optimization for the SmartStore Tracker application logout functionality with the following professional specifications:

**Objective:**
Enhance post-authentication user experience by redirecting users to the marketing homepage upon logout, rather than the login page, to improve user engagement and provide clear re-entry pathways.

**Current State Analysis:**
- Users currently redirected to login page after logout
- Limited re-engagement opportunities
- Reduced exposure to application value proposition

**Proposed Enhancement:**

**Technical Implementation:**
- Modify `backend/logout.php` redirect destination
- Change from `login.php` to `index.php` (homepage)
- Maintain complete session cleanup and security protocols
- Preserve existing logout security measures

**User Experience Benefits:**
- **Enhanced Re-engagement:** Users see marketing content and feature highlights
- **Clear Navigation:** Prominent authentication buttons for easy re-entry
- **Professional Flow:** Smooth transition from application to marketing experience
- **Value Reinforcement:** Users reminded of application benefits and features

**Business Impact:**
- Increased user retention through marketing exposure
- Improved user journey and experience flow
- Enhanced brand engagement post-session
- Professional application lifecycle management

**Security Considerations:**
- Maintain complete session destruction
- Clear all authentication cookies and tokens
- Ensure secure redirect implementation
- Preserve existing security protocols

**Implementation Requirements:**
- Update logout redirect logic
- Test complete session cleanup
- Verify cross-browser compatibility
- Validate mobile and desktop experiences

**Success Metrics:**
- Improved user re-engagement rates
- Reduced friction in re-authentication process
- Enhanced overall user experience scores
- Professional application flow completion

This enhancement aligns with modern SaaS application best practices for user experience optimization and engagement retention.
```

# Project Title

> ⚠️ **Notice:** This project could not be hosted online due to unavoidable technical errors. However, it is fully functional and available for offline viewing. Below are screenshots showcasing the application's features and design.

## 📸 Screenshots

### 1. Home Page
![Home Page](![image](https://github.com/user-attachments/assets/66ed4ba6-af24-40d8-af34-da047f869ccb)

![Home Page](![image](https://github.com/user-attachments/assets/dcdf510f-6534-4266-8e3d-f284fa002997)

### 2. Login Page
![Registration page](![image](https://github.com/user-attachments/assets/fca192d5-beb8-4b00-8f5d-2d6f55e92232))

![Login Page](![image](https://github.com/user-attachments/assets/1235129d-a61a-4726-ba40-c6e420719351))

### 3. Dashboard
![Dashboard](![image](https://github.com/user-attachments/assets/846110c8-f667-4d05-9977-870c072d16ba))

![Dashboard](![image](https://github.com/user-attachments/assets/6a6dcc06-e386-4b1c-90e5-6d4f866f1cf9))

### 4. Product List
![Product List](![image](https://github.com/user-attachments/assets/e5668e69-8da8-4828-a80e-f93d7aa76999))

![Product List](![image](https://github.com/user-attachments/assets/ba2c4aaf-09bf-4898-96a3-f037798c33a9))

![Product List](![image](https://github.com/user-attachments/assets/2b517d61-3d5e-48cb-a1e0-942fee189adc))

![Product List](![image](https://github.com/user-attachments/assets/e959c4d5-cf1e-4a04-8259-e83606a29b18))

### 5. Add New Product
![Add New Product](![image](https://github.com/user-attachments/assets/d54fc6c5-f275-499b-80b1-f53cc0238c2e))

### 6.
![New Product](![image](https://github.com/user-attachments/assets/3541d5d6-c2d3-4715-816a-609f905d2908))

### 7. Categories
![categories](![image](https://github.com/user-attachments/assets/36974747-4051-45d6-9727-06d87ba2c96e))

### 8.
![Categories](![image](https://github.com/user-attachments/assets/4674319b-335d-4734-a8a3-b436c60241af))

---




---

**Made with ❤️ for small business owners everywhere**

*SmartStore Tracker - Empowering businesses through technology*
```
