# System Sight

**See it. Build it. Improve it.**

System Sight is a Business Machine management system built with Laravel. It helps you visualize, manage, and improve your business processes through a hierarchical structure of Machines, Subsystems, Components, and Upgrades.

## Features

- 🎯 **Business Machine Dashboard** - Visual overview of all your business machines
- 🔧 **Hierarchical Structure** - Machines → Subsystems → Components → Upgrades
- 📊 **Health Monitoring** - Track component status (Smooth, Needs Love, On Fire)
- 🚀 **Upgrade Management** - Create and ship upgrades with steps and definitions
- 🔥 **Streak Tracking** - Monitor your consistency in shipping upgrades
- 🌐 **Multi-language** - Support for English and Vietnamese
- 📱 **PWA Support** - Install as a Progressive Web App

## Tech Stack

- **Backend:** Laravel 11
- **Database:** SQLite
- **Frontend:** Blade Templates, Vanilla JavaScript
- **Styling:** Custom CSS with Light Theme
- **Icons:** Font Awesome, Lucide Icons

## Installation

1. Clone the repository
```bash
git clone <repository-url>
cd system-sight
```

2. Install dependencies
```bash
composer install
npm install
```

3. Setup environment
```bash
cp .env.example .env
php artisan key:generate
```

4. Run migrations and seeders
```bash
php artisan migrate --seed
```

5. Start the development server
```bash
php artisan serve
```

Visit `http://localhost:8000` to access System Sight.

## Documentation

- [Business Machine Guide](BUSINESS_MACHINE_GUIDE.md) - Complete guide for using the system
- [Theme Update Summary](THEME_UPDATE_SUMMARY.md) - Details about the light theme

## Database Structure

```
machines (Level 1)
├── subsystems (Level 2)
│   └── components (Level 3)
│       └── upgrades (Level 4)
└── streaks (User progress tracking)
```

## Default Credentials

After seeding, you can login with:
- Username: (check your seeder)
- Password: (check your seeder)

## Contributing

Contributions are welcome! Please feel free to submit a Pull Request.

## License

This project is open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT).

---

Built with Laravel
