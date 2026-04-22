# Required APIs for Production Go-Live

This document lists the external APIs/services and credentials needed to run **Sara Commercial** in production.

## 1) Must-Have APIs

### 1.1 SMS / WhatsApp (OTP + notifications)
- Provider examples: **Twilio** (current integration), MSG91, Gupshup, Kaleyra
- Used for:
  - OTP login
  - order notifications (placed, shipped, delivered, etc.)

### 1.2 Payment Gateway
- Provider: **Razorpay**
- Used for:
  - full online payment
  - partial payment (10% online + COD)
  - payment webhook status updates

### 1.3 Shipping / Tracking
- Provider: **Delhivery**
- Used for:
  - shipment creation
  - waybill generation
  - tracking updates via webhook/polling

### 1.4 Email Provider
- Provider options:
  - SMTP (Hostinger/Zoho/custom SMTP)
  - Mailgun / AWS SES / Postmark
- Used for:
  - register/login/order email alerts

---

## 2) Required Environment Variables

## 2.1 Twilio
- `TWILIO_ACCOUNT_SID`
- `TWILIO_AUTH_TOKEN`
- `TWILIO_SMS_FROM`
- `TWILIO_WHATSAPP_FROM`
- `TWILIO_DEFAULT_COUNTRY_CODE`

## 2.2 Razorpay
- `RAZORPAY_KEY_ID`
- `RAZORPAY_KEY_SECRET`
- `RAZORPAY_WEBHOOK_SECRET`

## 2.3 Delhivery
- `DELHIVERY_API_TOKEN` (or provider key/token as per account)
- `DELHIVERY_WEBHOOK_TOKEN`

## 2.4 Mail
- `MAIL_MAILER`
- `MAIL_HOST`
- `MAIL_PORT`
- `MAIL_USERNAME`
- `MAIL_PASSWORD`
- `MAIL_ENCRYPTION`
- `MAIL_FROM_ADDRESS`
- `MAIL_FROM_NAME`

---

## 3) Webhook URLs to Configure in Provider Dashboards

- Razorpay webhook:
  - `https://your-domain.com/webhooks/razorpay`
- Delhivery webhook:
  - `https://your-domain.com/webhooks/delhivery`
- Twilio status callback (optional but recommended):
  - your message-delivery callback URL

---

## 4) Production Infrastructure Requirements (Non-API)

- Domain + SSL
- VPS/Server with PHP + MySQL
- Queue worker running:
  - `php artisan queue:work --queue=notifications,default`
- Scheduler running:
  - cron + `php artisan schedule:run`
- Storage link:
  - `php artisan storage:link`
- Monitoring for:
  - logs
  - failed queue jobs
- Backup setup:
  - DB backups
  - uploaded files backups

---

## 5) Optional but Recommended APIs

- Error monitoring: Sentry
- Product analytics: GA4 / PostHog
- Address validation API
- CDN/Image optimization service
- Fraud/risk tools for payments

