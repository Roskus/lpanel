# Design

## Context

See proposal.md for the motivation. Today php-fpm runs panel and hosted sites as `www-data`, which owns `sites-available`, `sites-enabled` and `conf.d`. `infrastructure/etc/php/sudoers` allows `nginx -t` and `nginx -s reload`, and nginx opens the log files of any loaded config as root. The commands write vhosts directly from `resources/templates`, which the panel user can also write.

In Docker, one `php` container runs nginx and php-fpm under supervisord, and `www-data` was remapped to UID 1000 so it matches the checkout owner. The operator manages production alone, so per-website users must remain optional.

## Goals / Non-Goals

**Goals:**
- No path from hosted PHP to root, in either user mode.
- Keep the panel's existing behavior: validation, rollback and visible errors.
- Keep the same flow in Docker and in a real server, with differences only in a root-owned configuration file.

**Non-Goals:**
- Certificates and the form options that depend on them.
- Apache virtual hosts.
- Removing the vhost and files when a website is deleted in the panel, which stays as today.
- Deleting a Linux user or its files when a website leaves dedicated mode.
- Isolation between websites in the default shared mode, which is an accepted trade-off.

## Decisions

### Root-owned helper as the only privileged entry point

Install `/usr/local/sbin/lpanel-webserver` (`root:root`, 0755) with subcommands such as `site-write`, `site-enable`, `site-disable`, `site-rename` and `site-user`. The `sudoers` rule allows only this path to the panel user. It reads templates from `/usr/local/share/lpanel` and settings from `/etc/lpanel/webserver.conf`, both root-owned.

The helper never reads anything from the checkout. Otherwise the panel user could change what runs as root. Its validation is authoritative. PHP keeps validating too, for friendly errors.

Written in POSIX shell plus standard tools, because a PHP helper would depend on `php.ini` and include paths at root level. Rejected alternatives: keeping `sudo nginx` with a config scanner, which cannot be made safe against every nginx directive, and a long-running root daemon, which adds a service to run and secure for no gain.

### Transaction inside the helper

Each subcommand copies the affected vhost and link, applies the change, runs `nginx -t` and `nginx -s reload`, and restores the copies on failure. A lock with `flock` serializes concurrent calls, because the rollback relies on a stable previous state. `SiteController` keeps its database transaction and rolls back when the helper fails.

### Panel user `lpanel`, shared website user `lpsite`

The panel gets a `lpanel` user and pool, with a `/var/run/php/lpanel.sock` socket owned `lpanel:www-data` 0660, so nginx workers can connect. `.env` becomes `lpanel` 0640 and `storage` and `bootstrap/cache` are writable only by `lpanel`. `public/` stays world-readable. Shared-mode websites run as a new `lpsite` user, keeping the `php<version>-fpm.sock` name. nginx workers remain `www-data` and `lpsite` is not a member of that group, which matters for dedicated mode below.

Only `lpanel` gets a `sudo` rule. In Docker, the UID 1000 mapping moves from `www-data` to `lpanel`, and `www-data` returns to its default UID.

Website folders are owned by the website user, so the panel cannot write them. Creating the folder and the initial `index.php` becomes part of `site-write`.

### Dedicated user mode

`site-user` creates a system user `site_<slug>` with no login shell and a pool file with its own socket `/var/run/php/site-<slug>.sock`. Files use `site_<slug>:www-data` with `2750` folders and `0640` files, so nginx reads through the group and other website users cannot.

Keeping shared PHP outside the `www-data` group is what stops shared-mode sites from reading dedicated sites. Keeping the old `www-data` pool for them would give them group access to those files.

The primary group of `site_<slug>` must not be `www-data` for the same reason: dedicated users would read each other through the group. Each user gets its own primary group, and the folder group is set to `www-data` with setgid, so new files stay readable by nginx.

The slug is derived from the domain and truncated with a hash suffix to fit the 32-character limit of Linux user names. The pool path and the php-fpm reload command differ between systems, so they are settings in `/etc/lpanel/webserver.conf`.

### Website data

Add nullable `php_version` and `linux_user` columns to `website`. `null` in `linux_user` means shared mode. Keeping the PHP version stored is what lets a regeneration keep it, because the command currently falls back to the running version.

### Editing and rename

The list matches entries to `website` rows by domain to add the edit link. `save()` already accepts an `id`. It compares the stored domain with the submitted one and calls `site-rename` when they differ. Rename stages the new vhost, moves the folder, runs the check and reload, and undoes each step on failure. The new domain must be free in the database and on disk. The helper preserves the enabled state, so `panel:website:create` stops creating the link for an existing disabled site.

## Risks / Trade-offs

- [A helper bug runs as root] → Fixed subcommands, strict allow-list validation, no `eval`, no shell expansion of parameters, and a dedicated test script.
- [Existing installs break on upgrade] → Migration steps below, and the helper refuses to run until its configuration exists.
- [Shared-mode sites can still read each other] → Accepted default for a single operator, documented in the README.
- [Moving `storage` ownership breaks host tools in Docker] → Keep UID 1000 for `lpanel`, matching the checkout owner in the common case.
- [Dedicated pools use more memory] → Pools use `ondemand` process management.
- [Docker differs from production in how php-fpm reloads] → Only the reload command differs, as a setting.

## Migration Plan

1. Add the database columns, with existing rows in shared mode.
2. Install the helper, templates, configuration and `sudoers` rule, and remove the old `nginx` rule.
3. Create the `lpanel` and `lpsite` users and pools, and switch the panel vhost to the new socket.
4. Change ownership of `.env`, `storage`, `bootstrap/cache`, website folders and nginx configuration.
5. Regenerate every stored website with the helper and verify `nginx -t`.

Rollback: restore the previous `sudoers` rule, pool and ownership. Vhost contents remain compatible with the shared socket name.

## Open Questions

- Default PHP version offered in the edit form when a server has several installed. It can be resolved during implementation without changing the specs.
