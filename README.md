#<div align="center">

<img src="assets/image/FlyOn3.png" alt="FlyOn Logo" width="220"/>

✈️ FlyOn
A Dynamic Air Ticketing & Flight Booking System

Search smarter. Book faster. Fly better.

A full-featured web-based airline reservation platform built with PHP, MySQL, Tailwind CSS, and JavaScript, designed to provide an end-to-end flight booking experience with dynamic pricing, seat selection, promotions, loyalty rewards, administrative controls, REST-style APIs, and flight-data synchronization capabilities.









Features •
Architecture •
Installation •
Database •
API •
Booking Flow

</div>

📖 About FlyOn

FlyOn is a dynamic air-ticketing system that simulates the workflow of a modern online travel and airline reservation platform.

Rather than functioning as a simple flight-listing website, FlyOn brings together the major components required by a real booking system:

flight discovery
dynamic ticket pricing
passenger information management
seat allocation
optional add-ons
promotional discounts
payment processing workflow
booking management
loyalty rewards
user reviews
administrative operations
flight-data synchronization

The project is especially focused on the Bangladesh aviation market, with sample routes, airlines, airports, currency formatting, timezone configuration, and payment architecture suitable for a Bangladesh-oriented travel platform.

✨ Key Features
🔍 Intelligent Flight Search

Users can search available flights by:

departure city
destination city
departure date
return date
number of passengers
travel class

FlyOn retrieves matching scheduled flights from the database and provides information such as:

airline
flight number
departure airport
arrival airport
departure time
arrival time
flight duration
seat availability
calculated ticket price

The search system also tracks how frequently flights are searched, allowing demand to influence dynamic pricing.

💸 Dynamic Ticket Pricing

One of FlyOn's key features is its built-in dynamic pricing engine.

Ticket prices can automatically adjust based on active pricing rules.

Supported pricing strategies
Strategy	Example Behavior
⏰ Time-Based Pricing	Price increases when departure is very close
💺 Seat-Based Pricing	Price increases as available seats become scarce
📈 Demand-Based Pricing	Frequently searched flights can become more expensive

Pricing rules are stored in the database rather than being permanently hard-coded into the booking interface, making the system extensible.

🧳 Complete Multi-Step Booking Experience

FlyOn provides a structured five-stage booking workflow.

Flight Search
     ↓
Passenger Information
     ↓
Seat Selection
     ↓
Add-ons & Promotions
     ↓
Payment
     ↓
Booking Confirmation
Step 1 — Passenger Information

Collect and manage passenger details including:

title
first and last name
date of birth
gender
passport information
Step 2 — Seat Selection

Passengers can choose seats for their selected flight.

The database supports:

Economy
Business
First Class

Seats can have the following states:

Available → Locked → Booked

Temporary seat locking is supported in the system configuration to reduce the possibility of simultaneous users selecting the same seat during a booking session.

Step 3 — Add-ons & Promotions

The booking engine supports additional charges and promotional discounts.

Promo codes may apply either:

percentage discounts
fixed-amount discounts

Promotions also contain validity periods and active/inactive states.

Step 4 — Payment

FlyOn contains payment architecture for:

🇧🇩 SSLCommerz
💳 Stripe
🌐 PayPal
💵 Cash

Development Note:
The current SSLCommerz, Stripe, and PayPal handlers are integration-ready placeholders that generate simulated transaction IDs after configuration checks. Production deployments should replace these handlers with the official payment-provider SDKs/APIs and server-side payment verification.

Step 5 — Confirmation

After the booking process is completed, the system can provide:

unique booking reference
booking information
flight details
payment state
confirmation information
👤 User System

FlyOn includes a complete user-facing account system.

Authentication

Users can:

register
log in
log out
maintain account sessions
edit profile information

Passwords are hashed using PHP's password hashing API.

User Dashboard

Registered users have access to their own dashboard where they can manage personal travel activity.

Features include:

booking history
booking details
booking cancellation
profile management
loyalty information
flight ratings and reviews
🎁 Loyalty Program

FlyOn contains a tier-based loyalty system.

Loyalty tiers
🥉 Bronze
    ↓
🥈 Silver
    ↓
🥇 Gold
    ↓
💎 Platinum

The loyalty architecture supports:

reward points
available points
total points
membership tiers
referral codes

Tier thresholds and point calculations are configurable from the application configuration.

⭐ Flight Rating System

Users can submit ratings for completed booking experiences.

The database supports:

ratings from 1–5
written reviews
one review per booking
average flight rating
total review count

