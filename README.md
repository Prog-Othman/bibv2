# **Library Management System - SUP-MTI**  

## **📌 Overview**  
This is a **Library Management System** developed for **SUP-MTI** (or any educational institution) to manage books, members, loans, and returns efficiently.  

### **✨ Features**  
- **Book Management** (Add, Edit, Delete, Search)  
- **Member Management** (Students, Teachers)  
- **Loan & Return System**  
- **Fine Calculation** for overdue books  
- **Reports & Statistics**  
- **User Authentication** (Admin, Librarian, Members)  

---

## **🛠️ Installation & Setup**  

### **Prerequisites**  
- PHP (≥ 8.1)  
- Composer  
- MySQL / MariaDB  
- Node.js (for frontend assets)  

### **1. Clone the Repository**  
```bash
git clone https://github.com/your-repo/library-management.git
cd library-management
```

### **2. Install Dependencies**  
```bash
composer install
npm install
```

### **3. Configure Environment**  
Copy `.env.example` to `.env` and update database credentials:  
```bash
cp .env.example .env
```  
Edit `.env`:  
```ini
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=library_db
DB_USERNAME=root
DB_PASSWORD=
```

### **4. Generate App Key & Run Migrations**  
```bash
php artisan key:generate
php artisan migrate --seed
```
*(The `--seed` flag will populate initial data like admin user, book categories, etc.)*  

### **5. Compile Frontend Assets**  
```bash
npm run build
```

### **6. Start the Development Server**  
```bash
php artisan serve
```
Visit: [http://localhost:8000](http://localhost:8000)  

---

## **🔐 Default Login Credentials**  
- **Admin**:  
  - Email: `admin@supmti.com`  
  - Password: `password`  

- **Librarian**:  
  - Email: `librarian@supmti.com`  
  - Password: `password`  

*(Change passwords after first login!)*  

---

## **📂 Project Structure**  
```
├── app/           # Models, Controllers, Policies  
├── database/      # Migrations, Seeders, Factories  
├── resources/     # Views, JS, CSS  
├── routes/        # Web & API Routes  
├── public/        # Compiled Assets  
└── config/        # Configuration Files  
```

---

## **🔄 Running Migrations & Seeding**  
- **Run fresh migrations & seed data**:  
  ```bash
  php artisan migrate:fresh --seed
  ```
- **Rollback last migration**:  
  ```bash
  php artisan migrate:rollback
  ```

---

## **📊 Generating Reports**  
To export loan history (Excel/PDF):  
```bash
php artisan export:loans --format=excel
```

---

## **📜 License**  
MIT License.  

---

## **📧 Contact**  
For support, email: `support@supmti.com`  

🚀 **Happy Coding!**  

---

Would you like me to add anything specific for your SUP-MTI project? 😊
