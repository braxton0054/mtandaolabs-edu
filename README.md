# mtandaolabsEdu — School Management System

**mtandaolabsEdu** is a multi-school management system by **Mtandao Labs** (Nairobi, Kenya), forked and rebranded from the excellent [Skuul](https://github.com/yungifez/skuul) project (MIT licensed, by yungifez).

It makes school administration and activities a breeze — classes, students, teachers, timetables, exams, fees and more, with **multi-school / multi-tenant support** built in (one super admin manages many schools).

---

## About this fork

This project is a **rebranded fork of Skuul** (v2, Laravel 13 / PHP 8.5 / Livewire). It keeps the MIT license and credits the original author — see [LICENSE](LICENSE).

**Mtandao Labs modifications:**
- Rebranded UI: name, logo, login page → mtandaolabsEdu branding
- Deployed at https://skuul.mtandaolabs.com (Laravel Sail, Docker, Caddy + Cloudflare)
- *(More CBC-specific work in progress: Kenyan CBC class structure PP1–Grade 9, strand-based assessment, M-Pesa fee payments, parent portal)*

**Upstream project:** https://github.com/yungifez/skuul · by [yungifez](https://github.com/yungifez)
**UI component library:** https://github.com/yungifez/april-ui (required as a sibling path repo)

---

## Requirements

* PHP 8.3+ (project targets 8.5)
* Composer
* Node + npm (for asset bundling)
* MySQL 8 (or MariaDB)
* Docker + Docker Compose (for Laravel Sail)

## Installation (Laravel Sail / Docker)

```shell
git clone https://github.com/braxton0054/mtandaolabs-edu.git ./mtandaolabs-edu
cd mtandaolabs-edu

# the project uses a local path dependency (april-ui) — clone it as a sibling
git clone https://github.com/yungifez/april-ui.git ../april-ui

# composer install (inside docker so PHP isn't needed on the host)
docker run --rm -v /root:/root -w /root/mtandaolabs-edu composer:latest install --no-interaction --prefer-dist

cp .env.example .env
# edit .env: APP_URL, DB_*, APP_PORT, WWWUSER/WWWGROUP, FORWARD_DB_PORT etc.

docker compose up -d --build

docker compose exec laravel.test php artisan key:generate
docker compose exec laravel.test php artisan migrate --force
docker compose exec laravel.test php artisan db:seed --class=RunInProductionSeeder --force
docker compose exec laravel.test php artisan storage:link

# frontend assets
docker compose exec laravel.test bash -c "cd /var/www/html && npm install && npm run build"

# create super admin
docker compose exec laravel.test php artisan skuul:create-super-admin
```

> **Note:** the app container runs PHP as uid/gid 1000 (`sail`). After bind-mounting the repo, run
> `chown -R 1000:1000 storage bootstrap/cache` or the app returns 500s (cannot write logs/views).

## Setup after install

1. Log in as the super admin you created
2. **Multi Schools → Create School** → add a school (name, address, initials)
3. **View Schools → Set School** (set it as your school of operation)
4. **Academic years → create an academic year + semester, set it active**
5. Then add class groups, classes, sections, students, teachers, subjects…

## Default access

* Super admin login via `php artisan skuul:create-super-admin`
* (In local/dev mode, Skuul seeds a demo super admin — see upstream docs)

## Credits & License

- Original project: **Skuul** by yungifez — https://github.com/yungifez/skuul (MIT)
- UI library: **april-ui** by yungifez — https://github.com/yungifez/april-ui (MIT)
- This fork: **mtandaolabsEdu** by Mtandao Labs — MIT license (see [LICENSE](LICENSE))
