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

1. Copy or symlink this plugin to `plugins/ZenderSmsBundle/` in your Mautic installation.
2. Clear the Mautic cache:
   ```bash
   bin/console cache:clear
   ```
3. Navigate to **Settings > Plugins** and click **Install/Upgrade Plugins**.
4. Find **Zender SMS** and click **Configure**.

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
