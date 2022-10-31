<?php

namespace PhpCas\Tests;

use Monolog\Formatter\LineFormatter;
use Monolog\Handler\StreamHandler;
use Monolog\Logger;
use PHPUnit\Framework\TestCase;

class LogTest extends TestCase
{
    /**
     * @var string
     */
    private $logPath;

    /**
     * @var Logger
     */
    private $logger;

    public function setUp(): void
    {
        parent::setUp();
        $this->logPath = tempnam(sys_get_temp_dir(), 'phpCAS');
        $this->logger = new Logger('name');
        $handler = new StreamHandler($this->logPath);
        $format = "%message%\n";
        $formatter = new LineFormatter($format);
        $handler->setFormatter($formatter);
        $this->logger->pushHandler($handler);
    }

    public function tearDown(): void
    {
        unlink($this->logPath);
        parent::tearDown();
    }

    public function testSetLogger()
    {
        \phpCAS::setLogger($this->logger);
        $client = new \CAS_Client(
            CAS_VERSION_2_0, // Server Version
            false, // Proxy
            'cas.example.edu', // Server Hostname
            443, // Server port
            '/cas/', // Server URI
            'http://www.service.com', // Service Name
            false // Start Session
        );
        $contents = file_get_contents($this->logPath);
        // C750 .START (2020-01-11 23:18:05) phpCAS-1.3.8+ ****************** [CAS.php:454]
        // C750 .=> CAS_Client::__construct('2.0', false, 'cas.example.edu', 443, '/cas/', false) [LogTest.php:39]
        // C750 .|    Session is not authenticated [Client.php:938]
        // C750 .<= ''
        // EOF
        $lines = explode("\n", $contents);
        $this->assertCount(5, $lines);
        $this->assertStringContainsString('Session is not authenticated', $lines[2]);
    }

    public function testSetLoggerNull()
    {
        \phpCAS::setLogger($this->logger);
        \phpCAS::setLogger(null);
        $client = new \CAS_Client(
            CAS_VERSION_2_0, // Server Version
            false, // Proxy
            'cas.example.edu', // Server Hostname
            443, // Server port
            '/cas/', // Server URI
            'http://www.service.com', // Service Name
            false // Start Session
        );
        $contents = file_get_contents($this->logPath);
        // C750 .START (2020-01-11 23:18:05) phpCAS-1.3.8+ ****************** [CAS.php:454]
        // EOF
        $lines = explode("\n", $contents);
        $this->assertCount(2, $lines);
    }
}
