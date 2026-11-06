#!/usr/bin/env bash
# Verifies the packaged runtime of the app-store archive, run by the shared
# build workflow with APP_ID, APP_VERSION and ARCHIVE set.
set -euo pipefail

test -s "$ARCHIVE"
package_dir="$(mktemp -d)"
trap 'rm -rf "$package_dir"' EXIT
tar -xzf "$ARCHIVE" -C "$package_dir"
app_dir="$package_dir/$APP_ID"
for file in appinfo/info.xml vendor/autoload.php vendor-scoped/autoload.php js/asset-meta.json COPYING README.md; do
  test -s "$app_dir/$file"
done
test ! -d "$app_dir/vendor/bamarni"
APP_DIR="$app_dir" php <<'PHP'
<?php
$appDir = getenv('APP_DIR');
require $appDir . '/vendor/autoload.php';
require $appDir . '/vendor-scoped/autoload.php';
if (!class_exists(\OCA\BAV\Scoped\PHP_IBAN\IBAN::class)
    || !class_exists(\OCA\BAV\Scoped\rotdrop\bav\BAV::class)
    || !class_exists(\OCA\BAV\Scoped\rotdrop\bav\DataBackend\Orm\Doctrine\DoctrineBackendContainer::class)
    || !class_exists(\OCA\BAV\Scoped\Doctrine\ORM\EntityManager::class)) {
  throw new RuntimeException('Packaged runtime dependencies are missing.');
}
$iban = new \OCA\BAV\Scoped\PHP_IBAN\IBAN('DE89370400440532013000');
if (!$iban->Verify()
    || \OCA\BAV\Scoped\iban_to_machine_format('DE89 3704 0044 0532 0130 00') !== $iban->MachineFormat()
    || \OCA\BAV\Scoped\iban_to_human_format($iban->MachineFormat()) !== 'DE89 3704 0044 0532 0130 00') {
  throw new RuntimeException('The packaged IBAN validator is not functional.');
}
if (!trait_exists(\OCA\BAV\Scoped\OCA\RotDrop\Toolkit\Traits\LoggerTrait::class)
    || \OCA\BAV\Scoped\OCA\RotDrop\Toolkit\Service\AppInfoService::getAppInfoAppName() !== 'bav') {
  throw new RuntimeException('The packaged toolkit is missing or cannot locate the app.');
}
$doctrineConfig = new \OCA\BAV\Scoped\Doctrine\ORM\Configuration();
$doctrineConfig->setMetadataDriverImpl(new \OCA\BAV\Scoped\Doctrine\ORM\Mapping\Driver\AttributeDriver(
  \OCA\BAV\Scoped\rotdrop\bav\DataBackend\Orm\Doctrine\DoctrineBackendContainer::getEntityPaths()
));
$doctrineConfig->enableNativeLazyObjects(true);
$connection = \OCA\BAV\Scoped\Doctrine\DBAL\DriverManager::getConnection([
  'driver' => 'pdo_sqlite',
  'memory' => true,
]);
$entityManager = new \OCA\BAV\Scoped\Doctrine\ORM\EntityManager($connection, $doctrineConfig);
$metadata = $entityManager->getMetadataFactory()->getAllMetadata();
$entityNames = array_map(static fn($entity) => $entity->getName(), $metadata);
sort($entityNames);
if ($entityNames !== [
  \OCA\BAV\Scoped\rotdrop\bav\Bank\Agency::class,
  \OCA\BAV\Scoped\rotdrop\bav\Bank\Bank::class,
  \OCA\BAV\Scoped\rotdrop\bav\DataBackend\Orm\MetaData::class,
]) {
  throw new RuntimeException('Packaged Doctrine entities were not discovered.');
}
$mappingErrors = (new \OCA\BAV\Scoped\Doctrine\ORM\Tools\SchemaValidator($entityManager))->validateMapping();
if ($mappingErrors !== []) {
  throw new RuntimeException('Packaged Doctrine mappings are invalid: ' . json_encode($mappingErrors, JSON_THROW_ON_ERROR));
}
(new \OCA\BAV\Scoped\Doctrine\ORM\Tools\SchemaTool($entityManager))->createSchema($metadata);
if ($entityManager->createQuery(
  'SELECT metadata FROM ' . \OCA\BAV\Scoped\rotdrop\bav\DataBackend\Orm\MetaData::class . ' metadata'
)->getResult() !== []) {
  throw new RuntimeException('Packaged Doctrine query returned unexpected results.');
}
$entityManager->getConnection()->close();
$assets = json_decode(file_get_contents($appDir . '/js/asset-meta.json'), true, flags: JSON_THROW_ON_ERROR);
if (empty($assets)) {
  throw new RuntimeException('The asset manifest is empty.');
}
foreach ($assets as $entry) {
  if (empty($entry['js'])) {
    throw new RuntimeException('An asset entry has no JavaScript bundle.');
  }
  foreach ($entry as $asset) {
    if (!is_file($appDir . '/' . $asset) || filesize($appDir . '/' . $asset) === 0) {
      throw new RuntimeException('Missing or empty packaged asset: ' . $asset);
    }
  }
}
PHP
