<?php

namespace YoRus\BehatContext\Context;

use Behat\Behat\Context\Context;
use Behat\Gherkin\Node\TableNode;
use Doctrine\ORM\EntityManagerInterface;
use Fidry\AliceDataFixtures\LoaderInterface;
use Fidry\AliceDataFixtures\Persistence\PurgeMode;

/**
 * FidryAliceFixturesContext
 *
 * @uses Context
 */
class FidryAliceFixturesContext implements Context
{
    private ?PurgeMode $purgeMode = null;

    /**
     * @param EntityManagerInterface $entityManager
     * @param LoaderInterface        $loader
     * @param string                 $projectDir
     * @param string|null            $basepath
     */
    public function __construct(
        private EntityManagerInterface $entityManager,
        private LoaderInterface $loader,
        private string $projectDir,
        private ?string $basepath = null,
    ) {
    }

    /**
     * @Given I use fixture files:
     */
    public function iUseFixtureFiles(TableNode $table)
    {
        $this->purgeMode = PurgeMode::createTruncateMode();
        $this->useFixtureFiles($table);
    }

    /**
     * @Given I add fixture files:
     */
    public function iAddFixtureFiles(TableNode $table)
    {
        $this->purgeMode = PurgeMode::createNoPurgeMode();
        $this->useFixtureFiles($table);
    }

    private function useFixtureFiles(TableNode $table)
    {
        $files = [];

        foreach ($table->getRows() as $file) {
            $files[] = $this->getFilepath(current($file));
        }

        $this->loadFiles($files);
    }

    /**
     * @Given I use fixture file :filename
     */
    public function iUserFixtureFile(string $filename)
    {
        $this->loadFiles([$this->getFilepath($filename)]);
    }

    /**
     * @param array $files files
     *
     * @return void
     */
    private function loadFiles(array $files): void
    {
        $this->loader->load($files, [], [], $this->purgeMode);

        $this->entityManager->flush();
        $this->entityManager->clear();
    }

    /**
     * @param string $filename filename
     *
     * @return string
     */
    private function getFilepath(string $filename): string
    {
        if (null === $this->basepath) {
            $this->basepath = 'tests/fixtures';
        }

        return $this->projectDir . '/' . $this->basepath . '/' . $filename;
    }
}
