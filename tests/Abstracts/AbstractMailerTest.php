<?php
namespace Arrounded\Abstracts;

use Arrounded\Dummies\DummyMailer;
use Arrounded\TestCases\ArroundedTestCase;
use Mockery;

class AbstractMailerTest extends ArroundedTestCase
{
    /**
     * @type DummyMailer
     */
    protected $mailer;

    /**
     * Setup the tests.
     */
    public function setUp()
    {
        Mockery::mock('alias:Config')->shouldReceive('get')->andReturn('User');
        $mailer = Mockery::mock('Illuminate\Mail\Mailer');
        $queue  = Mockery::mock('Illuminate\Queue\QueueManager');

        $this->mailer = new DummyMailer($mailer, $queue);
    }

    public function testCanSetSender()
    {
        $user = Mockery::mock('Illuminate\Auth\UserInterface');
        $this->mailer->setSender($user);

        $this->assertEquals($user, $this->mailer->getDatabag()['from']);
    }
}
