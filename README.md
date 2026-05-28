# OXID eShop Consistency Check Component

[![Development](https://github.com/OXID-eSales/consistency-check-tool/actions/workflows/trigger.yaml/badge.svg?branch=b-7.6.x)](https://github.com/OXID-eSales/consistency-check-tool/actions/workflows/trigger.yaml)
[![Latest Version](https://img.shields.io/packagist/v/OXID-eSales/consistency-check-tool?logo=composer&label=latest&include_prereleases&color=orange)](https://packagist.org/packages/oxid-esales/consistency-check-tool )
[![PHP Version](https://img.shields.io/packagist/php-v/oxid-esales/consistency-check-tool)](https://github.com/oxid-esales/consistency-check-tool)

[![Quality Gate Status](https://sonarcloud.io/api/project_badges/measure?project=OXID-eSales_consistency-check-tool&metric=alert_status)](https://sonarcloud.io/dashboard?id=OXID-eSales_consistency-check-tool)
[![Coverage](https://sonarcloud.io/api/project_badges/measure?project=OXID-eSales_consistency-check-tool&metric=coverage)](https://sonarcloud.io/dashboard?id=OXID-eSales_consistency-check-tool)
[![Technical Debt](https://sonarcloud.io/api/project_badges/measure?project=OXID-eSales_consistency-check-tool&metric=sqale_index)](https://sonarcloud.io/dashboard?id=OXID-eSales_consistency-check-tool)


The OXID eSales Consistency Check component is a flexible tool designed to perform various consistency checks on your eShop. It helps maintain shop performance and data hygiene by identifying and resolving data inconsistencies.

**Current capabilities include:**
- **Unused Image Detection** - Identify orphaned image files no longer connected to products, categories, or manufacturers
- **SEO URL Verification** - Detect unused and duplicate SEO URLs that may affect shop performance and search rankings
- **Export by Filter** - Export data matching specific criteria (e.g., users whose password hash is not native `$2y$` Bcrypt) to CSV

This component ensures that your eShop remains optimized by helping you clean up unnecessary data while keeping track of all changes.

## Features

### Image Management
- Provides commands to move or delete unused images
- Supports dry-run mode, allowing safe testing before making changes
- Filter unused images across product, categories, and manufacturers
- Progress bar for real-time feedback
- Logs all actions in log/oe_consistency_check.log for review and auditing
- Customizable directory paths for images

### SEO URL Management
- Detect unused SEO URLs (orphaned entries where target object no longer exists)
- Detect duplicate SEO URLs (entries with collision suffixes)
- Export findings to CSV format for review
- Batch delete SEO URLs from exported CSV file
- Support for OXID SEO types with reference tables (oxarticle, oxcategory, oxmanufacturer, oxvendor, oxcontent)

## Compatibility
This component assumes you have OXID eShop Compilation version 7.6.0 installed.

## Installation
To install this component in your OXID eShop environment, use Composer:
```bash
$ composer require oxid-esales/consistency-check-tool
```

## Development installation

The installation instructions below are shown for the current [SDK](https://github.com/OXID-eSales/docker-eshop-sdk)
for shop 7.6. Make sure your system meets the requirements of the SDK.

0. Ensure all docker containers are down to avoid port conflicts

1. Clone the SDK for the new project
```shell
echo MyProject && git clone https://github.com/OXID-eSales/docker-eshop-sdk.git $_ && cd $_
```

2. Clone the repository to the source directory
```shell
git clone --recurse-submodules https://github.com/OXID-eSales/consistency-check-tool.git --branch=b-7.6.x ./source
```

3. Run the recipe to setup the development environment
```shell
./source/recipes/setup-development.sh
```

You should be able to access the shop with http://localhost.local and the admin panel with http://localhost.local/admin
(credentials: noreply@oxid-esales.com / admin)

## Usage
The tool provides several commands for managing unused images.

### Move Unused Images
Move unused images to a specific directory (e.g., /backup/images)
```bash
$ vendor/bin/oe-console oe:consistency_check:move-unused-images --destination=out/pictures/backup/
```
When specifying a destination folder for moving images, **note that paths are relative to the shop root**.

For example:
- If you set `destination` to `/`, the **source and target directories will be identical**.
- To move images to `out/pictures/backup/`, simply provide `out/pictures/backup/` as the destination.

### Delete Unused Images
Permanently delete unused images:
```bash
$ vendor/bin/oe-console oe:consistency_check:delete-unused-images
```

### Dry Run Mode
To perform a dry-run (simulate the move without making changes):
```bash
$ vendor/bin/oe-console oe:consistency_check:delete-unused-images --dry-run
$ vendor/bin/oe-console oe:consistency_check:move-unused-images --destination=/path/to/backup --dry-run
```

### Verbose Output (-v)
To view detailed logs of affected files (e.g., which images would be deleted or moved), you can pass the `-v` flag along with your command:
```bash
$ vendor/bin/oe-console oe:consistency_check:delete-unused-images --dry-run -v
$ vendor/bin/oe-console oe:consistency_check:move-unused-images --destination=/path/to/backup --dry-run -v
```
When `-v` is enabled, the command displays relevant entries from the consistency check log file directly in the console output. This is especially useful for shop owners who want to inspect which images are impacted before taking action.

### Filter by Entity Type (optional)
Process specific entity types (e.g., product, category, manufacturer):
```bash
$ vendor/bin/oe-console oe:consistency_check:delete-unused-images --type=product
```
If this parameter is not set the application perform actions on all images.

## SEO URL Commands

### Check Unused SEO URLs
Detect SEO URLs where the target object no longer exists in the database:
```bash
$ vendor/bin/oe-console oe:consistency_check:check-unused-seo-urls
```

Export results to CSV file:
```bash
$ vendor/bin/oe-console oe:consistency_check:check-unused-seo-urls --export
```

### Check Duplicate SEO URLs
Detect SEO URLs with collision suffixes (e.g., URLs ending with `-oxid`):
```bash
$ vendor/bin/oe-console oe:consistency_check:check-duplicate-seo-urls
```

The command reads the collision suffix from shop configuration (`sSEOuprefix`). You can override it:
```bash
$ vendor/bin/oe-console oe:consistency_check:check-duplicate-seo-urls --suffix=-duplicate
```

Export results to CSV:
```bash
$ vendor/bin/oe-console oe:consistency_check:check-duplicate-seo-urls --export
```

### Delete SEO URLs from CSV
Delete SEO URLs listed in a previously exported CSV file:
```bash
$ vendor/bin/oe-console oe:consistency_check:delete-seo-urls /absolute/path/to/exported.csv
```

Use `--dry-run` to preview deletions without making changes:
```bash
$ vendor/bin/oe-console oe:consistency_check:delete-seo-urls /absolute/path/to/exported.csv --dry-run
```

**Recommended workflow:**
1. Run check command to see results in console
2. Run check command with `--export` to generate CSV
3. Review the CSV file and remove any rows you want to keep
   > **Note:** Each row is deleted by its unique combination of OXOBJECTID, OXSHOPID, and OXLANG.
   > You can filter the CSV to delete URLs for specific shops or languages only.
4. Run delete command with `--dry-run` to preview
5. Run delete command to perform actual deletion

## Export by Filter

Export data matching specific filter criteria to CSV files.

### Usage
```bash
$ vendor/bin/oe-console oe:consistency_check:export-by-filter --filter-name=<filter>
```

### Available Filters

| Filter | Description |
|--------|-------------|
| `deprecated-credentials` | Exports every user whose password hash is **not** native `$2y$` Bcrypt and is **not** empty. The CSV surfaces both deprecated/unsupported hashes (security risk) and externally-produced Bcrypt hashes (informational audit trail for imports from non-PHP systems). |

### Example: deprecated-credentials filter
```bash
$ vendor/bin/oe-console oe:consistency_check:export-by-filter --filter-name=deprecated-credentials
```

Output file: `export/deprecated-credentials-{timestamp}.csv`

The exported CSV includes:
- `user_id` - User OXID (database key only — no email, name, or other PII; see "Privacy" below)
- `active` - Whether the user account is active
- `created_at` - Account creation date
- `user_updated_at` - Last modification date of the user row (`OXTIMESTAMP`). Touched by any update to the account, not specifically by password changes. Useful as an "account activity" signal; **not** a "password last changed" timestamp.
- `last_order_at` - Date of last order (empty if none)
- `credential_status` - Detected hash classification, see the table below
- `credential_hash_scheme` - Detected algorithm name (e.g. `bcrypt`, `sha512`, `md5`, `unknown`)

#### `credential_status` values

| Value | Meaning | Recommended action |
|---|---|---|
| `deprecated` | SHA512 — older shop hashing scheme | Force password reset / re-hash on next login |
| `unsupported` | MD5 — very old, broken | Force password reset |
| `supported` | Bcrypt `$2a$` / `$2b$` — externally produced (Java BCrypt, OpenBSD, modern non-PHP libraries, manual imports). Cryptographically equivalent to `$2y$` and accepted transparently by PHP's `password_verify()`. | **No action needed.** Informational only — surfaces accounts imported from non-PHP backends. |
| `unknown` | Any other format — argon2, scrypt, custom hash plugin, garbled data | Investigate manually. |
| `not_set` | Empty password hash — account cannot log in via password | Filtered out at the database level and **never appears in the export**. Listed here only for completeness of the `CredentialStatus` enum. |

When to expect each status:
- Migrations from non-PHP backends (Java, OpenBSD, Python/Ruby auth) → expect `supported` rows
- Shops upgraded from OXID 4.x/5.x/6.x → expect `deprecated` (SHA512) rows
- Very old shops or hand-imported users → expect `unsupported` (MD5) rows
- Shops using non-standard hashing plugins → expect `unknown` rows
- Empty password hashes (`not_set`) are excluded by the SQL query and will not appear in the CSV

#### Privacy: minimal export by design

The CSV contains only the `user_id` (OXID database key) — **no email, name, or other personal data**. This is intentional: the file can be shared, archived, or attached to tickets without leaking PII. To contact flagged users, look up `OXUSERNAME` from the `oxuser` table separately via the `user_id` column.

#### Subshop scope

The export covers users from **all shops** in an Enterprise Edition installation. There is no shop-id column on purpose:
- With `blMallUsers = true`, a single user record can authenticate against any shop in the mall, so a weak hash is a shop-installation-wide concern rather than a per-shop one.
- Even with mall-user disabled, the origin shop (`OXSHOPID`) is not a reliable signal for who should drive the cleanup, since users created in one shop frequently order from another.

If you have a specific reason to slice differently, implement your own `FilterInterface` using the tag-based extension mechanism described in "Extending: Creating Custom Filters" below.

### Logs
All operations, including moved and deleted images, are logged in:
```
log/oe_consistency_check.log
```
This log file helps you track the changes and verify actions performed by the tool.

## Customizable parameters

There are several parameters in the `services.yaml` that can be customized for the module:
* `app.log_file_path` - Path to the log file where the consistency check results will be stored.
* `app.export_directory_path` - Directory where CSV export files are saved.
* `app.unused_seo_urls_file` - File name prefix for unused SEO URLs export.
* `app.duplicate_seo_urls_file` - File name prefix for duplicate SEO URLs export.

Path parameters (`app.log_file_path`, `app.export_directory_path`) support both **relative** and **absolute** paths:
- **Relative paths** (e.g., `log/oe_consistency_check.log`) are resolved relative to the OXID eShop `source` directory.
- **Absolute paths** (e.g., `/var/log/oxid/consistency_check.log`) are used as-is.

#### ⚠️ Configure the export directory before first use

With the default `app.export_directory_path: 'export'`, output files land at `<shop>/source/export/<filename>.csv` — a directory inside the web document root. The shop's global `source/.htaccess` denies common sensitive extensions (`.log`, `.tpl`, `.ini`, `.pem`, etc.) but **does not cover `.csv`**, and `source/export/` has no dedicated `.htaccess`. Files written there are reachable via `https://<your-shop>/export/<filename>.csv` as static content. Although the consistency-check CSVs are privacy-minimal (`user_id` only, no PII), they still expose lists of accounts plus activity dates.

Before running export commands in production, either:
- Override `app.export_directory_path` to an absolute path **outside** the document root (for example, `/var/log/oxid/exports`), **or**
- Drop a deny-all `.htaccess` into `source/export/`:
  ```apache
  <FilesMatch .*>
      <IfModule mod_authz_core.c>
          Require all denied
      </IfModule>
      <IfModule !mod_authz_core.c>
          Order allow,deny
          Deny from all
      </IfModule>
  </FilesMatch>

  Options -Indexes
  ```

This applies to every feature writing to `source/export/`, not just this tool.

To modify the parameters, create the `configurable_services.yaml` file in the `var/configuration` folder as
described in the [Documentation](https://docs.oxid-esales.com/developer/en/latest/development/tell_me_about/service_container.html#replacing-oxid-eshop-services-in-a-project),
and overwrite the parameters you want to change. Ex.:

```yaml
parameters:
  app.log_file_path: 'log/oe_consistency_check.log'
  app.export_directory_path: 'var/exports'
  app.unused_seo_urls_file: 'orphaned-seo-urls'
  app.duplicate_seo_urls_file: 'collision-seo-urls'
```

### Custom file paths for images

If you are using custom directory paths for storing images (products, categories, manufacturers), overwrite the 
services defined in `src/ImageManager/Entity/services.yaml` by using the same procedure described in the 
[Documentation](https://docs.oxid-esales.com/developer/en/latest/development/tell_me_about/service_container.html#replacing-oxid-eshop-services-in-a-project) 
for service overriding - use already mentioned `configurable_services.yaml` file in the `var/configuration` folder.:

Example override for product images:
```
services:
  oxid_esales.consistency_check.entity.image_entity.product.oxpic1:
    class: OxidEsales\ConsistencyCheck\ImageManager\Entity\ImageEntity
    arguments:
      $name: 'Product'
      $table: 'oxarticles'
      $fieldName: 'OXPIC1'
      $directory: 'custom/directory/to/pictures/master/products/1'
    tags: ['oe.consistency_check.image_entity']
```

## Testing
### Linting, syntax check, static analysis

```bash
$ composer update
$ composer static
```

### Unit/Integration tests

- Install this component in a running OXID eShop

- run Unit + Integration tests
```bash
$ composer phpunit
```
- run Unit tests
```bash
$ ./vendor/bin/phpunit -c vendor/oxid-esales/consistency-check-tool/tests/phpunit.xml
```
- run Integration tests
```bash
$ ./vendor/bin/phpunit --bootstrap=./source/bootstrap.php -c vendor/oxid-esales/consistency-check-tool/tests/phpintegration.xml
```

## Extending: Creating Custom Filters

You can create custom filters for the `export-by-filter` command to export any data matching your criteria.

### Step 1: Create Filter Class

Implement `OxidEsales\ConsistencyCheck\ExportByFilter\Filter\FilterInterface` in your module.

See interface at `src/ExportByFilter/Filter/FilterInterface.php`.

### Step 2: Register Filter Service

Add to your module's `services.yaml` with the `oe.consistency_check.export_filter` tag:

```yaml
services:
  YourVendor\YourModule\Filter\YourCustomFilter:
    autowire: true
    tags: ['oe.consistency_check.export_filter']
```

### Step 3: Use Your Filter

```bash
$ vendor/bin/oe-console oe:consistency_check:export-by-filter --filter-name=your-filter-name
```

For a complete example, see the `DeprecatedCredentialsFilter` implementation in `src/ExportByFilter/Filter/Credentials/`.

## Troubleshooting

### Log directory does not exist

By default, the tool uses `log/oe_consistency_check.log` relative to the OXID eShop `source` directory.
If the `log` directory does not exist, you may encounter an error like:
```
There is no existing directory at "/var/www/source/log" and its not buildable: Permission denied.
```

To resolve this, either:
- Create the `log` directory in your `source` folder, or
- Configure a custom path in `var/configuration/configurable_services.yaml` (see "Customizable parameters" section)
