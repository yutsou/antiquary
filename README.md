## Tech Stack & Architecture

### Backend Framework
- **Laravel** – Core backend framework (Artisan CLI, routing, middleware, service providers).
- **PHP >= 8.0.2** – Defined in `composer.json`.

### Dependency & Asset Management
- **Composer** – Backend dependency management.
- **npm + Laravel Mix** – Frontend asset bundling and compilation.
- **Webpack & PostCSS** – Compiles JS/CSS assets into the `public/` directory.

### Real-Time & Event Broadcasting
- **Laravel Broadcasting**
- **laravel-echo + pusher-js** (frontend)
- **pusher/pusher-php-server** (backend)  
  Enables real-time notifications, live updates, and event-driven interactions.

### Authentication & API
- **Laravel Sanctum** – Token-based API authentication.
- **Laravel Socialite** – OAuth-based social login integration.

### Search & Data Access
- **Laravel Scout** – Full-text search abstraction (pluggable drivers).
- **Eloquent ORM** – Database abstraction and model-based design.

### Background Jobs & Queues
- **Laravel Queue System**
- **Beanstalkd (pda/pheanstalk)** – Job queue processing.
- **Redis (predis/predis)** – Caching, queue backend, and broadcasting support.

### Third-Party Integrations
- **ECPay (ecpay/sdk)** – Payment gateway integration.
- **LINE Bot SDK (linecorp/line-bot-sdk)** – Messaging bot and notification integration.

### Data & UI Utilities
- **Yajra DataTables** – Server-side DataTables integration.
- **kalnoy/nestedset** – Hierarchical / tree-structured data management.
- **spatie/laravel-sitemap** – Automated sitemap generation.

### HTTP & Networking
- **Guzzle** – Backend HTTP client for external API calls.
- **Axios** – Frontend HTTP client.

### Development & Testing Tools
- **Laravel Sail** – Docker-based local development environment.
- **PHPUnit** – Unit and integration testing.
- **fakerphp/faker** – Test data generation.
- **Mockery** – Mocking framework.
- **barryvdh/laravel-ide-helper** – IDE autocompletion support.
- **spatie/laravel-ignition** – Error handling and debugging.

### Code Structure & Conventions
- **PSR-4 Autoloading**
- **MVC Architecture**
- **Artisan CLI**
- **Laravel routing, middleware, and service provider conventions**
