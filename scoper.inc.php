<?php

declare(strict_types=1);

use Isolated\Symfony\Component\Finder\Finder;

return [
  'prefix' => 'OCA\\BAV\\Scoped',
  'expose-global-classes' => false,
  'exclude-classes' => [
    'OC',
    'Symfony\\Component\\Console',
  ],
  'exclude-namespaces' => [
    '/^(OC|OCP|NCU)(\\\\|$)/',
    '/^Psr(\\\\|$)/',
    '/^Symfony\\\\Component\\\\(Console|Process)(\\\\|$)/',
    '/^OCA\\\\(GroupFolders|RotDrop\\\\DevScripts)(\\\\|$)/',
    '/^Spatie\\\\TypeScriptTransformer(\\\\|$)/',
    '/^PHPUnit(\\\\|$)/',
    '/^GuzzleHttp(\\\\|$)/',
    '/^MathPHP(\\\\|$)/',
  ],
  'finders' => [
    Finder::create()
      ->files()
      ->ignoreVCS(true)
      ->in('vendor-scoped'),
  ],
];
