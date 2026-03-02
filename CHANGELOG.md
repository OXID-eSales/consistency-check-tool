# Change Log for Oxid eSales Consistency Check Component

All notable changes to this project will be documented in this file.
The format is based on [Keep a Changelog](http://keepachangelog.com/)
and this project adheres to [Semantic Versioning](http://semver.org/).

## [2.1.0] - Unreleased

### Changed
- Updated to work with OXID eShop 7.5.x
- Minimum PHP version is now 8.3, tested up to PHP 8.5

## [2.0.1] - 2026-02-16

### Changed
- Path parameters (`app.log_file_path`, `app.export_directory_path`) now support both relative (`source` path) and absolute paths
- Default paths for `app.log_file_path` and `app.export_directory_path` changed from absolute (`/var/www/source/...`) to relative (`log/...`, `export`)

### Fixed
- SEO URL duplicate check now falls back to default suffix when an empty string is provided
- Default image entity directory paths changed from `/out/pictures/...` to `out/pictures/...` to be correctly resolved as relative paths

## [2.0.0] - 2026-01-15

### Added
- SEO URL consistency checking functionality
- Console command `oe:consistency_check:check-unused-seo-urls` to detect orphaned SEO URLs
- Console command `oe:consistency_check:check-duplicate-seo-urls` to detect SEO URLs with collision suffixes
- Console command `oe:consistency_check:delete-seo-urls` for batch deletion from CSV file
- CSV export functionality for SEO URL check results
- Support for OXID SEO types with reference tables (oxarticle, oxcategory, oxmanufacturer, oxvendor, oxcontent)
- Generic CSV export/import services in `Export/` domain for reusability
- `ExportFileNameGenerator` service for timestamped export filenames

### Changed
- Update component to work with OXID eShop 7.4
- Renamed `ImageManager/DataTransferObject` directory to `ImageManager/Dto` for naming consistency
- Removed `FileSystemUtils::getAbsolutePath()` wrapper method; `ImageManagerService` now uses `PathResolverInterface` directly
- `ImageManagerService` requires `PathResolverInterface` instance instead of `FileSystemUtilsInterface`
- `FileSystemUtils` requires `PathResolverInterface` instance instead of eShop `ContextInterface`

### Removed
- `FileSystemUtilsInterface::getAbsolutePath` method

## [1.0.0] - 2025-06-11

## Added
- Improved documentation in README explaining how to configure custom log path and avoid permission errors.
- OXID SDK recipe for development setup.

## [1.0.0-rc.1] - 2025-05-13
- Initial release

[2.1.0]: https://github.com/OXID-eSales/consistency-check-tool/compare/v2.0.1...v2.1.0
[2.0.1]: https://github.com/OXID-eSales/consistency-check-tool/compare/v2.0.0...v2.0.1
[2.0.0]: https://github.com/OXID-eSales/consistency-check-tool/compare/v1.0.0...v2.0.0
[1.0.0]: https://github.com/OXID-eSales/consistency-check-tool/compare/v1.0.0-rc.1...v1.0.0
[1.0.0-rc.1]: https://github.com/OXID-eSales/consistency-check-tool/releases/tag/v1.0.0-rc.1