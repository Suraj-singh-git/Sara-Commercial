# Production ENV Template (Sara Commercial)

Use this as a base for your production `.env`.

> Replace all placeholder values before deployment.
> Never commit real production secrets to Git.

```env
#############################################
# APP
#############################################
APP_NAME="Sara Commercial"
APP_ENV=production
APP_DEBUG=false
APP_URL=https://your-domain.com

APP_LOCALE=en
APP_FALLBACK_LOCALE=en
APP_FAKER_LOCALE=en_US

#############################################
# LOGGING
#############################################
LOG_CHANNEL=stack
LOG_STACK=single
LOG_LEVEL=info

#############################################
# DATABASE (MySQL)
#############################################
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=sara
DB_USERNAME=your_db_user
DB_PASSWORD=your_db_password

#############################################
# SESSION / CACHE / QUEUE
#############################################
SESSION_DRIVER=database
SESSION_LIFETIME=120
SESSION_ENCRYPT=false
SESSION_PATH=/
SESSION_DOMAIN=.your-domain.com

CACHE_STORE=database

QUEUE_CONNECTION=database
DB_QUEUE_CONNECTION=mysql
DB_QUEUE_TABLE=jobs
DB_QUEUE=default
DB_QUEUE_RETRY_AFTER=120

#############################################
# MAIL (SMTP example)
#############################################
MAIL_MAILER=smtp
MAIL_SCHEME=tls
MAIL_HOST=smtp.your-provider.com
MAIL_PORT=587
MAIL_USERNAME=your_smtp_username
MAIL_PASSWORD=your_smtp_password
MAIL_FROM_ADDRESS=hello@your-domain.com
MAIL_FROM_NAME="Sara Commercial"

#############################################
# TWILIO (SMS + WhatsApp)
#############################################
TWILIO_ACCOUNT_SID=ACxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx
TWILIO_AUTH_TOKEN=xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx
TWILIO_SMS_FROM=+1XXXXXXXXXX
TWILIO_WHATSAPP_FROM=+1XXXXXXXXXX
TWILIO_DEFAULT_COUNTRY_CODE=+91

#############################################
# RAZORPAY
#############################################
RAZORPAY_KEY_ID=rzp_live_xxxxxxxxxxxx
RAZORPAY_KEY_SECRET=xxxxxxxxxxxxxxxx
RAZORPAY_WEBHOOK_SECRET=xxxxxxxxxxxxxxxx

#############################################
# DELHIVERY
#############################################
DELHIVERY_API_TOKEN=xxxxxxxxxxxxxxxx
DELHIVERY_WEBHOOK_TOKEN=xxxxxxxxxxxxxxxx

#############################################
# FILESYSTEM
#############################################
FILESYSTEM_DISK=public

#############################################
# OPTIONAL: REDIS (if used later)
#############################################
REDIS_CLIENT=phpredis
REDIS_HOST=127.0.0.1
REDIS_PASSWORD=null
REDIS_PORT=6379
```

---

## Post-Deploy Commands

Run these once after deploy:

```bash
php artisan key:generate --force
php artisan migrate --force
php artisan storage:link
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

---

## Required Runtime Processes

Queue worker:

```bash
php artisan queue:work --queue=notifications,default --tries=3 --sleep=3
```

Scheduler (cron every minute):

```bash
* * * * * cd /path/to/project && php artisan schedule:run >> /dev/null 2>&1
```

---

## Webhook Endpoints to Register

- Razorpay: `https://your-domain.com/webhooks/razorpay`
- Delhivery: `https://your-domain.com/webhooks/delhivery`

---

## Security Checklist

- Keep `.env` out of Git
- Use HTTPS everywhere
- Restrict server ports/firewall
- Rotate API keys periodically
- Monitor failed jobs and logs

