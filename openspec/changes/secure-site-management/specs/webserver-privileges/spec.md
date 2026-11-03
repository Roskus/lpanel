# Spec Delta

## Purpose

Lets the panel change the webserver configuration through a narrow privileged interface, so that code running in a hosted website can never reach root.

## ADDED Requirements

### Requirement: Webserver configuration is root-owned
The active webserver configuration, including virtual hosts and enabled-site links, SHALL only be writable by root.

#### Scenario: Hosted code tries to write a virtual host
- **WHEN** PHP running in a hosted website tries to write a file in the webserver configuration folders
- **THEN** the operating system denies the write

#### Scenario: Panel tries to write a virtual host directly
- **WHEN** the panel user tries to write a file in the webserver configuration folders without the privileged interface
- **THEN** the operating system denies the write

### Requirement: Privileged operations are limited to a fixed interface
The system SHALL perform privileged webserver operations only through a fixed set of operations available to the panel user. No other user SHALL be able to run them.

#### Scenario: Hosted code calls a privileged operation
- **WHEN** a process running as a website user requests a privileged operation
- **THEN** the request is denied

#### Scenario: Panel requests an unknown operation
- **WHEN** the panel user requests an operation outside the fixed set
- **THEN** the request fails and no change is made

#### Scenario: Direct control of nginx
- **WHEN** the panel user tries to run nginx with elevated privileges directly
- **THEN** the request is denied

### Requirement: Privileged operations do not accept configuration
Privileged operations SHALL accept only validated values and SHALL generate configuration from a root-owned template. They MUST NOT accept configuration text or template paths from the caller.

#### Scenario: Invalid domain
- **WHEN** a privileged operation receives a domain or alias that contains a path separator, whitespace, a semicolon or a newline
- **THEN** the operation fails and no change is made

#### Scenario: Configuration injection in a parameter
- **WHEN** a privileged operation receives a PHP version or Linux user that does not match its expected format
- **THEN** the operation fails and no change is made

#### Scenario: Website root outside the websites folder
- **WHEN** a privileged operation would use a website folder outside the configured websites base path
- **THEN** the operation fails and no change is made

### Requirement: Configuration changes are checked and reversible
Every configuration change SHALL be checked before the webserver is reloaded. If the check or the reload fails, the system SHALL restore the previous configuration and report the error.

#### Scenario: Valid change
- **WHEN** a website change produces a valid configuration
- **THEN** the webserver applies it without a manual reload

#### Scenario: Invalid new website
- **WHEN** the configuration check fails for a new website
- **THEN** its virtual host and enabled link do not remain, the website is not saved and the error is shown

#### Scenario: Invalid change to an existing website
- **WHEN** the configuration check fails for an existing website
- **THEN** its previous virtual host and enabled state are restored and the website keeps working

### Requirement: Websites can be enabled and disabled
The system SHALL enable and disable a website through the privileged interface and SHALL keep its virtual host and files while it is disabled.

#### Scenario: Disable a website
- **WHEN** the user disables an enabled website
- **THEN** the webserver stops serving it and its virtual host and files remain

#### Scenario: Enable a website
- **WHEN** the user enables a disabled website
- **THEN** the webserver serves it again with its stored configuration
