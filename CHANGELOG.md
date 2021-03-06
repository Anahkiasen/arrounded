# CHANGELOG

## 0.9.1 - 2016-08-09

### Removed

- Removed `Collection` `shuffle` method.

## 0.9.0 - 2016-01-26

### Changed

- The `JavascriptBridge::add` now merges recursively

## 0.8.0 - 2015-12-02

### Added

- Allow passing `custom_search_fields` to the search form on the admin/index page

### Changed
- Pass query strings to the pagination links
- Grouping `orWhere` clauses into a single statement

### Fixed
- Fixed the `AbstractFinder` to allow searching for boolean fields or numeric fields

## 0.7.0 - 2015-06-18

### Added
- Added ability to define a whitelist instead of a blacklist on Crawler class

### Changed
- Laravel 5.x support

### Deprecated
- The `HTML::meta` alias has been removed in favor of a facade `Meta::render()`

### Fixed
- Fixed an issue with columns fetching on some DB drivers
- Fixed S3 configuration not being passed to Stapler
- Fixed a routing issue with `dingo/api` and/or `barryvdh/debugbar`

## 0.6.2 - 2015-06-11

### Fixed
- Type hint to a `ValidatableInterface` instead of `AbstractModel` directly

## 0.6.1 - 2015-05-18

### Fixed
- Fixed issue with files not being properly deleted on `AbstractUploadModels`

## 0.6.0 - 2015-04-09

### Added
- Added ability to configure application namespaces more in depth
- Added `DraftScope` trait for models who can be in draft
- Added ability to set custom error messages in form classes
- Added `AbstractComposer:buildOptionsFromList`

### Changed
- Form classes will now also throw a ResourceException in a JSON context, for proper API error handling
- Also allow ParameterBags to be passed as attributes to form classes
- The redirect controller helpers have been moved to a `Redirectable` trait

### Fixed
- Fixed namespaced controllers support
- Fixed various issues with snake casing

## 0.5.6 - 2015-01-26

### Changed
- Made `Arrounded::getModelsFolder` also look in the root namespace (for `Acme\Repositories` per example instead of `Acme\Models\Repositories`)

### Fixed
- Fixed incorrect typehint in `Arrounded::getFirstExistingClass`

## 0.5.5 - 2015-01-15

### Added
- Added some folders helpers

## 0.5.4 - 2014-12-17

### Added
- Added `Arrounded` facade

## 0.5.3 - 2014-12-17

### Added
- Added `modelsNamespace` property to Arrounded in case models are not in the default place

## 0.5.2 - 2014-12-11

### Fixed
- Missing use

## 0.5.1 - 2014-12-11

### Fixed
- Fixed behavior of relationships-scoped repositories

## 0.5.0 - 2014-12-10

### Added
- Added `AbstractFinder` class
- Added search form to admin views
- Added pagination to core controllers

### Fixed
- Fixed some issues in the crawler
- Make AbstractMailer set the locale on emails when sending them

## 0.4.1 - 2014-11-18

### Changed
- Allow Crawler to process routes with multiple patterns

### Fixed
- Fixed Crawler listing same routes twice

## 0.4.0 - 2014-11-05

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
