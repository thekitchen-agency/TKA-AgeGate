# Changelog

All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [1.0.6] - 2026-05-21
### Fixed
- Fixed critical crawler-detection early-exit crash. The plugin now registers its events, template roots, and variables (`craft.ageGate`) for all requests (including automated engines like Googlebot and `curl`), bypassing only redirect and visual dialog behaviors. This resolves 500 fatal exceptions for indexers and bots.

## [1.0.3] - 2026-05-17
### Added
- Added visual styling options (Primary Color, Background Color, Blur Amount, Border Radius).
- Modern Glassmorphism styling for Age Gate.
- Introduced Validation Type setting (Simple, Year Only, Birthdate).
- Dynamic Frontend validation via JS.

### Changed
- Refactored frontend templates to support dynamic inputs.

## [1.0.0] - 2026-05-01
### Added
- Initial release of the plugin.
