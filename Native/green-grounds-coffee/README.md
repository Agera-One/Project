# Green Grounds Coffee - POS System

A complete, production-ready Point of Sale (POS) system for coffee shops built with pure PHP, HTML, CSS, and JavaScript.

## Features

### 🎯 Core POS Functionality
- **Real-time Shopping Cart** - Add/remove items, adjust quantities with live calculations
- **Multiple Payment Methods** - Cash, Card, Digital payment options
- **Receipt Generation** - Print-friendly receipts with order details
- **Order Management** - Track dine-in, takeaway, and delivery orders
- **Customer Information** - Optional customer name and table number tracking

### 📊 Admin Dashboard
- **Sales Analytics** - Today's revenue, monthly totals, average order value
- **Top Products** - View best-selling items by revenue
- **Recent Orders** - Quick access to latest transactions
- **Inventory Tracking** - Real-time stock levels and inventory value
- **Performance Metrics** - Key statistics at a glance

### 🛒 Product Management
- **Product Catalog** - Add, edit, delete products
- **Categories** - Organize products by type (Coffee, Tea, Snacks)
- **SKU Tracking** - Track products by unique identifiers
- **Price & Cost** - Manage pricing and profit margins
- **Inventory Management** - Monitor stock levels with low-stock alerts

### 👥 User Management
- **Role-Based Access** - Admin, Manager, and Cashier roles
- **User Management** - Create and manage team members
- **Activity Logging** - Track user actions and logins
- **Session Security** - Secure session management with timeout

### 📈 Reporting & Analytics
- **Daily Sales Reports** - Revenue breakdown by date
- **Payment Method Analysis** - Track payment method usage
- **Product Performance** - Sales and revenue by product
- **Cashier Performance** - Monitor individual cashier sales
- **Custom Date Ranges** - Filter reports by any time period

## System Requirements

- PHP 7.4 or higher
- MySQL 5.7 or higher
- Modern web browser (Chrome, Firefox, Safari, Edge)
- Web server with .htaccess support (Apache) or URL rewriting enabled

## Installation

### 1. Database Setup

First, create a MySQL database:

```sql
CREATE DATABASE green_grounds_coffee;
```

### 2. Configure Database Connection

Edit `config/database.php` and set your database credentials:

```php
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', 'your_password');
define('DB_NAME', 'green_grounds_coffee');
```

Alternatively, use environment variables:

```bash
DB_HOST=localhost
DB_USER=root
DB_PASS=your_password
DB_NAME=green_grounds_coffee
```

### 3. Run Database Migrations

Execute the setup script to create tables:

```bash
php scripts/setup_database.php
```

Output:
```
✓ Table created/verified
✓ Default categories inserted
✓ Default admin user created (email: admin@greengrounds.local, password: admin123)
✓ Database setup completed successfully!
```

### 4. (Optional) Load Sample Data

Populate the database with sample products and users:

```bash
php scripts/seed_data.php
```

Output:
```
✓ 60 products inserted
✓ Demo users created
✓ Seed data completed successfully!
```

## Default Credentials

### Admin Account
- **Email**: admin@greengrounds.local
- **Password**: admin123

### Demo Cashier
- **Email**: sarah@greengrounds.local
- **Password**: cashier123

### Demo Manager
- **Email**: emma@greengrounds.local
- **Password**: manager123

**⚠️ Security Note**: Change default passwords immediately in a production environment!

## Usage

### Accessing the System

1. Open your browser and navigate to `http://localhost/` (or your server URL)
2. Login with admin credentials
3. Navigate to appropriate section based on your role

### For Cashiers

1. **Cashier Interface** (`/cashier/index.php`)
   - Browse products by category
   - Add items to cart
   - Adjust quantities
   - Proceed to checkout
   - Enter customer details and payment method
   - Print receipt

### For Administrators

