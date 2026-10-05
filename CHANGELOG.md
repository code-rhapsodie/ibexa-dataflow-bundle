# Unreleased
* `NotModifiedProductFilter` now compares product attributes (per attribute type) and no longer skips the update of a product whose attributes changed or are unset, e.g. a null `checkbox` value
* Add `ProductAttributesComparatorInterface` (default `ApiProductAttributesComparator`, using the public product catalog API) and `ProductAttributeValueComparatorInterface` (tag `coderhapsodie.ibexa_dataflow.product_attribute_value_comparator`) to customize the comparison

# Version 6.6.0
* Add "Information" and "Log" tabs in the job popin, with content kept in memory, an empty log message and a back button to the executions history
* Add `?job=<id>` query parameter to open the job details popin directly (e.g. from an Ibexa notification)
* Improve form popins: focus the first field on open, reset fields and errors on close, disable submit while pending
* Fix scheduled dataflow form validation (constraints as PHP attributes)
* CI: test PHP 8.4 and 8.5 with lowest and highest dependencies, and add PHP CS Fixer, PHPStan (level 4, with banned code) and Rector checks
* Migrate tests to PHPUnit 12
* Apply PHP CS Fixer and Rector rules (PHP 8.4 syntax, non-Yoda comparisons)

# Version 6.5.1
* Fix missing permission check on scheduled dataflow edit (broken access control)
* Fix XSS in streamed job log output
* Require POST for enable/disable scheduled dataflow actions
* Fix 500 error (instead of 404) when a job or scheduled dataflow id does not exist

# Version 6.5.0
* Add `ibexa_seo` field comparator
* Add type filter
* Add count on dashbaord
* Add average time on scheduled tab
* Better options display

# Version 6.4.0
* Add the possibility to download the log file

# Version 6.3.0
* add stream exceptions management

# Version 6.2.0
* Added delete pending job button by

# Version 6.1.2
* Fix BillingAddressComparator

# Version 6.1.1
* Add missing status labels

# Version 6.1.0
* Add `ibexa_product_specification` comparator
* Add `attributes` in `ProductCreateStructure` and `ProductUpdateStructure`

# Version 6.0.1
* Fix taxonomy comparator

# Version 6.0.0
* Ibexa 5.0+ support
* Comparators for Ibexa Commerce / Experience field types

# Version 5.2.1
* Fixed datepicker in oneshot modal

# Version 5.2.0
* Added Dashboard tab

# Version 5.1.1
* Add branding label

# Version 5.1.0
* Added possibility to create one shot job from scheduled job

# Version 5.0.0

* Renamed bundle to IbexaDataflowBundle
* Replaced every trace of ezdataflow with ibexa_dataflow or ibexa-dataflow
* Added automatic replacement script

# Version 4.3.0

* Replaced date field with date picker

# Version 4.2.0

* Added error count columns to job tables
* Fixed scheduled job run history pagination

# Version 4.1.2

* Fix main menu label
* Fix redirection after adding a new oneshot job

# Version 4.1.1

* Fix NotModifiedContentFilter when creating new translation

# Version 4.1.0

* Allow Dataflow 4 by @jeremycr in https://github.com/code-rhapsodie/ezdataflow-bundle/pull/44

# Version 4.0.0

* Add compatibility with Ibexa 4.0+ and drop compatibility for eZPlatform 2 and Ibexa 3 
