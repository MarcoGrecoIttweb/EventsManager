# Event Management System

A comprehensive Laravel-based event management platform with user registration, event creation, participation system, and administrative controls.

## Features

- **Event Management**: Create, edit, and manage events with cover images and galleries
- **User System**: Registration with admin approval workflow
- **Participation System**: RSVP for events with guest management
- **Comments**: Rich text comments with editing capabilities
- **Admin Panel**: Full administrative control over users and events
- **Newsletter System**: Email newsletter functionality
- **Responsive Design**: Mobile-friendly Bootstrap interface

## Requirements

- PHP 8.1+
- Laravel 10+
- MySQL/PostgreSQL/SQLite
- Composer
- Node.js & NPM (for frontend assets)

## Installation

1. **Clone the repository**
   ```bash
   git clone <repository-url>
   cd event-management-system
Install PHP dependencies

bash
composer install
Install frontend dependencies

bash
npm install
Environment setup

bash
cp .env.example .env
php artisan key:generate
Configure environment
Edit .env file with your database and mail settings:

env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=event_management
DB_USERNAME=your_username
DB_PASSWORD=your_password

MAIL_MAILER=smtp
MAIL_HOST=your_smtp_host
MAIL_PORT=587
MAIL_USERNAME=your_email
MAIL_PASSWORD=your_password
MAIL_ENCRYPTION=tls
Database setup

bash
php artisan migrate --seed
Storage link

bash
php artisan storage:link
Build assets

bash
npm run build
Configuration
Event Configuration
Create a configuration file config/events.php to customize event settings:

php
<?php

return [
    'images' => [
        'cover' => [
            'max_size' => 2048, // KB
            'mimes' => ['jpeg', 'png', 'jpg', 'gif', 'webp'],
            'dimensions' => [
                'min_width' => 400,
                'min_height' => 300,
            ],
        ],
        'gallery' => [
            'max_size' => 5120, // KB
            'mimes' => ['jpeg', 'png', 'jpg', 'gif', 'webp'],
            'max_files' => 10,
        ],
    ],
    
    'participation' => [
        'allow_guests' => true,
        'max_guests_per_user' => 3,
        'auto_approve_participation' => false,
    ],
    
    'limits' => [
        'max_participants_default' => 50,
        'events_per_page' => 12,
        'upcoming_events_days' => 30,
    ],
    
    'features' => [
        'comments' => true,
        'rich_text_editor' => true,
        'event_gallery' => true,
        'cover_images' => true,
        'past_events' => true,
    ],
    
    'notifications' => [
        'new_event' => true,
        'event_reminder' => true,
        'reminder_days_before' => 1,
    ],
];
User Registration
New users require admin approval

Admin users can approve/ban users

User roles: Admin, Approved User, Pending User

Event Features
Cover images and gallery support

Participant limits with guest management

Rich text descriptions with TinyMCE

Date-based event filtering (upcoming/past)

Guest System
Events can allow guests (configurable per event)

Maximum guests per user: 1-10 (configurable)

Guest count included in participant limits

Usage
Access the application

Homepage: /

Events list: /events

Past events: /events/past (authenticated users only)

Admin Access

Admin dashboard: /admin/dashboard

Manage events: /admin/events

User management: /admin/users

Newsletter: /admin/newsletter

Default Admin Account
After seeding, create an admin user through registration or manually:

bash
php artisan tinker
\App\Models\User::factory()->create(['is_admin' => true]);
Key Commands
bash
# Run development server
php artisan serve

# Run queue worker (if using email features)
php artisan queue:work

# Clear caches
php artisan cache:clear
php artisan route:clear
php artisan config:clear
php artisan view:clear
Security Features
CSRF protection

XSS prevention in rich text fields

SQL injection prevention via Eloquent

File upload validation

Role-based access control

User approval system

Customization
Event Settings
Modify config/events.php to adjust:

Image upload sizes and types

Guest management settings

Participation limits

Feature toggles

Styling
Bootstrap 5 framework

Custom CSS in view files

Responsive design

#Support
For issues and questions, please check the application logs in storage/logs/ and ensure all environment variables are properly configured.