This enables flight quality and passenger feedback to become part of the platform.

🛡️ Admin Panel

FlyOn contains a dedicated administrative environment separated from normal user functionality.

Administrators can perform operations including:

✈️ Flight Management
view flights
add flights
configure flight schedules
cancel flights
manage flight inventory
💺 Seat Management
create seats
configure seat classes
inspect seat availability
📚 Booking Management

Administrators can:

view customer bookings
approve bookings
reject bookings
inspect booking activity
🎟️ Promotion Management
create promotional campaigns
configure discount type
configure discount amount
configure validity periods
enable/disable promotions
👥 User Management

Administrators can inspect and manage registered platform users.

🔄 Flight Synchronization

Administrators also have access to flight-data synchronization functionality through the GoZayaan integration module.

🌐 GoZayaan Flight Data Integration

FlyOn contains an integration layer designed to keep local flight information synchronized with external data from GoZayaan.

The synchronization system supports two approaches:

                     ┌─────────────────────┐
                     │   FlyOn Database    │
                     └──────────┬──────────┘
                                │
                       Synchronization
                                │
                ┌───────────────┴───────────────┐
                │                               │
         GoZayaan API                    Web Extraction
        (Preferred Method)               (Fallback Design)
Supported synchronization functionality
synchronize individual flights
synchronize upcoming flights
compare flight information
update changed flight schedules
store synchronization logs
perform manual synchronization
schedule automatic synchronization
limit requests between synchronization calls

A cron script is included for scheduled synchronization.

Important: The HTML extraction implementation is currently a template and its selectors must be adjusted to match the external provider's live page structure before relying on scraping in production. Official API access is preferable.

⏱️ Automated Cron Synchronization

FlyOn includes:

cron/sync_gozayaan.php

A Linux server can execute the synchronization process periodically using cron.

Example:

0 * * * * /usr/bin/php /path/to/FlyOn/cron/sync_gozayaan.php

This example runs the synchronization process once per hour.

Windows/XAMPP users can use Windows Task Scheduler with:

C:\xampp\php\php.exe

and execute:

C:\xampp\htdocs\FlyOn\cron\sync_gozayaan.php
🌙 Modern User Interface

FlyOn uses Tailwind CSS alongside custom CSS and JavaScript.

The interface includes:

responsive layouts
custom color theme
Font Awesome icons
dark mode
system-theme detection
locally remembered dark-mode preference
reusable navigation/footer components

The UI supports both manually selected and system-preferred dark themes.

🔐 Security Features

Several security mechanisms have been included in the application architecture.

Password Security

Passwords are handled using PHP's secure password APIs:

password_hash()
password_verify()
CSRF Protection Utilities

FlyOn provides functions to generate and verify CSRF tokens.

Prepared Database Queries

PDO prepared statements are used throughout major database operations.

Session Security

Session configuration includes:

HTTP-only cookies
cookie-based sessions
secure-cookie support when HTTPS is active
Role-Based Access

Two primary application roles are supported:

User
Admin

Protected administrative pages require appropriate authentication and role state.

Input Handling

The project includes reusable utilities for:

sanitizing input
email validation
phone validation
date validation
Activity Logging

Application events can be logged with:

user
action
entity type
entity ID
IP address
user agent
additional details
🧠 System Architecture

FlyOn follows a modular PHP architecture separating presentation, reusable logic, API operations, administrative functionality, user functionality, booking steps, configuration, and data access.

flowchart TD

    A[Visitor / User] --> B[FlyOn Web Interface]

    B --> C[Flight Search]
    B --> D[Authentication]
    B --> E[User Dashboard]

    C --> F[Search API]
    F --> G[Dynamic Pricing Engine]
    G --> H[(MySQL Database)]

    D --> H

    C --> I[Flight Details]
    I --> J[Booking Workflow]

    J --> J1[Passenger Details]
    J1 --> J2[Seat Selection]
    J2 --> J3[Add-ons & Promotions]
    J3 --> J4[Payment]
    J4 --> J5[Confirmation]

    J --> H

    E --> K[Bookings]
    E --> L[Loyalty]
    E --> M[Reviews]
    K --> H
    L --> H
    M --> H

    N[Admin Panel] --> O[Flights]
    N --> P[Bookings]
    N --> Q[Users]
    N --> R[Promotions]
    N --> S[Seat Management]
    N --> T[GoZayaan Sync]

    O --> H
    P --> H
    Q --> H
    R --> H
    S --> H

    T --> U[GoZayaan Integration Layer]
    U --> V[External Flight Source]
    U --> H

    W[Cron Job] --> U
