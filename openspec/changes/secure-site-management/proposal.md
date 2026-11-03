# Proposal

## Why

Hosted websites run as `www-data`, the same user as the panel. That user owns the nginx configuration and can run `sudo nginx -s reload`, so PHP from any hosted website can make nginx write files as root and can read the panel `.env`. Websites also cannot be edited after they are created, because the list has no edit link and the vhost is only generated on creation.

## What Changes

- **BREAKING** Replace the `sudo` rule for `nginx` with one root-owned privileged helper. The helper validates its input, generates vhosts from a root-owned template, checks the configuration and reloads nginx, restoring the previous state on failure.
- **BREAKING** Make the nginx configuration root-owned, not writable by the panel or by hosted websites.
- **BREAKING** Run the panel in its own php-fpm pool and Linux user. Only that user can read the panel `.env` and call the helper.
- Keep one shared Linux user for all websites as the default.
- Add an optional dedicated Linux user per website, with its own php-fpm pool. Files stay readable by nginx through the `www-data` group.
- Add website editing to the panel: edit link in the list, change aliases, PHP version, Linux user mode and domain, regenerating the vhost.
- Handle a domain rename, moving the vhost and website folder without leaving the old site active.
- Keep a disabled website disabled when it is edited or regenerated.

Out of scope: certificates (Let's Encrypt, custom, Cloudflare), which stay for a future change, and the form options that depend on them.

## Capabilities

### New Capabilities

- `webserver-privileges`: how the panel changes the webserver configuration without hosted code being able to reach root.
- `panel-isolation`: separation between the panel and hosted websites.
- `website-linux-users`: shared Linux user by default and optional dedicated user per website.
- `website-editing`: editing and renaming a website from the panel.

### Modified Capabilities

None, `openspec/specs/` has no capabilities yet.

## Impact

- **Commands and services**: `WebsiteCreate`, `WebsiteEnable`, `WebsiteDisable`, `App\Service\Webserver\Nginx`.
- **Panel**: `SiteController`, `routes/web.php`, `resources/views/site/*`, `Website` model.
- **Database**: new `website` columns for the PHP version and the Linux user.
- **New privileged files**: helper, root-owned templates, helper configuration and `sudoers` rule.
- **Docker**: `infrastructure/etc/php/*`, `docker-compose.yml`, nginx pool and socket configuration, `make permissions`.
- **Operations**: existing installs must migrate file ownership, regenerate vhosts and install the helper. `README.md` must document it.
