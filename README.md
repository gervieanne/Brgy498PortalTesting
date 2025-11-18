![Header](https://i.imgur.com/3ujoPvK.jpeg)
  <h3 align="center">Where community meets innovation</h3>

---
<div style="text-align: justify;"> “EaseAccess” is a website-based system designed to streamline document requests for Brgy 468 Zone II, Sampaloc, Manila. It improves efficiency for both Barangay Officials and residents by digitizing the request and approval process. </div> 


## Quick Start

For full documentation and tutorials, check out:

- 📘 [Documentation](https://www.notion.so/EaseAcess-Documentation-2a71ca71d7ba804c8d86f52894d441df?source=copy_link)
- 🎥 [Video Guide](https://drive.google.com/drive/folders/1NTW4akzBv7vwe1TgndjRpIWwH9SovoR3?usp=drive_link)

## Setup

> For local setup:

1. Clone the repository or download the source code.
2. Place the project folder inside your local server directory (`htdocs` for XAMPP).
3. Import the `498portal.sql` file into your local MySQL server using phpMyAdmin.
4. Update database credentials in `db_connection.php`.
5. Start Apache and MySQL via XAMPP.
6. Access the system via `http://localhost/your-folder-name`.

Admin Access:

    Username: Administrator
    Password: GROUP1SQA
User Access:

    Username: [FIRSTNAME in caps]
    Password: barangay498[firstname in lowercase]
![enter image description here](https://i.imgur.com/yOmgTFO.jpeg)
![UserName](https://i.imgur.com/TAn7WwU.jpeg)

## Features

- 🔐 Dual access: Admin and Resident
- 📄 Resident document request form
- 🗂 Admin dashboard to manage and process requests
- 📢 Announcement and calendar module for residents' view
- 👤 Resident profile management (editable by user, viewable by admin)

## Tech Stack

| Layer   | Technologies Used              |
|---------|--------------------------------|
| Client  | HTML, CSS, JavaScript          |
| Server  | PHP, MySQL                     |
| Libraries | Font Awesome, Chart.js       |

##  Demo
| Feature                     | Description                                      |
|----------------------------|--------------------------------------------------|
| Document Request Form      | Residents submit requests for barangay documents |
| Admin Dashboard            | View, approve, reject, and track requests        |
| Calendar & Announcements   | Admins post updates visible to residents         |
| Profile Management         | Residents edit their info; admins view all data |

---

## Folder Structure

    BRGY498PORTAL/
    │
    ├── admin-announcement/        # Admin: Manage announcements
    ├── admin-calendar/            # Admin: Manage calendar events
    ├── admin-document-req/        # Admin: Handle document requests
    ├── admin-login/               # Admin login system
    ├── admin-officials/           # Admin: Manage barangay officials
    ├── admin-residents-info/      # Admin: View resident profiles
    │
    ├── user-announcement/         # Resident: View announcements
    ├── user-calendar/             # Resident: View calendar
    ├── user-dashboard/            # Resident dashboard
    ├── user-login/                # Resident login system
    ├── user-officials/            # Resident: View barangay officials
    ├── user-profile/              # Resident: Edit profile
    ├── user-request/              # Resident: Submit document requests
    │
    ├── images/                    # Static images used in the app
    ├── includes/                  # Shared PHP includes (e.g., headers, footers)
    ├── landingpage/               # Public landing page
    ├── preloader/                 # Preloader animation or logic
    ├── uploads/                   # Uploaded files (e.g., official photos)
    │
    ├── admin-dashboard.php        # Main admin dashboard
    ├── admin-charts-dynamic.js    # Dynamic chart logic for admin
    ├── admin-dashboard.css        # Admin dashboard styling
    ├── admin-dashboard.js         # Admin dashboard interactivity
    ├── get-announcement.php       # Fetch announcements
    ├── logout-modal.js            # Logout confirmation modal
    ├── logout.php                 # Session termination

## Authors

Developed by **BSIT 2-1 Group 1.**
Pamantasan ng Lungsod ng Maynila
© 2025. For Academic Purposes Only. 



