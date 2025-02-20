<?php
class Queue
{
    private Predis\Client $client;

    function __construct($client)
    {
        $this->client = $client;
    }

    public function queue($queue, $data)
    {
        $this->client->lpush($queue, $data);
    }

    public function pop($queue)
    {
        return $this->client->lpop($queue);
    }

    public function next($queue)
    {
        return $this->client->lindex($queue, 0);
    }
}