🛠️ Technology Stack
Layer	Technology
Backend	PHP
Database	MySQL / MariaDB
Database Access	PDO
Frontend	HTML5
Styling	Tailwind CSS
Custom Styling	CSS
Client-Side Logic	JavaScript
Icons	Font Awesome 6
Web Server	Apache
Configuration	.env-based settings
Authentication	PHP Sessions
External Requests	PHP cURL
Data Format	JSON
Scheduled Jobs	Cron / Windows Task Scheduler
🗄️ Database Design

FlyOn ships with a complete SQL schema located at:

database/schema.sql

The schema includes application tables and sample data.

Main entities
erDiagram

    USERS ||--o{ BOOKINGS : creates
    USERS ||--o| LOYALTY : owns
    USERS ||--o{ NOTIFICATIONS : receives
    USERS ||--o{ REVIEWS : writes
    USERS ||--o{ ACTIVITY_LOGS : generates

    AIRLINES ||--o{ FLIGHTS : operates

    AIRPORTS ||--o{ FLIGHTS : departure
    AIRPORTS ||--o{ FLIGHTS : arrival

    FLIGHTS ||--o{ BOOKINGS : receives
    FLIGHTS ||--o{ SEATS : contains
    FLIGHTS ||--o{ REVIEWS : receives
    FLIGHTS ||--o{ FLIGHT_SYNC_LOGS : generates

    BOOKINGS ||--o{ PASSENGERS : contains
    BOOKINGS ||--o{ PAYMENTS : has
    BOOKINGS ||--o| REVIEWS : may_have

    PROMOTIONS {
        int id
        string code
        string discount_type
        decimal discount_value
        datetime valid_from
        datetime valid_until
    }

    PRICING_RULES {
        int id
        string rule_type
        string adjustment_type
        decimal adjustment_value
    }
Important database tables
Table	Purpose
users	User and administrator accounts
airlines	Airline information
airports	Airport and city information
flights	Flight schedules, prices and availability
bookings	Customer reservations
passengers	Passenger information
seats	Flight seat inventory
promotions	Promotional codes and discounts
loyalty	Loyalty points and membership tiers
notifications	User notification records
pricing_rules	Dynamic pricing configuration
reviews	User ratings and reviews
payments	Payment transaction records
activity_logs	System/user activity tracking
flight_sync_logs	Flight synchronization history
system_settings	System-level configuration
📁 Project Structure
FlyOn/
│
├── admin/
│   ├── add_flight.php
│   ├── add_promotion.php
│   ├── add_seats.php
│   ├── approve_booking.php
│   ├── bookings.php
│   ├── cancel_flight.php
│   ├── dashboard.php
│   ├── flights.php
│   ├── login.php
│   ├── promotions.php
│   ├── reject_booking.php
│   ├── sync_gozayaan.php
│   └── users.php
│
├── api/
│   ├── booking_api.php
│   ├── notification_api.php
│   ├── payment_api.php
│   ├── search_airports.php
│   └── search_api.php
│
├── assets/
│   ├── css/
│   │   ├── custom-theme.css
│   │   ├── dark-mode.css
│   │   └── style.css
│   │
│   ├── image/
│   │   ├── FlyOn3.png
│   │   ├── Flyon2.png
│   │   └── online-shopping-concept.jpg
│   │
│   └── js/
│       └── main.js
│
├── booking/
│   ├── step1_passenger.php
│   ├── step2_seat.php
│   ├── step3_addons.php
│   ├── step4_payment.php
│   └── step5_confirmation.php
│
├── cache/
│   └── index.php
│
├── cron/
│   ├── index.php
│   └── sync_gozayaan.php
│
├── database/
│   └── schema.sql
│
├── includes/
│   ├── GoZayaanIntegration.php
│   ├── db_connect.php
│   ├── footer.php
│   ├── functions.php
│   ├── header.php
│   └── navbar.php
│
├── uploads/
│   └── index.php
│
├── user/
│   ├── booking_details.php
│   ├── bookings.php
│   ├── cancel_booking.php
│   ├── dashboard.php
│   ├── login.php
│   ├── logout.php
│   ├── loyalty.php
│   ├── profile.php
│   ├── rate_flight.php
│   └── register.php
│
├── .gitignore
├── .htaccess
├── 403.php
├── 404.php
├── 500.php
├── about.php
├── check_admin.php
├── config.php
├── contact.php
├── flight_details.php
├── index.php
├── search.php
└── README.md
🔌 API Overview

FlyOn provides PHP endpoints that return JSON responses for asynchronous application operations.

Flight Search API
GET /api/search_api.php

Example:

/api/search_api.php?from=Dhaka&to=Chittagong&departure_date=2026-09-20&passengers=1&class=economy

The endpoint can:

retrieve matching flights
calculate flight duration
calculate dynamic prices
update demand/search counts
retrieve return flights for round trips
Airport Search API
GET /api/search_airports.php

Used for airport/location search and autocomplete functionality.

Booking API
/api/booking_api.php

Supported operations include:

POST   ?action=create
POST   ?action=update
GET    ?action=list
GET    ?action=details
DELETE booking

Authentication is required.

Payment API
POST /api/payment_api.php

Handles the booking payment workflow and updates booking/payment records after successful processing.

Notification API
/api/notification_api.php

Provides notification-related application operations.

⚙️ Installation & Setup
Prerequisites

Before running FlyOn, install:

Apache
PHP
MySQL or MariaDB
PHP PDO MySQL extension
PHP cURL extension
a modern web browser

The easiest Windows development environment is:

XAMPP
WAMP
Laragon
1. Clone the Repository
git clone https://github.com/MunamRahman/FlyOn---A-Dynamic-Air-Ticketing-System.git

Move into the project directory:

cd FlyOn---A-Dynamic-Air-Ticketing-System

For XAMPP, place the project inside:

C:\xampp\htdocs\

For example:

C:\xampp\htdocs\FlyOn
2. Create the Database

Start Apache and MySQL.

Open phpMyAdmin:

http://localhost/phpmyadmin

Import:

database/schema.sql

Alternatively:

mysql -u root -p < database/schema.sql

The script creates:

flyon_db

and populates the database with initial/sample information.

3. Configure Environment Variables

Create a .env file in the project's root directory.

Example:

# Application
APP_NAME=FlyOn
APP_URL=http://localhost/FlyOn
APP_ENV=development
APP_DEBUG=true

# Database
DB_HOST=localhost
DB_NAME=flyon_db
DB_USER=root
DB_PASS=

# Session
SESSION_LIFETIME=120
CSRF_TOKEN_NAME=csrf_token

# Email
MAIL_HOST=smtp.mailtrap.io
MAIL_PORT=2525
MAIL_USERNAME=
MAIL_PASSWORD=
MAIL_FROM_ADDRESS=noreply@flyon.com
MAIL_FROM_NAME=FlyOn

# SMS
SMS_API_KEY=
SMS_SENDER_ID=FlyOn

# GoZayaan
GOZAYAAN_API_KEY=
GOZAYAAN_API_URL=https://gozayaan.com/api/v1
GOZAYAAN_SYNC_ENABLED=false
GOZAYAAN_SYNC_INTERVAL=3600

# SSLCommerz
SSLCOMMERZ_STORE_ID=
SSLCOMMERZ_STORE_PASSWORD=
SSLCOMMERZ_MODE=sandbox

# Stripe
STRIPE_PUBLIC_KEY=
STRIPE_SECRET_KEY=

# PayPal
PAYPAL_CLIENT_ID=
PAYPAL_SECRET=
PAYPAL_MODE=sandbox

# File Upload
MAX_UPLOAD_SIZE=5242880
ALLOWED_IMAGE_TYPES=jpg,jpeg,png,gif

# Loyalty Program
POINTS_PER_DOLLAR=10
SILVER_THRESHOLD=1000
GOLD_THRESHOLD=5000
PLATINUM_THRESHOLD=10000

# Booking
SEAT_LOCK_DURATION=600
BOOKING_CANCELLATION_HOURS=24

Never commit real API keys, database passwords, or payment credentials to GitHub.

4. Run the Application

Visit:

http://localhost/FlyOn

If the project directory has a different name, update APP_URL accordingly.

👨‍💼 Demo Administrator

The SQL schema includes a development administrator account.

Email:    admin@flyon.com
Password: admin123

⚠️ Security Warning: Change or remove this account immediately before deploying the project publicly.

Admin login:

http://localhost/FlyOn/admin/login.php
🔄 Application Flow
sequenceDiagram

    actor User
    participant UI as FlyOn UI
    participant Search as Search Engine
    participant DB as MySQL
    participant Booking as Booking Engine
    participant Payment as Payment Layer

    User->>UI: Search flight
    UI->>Search: Submit route/date/class
    Search->>DB: Query scheduled flights
    DB-->>Search: Matching flights
    Search->>Search: Apply dynamic pricing
    Search-->>UI: Display results

    User->>UI: Select flight
    UI->>Booking: Start reservation
    Booking->>User: Request passenger details
    User->>Booking: Submit passenger data

    Booking->>User: Display seats
    User->>Booking: Select seats

    Booking->>User: Display add-ons/promotions
    User->>Booking: Select options

    Booking->>Payment: Create payment request
    Payment->>DB: Update payment/booking state
    Payment-->>Booking: Payment result

    Booking->>DB: Confirm booking
    Booking-->>User: Booking confirmation
🇧🇩 Bangladesh-Focused Sample Dataset

The included database seed data contains several Bangladesh aviation examples.

Airlines include
Biman Bangladesh Airlines
US-Bangla Airlines
Novoair

alongside international carriers such as:

Emirates
Qatar Airways
Singapore Airlines
Turkish Airlines
Air India
Bangladesh airports include
Hazrat Shahjalal International Airport — DAC
Shah Amanat International Airport — CGP
Osmani International Airport — ZYL
Cox's Bazar Airport — CXB
Jessore Airport — JSR
Saidpur Airport — SPD
Barisal Airport — BZL

The schema also contains international airports and sample domestic/international flight records for testing.

💰 Currency & Regional Configuration

FlyOn includes formatting support for:

🇧🇩 BDT — ৳
🇺🇸 USD — $
🇪🇺 EUR — €
🇬🇧 GBP — £
🇮🇳 INR — ₹

The application timezone defaults to:

Asia/Dhaka
🚀 Future Development

FlyOn provides a strong foundation for further development.

Potential improvements include:

Production SSLCommerz integration

Production Stripe integration

Production PayPal integration

verified external airline/GDS API

real-time flight status

email verification

OTP authentication

live SMS gateway

e-ticket PDF generation

QR-code boarding/ticket verification

automated invoice generation

refund automation

multi-city booking

advanced fare classes

baggage management

airline-specific seat maps

real-time seat inventory

saved travelers

saved payment methods

advanced admin analytics

revenue reporting

flight-delay notifications

Progressive Web App support

mobile application

Docker deployment

automated testing

CI/CD pipeline

🎯 Project Objectives

FlyOn was designed to demonstrate how several real-world software engineering concepts can work together within one application:

relational database design
user authentication
role-based authorization
session management
flight inventory management
multi-stage transactional workflows
dynamic business rules
dynamic pricing
REST-style API design
external API architecture
background/scheduled jobs
payment abstractions
reusable PHP components
responsive web design
administrative dashboards
⚠️ Development Status

FlyOn is primarily an educational and development project.

Some integrations intentionally contain development-stage or simulated implementations.

Before using FlyOn in a real commercial environment, additional work should be completed for:

payment-provider verification
live airline inventory integration
production email/SMS delivery
comprehensive security auditing
concurrency testing
transactional booking guarantees
automated testing
rate limiting
monitoring
PCI-related payment requirements
privacy/data-protection compliance
🤝 Contributing

Contributions, suggestions, bug reports, and improvements are welcome.

Recommended workflow
Fork the repository
Create a feature branch
git checkout -b feature/your-feature
Commit your changes
git commit -m "Add: your feature description"
Push the branch
git push origin feature/your-feature
Open a Pull Request
🐛 Bug Reports

When reporting a bug, please include:

a clear description
steps to reproduce
expected behavior
actual behavior
PHP version
MySQL/MariaDB version
operating system
browser
relevant screenshots or logs
👨‍💻 Maintainer

Munam Rahman

GitHub: @MunamRahman

Repository:

FlyOn — A Dynamic Air Ticketing System

📜 License

A software license has not currently been specified for this repository.

Until a license is added, usage, modification, and redistribution rights should not be assumed beyond what GitHub's Terms of Service permit.

If the project is intended to be open source, consider adding an appropriate license such as MIT, Apache-2.0, or GPL-3.0.

⭐ Support the Project

If you find FlyOn useful or interesting:

⭐ Star the repository
🍴 Fork the project
🐛 Report issues
💡 Suggest improvements
🤝 Contribute new features

<div align="center">

✈️ FlyOn
Your journey starts before takeoff.

Built to explore the engineering behind modern flight-booking systems.

⬆ Back to Top

</div> FlyOn---A-Dynamic-Air-Ticketing-System
