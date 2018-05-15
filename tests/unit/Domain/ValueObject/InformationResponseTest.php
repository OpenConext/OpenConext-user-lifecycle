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

namespace OpenConext\UserLifecycle\Tests\Unit\Domain\ValueObject;

use InvalidArgumentException;
use OpenConext\UserLifecycle\Domain\ValueObject\InformationResponse;
use PHPUnit\Framework\TestCase;

class InformationResponseTest extends TestCase
{
    public function test_build_from_ok_api_response()
    {
        $data = [['name' => 'fieldname', 'value' => 'fieldvalue']];
        $response = InformationResponse::fromApiResponse(
            $this->buildResponse(
                'my-service-name',
                'OK',
                $data,
                ''
            )
        );
        $this->assertInstanceOf(InformationResponse::class, $response);
        $this->assertEquals('my-service-name', $response->getName());
        $this->assertEquals(InformationResponse::STATUS_OK, $response->getStatus());
        $this->assertEquals($data, $response->getData());
        $this->assertNull($response->getErrorMessage());
    }

    public function test_build_from_failed_api_response()
    {
        $response = InformationResponse::fromApiResponse(
            $this->buildResponse(
                'my-service-name',
                'FAILED',
                [],
                'my message'
            )
        );
        $this->assertInstanceOf(InformationResponse::class, $response);
        $this->assertEquals('my-service-name', $response->getName());
        $this->assertEquals(InformationResponse::STATUS_FAILED, $response->getStatus());
        $this->assertEmpty($response->getData());
        $this->assertEquals('my message', $response->getErrorMessage());
    }

    public function test_build_from_failed_api_response_nested_data()
    {
        $data = [[
            'name' => 'my_field_value',
            'value' => [
                ['name' => 'nestedName', 'value' => 'nested value'],
                ['name' => 'nestedName', 'value' => 'nested value'],
            ]
        ]];

        $response = InformationResponse::fromApiResponse(
            $this->buildResponse(
                'my-service-name',
                'OK',
                $data,
                null
            )
        );
        $this->assertInstanceOf(InformationResponse::class, $response);
        $this->assertEquals('my-service-name', $response->getName());
        $this->assertEquals(InformationResponse::STATUS_OK, $response->getStatus());
        $this->assertEquals($data, $response->getData());
        $this->assertNull($response->getErrorMessage());
    }

    /**
     * @dataProvider buildInvalidResponses
     */
    public function test_fails_on_invalid_response($invalidResponse)
    {
        $this->expectException(InvalidArgumentException::class);
        InformationResponse::fromApiResponse($invalidResponse);
    }

    public function buildInvalidResponses()
    {
        return [
            // invalid names
            [$this->buildResponse('', 'OK', [['name' => 'test', 'value' => 'foobar'], ['name' => 'test2', 'value' => 'foobar2']], null)],
            [$this->buildResponse(123, 'OK', [['name' => 'test', 'value' => 'foobar']], null)],
            [$this->buildResponse([], 'FAILED', [], 'foobar')],
            [$this->buildResponse(true, 'FAILED', [], 'foobar')],

            // invalid status
            [$this->buildResponse('my-service', 'ALLRIGHT', [], null)],
            [$this->buildResponse('my-service', 200, [], null)],
            [$this->buildResponse('my-service', true, [], null)],
            [$this->buildResponse('my-service', null, [], null)],

            // invalid data
            [$this->buildResponse('my-service', 'OK', null, null)],
            [$this->buildResponse('my-service', 'OK', false, null)],
            [$this->buildResponse('my-service', 'OK', '', null)],
            [$this->buildResponse('my-service', 'OK', [['nam' => 'test', 'value' => 'foobar']], null)],
            [$this->buildResponse('my-service', 'OK', ['name' => 'ffoop', ['name' => 'test', 'value' => 'foobar']], null)],

            // invalid message
            [$this->buildResponse('my-service', 'FAILED', [], '')],
            [$this->buildResponse('my-service', 'FAILED', [], false)],
            [$this->buildResponse('my-service', 'FAILED', [], [])],
            [$this->buildResponse('my-service', 'OK', [], 'Something is wrong here!')],

        ];
    }

    private function buildResponse($name, $status, $data, $message)
    {
        return [
            'name' => $name,
            'status' => $status,
            'data' => $data,
            'message' => $message,
        ];
    }
}
