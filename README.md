# Mautic Zender SMS Plugin

Mautic SMS transport plugin for sending SMS messages via the [Zender](https://zender.hollandworx.nl) API.

## Features

- Send SMS through linked Android devices or gateway credits
- Configurable SIM slot selection and send priority
- E.164 phone number normalization
- Full logging of send results

## Requirements

- Mautic 4.x, 5.x, 6.x or 7.x
- PHP 8.0+
- A Zender account with API access

## Installation

### Via Composer (Docker)

Ensure the composer directories exist with correct permissions:

```bash
docker exec --user root mautic_web mkdir -p /var/www/.composer/cache
docker exec --user root mautic_web chown -R www-data:www-data /var/www/.composer
docker exec --user root mautic_web mkdir -p /var/www/.npm
docker exec --user root mautic_web chown -R www-data:www-data /var/www/.npm
```

Allow dev packages (only needed once per Mautic installation):

```bash
docker exec --user www-data --workdir /var/www/html mautic_web \
  composer config minimum-stability dev
docker exec --user www-data --workdir /var/www/html mautic_web \
  composer config prefer-stable true
```

Add the GitHub repository and install the plugin:

```bash
docker exec --user www-data --workdir /var/www/html mautic_web \
  composer config repositories.mautic-zender-sms vcs \
  https://github.com/radata/mautic-zender-sms --no-interaction
docker exec --user www-data --workdir /var/www/html mautic_web \
  composer require radata/mautic-zender-sms:dev-main -W --no-interaction
```

Update to the latest version:

```bash
docker exec --user www-data --workdir /var/www/html mautic_web \
  composer update radata/mautic-zender-sms -W --no-interaction
```

If the npm post-install hook fails after composer require, fix it:

```bash
docker exec --user root mautic_web rm -rf /var/www/html/node_modules
docker exec --user root mautic_web mkdir -p /var/www/.npm
docker exec --user root mautic_web chown -R www-data:www-data /var/www/.npm
docker exec --user www-data --workdir /var/www/html mautic_web npm ci --no-audit
```

### Manual Installation (docker cp)

```bash
docker cp plugins/ZenderSmsBundle mautic_web:/var/www/html/plugins/ZenderSmsBundle
docker exec --user root mautic_web chown -R www-data:www-data /var/www/html/plugins/ZenderSmsBundle
```

### Post-Installation

Clear cache (hard delete required), reload plugins, then enable in UI:

```bash
docker exec --user www-data mautic_web rm -rf /var/www/html/var/cache/prod
docker exec --user www-data --workdir /var/www/html mautic_web php bin/console cache:warmup --env=prod
docker exec --user www-data --workdir /var/www/html mautic_web php bin/console mautic:plugins:reload
```

1. Go to **Settings > Plugins > Zender SMS**
2. Set **Published** to **Yes**
3. Enter your API Secret from Zender (Tools > API Keys)
4. In Features tab: select Mode (devices/credits), enter Device ID or Gateway ID
5. Go to **Settings > Configuration > SMS Settings** and select **Zender SMS** as transport

## Configuration

In the plugin settings:

| Field | Description |
|---|---|
| **API Secret** | Your Zender API secret key |
| **Sending Mode** | `Devices` (linked Android) or `Credits` (gateway) |
| **Device ID** | Linked device unique ID (required for devices mode) |
| **Gateway ID** | Gateway or partner device ID (required for credits mode) |
| **SIM Slot** | SIM 1 or SIM 2 (devices mode only) |
| **Priority** | High (send immediately) or Normal (queued) |
| **API URL** | Zender API endpoint (default: `https://zender.hollandworx.nl`) |

After configuring, enable the plugin and select **Zender SMS** as your SMS transport under **Settings > Configuration > SMS Settings**.

## Plugin Structure

```
plugins/ZenderSmsBundle/
├── Config/config.php              # Service registration
├── Integration/
│   └── ZenderSmsIntegration.php   # Settings UI (API key, mode, device/gateway)
├── Transport/
│   ├── Configuration.php          # Reads credentials from integration settings
│   ├── ConfigurationException.php
│   └── ZenderTransport.php        # Sends SMS via POST /api/send/sms
├── Translations/en_US/messages.ini
└── ZenderSmsBundle.php            # Bundle class
```

## Uninstall

```bash
docker exec --user www-data --workdir /var/www/html mautic_web \
  composer remove radata/mautic-zender-sms -W --no-interaction
docker exec --user www-data --workdir /var/www/html mautic_web \
  composer config --unset repositories.mautic-zender-sms
docker exec --user www-data mautic_web rm -rf /var/www/html/var/cache/prod
docker exec --user www-data --workdir /var/www/html mautic_web php bin/console cache:warmup --env=prod
docker exec --user www-data --workdir /var/www/html mautic_web php bin/console mautic:plugins:reload
```

## License

MIT - see [LICENSE](LICENSE) for details.
