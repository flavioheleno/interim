<?php
declare(strict_types = 1);

namespace Interim;

use SplFileObject;

final class Temporary {
  public static function getDirectory(string $default = null): string {
    static $directory = null;
    if ($directory === null) {
      $directory = rtrim(sys_get_temp_dir(), DIRECTORY_SEPARATOR);

      $openBaseDir = ini_get('open_basedir');
      // open_basedir is disabled (same standard behavior)
      if ($openBaseDir === '' || $openBaseDir === false) {
        return $directory;
      }

      $dirList = array_map(
        static function (string $path): string {
          return rtrim($path, DIRECTORY_SEPARATOR);
        },
        explode(PATH_SEPARATOR, $openBaseDir)
      );

      // system's temp dir is aloowed by open_basedir
      if (in_array($directory, $dirList, true) === true) {
        return $directory;
      }

      // fallback to the first directory in open_basedir ($default is null/empty)
      if ($default === null || $default === '') {
        $directory = $dirList[0];

        return $directory;
      }

      $default = rtrim($default, DIRECTORY_SEPARATOR);
      // fallback to the first directory in open_basedir concatenated with $default (relative path)
      if (str_starts_with($default, DIRECTORY_SEPARATOR) === false) {
        $directory = $dirList[0] . DIRECTORY_SEPARATOR . $default;

        return $directory;
      }

      // check if $default is listed in open_basedir (absolute path - leading DIRECTORY_SEPARATOR)
      // fallback to it if true, otherwise return the first directory in open_basedir
      $directory = match (in_array($default, $dirList, true)) {
        true => $default,
        false => $dirList[0]
      };

      return $directory;
    }

    return $directory;
  }

  public static function getFile(string $prefix = null): SplFileObject {
    $filename = tempnam(self::getDirectory(), $prefix ?? '');
    if ($filename === false) {
      return false;
    }

    return new SplFileObject($filename, 'w+b');
  }
}