1. **Dashboard** (`/admin/dashboard.php`) - Overview of key metrics
2. **Products** (`/admin/products.php`) - Manage product catalog
3. **Inventory** (`/admin/inventory.php`) - Track stock levels
4. **Orders** (`/admin/orders.php`) - View all orders with filters
5. **Users** (`/admin/users.php`) - Manage team members
6. **Reports** (`/admin/reports.php`) - Generate detailed reports

## File Structure

```
├── config/
│   ├── database.php         # Database connection
│   ├── session.php          # Session management
│   └── utils.php            # Utility functions
├── public/
│   ├── login.php            # Login page
│   ├── css/
│   │   └── style.css        # Global styles
│   ├── js/
│   │   └── cashier.js       # POS functionality
│   ├── api/
│   │   ├── get_products.php # Product API
│   │   ├── get_categories.php # Category API
│   │   └── logout.php       # Logout handler
│   ├── cashier/
│   │   ├── index.php        # POS Interface
│   │   ├── checkout.php     # Checkout page
│   │   └── receipt.php      # Receipt display
│   └── admin/
│       ├── dashboard.php    # Admin dashboard
│       ├── products.php     # Product management
│       ├── inventory.php    # Inventory tracking
│       ├── orders.php       # Order management
│       ├── users.php        # User management
│       └── reports.php      # Reports & analytics
├── scripts/
│   ├── setup_database.php   # Database migration
│   └── seed_data.php        # Sample data
├── index.php                # Entry point
└── .htaccess                # URL rewriting rules
```

## Database Schema

### Tables

- **users** - Team members and their credentials
- **categories** - Product categories
- **products** - Product catalog
- **orders** - Customer orders
- **order_items** - Individual items in orders
- **transactions** - Payment transactions
- **activity_log** - User action logs

## Security Features

- ✅ Password hashing with bcrypt (cost 12)
- ✅ CSRF token protection
- ✅ Session security with IP validation
- ✅ Session timeout (30 minutes)
- ✅ Prepared statements (SQL injection prevention)
- ✅ Input sanitization and validation
- ✅ Role-based access control
- ✅ HTTP-only cookies
- ✅ Secure headers

## Customization

### Changing Tax Rate

Edit `public/js/cashier.js`:

```javascript
this.taxRate = 0.10; // Change to 0.15 for 15% tax, etc.
```

### Modifying Session Timeout

Edit `config/database.php`:

```php
define('SESSION_TIMEOUT', 1800); // 30 minutes in seconds
```

### Custom Branding

1. Change the store name in `public/login.php`, dashboard pages
2. Update CSS colors in `public/css/style.css`:
   - `--primary`: Main brand color
   - `--secondary`: Accent color
   - `--accent`: Warning/alert color

## Troubleshooting

### Database Connection Failed
- Check database credentials in `config/database.php`
- Ensure MySQL server is running
- Verify user has necessary permissions

### 404 Errors
- Ensure `.htaccess` file exists and URL rewriting is enabled
- Check web server configuration
- For Nginx, implement equivalent routing rules

### Session Issues
- Clear browser cookies
- Check server timezone in PHP configuration
- Ensure write permissions for session storage

## Support & Maintenance

### Regular Maintenance Tasks
1. Monitor and analyze sales reports weekly
2. Verify inventory levels daily
3. Review user activity logs monthly
4. Backup database regularly
5. Update product pricing as needed

### Backup Strategy
```bash
# MySQL backup
mysqldump -u root -p green_grounds_coffee > backup_$(date +%Y%m%d).sql
```

## Performance Tips

1. **Database Optimization**
   - Add indexes on frequently queried columns
   - Archive old orders periodically
   - Regular VACUUM and OPTIMIZE table operations

2. **Caching**
   - Cache product list if frequently accessed
   - Use browser caching for static assets

3. **Load Management**
   - Limit reports to specific date ranges
   - Use pagination for large result sets

## License

This POS system is provided for Green Grounds Coffee. Unauthorized copying or modification is prohibited.

## Version

**Current Version**: 1.0.0
**Last Updated**: February 2026

---

For issues or questions, contact the development team.
