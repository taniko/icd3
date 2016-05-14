<?php
namespace Hrgruri\Icd3;

class Slack
{
    private $loop;
    private $client;
    private $channel;

    public function __construct($config)
    {
        $this->loop = \React\EventLoop\Factory::create();
        $this->client = new \Slack\ApiClient($this->loop);
        $this->client->setToken($config->token);
        $this->client->getChannelByName($config->channel)->then(function (\Slack\Channel $channel) {
            $this->channel = $channel;
        });
        $this->loop->run();
    }

    /**
     * Slackに通知を送る
     * @param  string $text
     */
    public function send(string $text)
    {
        $this->client->send($text, $this->channel);
        $this->loop->run();
    }
}
