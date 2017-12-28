<?php
namespace KafkaTest\Base;

use Kafka\Broker;
use Kafka\Socket;
use Kafka\SocketSync;
use PHPUnit\Framework\TestCase;

class BrokerTest extends TestCase
{
    public function tearDown(): void
    {
        Broker::getInstance()->clear();
    }

    public function testGroupBrokerId(): void
    {
        $broker = Broker::getInstance();
        $broker->setGroupBrokerId(1);
        $this->assertEquals($broker->getGroupBrokerId(), 1);
    }

    public function testData(): void
    {
        $broker = Broker::getInstance();
        $data   = [
            'brokers' => [
                [
                    'host' => '127.0.0.1',
                    'port' => '9092',
                    'nodeId' => '0',
                ],
                [
                    'host' => '127.0.0.1',
                    'port' => '9192',
                    'nodeId' => '1',
                ],
                [
                    'host' => '127.0.0.1',
                    'port' => '9292',
                    'nodeId' => '2',
                ],
            ],
            'topics' => [
                [
                    'topicName' => 'test',
                    'errorCode' => 0,
                    'partitions' => [
                        [
                            'partitionId' => 0,
                            'errorCode' => 0,
                            'leader' => 0,
                        ],
                        [
                            'partitionId' => 1,
                            'errorCode' => 0,
                            'leader' => 2,
                        ],
                    ],
                ],
                [
                    'topicName' => 'test1',
                    'errorCode' => 25,
                    'partitions' => [
                        [
                            'partitionId' => 0,
                            'errorCode' => 0,
                            'leader' => 0,
                        ],
                        [
                            'partitionId' => 1,
                            'errorCode' => 0,
                            'leader' => 2,
                        ],
                    ],
                ],
            ],
        ];
        $broker->setData($data['topics'], $data['brokers']);
        $brokers = [
            0 => '127.0.0.1:9092',
            1 => '127.0.0.1:9192',
            2 => '127.0.0.1:9292',
        ];
        $topics  = [
            'test' => [
                0 => 0,
                1 => 2,
            ],
        ];
        $this->assertEquals($brokers, $broker->getBrokers());
        $this->assertEquals($topics, $broker->getTopics());
    }

    public function getConnect(): void
    {
        $broker = Broker::getInstance();
        $data   = [
            [
                'host' => '127.0.0.1',
                'port' => '9092',
                'nodeId' => '0',
            ],
            [
                'host' => '127.0.0.1',
                'port' => '9193',
                'nodeId' => '1',
            ],
            [
                'host' => '127.0.0.1',
                'port' => '9292',
                'nodeId' => '2',
            ],
        ];
        $broker->setData([], $data);

        $socket = $this->getMockBuilder(Socket::class)
            ->setConstructorArgs(['127.0.0.1', '9192'])
            ->disableOriginalClone()
            ->disableArgumentCloning()
            ->setMethods(['connect', 'setOnReadable', 'close'])
            ->getMock();

        $result = $broker->getMetaConnect('1');
        $this->assertFalse($result);
    }

    public function testConnectRandFalse(): void
    {
        $broker = Broker::getInstance();

        $result = $broker->getRandConnect();
        $this->assertNull($result);
    }

    public function testGetSocketNotSetConfig(): void
    {
        $broker   = Broker::getInstance();
        $hostname = '127.0.0.1';
        $port     = '9092';
        $socket   = $broker->getSocket($hostname, $port, true);

        $this->assertInstanceOf(SocketSync::class, $socket);
    }
}
