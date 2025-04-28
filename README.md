# OXID eShop Consistency Check Component

[![Development](https://github.com/OXID-eSales/consistency-check-tool/actions/workflows/trigger.yaml/badge.svg?branch=b-7.3.x)](https://github.com/OXID-eSales/consistency-check-tool/actions/workflows/trigger.yaml)
[![Latest Version](https://img.shields.io/packagist/v/OXID-eSales/consistency-check-tool?logo=composer&label=latest&include_prereleases&color=orange)](https://packagist.org/packages/oxid-esales/consistency-check-tool )
[![PHP Version](https://img.shields.io/packagist/php-v/oxid-esales/consistency-check-tool)](https://github.com/oxid-esales/consistency-check-tool)

[![Quality Gate Status](https://sonarcloud.io/api/project_badges/measure?project=OXID-eSales_consistency-check-tool&metric=alert_status)](https://sonarcloud.io/dashboard?id=OXID-eSales_consistency-check-tool)
[![Coverage](https://sonarcloud.io/api/project_badges/measure?project=OXID-eSales_consistency-check-tool&metric=coverage)](https://sonarcloud.io/dashboard?id=OXID-eSales_consistency-check-tool)
[![Technical Debt](https://sonarcloud.io/api/project_badges/measure?project=OXID-eSales_consistency-check-tool&metric=sqale_index)](https://sonarcloud.io/dashboard?id=OXID-eSales_consistency-check-tool)


The OXID eSales Consistency Check component is designed to perform various consistency checks on your eShop. It primarily focuses on detecting unused images and provides multiple actions such as

- Performing a dry-run to see how many images would be affected
- Moving unused images to another location
- Deleting unused images to free up storage

This component ensures that your eShop remains optimized by helping you remove or move unnecessary image files while keeping track of the changes.

## Features
- Provides commands to move or delete unused images. 
- Supports dry-run mode, allowing safe testing before making changes.
- Filter unused images across product, categories, and manufacturers.
- Progress bar for real-time feedback
- Logs all actions in log/oe_consistency_check.log for review and auditing. 
- Customizable directory paths for images.

## Compatibility
This component assumes you have OXID eShop Compilation version 7.3.0 installed.

## Installation
To install this component in your OXID eShop environment, use Composer:
```bash
$ composer require oxid-esales/consistency-check-tool
```

## Development installation

To install from github as a source, first clone the repository.

```bash
$ git clone https://github.com/OXID-eSales/consistency-check-tool ./dev-packages/consistency-check-tool
```
Set the repository up in composer.json

```bash
$ composer config repositories.oxid-esales/consistency-check-tool \
  --json '{"type":"path", "url":"./dev-packages/consistency-check-tool", "options": {"symlink": true}}'
```

Ensure you're in the shop root directory (the file `composer.json` and the directories `source/` and `vendor/` are located there) and require the component.

```bash
$ composer require oxid-esales/consistency-check-tool
```

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

### Logs
All operations, including moved and deleted images, are logged in:
```
log/oe_consistency_check.log
```
This log file helps you track the changes and verify actions performed by the tool.

## Configuration
If you are using custom directory paths for storing images (products, categories, manufacturers), update the paths in:
```
src/ImageManager/Entity/services.yaml
```
Example configuration:
```
oxid_esales.consistency_check.entity.image_entity.product.oxpic1:
  class: OxidEsales\ConsistencyCheck\ImageManager\Entity\ImageEntity
  arguments:
    $name: 'Product'
    $table: 'oxarticles'
    $fieldName: 'OXPIC1'
    $directory: 'pictures/master/products/1'
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

