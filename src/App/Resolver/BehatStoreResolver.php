<?php

namespace YoRus\BehatContext\App\Resolver;

use YoRus\BehatContext\Domain\BehatStore;
use Behat\Behat\Context\Argument\ArgumentResolver;

/**
 * Class BehatStoreResolver
 */
class BehatStoreResolver implements ArgumentResolver
{
    private BehatStore $behatStore;

    /**
     * @param BehatStore $store
     */
    public function __construct(BehatStore $store)
    {
        $this->behatStore = $store;
    }

    /**
     * @inheritdoc
     */
    public function resolveArguments(\ReflectionClass $classReflection, array $arguments)
    {
        $constructor = $classReflection->getConstructor();
        if ($constructor === null) {
            return $arguments;
        }

        $parameters = $constructor->getParameters();
        foreach ($parameters as $parameter) {
            if (
                null !== $parameter->getType()
                && ($parameter->getType()->getName()) === 'YoRus\BehatContext\Domain\BehatStore'
            ) {
                $arguments[$parameter->name] = $this->behatStore;
            }
        }

        return $arguments;
    }
}
