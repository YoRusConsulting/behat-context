<?php

namespace YoRus\BehatContext;

use Behat\Behat\Context\Context;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Tools\SchemaTool;

/**
 * DoctrineORMSchemaReloadContext
 *
 * @uses Context
 */
class DoctrineORMSchemaReloadContext implements Context
{
    private static $truncateSql;
    private EntityManagerInterface $entityManager;
    private bool $execMigration;

    private static bool $staticExecMigration = false;

    public function __construct(EntityManagerInterface $entityManager, bool $execMigration = false)
    {
        $this->entityManager = $entityManager;
        $this->execMigration = $execMigration;

        self::$staticExecMigration = $this->execMigration;
    }

    /**
     * @BeforeScenario @database
     *
     * @return void
     */
    public function beforeScenario()
    {
        $this->buildSchema();
    }

    /**
     * @BeforeScenario @dbtruncate
     */
    public function truncateData()
    {
        if (null === static::$truncateSql) {
            static::$truncateSql = $this->generateTruncateSql();
        }

        $this->entityManager->getConnection()->executeUpdate(static::$truncateSql);
    }

    /**
     * @BeforeSuite
     * @return void
     */
    public static function beforeSuite() {
        if (self::$staticExecMigration === true) {
            exec('php bin/console d:d:d --force');
            exec('php bin/console d:d:c');
            exec('php bin/console d:s:c');
            exec('php bin/console d:m:m --no-interaction');
        }
    }

    /**
     * @AfterScenario @database
     *
     * @return void
     */
    public function afterScenario()
    {
        $this->entityManager->clear();
    }

    /**
     * @return void
     */
    protected function buildSchema()
    {
        $metadata = $this->getMetadata();
        if (!empty($metadata)) {
            $tool = new SchemaTool($this->entityManager);
            $tool->dropSchema($metadata);
            $tool->createSchema($metadata);
        }

    }
    /**
     * @return array
     */
    protected function getMetadata()
    {
        return $this->entityManager->getMetadataFactory()->getAllMetadata();
    }

    protected function generateTruncateSql(): string
    {
//        $query = "SELECT table_name FROM information_schema.tables WHERE table_schema='public' and table_name!='migration_versions'";
        $query = "SELECT table_name FROM information_schema.tables WHERE table_schema='public'";
        $tables = array_column(
            $this->entityManager->getConnection()->fetchAllAssociative($query),
            'table_name'
        );
        $platform = $this->entityManager->getConnection()->getDatabasePlatform();

        $sqlDisable = $sqlTruncates = $sqlEnable = [];

        foreach ($tables as $tbl) {
            $sqlDisable[] = 'ALTER TABLE "'.$tbl.'" DISABLE TRIGGER ALL;';
            $sqlTruncates[] = '"'.$tbl.'"';
            $sqlEnable [] = 'ALTER TABLE "'.$tbl.'" ENABLE TRIGGER ALL;';
        }

        $sqlTruncates = sprintf('truncate table %s;', implode(',', $sqlTruncates));

        return implode(chr(10), $sqlDisable).chr(10).
            $sqlTruncates.chr(10).
            implode(chr(10), $sqlEnable);
    }
}
