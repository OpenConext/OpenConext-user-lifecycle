<?php

declare(strict_types = 1);

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

namespace OpenConext\UserLifecycle\Tests\Unit\Domain\ValueObject;

use InvalidArgumentException;
use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;
use OpenConext\UserLifecycle\Application\Client\InformationResponseFactory;
use OpenConext\UserLifecycle\Domain\Client\InformationResponse;
use OpenConext\UserLifecycle\Domain\ValueObject\Client\ResponseStatus;
use PHPUnit\Framework\TestCase;
use stdClass;

class InformationResponseFactoryTest extends TestCase
{
    use MockeryPHPUnitIntegration;

    private $informationResponseFactory;

    protected function setUp(): void
    {
        $this->informationResponseFactory = new InformationResponseFactory();
    }

    public function test_build_from_ok_api_response(): void
    {
        $data = [['name' => 'fieldname', 'value' => 'fieldvalue']];
        $response = $this->informationResponseFactory->fromApiResponse(
            $this->buildResponse(
                'my-service-name',
                'OK',
                $data,
                '',
            ),
        );
        $this->assertInstanceOf(InformationResponse::class, $response);
        $this->assertEquals('my-service-name', $response->getName());
        $this->assertEquals(ResponseStatus::STATUS_OK, (string) $response->getStatus());
        $this->assertEquals($data, $response->getData()->getData());
        $this->assertFalse($response->getErrorMessage()->hasErrorMessage());
    }

    public function test_build_from_failed_api_response(): void
    {
        $response = $this->informationResponseFactory->fromApiResponse(
            $this->buildResponse(
                'my-service-name',
                'FAILED',
                [],
                'my message',
            ),
        );
        $this->assertInstanceOf(InformationResponse::class, $response);
        $this->assertEquals('my-service-name', (string) $response->getName());
        $this->assertEquals(ResponseStatus::STATUS_FAILED, (string) $response->getStatus());
        $this->assertEmpty($response->getData()->getData());
        $this->assertEquals('my message', (string) $response->getErrorMessage());
    }

    public function test_build_from_failed_api_response_nested_data(): void
    {
        $data = [[
            'name' => 'my_field_value',
            'value' => [
                ['name' => 'nestedName', 'value' => 'nested value'],
                ['name' => 'nestedName', 'value' => 'nested value'],
            ]
        ]];

        $response = $this->informationResponseFactory->fromApiResponse(
            $this->buildResponse(
                'my-service-name',
                'OK',
                $data,
                null,
            ),
        );
        $this->assertInstanceOf(InformationResponse::class, $response);
        $this->assertEquals('my-service-name', (string) $response->getName());
        $this->assertEquals(ResponseStatus::STATUS_OK, (string) $response->getStatus());
        $this->assertEquals($data, $response->getData()->getData());
        $this->assertFalse($response->getErrorMessage()->hasErrorMessage());
    }

    public function test_multible_error_messages_are_allowed(): void
    {
        $data = [['name' => 'fieldname', 'value' => 'fieldvalue']];
        $response = $this->informationResponseFactory->fromApiResponse(
            $this->buildResponse(
                'my-service-name',
                'FAILED',
                $data,
                ['message 1', 'message 2'],
            ),
        );
        $this->assertEquals(ResponseStatus::STATUS_FAILED, (string) $response->getStatus());
        $this->assertEquals('message 1, message 2', (string) $response->getErrorMessage());

        $response = $this->informationResponseFactory->fromApiResponse(
            $this->buildResponse(
                'my-service-name',
                'FAILED',
                $data,
                ['singular message wrapped in array'],
            ),
        );
        $this->assertEquals(ResponseStatus::STATUS_FAILED, (string) $response->getStatus());
        $this->assertEquals('singular message wrapped in array', (string) $response->getErrorMessage());
    }

    /**
     * @dataProvider buildInvalidResponses
     */
    public function test_fails_on_invalid_response(
        $invalidResponse,
    ): void {
        $this->expectException(InvalidArgumentException::class);
        $this->informationResponseFactory->fromApiResponse($invalidResponse);
    }

    public function buildInvalidResponses()
    {
        return [
            // invalid names
            [
                $this->buildResponse(
                    '',
                    'OK',
                    [['name' => 'test', 'value' => 'foobar'], ['name' => 'test2', 'value' => 'foobar2']],
                    null,
                )
            ],

            // invalid status
            [$this->buildResponse('my-service', 'ALLRIGHT', [], null)],

            // invalid data
            [$this->buildResponse('my-service', 'OK', null, null)],
            [$this->buildResponse('my-service', 'OK', false, null)],
            [$this->buildResponse('my-service', 'OK', '', null)],
            [$this->buildResponse('my-service', 'OK', [['nam' => 'test', 'value' => 'foobar']], null)],
            [
                $this->buildResponse(
                    'my-service',
                    'OK',
                    ['name' => 'ffoop', ['name' => 'test', 'value' => 'foobar']],
                    null,
                )
            ],
            // invalid message
            [$this->buildResponse('my-service', 'FAILED', [], '')],
            [$this->buildResponse('my-service', 'FAILED', [], false)],
            [$this->buildResponse('my-service', 'FAILED', [], [])],
            [$this->buildResponse('my-service', 'OK', [], 'Something is wrong here!')],
            [$this->buildResponse('my-service', 'FAILED', [], [null, 'this is OK'])],
            [$this->buildResponse('my-service', 'FAILED', [], [['this is not OK'], 'this would be fine'])],
            [$this->buildResponse('my-service', 'FAILED', [], [new stdClass(), 'this would be fine'])],
            [$this->buildResponse('my-service', 'FAILED', [], [1337, 'this would be fine'])],
        ];
    }

    private function buildResponse(
        $name,
        $status,
        $data,
        $message,
    ) {
        return [
            'name' => $name,
            'status' => $status,
            'data' => $data,
            'message' => $message,
        ];
    }
}
