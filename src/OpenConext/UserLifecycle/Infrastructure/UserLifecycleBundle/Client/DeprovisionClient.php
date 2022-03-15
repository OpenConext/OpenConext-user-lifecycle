<?php

/**
 * Copyright 2018 SURFnet B.V.
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *     http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */

namespace OpenConext\UserLifecycle\Infrastructure\UserLifecycleBundle\Client;

use Exception;
use GuzzleHttp\ClientInterface;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\Promise\PromiseInterface;
use GuzzleHttp\Promise\RejectedPromise;
use GuzzleHttp\Psr7\Response;
use InvalidArgumentException as CoreInvalidArgumentException;
use OpenConext\UserLifecycle\Domain\Client\DeprovisionClientInterface;
use OpenConext\UserLifecycle\Domain\Client\InformationResponseFactoryInterface;
use OpenConext\UserLifecycle\Domain\Client\InformationResponseInterface;
use OpenConext\UserLifecycle\Domain\Exception\DeprovisionClientUnavailableException;
use OpenConext\UserLifecycle\Domain\ValueObject\CollabPersonId;
use OpenConext\UserLifecycle\Infrastructure\UserLifecycleBundle\Exception\InvalidArgumentException;
use OpenConext\UserLifecycle\Infrastructure\UserLifecycleBundle\Exception\InvalidResponseException;
use OpenConext\UserLifecycle\Infrastructure\UserLifecycleBundle\Exception\MalformedResponseException;
use OpenConext\UserLifecycle\Infrastructure\UserLifecycleBundle\Exception\ResourceNotFoundException;
use OpenConext\UserLifecycle\Infrastructure\UserLifecycleBundle\Exception\RuntimeException;
use Webmozart\Assert\Assert;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class DeprovisionClient implements DeprovisionClientInterface
{
    const DEPROVISION_ENDPOINT = 'deprovision/%s';
    const DRYRUN_ENDPOINT = 'deprovision/%s/dry-run';

    /**
     * @var ClientInterface
     */
    private $httpClient;

    /**
     * @var InformationResponseFactoryInterface
     */
    private $informationResponseFactory;

    /**
     * @var string
     */
    private $name;

    /**
     * @param ClientInterface $httpClient
     * @param InformationResponseFactoryInterface $factory
     * @param string $name
     */
    public function __construct(ClientInterface $httpClient, InformationResponseFactoryInterface $factory, $name)
    {
        Assert::string($name);
        $this->name = $name;

        $this->httpClient = $httpClient;
        $this->informationResponseFactory = $factory;
    }

    public function deprovision(CollabPersonId $user, bool $dryRun = false): PromiseInterface
    {
        if ($dryRun) {
            return $this->delete(self::DRYRUN_ENDPOINT, [$user->getCollabPersonId()]);
        }

        return $this->delete(self::DEPROVISION_ENDPOINT, [$user->getCollabPersonId()]);
    }

    public function information(CollabPersonId $user): PromiseInterface
    {
        return $this->read(self::DEPROVISION_ENDPOINT, [$user->getCollabPersonId()]);
    }

    public function getName(): string
    {
        return $this->name;
    }

    /**
     * Async read a deprovision API.
     *
     * A promise is returned which resolves to an InformationResponseInterface instance.
     *
     * @param string $path A URL path, optionally containing printf parameters. The parameters
     *               will be URL encoded and formatted into the path string.
     *               Example: "information/%s"
     * @param array $parameters
     *
     * @return PromiseInterface
     */
    private function read($path, array $parameters = [])
    {
        $resource = $this->buildResourcePath($path, $parameters);

        $promise = $this->httpClient->requestAsync('GET', $resource, ['exceptions' => false]);
        return $promise->then(
            function (Response $response) use ($resource) {
                try {
                    return $this->handleResponse($response, $resource);
                } catch (Exception $exception) {
                    return new RejectedPromise($exception);
                }
            }
        )->otherwise(
            function (Exception $exception) {
                return $this->informationResponseFactory->fromException($exception, $this->getName());
            }
        );
    }

    /**
     * Async delete on a deprovision API.
     *
     * A promise is returned which resolves to an InformationResponseInterface instance.
     *
     * @param string $path
     * @param array $parameters
     *
     * @return PromiseInterface
     */
    private function delete($path, array $parameters = [])
    {
        $resource = $this->buildResourcePath($path, $parameters);

        $promise = $this->httpClient->requestAsync('DELETE', $resource, ['exceptions' => false]);

        return $promise->then(
            function (Response $response) use ($resource) {
                try {
                    return $this->handleResponse($response, $resource);
                } catch (Exception $exception) {
                    return new RejectedPromise($exception);
                }
            }
        )->otherwise(
            function (Exception $exception) {
                return $this->informationResponseFactory->fromException($exception, $this->getName());
            }
        );
    }

    /**
     * @param string $path
     * @param array $parameters
     * @return string
     * @throws RuntimeException
     */
    private function buildResourcePath($path, array $parameters)
    {
        $resource = $path;
        if (count($parameters) > 0) {
            $resource = vsprintf($path, array_map('urlencode', $parameters));
        }

        if (empty($resource)) {
            throw new RuntimeException(
                sprintf(
                    'Could not construct resource path from path "%s", parameters "%s"',
                    $path,
                    implode('","', $parameters)
                )
            );
        }

        return $resource;
    }

    /**
     * @param Response $response
     * @param string $resource
     * @return InformationResponseInterface
     */
    private function handleResponse(Response $response, $resource)
    {
        $statusCode = $response->getStatusCode();

        if ($statusCode === 404) {
            throw new ResourceNotFoundException(sprintf('Resource "%s" not found', $resource));
        }

        if ($statusCode !== 200) {
            throw new InvalidResponseException(
                sprintf(
                    'Request to resource "%s" returned an invalid response with status code %s',
                    $resource,
                    $statusCode
                )
            );
        }

        try {
            $data = $this->parseJson((string)$response->getBody());
        } catch (InvalidArgumentException $e) {
            throw new MalformedResponseException(
                sprintf('Cannot read resource "%s": malformed JSON returned. %s', $resource, $e->getMessage())
            );
        }

        return $data;
    }

    /**
     * Function to provide functionality common to Guzzle 5 Response's json method,
     * without config options as they are not needed.
     *
     * @param string $json
     * @return InformationResponseInterface
     * @throws InvalidArgumentException
     */
    private function parseJson($json)
    {
        static $jsonErrors = [
            JSON_ERROR_DEPTH => 'JSON_ERROR_DEPTH - Maximum stack depth exceeded',
            JSON_ERROR_STATE_MISMATCH => 'JSON_ERROR_STATE_MISMATCH - Underflow or the modes mismatch',
            JSON_ERROR_CTRL_CHAR => 'JSON_ERROR_CTRL_CHAR - Unexpected control character found',
            JSON_ERROR_SYNTAX => 'JSON_ERROR_SYNTAX - Syntax error, malformed JSON',
            JSON_ERROR_UTF8 => 'JSON_ERROR_UTF8 - Malformed UTF-8 characters, possibly incorrectly encoded',
        ];

        $data = json_decode($json, true);

        if (JSON_ERROR_NONE !== json_last_error()) {
            $last = json_last_error();

            $errorMessage = $jsonErrors[$last];

            if (!isset($errorMessage)) {
                $errorMessage = 'Unknown error';
            }

            throw new InvalidArgumentException(sprintf('Unable to parse JSON data: %s', $errorMessage));
        }

        try {
            $response = $this->informationResponseFactory->fromApiResponse($data);
        } catch (CoreInvalidArgumentException $e) {
            throw new InvalidArgumentException(
                sprintf(
                    'Unable to parse the JSON response into an InformationResponse object: %s',
                    $e->getMessage()
                )
            );
        }

        return $response;
    }

    public function health(): void
    {
        try {
            $response = $this->httpClient->request('GET', '/health', ['timeout' => 5]);
            if ($response->getStatusCode() !== 200) {
                throw new DeprovisionClientUnavailableException($this->getName());
            }
        } catch (RequestException $e) {
            throw new DeprovisionClientUnavailableException($this->getName());
        }
    }
}
