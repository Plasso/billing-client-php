# Billing Client
PHP client for [Plasso Billing](https://plasso.com/billing)

## How to use it
1. Copy & paste the contents of `client.php` into your code base (or include it).
2. Uncomment the initalization line to ensure the code runs. This line: `$plassoBilling = new PlassoBilling( ...`
3. (Optional) You can access the Plasso User's `id` with: `$plassoBilling->plassoUserId`
4. (Optional) You can access the Plasso User's `planId` with: `$plassoBilling->plassoPlanId`

## Where to place the client code
At the **very** beginning of your script, on the pages you want to protect.
