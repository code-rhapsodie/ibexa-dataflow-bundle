In version 5, all references to ezdataflow were replaced by ibexa-dataflow, in namespaces, class names, tags and config keys.
This requires some changes in your application files.

## Updating source files

A script is provided to do all the replacing in your project files for you. Pass as arguments all directories where
replacing is needed.

```shell
    php vendor/bin/ibexa-dataflow-namespace-changer.phar src/ config/ templates/ translations/
```

You should check all changes performed afterward. Here is the list of all the replacement that should be performed:

- all namespaces starting by `CodeRhapsodie\EzDataflowBundle` should now start by `CodeRhapsodie\IbexaDataflowBundle`, anywhere in PHP sources and services definitions
- the class `CodeRhapsodieEzDataflowBundle` should be renamed `CodeRhapsodieIbexaDataflowBundle` in `bundles.php`
- the config key `code_rhapsodie_ezdataflow` should be renamed `code_rhapsodie_ibexa_dataflow` in config files
- all translation keys starting by `ezdataflow` should now start by `ibexa_dataflow` in translation files
- the security policy module `ezdataflow` should be renamed `ibexa_dataflow`
- all template paths containing `ezdataflow` should now contain `ibexa_dataflow`

## Updating database

Run the following command on your database:

```sql
UPDATE ezpolicy SET module_name = 'ibexa_dataflow' WHERE module_name = 'ezdataflow'
```
