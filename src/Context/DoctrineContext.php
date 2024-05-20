<?php

namespace YoRus\BehatContext\Context;

use Behat\Behat\Context\Context;
use Behat\Gherkin\Node\TableNode;
use Doctrine\DBAL\Exception;
use Doctrine\ORM\EntityManagerInterface;
use ReflectionException;

/**
 * DoctrineContext.
 *
 * @uses \Context
 */
class DoctrineContext implements Context
{
    /** @var string[] $entityMapping */
    private array $entityMapping;

    /**
     * @var string
     */
    protected string $lastSqlRequest;

    /**
     * @var mixed[]
     */
    protected array $dataSet;

    private EntityManagerInterface $entityManager;

    /**
     * @param string[] $mapping
     */
    public function __construct(array $mapping, EntityManagerInterface $entityManager)
    {
        $this->entityMapping = $mapping;
        $this->entityManager = $entityManager;
    }

    /**
     * @param string $entity
     * @param string $identifier
     *
     * @throws \Exception
     *
     * @Given I should have a :entity entity with identifier :identifier
     * @Given I should have an entity :entity with identifier :identifier
     * @Given I have an entity :entity with identifier :identifier
     */
    public function iShouldHaveAnEntityWithIdentifier(string $entity, string $identifier): void
    {
        $entity = $this->findEntityWithIdentifier($entity, $identifier);

        if (null === $entity) {
            throw new \Exception('Entity not found');
        }
    }


    /**
     * @param string $entity
     * @param string $identifier
     *
     * @throws \Exception
     *
     * @Then I should not have a :entity entity with identifier :identifier
     * @Then I should not have an entity :entity with identifier :identifier
     * @Then I have not an entity :entity with identifier :identifier
     */
    public function iShouldNotHaveAnEntityWithIdentifier(string $entity, string $identifier): void
    {
        $entity = $this->findEntityWithIdentifier($entity, $identifier);

        if (null !== $entity) {
            throw new \Exception('Entity found');
        }
    }

    /**
     * @param string $query
     *
     * @Then I execute SQL query:
     * @Then execute the query:
     *
     * @throws Exception
     */
    public function executeSqlQuery(string $query): void
    {
        $this->executeSql($query);
    }

    /**
     * @param TableNode $expectedData
     *
     * @throws \Exception
     *
     * @Then the result set must be:
     */
    public function theResultSetMustBe(TableNode $expectedData): void
    {
        if (count($expectedData->getHash()) < count($this->dataSet)) {
            throw new \Exception(
                sprintf(
                    'The obtained number of rows is lower than expected [expected: "%s", dataSet: "%s"]',
                    count($expectedData->getHash()),
                    count($this->dataSet)
                )
            );
        }

        if (count($expectedData->getHash()) > count($this->dataSet)) {
            throw new \Exception(
                sprintf(
                    'The obtained number of rows is greater than expected [expected: "%s", dataSet: "%s"]',
                    count($expectedData->getHash()),
                    count($this->dataSet)
                )
            );
        }

        foreach ($expectedData->getHash() as $rowNum => $expected) {
            foreach ($expected as $expectedColumnName => $expectedColumnValue) {
                $columnValue = $this->dataSet[$rowNum][strtolower($expectedColumnName)];

                if ($expectedColumnValue !== $columnValue) {
                    throw new \Exception(
                        sprintf(
                            'Invalid result for query "%s", expected "%s" and found "%s"',
                            $this->lastSqlRequest,
                            $expectedColumnValue,
                            $columnValue
                        )
                    );
                }
            }
        }
    }

    /**
     * @param string $table
     * @param int    $expected
     *
     * @throws Exception
     * @Then the rows number of :table table should be equals to :expected
     * @Then the rows number of table :table should be :expected
     */
    public function theRowsNumberOfShouldBeEqualsTo(string $table, int $expected): void
    {
        $this->executeSql(sprintf('SELECT COUNT(*) FROM %s WHERE TRUE', $table));
        if ($expected !== $this->dataSet[0]['count']) {
            throw new \Exception(
                sprintf(
                    'Invalid rows number in table "%s", expected "%s" and found "%s"',
                    $table,
                    $expected,
                    $this->dataSet[0]['count']
                )
            );
        }
    }

    /**
     * @Given the entity :entity with id :id has an entity attribute :attribute with value :attributeId
     * @throws \Exception|ReflectionException
     */
    public function entityWithIdHasAnAttributeWithValue(
        string $entity,
        string $id,
        string $attribute,
        string $attributeId
    ): void {
        $entityObject = $this->findEntityWithIdentifier($entity, $id);

        if (null === $entityObject) {
            throw new \Exception('Entity not found');
        }

        $attributeObject = $this->findEntityWithIdentifier($attribute, $attributeId);

        if (null === $attributeObject) {
            throw new \Exception('Entity not found');
        }

        $reflexion = new \ReflectionClass($entityObject);
        $property = $reflexion->getProperty($attribute);
        $property->setValue($entityObject, $attributeObject);

        $this->entityManager->persist($entityObject);
        $this->entityManager->flush();
    }

    /**
     * @param string $entity
     * @param string $identifier
     *
     * @return object|null
     *
     * @throws \Exception
     */
    private function findEntityWithIdentifier(string $entity, string $identifier): ?object
    {
//        $this->entityManager->clear();

        if (false === array_key_exists($entity, $this->entityMapping)) {
            throw new \Exception(sprintf('Mapping for entity “%s“ is not defined.', $entity));
        }

        return $this->entityManager->getRepository($this->entityMapping[$entity])->find($identifier);
    }

    /**
     * @param string $sql
     *
     * @throws Exception
     */
    private function executeSql(string $sql): void
    {
        $this->lastSqlRequest = $sql; // Log for debug
        $this->dataSet = $this->entityManager->getConnection()->query($sql)->fetchAll();
    }
}
