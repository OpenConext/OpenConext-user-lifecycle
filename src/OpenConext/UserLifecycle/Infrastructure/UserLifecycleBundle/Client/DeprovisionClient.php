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

use GuzzleHttp\ClientInterface;
use GuzzleHttp\Exception\GuzzleException;
use InvalidArgumentException as CoreInvalidArgumentException;
use OpenConext\UserLifecycle\Domain\Client\DeprovisionClientInterface;
use OpenConext\UserLifecycle\Domain\Client\InformationResponse;
use OpenConext\UserLifecycle\Domain\Client\InformationResponseInterface;
use OpenConext\UserLifecycle\Domain\ValueObject\CollabPersonId;
use OpenConext\UserLifecycle\Infrastructure\UserLifecycleBundle\Exception\InvalidArgumentException;
use OpenConext\UserLifecycle\Infrastructure\UserLifecycleBundle\Exception\InvalidResponseException;
use OpenConext\UserLifecycle\Infrastructure\UserLifecycleBundle\Exception\MalformedResponseException;
use OpenConext\UserLifecycle\Infrastructure\UserLifecycleBundle\Exception\ResourceNotFoundException;
use OpenConext\UserLifecycle\Infrastructure\UserLifecycleBundle\Exception\RuntimeException;
use Webmozart\Assert\Assert;

class DeprovisionClient implements DeprovisionClientInterface
{
    const DEPROVISION_ENDPOINT = '/deprovision/%s';

    /**
     * @var ClientInterface
     */
    private $httpClient;

    /**
     * @var string
     */
    private $name;

    /**
     * @param ClientInterface $httpClient
     * @param string $name
     */
    public function __construct(ClientInterface $httpClient, $name)
    {
        Assert::string($name);
        $this->name = $name;

        $this->httpClient = $httpClient;
    }

    public function deprovision(CollabPersonId $user, $dryRun = false)
    {
    }

    /**
     * @param CollabPersonId $user
     *
     * @return InformationResponseInterface
     *
     * @throws GuzzleException
     * @throws InvalidArgumentException
     * @throws RuntimeException
     */
    public function information(CollabPersonId $user)
    {
        return $this->read(self::DEPROVISION_ENDPOINT, [$user->getCollabPersonId()]);
    }

    public function getName()
    {
        return $this->name;
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
     * @param string $path A URL path, optionally containing printf parameters. The parameters
     *               will be URL encoded and formatted into the path string.
     *               Example: "information/%s"
     * @param array $parameters
     * @return InformationResponseInterface $data
     * @throws InvalidResponseException
     * @throws MalformedResponseException
     * @throws ResourceNotFoundException
     * @throws GuzzleException
     */
    private function read($path, array $parameters = [])
    {
        $resource = $this->buildResourcePath($path, $parameters);

        $response = $this->httpClient->request('GET', $resource, ['exceptions' => false]);

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
            $response = InformationResponse::fromApiResponse($data);
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
}
