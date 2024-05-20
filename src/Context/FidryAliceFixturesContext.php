<?php

namespace YoRus\BehatContext;

use Behat\Behat\Context\Context;
use Behat\Gherkin\Node\TableNode;
use Doctrine\ORM\EntityManagerInterface;
use Fidry\AliceDataFixtures\Persistence\PurgeMode;
use Symfony\Component\DependencyInjection\ContainerAwareTrait;
/**
 * FidryAliceFixturesContext
 *
 * @uses Context
 */
class FidryAliceFixturesContext implements Context
{
    use ContainerAwareTrait;

    /**
     * @var string
     */
    private $basepath;

    private ?PurgeMode $purgeMode = null;

    /**
     * @param string $basepath basepath
     */
    public function __construct(string $basepath = null)
    {
        $this->basepath = $basepath;
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
        $em     = $this->getDoctrine();
        $loader = $this->container->get('fidry_alice_data_fixtures.loader.doctrine');
        $loader->load($files, [], [], $this->purgeMode);

        $em->flush();
        $em->clear();
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

        return $this->container->getParameter('kernel.project_dir').'/'.$this->basepath.'/'.$filename;
    }

    /**
     * @return EntityManagerInterface
     */
    private function getDoctrine()
    {
        return $this->container->get('doctrine.orm.entity_manager');
    }
}
