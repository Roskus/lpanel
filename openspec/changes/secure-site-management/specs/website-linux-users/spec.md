# Spec Delta

## Purpose

Defines which Linux user runs each website: one shared user by default, for an operator managing their own sites, and an optional dedicated user per website for isolation.

## ADDED Requirements

### Requirement: Websites use a shared Linux user by default
A website SHALL run under one shared Linux user unless a dedicated user is requested. Creating a website with the defaults MUST NOT create a Linux user.

#### Scenario: Create a website with the defaults
- **WHEN** the user creates a website without asking for a dedicated user
- **THEN** it runs under the shared website user and no Linux user is created

### Requirement: Websites can use a dedicated Linux user
The user SHALL be able to request a dedicated Linux user per website. That website SHALL then run under that user in its own PHP worker pool.

#### Scenario: Create a website with a dedicated user
- **WHEN** the user creates a website and asks for a dedicated user
- **THEN** the Linux user and its worker pool exist and the website PHP runs as that user

#### Scenario: Website user generation fails
- **WHEN** the Linux user or its worker pool cannot be created
- **THEN** the website is not saved as created, no broken virtual host remains active and the error is shown

### Requirement: Dedicated users are isolated from other websites
A website with a dedicated user SHALL own its files, and other website users SHALL NOT be able to read them.

#### Scenario: Another website reads the files
- **WHEN** PHP from another website tries to read the files of a website with a dedicated user
- **THEN** the operating system denies the read

### Requirement: The webserver can read website files
The webserver SHALL be able to read the files of every website through the `www-data` group, in both user modes.

#### Scenario: Static file with a dedicated user
- **WHEN** a visitor requests a static file of a website with a dedicated user
- **THEN** the webserver serves the file

### Requirement: Linux user mode can be changed
The user SHALL be able to change a website between the shared user and a dedicated user, keeping its files.

#### Scenario: Move to a dedicated user
- **WHEN** the user switches a website from the shared user to a dedicated user
- **THEN** the files belong to the dedicated user, the website runs as that user and it keeps working

#### Scenario: Move back to the shared user
- **WHEN** the user switches a website from a dedicated user to the shared user
- **THEN** the files belong to the shared user, the website keeps working and its dedicated worker pool is removed
