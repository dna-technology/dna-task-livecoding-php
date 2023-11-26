# How should I start
1. install required dependencies by running the `composer install` command
2. run `php artisan migrate` to set up the database schema
3. run tests by executing the `./artisan test` command
4. run the `php artisan serve` command to see whether the server starts

## Scenario
Let’s imagine you are working on API for backend of an online payment system.
The actors are: users (festival participants), merchants, event organiser.
We want to enable backoffice operations to the event organiser.

Currently implemented:
- Users can make a payment (with a virtual currency) to a specific merchant.
- Our system has a payment log.
- It means that information about payments are stored in a database.
- This data can be used for reporting.

# Tasks
## Task 1:
Please make a code review of the currently implemented solution.
## Task 2:
Add new endpoint which give total income for payments for selected time period for given merchant.
