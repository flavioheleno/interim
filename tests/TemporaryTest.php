<?php
declare(strict_types = 1);

namespace Interim\Test;

use Interim\Temporary;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class TemporaryTest extends TestCase {
  private static function normalizeDirectorySeparators(string $path): string {
    if (DIRECTORY_SEPARATOR === '/') {
      return $path;
    }

    return substr(__DIR__, 0, 2) . str_replace('/', DIRECTORY_SEPARATOR, $path);
  }

  public function testStandardBehavior(): void {
    $this->assertSame(sys_get_temp_dir(), Temporary::getDirectory());
  }

  #[DataProvider('standardBehaviorProvider')]
  public function testStandardBehaviorEnv(string $setting, string|null $default, string $expected): void {
    if (putenv("TMPDIR={$setting}") === false) {
      $this->markTestSkipped('Failed to set "TMPDIR"');
    }

    $this->assertSame($expected, Temporary::getDirectory($default));
  }

  #[DataProvider('standardBehaviorProvider')]
  public function testStandardBehaviorINI(string $setting, string|null $default, string $expected): void {
    if (ini_set('sys_temp_dir', $setting) === false) {
      $this->markTestSkipped('Failed to set "sys_temp_dir"');
    }

    $this->assertSame($expected, Temporary::getDirectory($default));
  }

  /**
   * @return array<array<string, string|null, string>>
   */
  public static function standardBehaviorProvider(): array {
    $normalizedDef = self::normalizeDirectorySeparators('/def');
    $normalizedMyTmpDir = self::normalizeDirectorySeparators('/my/tmp/dir');

    return [
      [$normalizedMyTmpDir, null,           $normalizedMyTmpDir],
      [$normalizedMyTmpDir, 'def',          $normalizedMyTmpDir],
      [$normalizedMyTmpDir, $normalizedDef, $normalizedMyTmpDir]
    ];
  }

  #[DataProvider('openBaseDirProvider')]
  public function testOpenBaseDir(string $setting, string|null $default, string $expected): void {
    if (ini_set('open_basedir', $setting) === false) {
      $this->markTestSkipped('Failed to set "open_basedir"');
    }

    $this->assertSame($expected, Temporary::getDirectory($default));
  }

  /**
   * @return array<array<string, string|null, string>>
   */
  public static function openBaseDirProvider(): array {
    $rootDir = dirname(__DIR__);
    $openBaseDirWithTmp = $rootDir . PATH_SEPARATOR . sys_get_temp_dir();
    $normalizedOpenBaseDirDef = self::normalizeDirectorySeparators($rootDir . '/def');
    $normalizedDef = self::normalizeDirectorySeparators('/def');
    $normalizedVarTmp = self::normalizeDirectorySeparators('/var/tmp');
    $openBaseDirWithVarTmp = $rootDir . PATH_SEPARATOR . $normalizedVarTmp;

    return [
      // single restriction
      [$rootDir, null,           $rootDir],
      [$rootDir, 'def',          $normalizedOpenBaseDirDef],
      [$rootDir, $normalizedDef, $rootDir],

      // restriction with system's tmp (ignores $default as expected)
      [$openBaseDirWithTmp, null,           sys_get_temp_dir()],
      [$openBaseDirWithTmp, 'def',          sys_get_temp_dir()],
      [$openBaseDirWithTmp, $normalizedDef, sys_get_temp_dir()],

      // restriction with custom tmp
      [$openBaseDirWithVarTmp, null,              $rootDir],
      [$openBaseDirWithVarTmp, 'def',             $normalizedOpenBaseDirDef],
      [$openBaseDirWithVarTmp, $normalizedDef,    $rootDir],
      [$openBaseDirWithVarTmp, $normalizedVarTmp, $normalizedVarTmp]
    ];
  }
}
