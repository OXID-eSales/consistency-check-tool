# OXID eShop Consistency Check Component

[![Development](https://github.com/OXID-eSales/consistency-check-tool/actions/workflows/trigger.yaml/badge.svg?branch=b-7.4.x)](https://github.com/OXID-eSales/consistency-check-tool/actions/workflows/trigger.yaml)
[![Latest Version](https://img.shields.io/packagist/v/OXID-eSales/consistency-check-tool?logo=composer&label=latest&include_prereleases&color=orange)](https://packagist.org/packages/oxid-esales/consistency-check-tool )
[![PHP Version](https://img.shields.io/packagist/php-v/oxid-esales/consistency-check-tool)](https://github.com/oxid-esales/consistency-check-tool)

[![Quality Gate Status](https://sonarcloud.io/api/project_badges/measure?project=OXID-eSales_consistency-check-tool&metric=alert_status)](https://sonarcloud.io/dashboard?id=OXID-eSales_consistency-check-tool)
[![Coverage](https://sonarcloud.io/api/project_badges/measure?project=OXID-eSales_consistency-check-tool&metric=coverage)](https://sonarcloud.io/dashboard?id=OXID-eSales_consistency-check-tool)
[![Technical Debt](https://sonarcloud.io/api/project_badges/measure?project=OXID-eSales_consistency-check-tool&metric=sqale_index)](https://sonarcloud.io/dashboard?id=OXID-eSales_consistency-check-tool)


The OXID eSales Consistency Check component is a flexible tool designed to perform various consistency checks on your eShop. It helps maintain shop performance and data hygiene by identifying and resolving data inconsistencies.

**Current capabilities include:**
- **Unused Image Detection** - Identify orphaned image files no longer connected to products, categories, or manufacturers
- **SEO URL Verification** - Detect unused and duplicate SEO URLs that may affect shop performance and search rankings

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
This component assumes you have OXID eShop Compilation version 7.4.0 installed.

## Installation
To install this component in your OXID eShop environment, use Composer:
```bash
$ composer require oxid-esales/consistency-check-tool
```

## Development installation

The installation instructions below are shown for the current [SDK](https://github.com/OXID-eSales/docker-eshop-sdk)
for shop 7.4. Make sure your system meets the requirements of the SDK.

0. Ensure all docker containers are down to avoid port conflicts

1. Clone the SDK for the new project
```shell
echo MyProject && git clone https://github.com/OXID-eSales/docker-eshop-sdk.git $_ && cd $_
```

2. Clone the repository to the source directory
```shell
git clone --recurse-submodules https://github.com/OXID-eSales/consistency-check-tool.git --branch=b-7.4.x ./source
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
$ vendor/bin/oe-console oe:consistency_check:delete-seo-urls --file=/path/to/exported.csv
```

Use `--dry-run` to preview deletions without making changes:
```bash
$ vendor/bin/oe-console oe:consistency_check:delete-seo-urls --file=/path/to/exported.csv --dry-run
```

**Recommended workflow:**
1. Run check command to see results in console
2. Run check command with `--export` to generate CSV
3. Review the CSV file and remove any rows you want to keep
4. Run delete command with `--dry-run` to preview
5. Run delete command to perform actual deletion

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

All paths are relative to the OXID eShop root directory.

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

## Troubleshooting

### Wrong log file path

This tool uses a default log path `/var/www/source/log/oe_consistency_check.log` in a standard OXID eShop
directory structure. However, if your project uses a different structure (e.g. `/var/www/custom/source/log`), this
directory may not exist, and you may encounter an error like:
```
There is no existing directory at "/var/www/custom/source/log" and its not buildable: Permission denied.
```
Refer to the "Customizable parameters" section to learn how to change the log file path.
