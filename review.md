## Disclaimer
The review is not 100% complete. Honestly there's just too much. I'd re-review after these are addressed. I did not bother looking up some information that I didn't remember off the top of my head, like whether Laravel adds columns as nullable by default or not. I took the approach that assumend the developer tested their solution and that the simplest use case actually works properly. Browsing the code is a bit troublesome, I did my best to browse the entire repository, but for the future perhaps you should add the framework base in a separate commit, so it can be easily excluded from the review.

## Definitions
For the purpose of this excercise:
- **HIGH** - MUST be resolved before PR approval.
- **MID** - SHOULD be resolved before PR approval. If there is a valid reason to delay the fix a backlog task must be created to fix at a later date.
- **LOW** - Can be treated as an optional suggestion. Low priority comments do not block merging, author MAY choose to apply, ignore or backlog it.

The primary perspective for these is a single task and a single pull request.
I tried to put comments that will most likely require a separate task as MID, even though the API authentication is crucial. Depending on the project state I might've escalated the issue to HIGH. For this excercise I assumed this project is not production ready and no permament data will be generated, therefore we can delay certain fixes until later as they might be out of scope for now.

## Comments

### HIGH
- The .env file MUST be excluded from the repository and the APP_KEY must be regenerated wherever it's used for security reasons.
- The input data is not validated. For example, `UserController::addUser` could use a dedicated `FormRequest` validating the data. I believe the code could then be further simplified to this:
```php
$res = $this->userService->addUser(...$req->validated());
```
Apply to all routes.
- `PaymentService` takes a DTO as argument, other services do not. Stay consistent.
- Amounts are currently float values. This **will** lead to errors. A different strategy must be chosen. Either use ints and represent all money as subunits (cents) or use an approach like bcmath that will ensure accuracy.
- Payments table has unique constraints on 3 separate columns. I think that the dev meant to add a composite unique key instead. Currently a user could only have a single payment.
- Relations are missing in the database as well as the Eloquent models.
- The project contains frontend resources, even though the `package.json` file was removed. I assume this project is just an API and therefore frontend assets, routes and views should be removed as well.
- Code formatting is not adhering to PER (example: method opening bracket should be on the next line, example: some newlines are missing which is inconsistent and hiders readability).
- `thereIs` in Feature tests is not descriptive. This suggest checking if something exists, but the methods create an entity in the database instead. My suggestion would be `seedTestMerchant` or `createTestMerchant`. Additionally the `string` return is unintuitive. Either return the entire Merchant entity (which IMO could be useful in future tests) or at least add a phpdoc describing the return value as "merchant id".
- `thereIsMerchant` functionality is duplicated between tests. Instead we could create traits like `SeedsMerchants`.
- Test cases currently only cover the successful path, we should also add tests covering cases with errors and bad input data.
- `PaymentControllerTest::test_should_save_payment_transaction` some data is defined in the "when" section.
- `PaymentController::addPayment` you're casting amount to string and then back to float.
- `database/database.sqlite` needs to be added to .gitignore.
- Test database should be separated to avoid wiping out the data used for development.
- What is the difference between `payment->id` and `payment->paymentId`? If it's external, for example id coming from Stripe maybe it should be called `externalId` or `vendorId`? We should probably identify the 3rd party as well.
- In tests there's no need to manually delete data when tests are using the `RefreshDatabase` trait.

### MID
- Are we expecting every user to have an account? If so the `UserService::addUser` method should wrap the user and account creation in a transaction (or perhaps each API request should entirely be encapsulated in a transaction to ensure that each API call fails or succeeds in it's entirety.
- The default timestamp columns were removed. While I agree that they might not always be needed, for Payments at least I believe this is crucial information. Unless we take an alternative approach like an additional audit table we should think which models should retain this information.
- The payments table should store both amount and currency.
- The API does not require any authentication.
- Account balance MUST NOT be represented as a single value. Rather event sourcing approach should be taken to ensure consistency. Otherwise two transactions taking place at the same time would overwrite each other.

### LOW
- When creating `User` and `Account` entities, the corresponding service manually assigns an UUID. This can be simplified using Laravel's `HasUuids` trait. If we need to stick to non-ordered UUIDs we can instead create our own trait that will handle this automatically or perhaps even rely on the database itself to do this (SQLite does not support UUIDs by default).
- Similarily other default values (`Account->balance = 0.0`) can be set by the database or the model itself to simplify the services.
- DTOs have a `toResponse` method. That seems out of scope for a DTO class, consider adding transformers instead.
- What is the purpose of `ResponseAlias` in tests? I feel like it could've just been `Response`.
- The existing services look more like repositories.
- Is there a reason test method names are snake_case?
