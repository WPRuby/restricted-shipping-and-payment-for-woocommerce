# Conditional Payments and Shipping for WooCommerce

Hide WooCommerce shipping methods and payment gateways when the cart matches any of a condition's rules: cart total, package weight, shipping class, coupon code, shipping or billing country, or customer email.

- Requires WordPress 6.6+, PHP 7.4+ and WooCommerce 8.9+.
- Works with the classic checkout and the Cart and Checkout blocks.
- Release notes are in [readme.txt](readme.txt).

## Development

```bash
composer install
npm ci
```

### Code layout

| Path | What |
| --- | --- |
| `src/` | PHP classes, namespace `WPRuby\RestrictedShippingPaymentLite` (PSR-4). `Plugin` wires everything. |
| `src/Rules/` | Rule types. Add one by extending `AbstractRule` and listing it in `RuleFactory`. |
| `client/` | Admin React sources (condition editor), built to `build/`. |
| `tests/` | PHPUnit tests. |

Conditions keep their data in the same post meta as 1.x (`{shipping,payment}_condition_rules`, `excluded_shipping_methods`, `excluded_payment_gateways`), which the Pro version also reads.

`build/` is committed because release zips are made with `git archive` (`make pack`). After changing `client/`, run:

```bash
npm run build
```

### Tests

The tests need a MySQL database. `bin/install-wp-tests.sh` installs WordPress, WooCommerce and the WordPress test library (defaults to `/tmp`):

```bash
bash bin/install-wp-tests.sh wordpress_test root root 127.0.0.1 latest latest
composer test
```

The fifth and sixth arguments are the WordPress and WooCommerce versions, e.g. `6.6 8.9.3`. Set `WP_TESTS_DIR` and `WP_CORE_DIR` to install elsewhere; the tests read the same variables. Set `WC_HPOS=1` to run the tests with orders in WooCommerce's order tables (HPOS) instead of posts.

### Static analysis and linting

```bash
composer phpstan
npm run lint:js
```

### Continuous integration

GitHub Actions (`.github/workflows/ci.yml`) runs PHPUnit on PHP 7.4 to 8.3 against several WordPress and WooCommerce versions (one job with HPOS), PHPStan, the JS lint, and checks that `build/` matches `client/`.
