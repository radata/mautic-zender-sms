# Mautic Zender SMS Plugin

Mautic SMS transport plugin for sending SMS messages via the [Zender](https://zender.hollandworx.nl) API.

## Features

- Send SMS through linked Android devices or gateway credits
- Configurable SIM slot selection and send priority
- E.164 phone number normalization
- Full logging of send results

## Requirements

- Mautic 4.x or 5.x
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

Add the GitHub repository to your Mautic project's `composer.json`:

```bash
docker exec --user www-data --workdir /var/www/html mautic_web \
  composer config repositories.radata/mautic-zender-sms vcs \
  https://github.com/radata/mautic-zender-sms --no-interaction
```

Install the plugin:

```bash
docker exec --user www-data --workdir /var/www/html mautic_web \
  composer require radata/mautic-zender-sms:dev-main -W --no-interaction
```

Update to the latest version:

```bash
docker exec --user www-data --workdir /var/www/html mautic_web \
  composer update radata/mautic-zender-sms -W --no-interaction
```

### Manual Installation

1. Copy or symlink this plugin to `plugins/ZenderSmsBundle/` in your Mautic installation.
2. Clear the Mautic cache:
   ```bash
   bin/console cache:clear
   ```

### Post-Installation

1. Navigate to **Settings > Plugins** and click **Install/Upgrade Plugins**.
2. Find **Zender SMS** and click **Configure**.

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

## License

MIT - see [LICENSE](LICENSE) for details.
