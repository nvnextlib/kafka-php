<?php
require '../../vendor/autoload.php';
use Kafka\Protocol;
use Kafka\Socket;

class DescribeGroups
{
    /**
     * @var string[]
     */
    protected $group = [];
    // {{{ functions
    // {{{ protected function joinGroup()

    protected function joinGroup(): void
    {
        $data = [
            'group_id' => 'test',
            'session_timeout' => 6000,
            'rebalance_timeout' => 6000,
            'member_id' => '',
            'data' => [
                [
                    'protocol_name' => 'group',
                    'version' => 0,
                    'subscription' => ['test'],
                    'user_data' => '',
                ],
            ],
        ];

        Protocol::init('0.9.1.0');
        $requestData = \Kafka\Protocol::encode(\Kafka\Protocol::JOIN_GROUP_REQUEST, $data);

        $socket = new Socket('127.0.0.1', '9192');
        $socket->setOnReadable(function ($data): void {
            $coodid      = \Kafka\Protocol\Protocol::unpack(\Kafka\Protocol\Protocol::BIT_B32, substr($data, 0, 4));
            $result      = \Kafka\Protocol::decode(\Kafka\Protocol::JOIN_GROUP_REQUEST, substr($data, 4));
            $this->group = $result;
            Amp\stop();
        });

        $socket->connect();
        $socket->write($requestData);
        Amp\run(function () use ($socket, $requestData): void {
        });
    }

    // }}}
    // {{{ protected function syncGroup()

    protected function syncGroup(): void
    {
        $this->joinGroup();
        $data = [
            'group_id' => 'test',
            'generation_id' => $this->group['generationId'],
            'member_id' => $this->group['memberId'],
            'data' => [
                [
                    'version' => 0,
                    'member_id' => $this->group['memberId'],
                    'assignments' => [
                        [
                            'topic_name' => 'test',
                            'partitions' => [
                                0,
                            ],
                        ],
                    ],
                ],
            ],
        ];

        \Kafka\Protocol::init('0.9.1.0');
        $requestData = \Kafka\Protocol::encode(\Kafka\Protocol::SYNC_GROUP_REQUEST, $data);

        $socket = new \Kafka\Socket('127.0.0.1', '9192');
        $socket->setOnReadable(function ($data): void {
            $coodid = \Kafka\Protocol\Protocol::unpack(\Kafka\Protocol\Protocol::BIT_B32, substr($data, 0, 4));
            $result = \Kafka\Protocol::decode(\Kafka\Protocol::SYNC_GROUP_REQUEST, substr($data, 4));
            //echo json_encode($result);
            Amp\stop();
        });

        $socket->connect();
        $socket->write($requestData);
        Amp\run(function () use ($socket, $requestData): void {
        });
    }

    // }}}
    // {{{ public function run()

    public function run(): void
    {
        $this->joinGroup();
        $this->syncGroup();
        $data = [
            'test',
        ];

        \Kafka\Protocol::init('0.9.1.0');
        $requestData = \Kafka\Protocol::encode(\Kafka\Protocol::DESCRIBE_GROUPS_REQUEST, $data);

        $socket = new \Kafka\Socket('127.0.0.1', '9192');
        $socket->setOnReadable(function ($data): void {
            $coodid = \Kafka\Protocol\Protocol::unpack(\Kafka\Protocol\Protocol::BIT_B32, substr($data, 0, 4));
            $result = \Kafka\Protocol::decode(\Kafka\Protocol::DESCRIBE_GROUPS_REQUEST, substr($data, 4));
            echo json_encode($result);
            Amp\stop();
        });

        $socket->connect();
        $socket->write($requestData);
        Amp\run(function () use ($socket, $requestData): void {
        });
    }

    // }}}
    // }}}
}

$describe = new DescribeGroups();
$describe->run();
