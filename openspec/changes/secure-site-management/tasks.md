# Tasks

## 1. Privileged helper

- [ ] 1.1 Add `lpanel-webserver`, its `site-write`, `site-enable` and `site-disable` subcommands, `flock` serialization and input validation, and verify with a helper test script that invalid domains, aliases, PHP versions, paths and unknown subcommands change nothing
- [ ] 1.2 Add rollback for a failed `nginx -t` or reload and verify that the script restores new and existing vhosts and their enabled state
- [ ] 1.3 Install the helper, root-owned templates and `/etc/lpanel/webserver.conf` in the Docker image, and verify they are `root:root` and not writable by other users
- [ ] 1.4 Add `make test-helper` to run the helper script in the container and verify it passes

## 2. Panel isolation

- [ ] 2.1 Create the `lpanel` and `lpsite` users and their php-fpm pools and sockets, map UID 1000 to `lpanel`, and verify with `id` and a request that panel and website run under different users
- [ ] 2.2 Point the panel vhost to `lpanel.sock` and verify that the login page and assets return 200
- [ ] 2.3 Replace the `sudoers` file so only `lpanel` can run the helper, make nginx configuration root-owned, and verify that website code cannot write configuration, run `sudo` or read `.env`
- [ ] 2.4 Update `make permissions` for the new ownership and verify that a website creates and the panel writes logs after running it
- [ ] 2.5 Document the users, helper and server installation in `README.md`, replacing the current warning, and verify the steps against a fresh Docker build

## 3. Panel integration

- [ ] 3.1 Add the `php_version` and `linux_user` migration and verify that migrations run on SQLite and MariaDB
- [ ] 3.2 Move `WebsiteCreate`, `WebsiteEnable`, `WebsiteDisable` and `App\Service\Webserver\Nginx` to the helper, and update their tests to assert the helper calls with `Process::fake`
- [ ] 3.3 Remove folder and `index.php` creation from PHP in favor of `site-write`, and verify that a website created from the form serves its welcome page

## 4. Linux users

- [ ] 4.1 Add the `site-user` subcommand to create the user, pool and socket with the ownership in design.md, and verify rollback in the helper script when creation fails
- [ ] 4.2 Add switching between shared and dedicated mode and verify that the website keeps working in both directions
- [ ] 4.3 Add the option to the website form with shared mode as default, and cover it with feature tests
- [ ] 4.4 Verify in Docker that PHP in a website cannot read files of a dedicated website and that nginx serves its static files

## 5. Website editing

- [ ] 5.1 Add the edit action to the websites list only for stored websites, with a feature test for both cases
- [ ] 5.2 Add the PHP version to the form, regenerate the vhost on save and keep the enabled state, with feature tests for aliases, PHP version and a disabled website
- [ ] 5.3 Add the `site-rename` subcommand with rollback and verify in the helper script a successful rename, a domain in use and a rejected configuration
- [ ] 5.4 Call the rename from `SiteController::save()`, rolling back the database on failure, and cover it with feature tests

## 6. Integration

- [ ] 6.1 Test the migration on a Docker environment with existing websites and verify that they keep working after regeneration
- [ ] 6.2 Test end to end through the panel: create, edit, rename, disable, enable and switch user mode, and verify each result with real requests
- [ ] 6.3 Confirm with real commands that the privilege escalation reported in PR #8 no longer works from hosted PHP

## Workflow follow-up

- Archive the change after review.
