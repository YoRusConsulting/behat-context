<?php

namespace YoRus\BehatContext\App\Resolver;

use YoRus\BehatContext\Domain\Jwt\Configuration;
use Behat\Behat\Context\Argument\ArgumentResolver;

/**
 * Class JwtConfigurationResolver
 */
class JwtConfigurationResolver implements ArgumentResolver
{
    private Configuration $jwtConfiguration;

    /**
     * @param Configuration $jwtConfiguration
     */
    public function __construct(Configuration $jwtConfiguration)
    {
        $this->jwtConfiguration = $jwtConfiguration;
    }

    /**
     * @inheritDoc
     */
    public function resolveArguments(\ReflectionClass $classReflection, array $arguments)
    {
        $constructor = $classReflection->getConstructor();
        if ($constructor === null) {
            return $arguments;
        }

        $parameters = $constructor->getParameters();
        foreach ($parameters as $parameter) {
            if (null !== $parameter->getType() && ($parameter->getType()->getName()) === 'YoRus\BehatContext\Domain\Jwt\Configuration') {
                $arguments[$parameter->name] = $this->jwtConfiguration;
            }
        }

        return $arguments;
    }
}
