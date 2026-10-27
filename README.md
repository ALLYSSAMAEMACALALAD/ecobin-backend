# Eco-Bin: Initial Web Setup

Eco-Bin is an integrated smart waste collection and municipal recycling reward system.
This is the **initial setup** for Checking 1 and the start of Checking 2 and 3. It is built on the
[LavaLust](https://github.com/ronmarasigan/LavaLust) PHP MVC framework (framework commit `74045f1`) with MySQL.

## What is working

- **Project structure** following LavaLust MVC (controllers, models, views, config, helpers, database folder).
- **All routes** from the Checking 1 route table and site map, defined in `app/config/routes.php`.
- **Auth:** resident registration, login and logout with hashed passwords (`password_hash`), session-based
  login, and role-based access (resident, collector, admin).
- **Resident pages:** the dashboard and the collection schedule read real records from the database.
- **Admin schedule CRUD** (Checking 3 scope): list, add, view details, edit, and delete, with server-side validation
  (required fields, valid date, point must exist, waste type from a whitelist) and a confirm step before delete.
- **Resident Eco-ID:** shows the resident's name, Eco-ID (`ECOID-####`), points, and a QR code for the drop-off hub.
- **Report illegal dumping (FR6):** resident form with description, location, optional GPS, and a photo upload
  (JPG, PNG, or WEBP, max 5 MB). Validation runs on the server and the file type is checked by content, not by name.
- **My Reports:** the resident's own reports from the database with their status badge and photo link.
- **Stub pages** for the features that are planned for later checkings. Each one loads with the correct layout and
  navigation and says what it will do.
- **Database schema** for all tables in Section 4 of the Checking 1 document, plus sample seed data.

## Requirements

- XAMPP (or Laragon/WAMP) with **PHP 8.1 or newer** and **MySQL / MariaDB**
- Extensions: `pdo_mysql` and `fileinfo` (both enabled by default in XAMPP)
- Apache with `mod_rewrite` enabled (default in XAMPP)

> **Upgrading from the earlier zip?** The `dumping_reports` table has a new `location_text` column. Re-run
> `schema.sql` and then `seed.sql` in phpMyAdmin. This resets all data to the sample set.

## Setup (XAMPP)

1. Copy this folder to `C:\xampp\htdocs\eco-bin`.
2. Start **Apache** and **MySQL** in the XAMPP control panel.
3. Create the database and sample data. Open phpMyAdmin (or Navicat) and run:
   - `database/schema.sql` (creates `ecobin_db` and all tables)
   - `database/seed.sql` (adds sample users, collection points, schedules, and rewards)
4. Copy `.env.example` to `.env` and check the values. Set `APP_KEY` to any long random text and
   `DB_PASSWORD` to your MySQL password (blank by default in XAMPP).
5. Open **http://localhost/eco-bin/public/** in your browser.

> **Important:** open the app through the `public/` folder, as shown above. This version of LavaLust
> works out its URLs from the script path, so `BASE_URL` must end in `/public/`.

## Sample accounts

All accounts use the password **`password123`**. Change them before any real use.

| Role      | Email                   | Lands on                   |
|-----------|-------------------------|----------------------------|
| Admin     | admin@ecobin.test       | Admin dashboard            |
| Collector | collector@ecobin.test   | Today's route (stub)       |
| Resident  | resident@ecobin.test    | Resident dashboard         |

New accounts created through **Register** are residents.

## Project structure

```
eco-bin/
├── .env.example            copy to .env (not committed)
├── app/
│   ├── config/             routes.php, database.php, config.php, autoload.php
│   ├── controllers/        Home, Auth, Resident, Collector, Report, Admin
│   ├── models/             User_model, Schedule_model, CollectionPoint_model, Report_model
│   ├── helpers/            auth_helper.php (session user, role checks, nav, escaping)
│   └── views/              partials/ (header, footer), auth/, resident/, admin/schedules/, shared/stub.php
├── database/
│   ├── schema.sql          MySQL schema (Section 4 tables)
│   └── seed.sql            sample data
├── public/                 web root (index.php, assets/css/app.css, assets/js/qrcode.min.js, uploads/)
└── scheme/                 LavaLust core (do not edit)
```

## Route status

| Method | URL | Controller::method | Status |
|---|---|---|---|
| GET | / | Home::index | Done |
| GET | /faq | Home::faq | Done |
| GET, POST | /login | Auth::login / authenticate | Done |
| GET, POST | /register | Auth::register / store | Done |
| GET | /logout | Auth::logout | Done |
| GET | /profile | Auth::profile | Stub |
| GET | /resident/dashboard | Resident::dashboard | Done |
| GET | /resident/schedule | Resident::schedule | Done |
| GET | /resident/eco-id | Resident::ecoId | Done (QR) |
| GET | /resident/rewards | Resident::rewards | Done |
| POST | /resident/rewards/{id}/redeem | Resident::redeem | Done |
| GET | /resident/reports | Report::mine | Done |
| GET, POST | /resident/reports/new | Report::create / store | Done (photo upload) |
| GET | /collector/route | Collector::route | Stub |
| GET | /collector/stops/{id} | Collector::stop | Stub |
| POST | /collector/stops/{id}/complete | Collector::completeStop | Stub |
| GET | /admin/dashboard | Admin::dashboard | Done |
| GET | /admin/schedules | Admin::schedules | Done (CRUD) |
| GET | /admin/schedules/create | Admin::createSchedule | Done (CRUD) |
| POST | /admin/schedules | Admin::storeSchedule | Done (CRUD) |
| GET | /admin/schedules/{id} | Admin::showSchedule | Done (CRUD) |
| GET | /admin/schedules/{id}/edit | Admin::editSchedule | Done (CRUD) |
| POST | /admin/schedules/{id} | Admin::updateSchedule | Done (CRUD) |
| POST | /admin/schedules/{id}/delete | Admin::deleteSchedule | Done (CRUD) |
| GET | /admin/points | Admin::points | Done (CRUD) |
| GET | /admin/points/create | Admin::createPoint | Done (CRUD) |
| POST | /admin/points | Admin::storePoint | Done (CRUD) |
| GET | /admin/points/{id}/edit | Admin::editPoint | Done (CRUD) |
| POST | /admin/points/{id} | Admin::updatePoint | Done (CRUD) |
| POST | /admin/points/{id}/delete | Admin::deletePoint | Done (blocked if in use) |
| GET | /admin/dropoffs | Admin::dropoffs | Done |
| POST | /admin/dropoffs | Admin::recordDropoff | Done (credits points) |
| GET | /admin/redemptions | Admin::redemptions | Done |
| POST | /admin/redemptions/{id}/approve | Admin::approveRedemption | Done |
| POST | /admin/redemptions/{id}/reject | Admin::rejectRedemption | Done |
| GET | /admin/tickets | Report::index | Done (filter by status) |
| GET | /admin/tickets/{id} | Report::showTicket | Done |
| POST | /admin/tickets/{id}/status | Report::updateTicket | Done (queues notice) |
| GET | /admin/reports | Admin::reports | Done |
| GET | /admin/reports/export.csv | Report::exportCsv | Done |
| GET | /admin/reports/export.pdf | Report::exportPdf | Done (FPDF) |
| GET | /admin/users | Admin::users | Done |
| POST | /admin/users | Admin::storeUser | Done (adds collectors) |
| POST | /admin/users/{id}/role | Admin::changeRole | Done |
| POST | /admin/users/{id}/status | Admin::changeStatus | Done |
| GET | /admin/audit | Admin::audit | Done |

## Testing notes

- 132 scripted checks, all passing on SQLite: 67 for the public, resident, and schedule pages, and 65 for the admin,
  rewards, and report features (points credit, redemption approval and stock, ticket status, CSV and PDF exports,
  user roles, and audit logs). **The MySQL schema has not been run on a MySQL server yet**, so import it once in
  phpMyAdmin to confirm it works.

## Known limits

- **Collector pages** (route, stop detail, marking stops done) are still placeholders.
- **SMS and email are not sent.** Notifications are saved in the queue (Admin > Audit Logs). Connect Semaphore or
  Twilio to send them.
- **Photos are stored locally** in `public/uploads/reports/`. Cloudinary is not connected.
- **The map is a list with Google Maps links**, not an embedded map. An embedded map needs a Google Maps API key.
- **Rewards are managed in the database.** There is no admin screen to add or edit rewards yet.
- **No CSRF tokens yet.** Add them before deployment.
- **Dates and times** are shown as the database stores them.
- The LavaLust `redirect()` helper in this release does not stop the script after redirecting, so the app uses its
  own `eco_go()` helper (see `app/helpers/auth_helper.php`).
- **Stack note:** the SDLC activity lists "Node.js/LavaLust". LavaLust is PHP, so the back end here is PHP.
- **Libraries bundled:** FPDF 1.9 (`app/libraries/fpdf/`) for PDF export, and qrcodejs (`public/assets/js/`) for QR codes.
