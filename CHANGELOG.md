# Change Log for Oxid eSales Consistency Check Component

All notable changes to this project will be documented in this file.
The format is based on [Keep a Changelog](http://keepachangelog.com/)
and this project adheres to [Semantic Versioning](http://semver.org/).

## [1.0.1] - unreleased

### Fixed
- Corrected the typo in the manufacturer thumbnail image entity service ID (`...manufacturer.oxthumbail` → `...manufacturer.oxthumbnail`). The entity is collected by tag, so behaviour is unchanged; shops overriding this service by its ID must update their configuration.

## [1.0.0] - 2025-06-11

## Added
- Improved documentation in README explaining how to configure custom log path and avoid permission errors.
- OXID SDK recipe for development setup.

## [1.0.0-rc.1] - 2025-05-13
- Initial release

[1.0.0]: https://github.com/OXID-eSales/consistency-check-tool/compare/v1.0.0-rc.1...v1.0.0
[1.0.0-rc.1]: https://github.com/OXID-eSales/consistency-check-tool/releases/tag/v1.0.0-rc.1