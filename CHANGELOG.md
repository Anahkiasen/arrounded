# CHANGELOG

### 0.5.3 - 2014-12-17

### Added
- Added `modelsNamespace` property to Arrounded in case models are not in the default place

### 0.5.2 - 2014-12-11

### Fixed
- Missing use

### 0.5.1 - 2014-12-11

### Fixed
- Fixed behavior of relationships-scoped repositories

### 0.5.0 - 2014-12-10

### Added
- Added `AbstractFinder` class
- Added search form to admin views
- Added pagination to core controllers

### Fixed
- Fixed some issues in the crawler
- Make AbstractMailer set the locale on emails when sending them

### 0.4.1 - 2014-11-18

### Changed
- Allow Crawler to process routes with multiple patterns

### Fixed
- Fixed Crawler listing same routes twice

### 0.4.0 - 2014-11-05

### Added
- Added `Metadata::setDefaultsFromFile`

### Changed
- Made Crawler class use Arrounded service to qualify models
- Moved slugs handling to `cviebrock/eloquent-sluggable`
- `UsesContainer` now lists all container entries as properties

### Fixed
- Do not paginate results when already paginated in back-end

## 0.3.2 - 2014-10-24

### Changed
- Only pass the email to the mail closure in AbstractMailer to reduce payloads

## 0.3.1 - 2014-10-16

### Added
- Allow attributes to be passed to `Illustrable::thumbnail`
- Added some redirection helpers to `AbstractSmartController`
- Added `AbstractSmartController:validateOwnership` as helper to create ownership filters

### Fixed
- Bugfixes in the creation of instances for related model classes

## 0.3.0 - 2014-10-10

### Added
- Added core "Arrounded" class with various reflection methods
- Added AbstractTransformer and DefaultTransformer
- Added `IllustrableInterface` for models implementing `Illustrable`

### Changed
- Abstract controllers were moved to `Abstracts\Controllers` (and all prefixed with Abstract)
- `Models\Upload` was moved to `Abstracts\Models\AbstractUploadModel`
- `Abstracts\AbstractModel` was moved to `Abstracts\Models\AbstractModel`

### Fixed
- Fixed a bug in ReflectionModel::hasTrait

## 0.2.2 - 2014-09-26

### Added
- Allow string boolean presenters

### Changed
- Changes to how soft-deletes are handled
- Work on presenters

## 0.2.1 - 2014-09-19

### Added
- Add statistics and charts classes
- Added `Collection::distribution`

### Changed
- Make administration templates compatible with Angular
- Pass the received attributes to `AbstractForm::getRules`

## 0.2.0 - 2014-09-15

### Added
- Added Chart and Statistics classes
- Added Metadata class
- Add support for placeholders in uploads

### Changed
- Delegate everything API related to Dingo API
- Delegate everything uploads related to Stapler
- Delegate foreign keys migration to ForeignKeysMigrator

### Removed
- Remove some unused traits

### Fixed
- Various bugfixes

## 0.1.0 - 2014-09-10

### Added
- Initial tagged release
