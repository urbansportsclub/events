# Changelog

All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [Unreleased]

## [1.1.4] - 2020-08-19

### Removed
- Remove domain specific interfaces

## [1.1.3] - 2020-05-20

### Added
- Allow setting defaults for avro encoded messages

## [1.1.2] - 2020-05-18

### Added
- Auto register missing schemas and subjects option

## [1.1.1] - 2020-05-18

### Changed
- Allow nullable schema properties

## [1.0.2] - 2020-04-17

### Changed
- Backward compatibility support

## [1.0.1] - 2020-04-15

### Changed
- Proper return type from cache adapter

## [1.0.0] - 2020-04-14

### Added
- Added support for avro schemas

### Removed
- id member has been removed from Message model
- connection member has been removed from Message model
- payload member changed type to array in Message model
- ProducerService now accepts serializer and schemas parameters
- ConsumerService now accepts serializer and schemas parameters
- GenericObserver has been renamed to CustomObserver
- Observers are not coupled with QueueableEntity anymore, instead they accept any JsonSerializable implementation

[unreleased]: https://github.com/onefit/events/compare/1.1.4...HEAD
[1.1.4]: https://github.com/onefit/events/compare/1.1.3...1.1.4
[1.1.3]: https://github.com/onefit/events/compare/1.1.2...1.1.3
[1.1.2]: https://github.com/onefit/events/compare/1.1.1...1.1.2
[1.1.1]: https://github.com/onefit/events/compare/1.0.2...1.1.1
[1.0.2]: https://github.com/onefit/events/compare/1.0.1...1.0.2
[1.0.1]: https://github.com/onefit/events/compare/1.0.0...1.0.1
[1.0.0]: https://github.com/onefit/events/compare/0.10.6...1.0.0
