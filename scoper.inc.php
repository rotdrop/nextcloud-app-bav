<?php

declare(strict_types=1);

use Isolated\Symfony\Component\Finder\Finder;

return [
  'prefix' => 'OCA\\BAV\\Scoped',
  'expose-global-classes' => false,
  'exclude-classes' => [
    'OC',
  ],
  'exclude-namespaces' => [
    '/^(OC|OCP|NCU)(\\\\|$)/',
    '/^Psr(\\\\|$)/',
  ],
  'patchers' => [
    static function (string $filePath, string $prefix, string $contents): string {
      if (!str_ends_with($filePath, '/globalcitizen/php-iban/oophp-iban.php')) {
        return $contents;
      }

      // The OO wrapper relies on PHP's fallback to global procedural functions.
      return preg_replace_callback(
        '/\b(verify_iban|iban_[a-z_]+)\(/',
        static fn(array $match): string => '\\' . $prefix . '\\' . $match[1] . '(',
        $contents,
      ) ?? throw new RuntimeException('Unable to scope PHP-IBAN function calls.');
    },
  ],
  'finders' => [
    Finder::create()
      ->files()
      ->ignoreVCS(true)
      ->in('vendor-scoped'),
  ],
];
