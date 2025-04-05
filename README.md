# Verify

A secure player verification system for PocketMine-MP servers with multiple authentication methods.

## Features
- Player verification system with code authentication
- Device ID checking for security
- Configurable timeout protection
- Multi-server transfer support
- Lightweight and efficient

## Installation
1. Download the latest release
2. Place the `Verify.phar` in your server's `plugins` folder
3. Restart the server

## Configuration
This plugin works with default settings, but can be customized in `config.yml`.

## Commands

### Main Command:
- `/verify` - Main verification command

### Subcommands:
| Command | Description | Permission |
|---------|-------------|------------|
| `/verify add <player>` | Verify a player | `verify.command` |
| `/verify remove <player>` | Unverify a player | `verify.command` |
| `/verify list` | List verified players | `verify.command` |
| `/verify createcode <player>` | Generate verification code | `verify.command` |
| `/verify help` | Show help menu | none |

### Usage Examples:
- `/verify add xDqZtop` - Verify player xDqZtop
- `/verify remove Notch` - Unverify player Notch
- `/verify createcode Steve` - Create code for Steve

## Permissions
- `verify.command` - Access to verify commands (default: op)
- `verify.bypass` - Bypass verification (default: false) soon...

## Support
Report issues on [GitHub Issues](https://github.com/xDqZtop/Verify/issues)

## Planned Features
- [ ] MySQL database support
- [ ] Full customize
- [ ] Discord integration
- [ ] Email verification
