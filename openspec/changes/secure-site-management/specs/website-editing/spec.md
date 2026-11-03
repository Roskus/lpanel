# Spec Delta

## Purpose

Lets the user change an existing website from the panel and keeps its webserver configuration, stored data and files consistent.

## ADDED Requirements

### Requirement: Websites can be opened for editing
The websites list SHALL offer an edit action for every website stored by the panel. Sites found in the webserver but not stored by the panel MUST NOT offer it.

#### Scenario: Stored website
- **WHEN** the user views a website stored by the panel in the list
- **THEN** an edit action opens its form with the stored values

#### Scenario: Site unknown to the panel
- **WHEN** the list shows a site that only exists in the webserver configuration
- **THEN** it has no edit action

### Requirement: Website changes regenerate the virtual host
Saving a website SHALL regenerate its virtual host with the stored aliases, PHP version and Linux user mode.

#### Scenario: Change aliases
- **WHEN** the user changes the aliases of a website
- **THEN** the webserver answers for the new aliases and stops answering for the removed ones

#### Scenario: Change PHP version
- **WHEN** the user changes the PHP version of a website
- **THEN** the virtual host uses that version and the value is kept the next time it is regenerated

#### Scenario: Invalid value
- **WHEN** the user saves an invalid alias or PHP version
- **THEN** the error is shown, the form keeps the submitted values and the website stays as it was

### Requirement: Editing keeps the enabled state
Editing or regenerating a website SHALL NOT change whether it is enabled.

#### Scenario: Edit a disabled website
- **WHEN** the user edits a disabled website
- **THEN** the changes are stored and the website stays disabled

### Requirement: Websites can be renamed
Changing the domain of a website SHALL move its virtual host and website folder to the new domain and SHALL NOT leave the old domain active.

#### Scenario: Successful rename
- **WHEN** the user changes the domain of a website to a free domain
- **THEN** the website answers on the new domain with its files, and the old domain no longer serves it

#### Scenario: Domain already in use
- **WHEN** the new domain belongs to another website, or its folder or virtual host already exists
- **THEN** the rename fails, the error is shown and nothing changes

#### Scenario: Rename fails on the webserver
- **WHEN** the webserver rejects the configuration for the new domain
- **THEN** the old virtual host, folder and stored domain are restored
