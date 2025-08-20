# MyManager

A modern web application for management tasks with user authentication and dashboard functionality.

## Project Structure

```
MyManager/
├── Back-End/
│   ├── index.php          # Main PHP application with routing
│   ├── config.php         # Database configuration
│   └── .htaccess          # URL rewriting rules
├── Front-End/
│   ├── Login.html         # Login page
│   ├── Signup.html        # Signup page
│   ├── dashboard.html     # Main dashboard after login
│   └── style.css          # Custom CSS styles
└── README.md              # This file
```

## Features

- ✅ **User Authentication** - Login and signup functionality
- ✅ **Modern UI** - Bootstrap 5.3 with responsive design

- ✅ **Dashboard** - Main menu after successful login
- ✅ **Database Integration** - MariaDB with stored procedures
- ✅ **Security** - Password hashing and prepared statements

## Setup Instructions

### Prerequisites
- PHP 8.0+ with mysqli extension
- MariaDB/MySQL database
- Web server (Apache/XAMPP/WAMP) or PHP built-in server

### Database Setup
1. Create a database named `user_details`
2. Create a table named `user_info` with columns:
   - `username` (VARCHAR, PRIMARY KEY)
   - `password_hash` (VARCHAR)
3. Set up the required stored procedures:
   - `user_proc(username, password_hash)` for user registration
   - `user_login(username, password_hash)` for user authentication
4. Update database credentials in `Back-End/config.php`

### Database Usage
- **`user_info` table**: Stores user account data
- **`user_proc` procedure**: Handles new user registration
- **`user_login` procedure**: Validates user login credentials
- **Duplicate protection**: PHP checks for existing usernames before registration
- **Input validation**: Username (3-20 chars, alphanumeric + underscore), Password (min 6 chars)

### Running the Application

#### Option 1: PHP Built-in Server
```bash
cd Back-End
php -S localhost:8000 index.php
```

#### Option 2: Apache/XAMPP
1. Copy project to `htdocs` folder
2. Start Apache and MySQL services
3. Access via `http://localhost/MyManager/Back-End/`

## Usage

1. **Visit** `http://localhost:8000/login`
2. **Sign up** for a new account
3. **Login** with your credentials
4. **Access dashboard** with menu options
5. **Access dashboard** with menu options

## File Descriptions

- **`index.php`** - Main application router and authentication logic
- **`config.php`** - Database connection settings
- **`Login.html`** - User login interface
- **`Signup.html`** - User registration interface
- **`dashboard.html`** - Main application dashboard
- **`style.css`** - Custom styling


## Technologies Used

- **Backend**: PHP 8.4, MariaDB
- **Frontend**: HTML5, CSS3, JavaScript, Bootstrap 5.3
- **Security**: SHA-256 password hashing, prepared statements

## License

© 2025 MyManager. All rights reserved. 