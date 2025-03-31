<div align="center">
    <a href="https://github.com/404NotFoundIndonesia/" target="_blank">
        <img src="./public/404NFID.png" width="150" alt="Hyperion">
    </a>

[![GitHub Stars](https://img.shields.io/github/stars/404NotFoundIndonesia/influenser.svg)](https://github.com/404NotFoundIndonesia/influenser/stargazers)
[![GitHub license](https://img.shields.io/github/license/404NotFoundIndonesia/influenser)](https://github.com/404NotFoundIndonesia/influenser/blob/main/LICENSE)
</div>

# Influenser - Influencer Campaign Management

## 📌 Overview
**Influenser** is a web application built with Laravel and Vue.js, designed by **404 Not Found Indonesia** to help manage endorsement campaigns and influencers (KOL). The application enables brands and agencies to search, manage, and analyze influencer performance in their marketing campaigns.

## ✨ Features
- **Influencer (KOL) Management**: Add, edit, and delete influencer data along with performance metrics.
- **Campaign Management**: Create, manage, and track the progress of endorsement campaigns.
- **Engagement Tracking**: Analyze influencer interactions with campaigns.
- **Notifications & Reminders**: Send automated notifications to influencers and clients.
- **Invoices & Payments**: Manage influencer payments transparently.
- **Dashboard Analytics**: View insights on campaign and influencer performance.
- **CreatorDB Integration**: Fetch and add influencer data automatically from CreatorDB.

## 🏗️ Tech Stack
- **Frontend**: Vue.js, TailwindCSS
- **Backend**: Laravel 12
- **Database**: MySQL / PostgreSQL
- **Storage**: AWS S3 / Laravel Filesystem
- **Notifications**: Email (SMTP)

## 🚀 Installation
### Prerequisites
- PHP 8.4+
- Composer
- Node.js & npm
- MySQL

### Setup
1. **Clone Repository**
   ```bash
   git clone https://github.com/404NotFoundIndonesia/influenser.git
   cd influenser
   ```
2. **Install Dependencies**
   ```bash
   composer install
   npm install
   ```
3. **Setup Environment**
   ```bash
   cp .env.example .env
   php artisan key:generate
   ```
   Configure `.env` according to your database and storage settings.
4. **Migrate Database**
   ```bash
   php artisan migrate --seed
   ```
5. **Run Development Server**
   ```bash
   php artisan serve
   npm run dev
   ```
6. **Setup Storage Link**
   ```bash
   php artisan storage:link
   ```
7. **Login**
   
   You can log in with the following credential:
   - email: `iqbaleff214@gmail.com`
   - password: `password`

8. **Dummy Data (optional)**
   ```bash
   php artisan db:seed --class=DummySeeder
   ```

## 📜 License
[MIT License](LICENSE).

## 🤝 Contributing
Pull requests are welcome! If you want to contribute, please fork this repo and submit a pull request with your proposed changes.

## 📧 Contact
For questions or collaborations, reach out via email: `iqbaleff214@gmail.com` or create an issue in this repository.

---
🚀 Build and manage endorsement campaigns more efficiently with **Influenser**!

