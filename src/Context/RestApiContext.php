<?php

namespace YoRus\BehatContext\Context;

use Exception;
use YoRus\BehatContext\Domain\Jwt\Configuration;
use YoRus\BehatContext\Domain\BehatStore;
use Behat\Behat\Context\Context;
use Ubirak\RestApiBehatExtension\Rest\RestApiBrowser;
use Ubirak\RestApiBehatExtension\Json\JsonInspector;

/**
 * RestApiContext
 *
 * @uses \Context
 */
class RestApiContext implements Context
{
    /** @var RestApiBrowser $restApiBrowser */
    private RestApiBrowser $restApiBrowser;

    /**
     * @var JsonInspector $jsonContext
     */
    private JsonInspector $jsonContext;

    private BehatStore $store;

    private Configuration $jwtConfiguration;

    /**
     * @param RestApiBrowser $restApiBrowser
     * @param JsonInspector  $jsonContext
     * @param BehatStore     $store
     * @param Configuration  $jwtConfiguration
     */
    public function __construct(
        RestApiBrowser $restApiBrowser,
        JsonInspector $jsonContext,
        BehatStore $store,
        Configuration $jwtConfiguration
    ) {
        $this->restApiBrowser = $restApiBrowser;
        $this->jsonContext = $jsonContext;
        $this->store = $store;
        $this->jwtConfiguration = $jwtConfiguration;
    }

    /**
     * @param string $api
     *
     * @throws Exception
     */
    private function iGetAJwtTokenFromWithBody(string $api): void
    {
        $resource = $this->jwtConfiguration->getResource($api);
        $body = $this->jwtConfiguration->getJwtLoginResourceBody($api);

        $this->restApiBrowser->sendRequest("POST", $resource, $body);

        // Check status code is 200
        $statusCode = $this->restApiBrowser->getResponse()->getStatusCode();
        if ($statusCode !== 200) {
            throw new Exception(
                sprintf('Response header value is equals to 200, but value is equals to %s', $statusCode)
            );
        }

        // Retrieve token
        $token = $this->jsonContext->readJsonNodeValue("token");
        $this->store->$api = new \stdClass();
        $this->store->$api->token = $token;
    }

    /**
     * @param string $api
     *
     * @throws Exception
     * @Given I am authenticated on :api
     */
    public function iAmAuthenticatedOnWith(string $api): void
    {
        if (null === $this->store->$api || null === $this->store->$api->token) {
            $this->iGetAJwtTokenFromWithBody($api);
        }

        // Send token to request header of restApiBrowser
        $this->restApiBrowser->addRequestHeader("Authorization", "Bearer " . $this->store->$api->token);
    }

    /**
     * @param string $header
     * @param string $value
     *
     * @throws Exception
     *
     * @Then the response header :header should be equal to :value
     */
    public function theResponseHeaderShouldBeEqualTo(string $header, string $value): void
    {
        $response = $this->restApiBrowser->getResponse();
        $headerInResponse = implode(',', $response->getHeader($header));

        if ($headerInResponse !== $value) {
            throw new Exception(sprintf('Response header value is equals to %s', $headerInResponse));
        }
    }

    /**
     * @param string $header
     *
     * @throws Exception
     *
     * @Then the response header :header should exist
     */
    public function theResponseHeaderShouldExist(string $header): void
    {
        $response = $this->restApiBrowser->getResponse();
        $headerInResponse = $response->getHeader($header);

        if (empty($headerInResponse)) {
            throw new Exception(sprintf('Response header value does not contain `%s` key', $header));
        }
    }

    /**
     * @param string $method
     * @param string $url
     * @param string $header
     *
     * @throws Exception
     *
     * @Then I send a :method request to :url with :header header value retrieved from resource just created before
     */
    public function iSendARequestToWithHeaderValueRetrievedFromResourceJustCreatedBefore(
        string $method,
        string $url,
        string $header
    ): void {
        $this->theResponseHeaderShouldExist($header);

        $response = $this->restApiBrowser->getResponse();
        $headerInResponse = implode(',', $response->getHeader($header));

        $replacedUrl = sprintf($url, $headerInResponse);
        $this->restApiBrowser->sendRequest($method, $replacedUrl);
    }

    /**
     * Checks, whether the response content is null or empty string
     *
     * @throws Exception
     *
     * @Then the response should be empty
     */
    public function theResponseShouldBeEmpty()
    {
        $actual = $this->restApiBrowser->getResponse()->getBody()->getContents();
        $message = "The response of the current page is not empty, it is: $actual";

        if (null !== $actual && "" !== $actual) {
            throw new Exception($message);
        }
    }
}
