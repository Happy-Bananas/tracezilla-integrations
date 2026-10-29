# tracezilla-woocommerce-sandbox

A disposable WordPress and WooCommerce development store for the tracezilla
integration examples. The sandbox is separate from the deployable integration
application in `tracezilla-woocommerce-php`.

## Start the sandbox

```bash
docker compose up -d
docker compose logs -f wp-cli
```

When setup is complete, the sandbox has these preinstalled credentials:

| What | Value |
|:--|:--|
| WordPress login (<http://localhost:8080/wp-admin>) | `admin` / `admin` |
| WooCommerce REST API key (read/write) | `ck_sandbox` / `cs_sandbox` |

They are recreated on every start, so they also work after a reset. The
credentials are well known and the sandbox is reachable from your local
network: do not run it on an untrusted network, and never use these values on
a real store.

The sandbox runs on plain HTTP. WooCommerce normally accepts REST API keys only
over HTTPS, so the sandbox-only plugin `mu-plugins/sandbox-rest-over-http.php`
treats REST API requests as secure. Real stores must use HTTPS.

The setup creates 200 top-level products, two variations, three customers, and
three recent orders.

## Restore the fixture data

```bash
docker compose run --rm --entrypoint wp wp-cli \
  eval-file /seed-products.php --path=/var/www/html
```

The seeder is idempotent and does not send customer or order emails.

## Stop or reset the sandbox

Retain the database and WordPress files:

```bash
docker compose down
```

Permanently delete the disposable store:

```bash
docker compose down --volumes
```
