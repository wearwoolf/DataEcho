<?php

declare(strict_types=1);

namespace DataEcho\Tests;

use DataEcho\DataEcho;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use PDO;

class DataTest extends TestCase
{
    /**
     * @var DataEcho
     */
    protected $object;

    /**
     * @var PDO|MockObject
     */
    protected $db;

    public function setUp() : void
    {
        $this->db = $this->createMock(PDO::class);
        $this->object = new DataEcho($this->db);
    }

    /**
     * @dataProvider prepareProvider
     */
    public function testPrepare(array $expected, array $data) : void
    {
        $this->db->method('query')->willReturn($data['db']);
        $this->assertEquals($expected, $this->object->prepare($data['request'])->getResult());
    }

    public function prepareProvider() : array
    {
        return [
            'test1' => [
                'expected' => [
                    'delete' => [],
                    'update' => [],
                    'new' => [],
                ],
                'data' => [
                    'request' => [],
                    'db' => [],
                ],
            ],
            'test2' => [
                'expected' => [
                    'delete' => [0, 1],
                    'update' => [],
                    'new' => [],
                ],
                'data' => [
                    'request' => [
                        'ident' => [0, 1],
                        'value' => [0, 1],
                        'version' => [0, 1],
                    ],
                    'db' => [],
                ],
            ],
            'test3' => [
                'expected' => [
                    'delete' => [0, 1],
                    'update' => [],
                    'new' => [],
                ],
                'data' => [
                    'request' => [
                        'ident' => [0, 1],
                    ],
                    'db' => [],
                ],
            ],
            'test4' => [
                'expected' => [
                    'delete' => [],
                    'update' => [],
                    'new' => [
                        0 => ['value' => 0, 'version' => 0],
                        1 => ['value' => 1, 'version' => 1],
                    ],
                ],
                'data' => [
                    'request' => [],
                    'db' => [
                        0 => ['ident' => 0, 'value' => 0, 'version' => 0],
                        1 => ['ident' => 1, 'value' => 1, 'version' => 1],
                    ],
                ],
            ],
            'test5' => [
                'expected' => [
                    'delete' => [],
                    'update' => [
                        'i1' => ['value' => 1, 'version' => 1],
                    ],
                    'new' => [],
                ],
                'data' => [
                    'request' => [
                        'ident' => ['i0', 'i1'],
                    ],
                    'db' => [
                        'i0' => ['ident' => 'i0', 'value' => 0, 'version' => 0],
                        'i1' => ['ident' => 'i1', 'value' => 1, 'version' => 1],
                    ],
                ],
            ],
            'test6' => [
                'expected' => [
                    'delete' => ['i2'],
                    'update' => [
                        'i1' => ['value' => 11, 'version' => 111],
                    ],
                    'new' => [
                        'i0' => ['value' => 10, 'version' => 100],
                    ],
                ],
                'data' => [
                    'request' => [
                        'ident' => ['i1', 'i2'],
                    ],
                    'db' => [
                        'i0' => ['ident' => 'i0', 'value' => 10, 'version' => 100],
                        'i1' => ['ident' => 'i1', 'value' => 11, 'version' => 111],
                    ],
                ],
            ],
        ];
    }
}
