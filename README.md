# Interim

Interim is a simple wrapper around PHP's `tmpfile()` and `sys_get_temp_dir()` that takes into account open basedir restriction.

## Installation

To use Interim, simple run:

```bash
composer require flavioheleno/interim
```

## Usage

When `open_basedir` is disabled, Interim behaves exactly the same way as `sys_get_temp_dir()`:

```php
// before
var_dump(sys_get_temp_dir());
// string(4) "/tmp"

// after
var_dump(Interim\Temporary::getDirectory());
// string(4) "/tmp"

// -or-
var_dump(Interim\Temporary::getDirectory('tmp'));
// string(4) "/tmp"

// -or-
var_dump(Interim\Temporary::getDirectory('/var/tmp'));
// string(4) "/tmp"
```

When `open_basedir` is enabled, Interim will check if temporary dir is in the open_basedir list, if not, it will return an alternative.

```php
ini_set('open_basedir', '/var/www/html:/var/tmp');

// before (if you try to use "/tmp", an "open_basedir restriction in effect" warning will be raised)
var_dump(sys_get_temp_dir());
// string(4) "/tmp"

// after
var_dump(Interim\Temporary::getDirectory());
// string(4) "/var/www/html"

// -or-
var_dump(Interim\Temporary::getDirectory('tmp'));
// string(4) "/var/www/html/tmp"

// -or-
var_dump(Interim\Temporary::getDirectory('/var/tmp'));
// string(4) "/var/tmp"
```

## License

This library is licensed under the [MIT License](LICENSE).
