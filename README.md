# Engineering Undergraduate Society Room Booking System

Welcome! This is the central repository with the code for room bookings. The code contained here runs almost as-is at [bookings.mcgilleus.ca](https://bookings.mcgilleus.ca).

The code here leverages the open-source platform [Meeting Room Booking System](https://mrbs.sourceforge.io/), customized to fit the EUS' needs.

The code contained here may not run automatically as it doesn't contain any of the required "secrets" (passwords and other secret keys for APIs used). Please scroll further for more information on those.

## Overview
The meat of the platform is contained directly at the root of this repo. The main file the user accesses when clicking the URL is `index.php`. This in turn redirects him to a variety of different components from this repository. 

**The general flow is as follows**:

- `week.php`: User views empty and taken time slots (referred to as "entries") of a given day.

- `view_entry.php`: User selects an empty entry on a given day;

- `edit_entry.php`: User edits the entry to add their personal room booking.

Some other very notable files where you are likely to make changes:

- `mrbs_sql.inc`: This contains most of the SQL commands used by the system. A file like `edit_entry.php` will call functions like "create a booking" which ultimately map to a SQL query found within `mrbs_sql.inc`.

## Configuration

The most important thing to configure to get the room booking system to run is `config.inc.php`, at the root of this repository. This file contains the connection strings to allow the PHP code of the room booking system to interact with the MySQL database where room booking data is stored.

Inside of `config.inc.php`, you have to make sure to place the name, username and password related to the database which will contain your instance of the room booking system.

[Documentation here is lacking for the full DB setup, but you can check out the official docs here](https://mrbs.sourceforge.io/view_text.php?section=Documentation&file=INSTALL)

## Changes

If you choose to make a change on the live version of the room booking system, please **do not copy over this repository to gastly**. The correct flow for modifying the room booking system should be the following:

- Create a ZIP of the bookings folder by performing this operation within `/srv/www/` on gastly: `tar -zcvf bookings_<date>.tar booking`. This is a backup in case things go wrong.

- Outline the changes you wish to make within this repository and create a Pull Request. Another member of the IT Committee should review the change and ensure it is legitimate.

- Update the file `changelog.md` to include your new updates. Please be very descriptive about **what** you have changed and **why**. This changelog update should be part of your pull request.

- Once your Pull Request is merged, update the live version on gastly to match the version here by manually repeating the changes (using `vim` or `nano`).

## Secrets

The following variables are the reason why it is a bad idea to copy paste the code here directly on your server hosting the room booking system. You absolutely need to set them to be able to use Google's OAuth Authentication as well as an underlying MySQL database.

Generics:
- `approve_entry_handler.php`: `$mail->Password`
- `config.inc.php`: `$db_password`
- `password_compat/password.php`: `$hash`

Google OAuth:
- `del_entry.php`: `$CLIENT_ID` and `$CLIENT_SECRET` and `$mail->Password`
- `edit_entry.php`: `$CLIENT_ID` and `$CLIENT_SECRET`
- `gplus/vendor/google/apiclient/src/Google/Auth/AssertionCredentials.php`: `$privateKeyPassword`
- `gplus/vendor/google/apiclient/src/Google/Signer/P12.php`: `$password` line 49
- `password_compat/password.php`: `$hash`
- `gplus/signin.php`: `CLIENT_ID` and `CLIENT_SECRET`

