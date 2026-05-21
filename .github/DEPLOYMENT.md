# Deployment Setup

## Required GitHub Secrets

Go to: **Settings → Secrets and variables → Actions**

| Secret | Value |
|--------|-------|
| `SSH_HOST` | VPS IP yoki domain (masalan: `203.0.113.10`) |
| `SSH_USER` | SSH user (masalan: `root`) |
| `SSH_PRIVATE_KEY` | Private SSH key (`cat ~/.ssh/id_ed25519`) |
| `SSH_PORT` | SSH port (odatda `22`, optional) |

## VPS Setup (bir marta)

```bash
# VPS da deploy uchun SSH key yaratish
ssh-keygen -t ed25519 -C "github-deploy" -f ~/.ssh/github_deploy -N ""

# Public key ni authorized_keys ga qo'shish
cat ~/.ssh/github_deploy.pub >> ~/.ssh/authorized_keys

# Private key ni nusxa olib GitHub Secret ga qo'shish
cat ~/.ssh/github_deploy
```

## Workflows

- **ci.yml** — har bir push/PR da test va lint
- **deploy.yml** — faqat `main` branch ga push bo'lganda VPS ga deploy

## Deploy jarayoni

1. `git pull` — yangi kodni oladi
2. `composer install --no-dev` — production dependencies
3. `npm run build` — Vite assets
4. `php artisan *:cache` — config, route, view, event cache
5. `php artisan migrate --force` — migratsiyalar
6. `php artisan queue:restart` — queue workers restart
