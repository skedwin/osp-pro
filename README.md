
# **OSP Pro**

OSP Pro is a Laravel-based web application for managing practitioner profiles, renewals, CPD tracking, invoicing, and payment flows. It uses Blade for the frontend with Vite-managed assets and includes domain services for profile/CPD normalization, with PHPUnit coverage for core logic.

---

## **Key Features**

* Practitioner profiles & licensing
* CPD (Continuing Professional Development) normalization & eligibility checks
* Renewals and invoicing UI with integrated payment iframe + fallback
* Clean service-driven architecture for testable business logic
* PHPUnit tests (unit + feature) for essential flows

---

## **System Contract (High-Level)**

**Inputs:**

* Standard Laravel `.env` configuration
* Authenticated session containing `bio_profile`

**Outputs:**

* Practitioner web UI
* Invoices and renewal views
* Blade-rendered frontend

**Error Modes:**

* Invalid CPD shapes normalized by `ProfileService`
* Payment retry + fallback inside invoice view

---

## **Requirements**

* PHP 8.0+
* Composer
* Node.js 16+
* MySQL/MariaDB/SQLite
* Git

---

## **Local Development (Windows / PowerShell)**

### **1. Clone the Repository**

SSH:

```powershell
git clone git@github.com:skedwin/osp-pro.git
cd osp-pro
```

HTTPS:

```powershell
git clone https://github.com/skedwin/osp-pro.git
cd osp-pro
```

---

### **2. Install PHP Dependencies**

```powershell
composer install --no-interaction --prefer-dist
```

---

### **3. Install JS Dependencies & Build Assets**

```powershell
npm install
npm run dev   # development
npm run build # production
```

---

### **4. Environment Setup**

```powershell
copy .env.example .env
php artisan key:generate
```

Edit `.env` (DB, MAIL, APP_URL).

---

### **5. Migrate the Database**

```powershell
php artisan migrate --seed
```

---

### **6. Start the Application**

```powershell
php artisan serve --host=127.0.0.1 --port=8000
```

Open: [http://127.0.0.1:8000](http://127.0.0.1:8000)

---

## **Running Tests**

```powershell
./vendor/bin/phpunit
```

Covers:

* CPD normalization (ProfileService)
* Renewals controller logic

---

## **SSH Setup for GitHub (Recommended)**

### Generate key:

```powershell
ssh-keygen.exe -t ed25519 -C "your-email@example.com" -f "$env:USERPROFILE\.ssh\osp_pro_id_ed25519" -N ""
```

### Start agent & add key:

```powershell
Start-Service ssh-agent -ErrorAction SilentlyContinue
ssh-add "$env:USERPROFILE\.ssh\osp_pro_id_ed25519"
```

### Copy public key:

```powershell
Get-Content "$env:USERPROFILE\.ssh\osp_pro_id_ed25519.pub" | Set-Clipboard
```

Add to GitHub → **Settings → SSH and GPG keys → New SSH key**

### Test connection:

```powershell
ssh -T git@github.com
```

### Push code:

```powershell
git push -u origin main
```

---

## **Project Structure (Overview)**

```
app/
  Http/Controllers/      - Controller layer
  Services/              - Business logic

resources/views/         - Blade templates
public/                  - Public assets
tests/                   - Unit + Feature tests
```

---

## **Troubleshooting**

* Wrong CPD totals → `ProfileService::getFormattedProfile()` performs normalization
* Payment iframe issues → check `invoice_details.blade.php`
* SSH errors on Windows → ensure OpenSSH Client is installed or use Git Bash

---

## **Contributing**

* Fork the repository
* Create a feature branch
* Submit a PR with explanation
* Ensure tests pass before submission

---

## **License**

See the `LICENSE` file included in this repository.

---

If you want, I can also:

✅ Save this into your project as README.md
✅ Commit and push it for you
Just say **“commit this README”**.
