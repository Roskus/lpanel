# Spec Delta

## Purpose

Keeps the panel separated from hosted websites, so hosted code cannot read panel secrets or act with the panel's permissions.

## ADDED Requirements

### Requirement: Panel runs under its own user
The panel SHALL run under a Linux user and PHP worker pool that no hosted website uses, in every website user mode.

#### Scenario: Panel request
- **WHEN** a request is handled by the panel
- **THEN** it runs as the panel user

#### Scenario: Website request
- **WHEN** a request is handled by a hosted website
- **THEN** it does not run as the panel user

### Requirement: Panel secrets are private
The panel environment file and writable application folders SHALL NOT be readable by website users.

#### Scenario: Hosted code reads the panel environment
- **WHEN** PHP running in a hosted website tries to read the panel `.env`
- **THEN** the operating system denies the read

#### Scenario: Panel reads its environment
- **WHEN** the panel starts
- **THEN** it reads its configuration and works normally

### Requirement: Panel stays reachable through the webserver
The webserver SHALL keep serving the panel after the separation, including its public assets.

#### Scenario: Open the panel
- **WHEN** a user opens the panel login page
- **THEN** the page and its assets load successfully
