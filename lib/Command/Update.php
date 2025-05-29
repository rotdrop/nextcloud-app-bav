<?php
/**
 * BAV - Bank Account Validator for German bank accounts.
 *
 * @author Claus-Justus Heine <himself@claus-justus-heine.de>
 * @copyright Claus-Justus Heine 2025
 * @license   AGPL-3.0-or-later
 *
 * Nextcloud DokuWiki is free software: you can redistribute it and/or
 * modify it under the terms of the GNU AFFERO GENERAL PUBLIC LICENSE
 * License as published by the Free Software Foundation; either
 * version 3 of the License, or (at your option) any later version.
 *
 * Nextcloud DokuWiki is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU
 * AFFERO GENERAL PUBLIC LICENSE for more details.
 *
 * You should have received a copy of the GNU Affero General Public
 * License along with Nextcloud DokuWiki. If not, see
 * <http://www.gnu.org/licenses/>.
 */

namespace OCA\BAV\Command;

use OCP\IL10N;
use Psr\Log\LoggerInterface;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\DescriptorHelper;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\Question;

use OCA\BAV\Service\BAV;

/** Command for updating the BAV database tables. */
class Update extends Command
{
  use \OCA\BAV\Toolkit\Traits\LoggerTrait;

  // phpcs:ignore Squiz.Commenting.FunctionComment.Missing
  public function __construct(
    protected string $appName,
    protected IL10N $l,
    protected BAV $bav,
    protected LoggerInterface $logger,
  ) {
    parent::__construct();
  }
  // phpcs:enable

  /** {@inheritdoc} */
  protected function configure()
  {
    $this
      ->setName($this->appName . ':update')
      ->setDescription($this->l->t('Fetch bank data from the Deutsche Bundesbank and update the database tables of the app.'))
      ->addOption(
        'force',
        'f',
        InputOption::VALUE_NONE,
        $this->l->t('Force update even if the databse tables appear to be up to data.'),
      )
      ->addOption(
        'dry',
        null,
        InputOption::VALUE_NONE,
        'Just simulate, do not change anything.',
      )
      ;
  }

  /** {@inheritdoc} */
  protected function execute(InputInterface $input, OutputInterface $output): int
  {
    $force = $input->getOption('force');
    $outdated = $this->bav->isOutdated();
    if (!$outdated && !$force) {
      $output->writeln($this->l->t('The database appears to be up to date, doing nothing.'));
      return 0;
    } elseif (!$outdated && $force) {
      $output->writeln($this->l->t('The database appears to be up to date, but updating anyway as requested by "--force" option.'));
    } elseif ($outdated) {
      $output->writeln($this->l->t('The database appears to be out of date, attempt to update.'));
    }
    $dry = $input->getOption('dry');
    $output->writeln($this->l->t('Attempting to update the database ...'));
    if ($dry) {
      $output->writeln('<info>' . $this->l->t('Not updating as requested by "--dry" option.') . '</info>');
    } else {
      $this->bav->update();
    }
    $output->writeln($this->l->t('... the database has been updated.'));

    return 0;
  }
}
