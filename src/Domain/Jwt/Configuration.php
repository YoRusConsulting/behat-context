<?php

namespace YoRus\BehatContext\Domain\Jwt;

/**
 * Class Configuration
 */
class Configuration
{
    /** @var array */
    private $configuration;

    /**
     * @param array $configuration
     */
    public function __construct(array $configuration = [])
    {
        $this->configuration = $configuration;
    }

    /**
     * @return array
     */
   public function getConfiguration(): array
   {
       return $this->configuration;
   }

    /**
     * @param string $key
     *
     * @return array|null
     */
   public function getConfigValue(string $key): ?array
   {
       if (!isset($this->configuration[$key])) {
           return null;
       }

       return $this->configuration[$key];
   }

    /**
     * @param string $key
     *
     * @return string
     * @throws \Exception
     */
   public function getResource(string $key): string
   {
       $configuration = $this->getConfigValue($key);

       $this->checkConfiguration($configuration, $key, 'resource');

       return $configuration['resource'];
   }

    /**
     * @param string $key
     *
     * @return string
     * @throws \Exception
     */
   public function getUsername(string $key): string
   {
       $configuration = $this->getConfigValue($key);

       $this->checkConfiguration($configuration, $key, 'username');

       return $configuration['username'];
   }

    /**
     * @param string $key
     *
     * @return string
     * @throws \Exception
     */
   public function getPassword(string $key): string
   {
       $configuration = $this->getConfigValue($key);

       $this->checkConfiguration($configuration, $key, 'password');

       return $configuration['password'];
   }

   /**
    * @param string $key
    *
    * @return string
    * @throws \Exception
    */
   public function getJwtLoginResourceBody(string $key): string
   {
       $body = [
           'username' => $this->getUsername($key),
           'password' => $this->getPassword($key)
       ];

       return json_encode($body);
   }

    /**
     * @param array  $configuration
     * @param string $key
     * @param string $resource
     *
     * @return void
     * @throws \Exception
     */
   private function checkConfiguration(array $configuration, string $key, string $resource)
   {
       if (null === $configuration) {
           throw new \Exception(sprintf('Resource %s not found in JWT configuration', $key));
       }

       if (!isset($configuration[$resource])) {
           throw new \Exception(sprintf('Resource `%s` not found in %s configuration', $resource, $key));
       }
   }
}
