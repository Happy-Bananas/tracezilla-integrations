# tracezilla-woocommerce-sandbox

A disposable WordPress and WooCommerce development store for the tracezilla
integration examples. The sandbox is separate from the deployable integration
application in `tracezilla-woocommerce-php`.

## Start the sandbox

```bash
docker compose up -d
docker compose logs -f wp-cli
```

When setup is complete, open <http://localhost:8080/wp-admin> and sign in with:

- Username: `admin`
- Password: `tracezilla`

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
