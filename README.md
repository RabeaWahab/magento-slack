# Magento Slack Notifier
Slack Notifications for Magento

**Compatible with**

Magento CE 1.5+ and EE 1.1+

####To install using [modgit](https://github.com/jreinke/modgit)

```
cd MAGENTO_ROOT
modgit init
modgit -i plugin/:. add Rabee3_Slack https://github.com/rabee3/magento-slack.git

```

####Enable Slack incoming webhooks and obtain a token
[https://api.slack.com/incoming-webhooks](https://api.slack.com/incoming-webhooks)

## currently including:
- Notification on order creation. `this is based on a cron that runs every minute, please make sure your Magento cron.php is set to run at the same time`
- Hourly Notification of total orders.
- Customer new registration.

## to do list
- Notifications on exceptions.
- Notifications on stock changes.
