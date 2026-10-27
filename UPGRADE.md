# Upgrade to v7

[BC] Dates (`next` of the scheduled dataflows, `requested_date`, `start_time` and `end_time` of the jobs) are now stored and read in UTC,
whatever the PHP default timezone is. The existing dates must be converted. **Make a backup of the tables first: the commands are not idempotent.**

Dates are not written the same way depending on their origin, so two commands are needed:

1. Dates written by PHP (`start_time`, `end_time`), in the PHP timezone: use the command of the dataflow-bundle
   (see its [upgrade guide](https://github.com/code-rhapsodie/dataflow-bundle/blob/master/UPGRADE.md)):

   ```shell
   php bin/console code-rhapsodie:dataflow:convert-dates-to-utc --dry-run
   ```

2. Dates filled by the forms of this bundle (`next`, `requested_date`): the old forms stored the UTC wall time shifted by the difference
   between the PHP timezone and the timezone of the user who filled the form. The original date can be recovered only if you tell
   which timezone the users had (the same for all of them) and which PHP timezone was configured:

   ```shell
   php bin/console code-rhapsodie:ibexa-dataflow:convert-dates --user-timezone=Europe/Paris [--php-timezone=UTC] --dry-run
   ```

   `--php-timezone` defaults to the current PHP default timezone. Rows created by the CLI or by PHP code are not tagged and are converted too:
   check the result with `--dry-run` first, and do not run the first command on `next` / `requested_date`
   (do not use its `--next` and `--requested-date` options).

Run each command once, without `--dry-run`, when the result is the expected one, then deploy the new version.

# Upgrade to v6

Version 6 requires `code-rhapsodie/dataflow-bundle` ^6.0, which adds new columns to the job table
(`exceptions`, `exception_count`). Update your database schema:

```shell
php bin/console code-rhapsodie:dataflow:database-schema --update
```

You can also generate the SQL queries to review or apply them manually:

```shell
php bin/console code-rhapsodie:dataflow:dump-schema --update
```

# Upgrade to v5

In version 5, all references to ezdataflow were replaced by ibexa-dataflow, in namespaces, class names, tags and config keys.
This requires some changes in your application files.

## Updating source files

A script is provided to do all the replacements in your project files for you. Pass as arguments all directories where
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
