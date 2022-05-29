#!/usr/bin/php
<?php

class Config
{

    private array $config = [
        'default_background_color' => '#ffffff',
        'profiles' => [
            'default' => '#feffb0',
        ],
    ];

    public function __construct(string $path)
    {
        if (file_exists($path) === false) {
            return;
        }

        $config = file_get_contents($path);

        try {
            $this->config = json_decode($config, true, 512, JSON_THROW_ON_ERROR);
        } catch (\Throwable $e) {
            echo 'Your config content is invalid. path: ' . $path;
            exit(1);
        }
    }

    public function getBackgroundColor(string $hostname): string
    {
        $profile = $this->config['hostnames'][$hostname] ?? 'default';

        return $this->getBackgroundColorOfProfile($profile);
    }

    public function getBackgroundColorOfProfile(string $profile): string
    {
        return $this->config['profiles'][$profile] ?? $this->getDefaultBackgroundColor();
    }

    public function getDefaultBackgroundColor(): string
    {
        return $this->config['default_background_color'];
    }
}

class BackgroundColorSwitcher
{

    public static function switchTo(string $color): void
    {
        echo "\x1b]11;{$color}\x07";
    }
}

class SshProcessMonitor
{

    private int $sshProcessID;

    public function __construct()
    {
        $this->sshProcessID = $this->detectSshProcessID();
    }

    private function detectSshProcessID(): int
    {
        $parentID = posix_getppid();

        $processStatus = file_get_contents("/proc/{$parentID}/status");

        if (preg_match('#Name\:\s+ssh#', $processStatus)) {
            // Bash Shell
            preg_match('#Pid\:\s+(\d+)#', $processStatus, $matches);
        } else if (preg_match('#Name\:\s+fish#', $processStatus)) {
            // Fish Shell
            preg_match('#PPid\:\s+(\d+)#', $processStatus, $matches);
        } else {
            echo 'This shell is not supported';

            exit(0);
        }

        return $matches[1];
    }

    public function waitForSshProcessToExit(): void
    {
        $pid = pcntl_fork();

        if ($pid == -1) {
            die('could not fork background process.');
        } else if ($pid) {
            // we are the parent
            exit(0);
        } else {
            // we are the child
            do {
                sleep(1);
            } while (file_exists("/proc/{$this->sshProcessID}/status"));
        }
    }
}

if (count($argv) < 2) {
    echo 'Please provide hostname to this command as argument';
    exit(1);
}

$hostname = $argv[1];

$profile = $argv[2] ?? '';

$configPath = getenv("HOME") . '/.config/ssh-background-switcher/config.json';

$config = new Config($configPath);

$newBackgroundColor = ($profile === '') ? $config->getBackgroundColor($hostname) : $config->getBackgroundColorOfProfile($profile);

$backgroundColorSwitcher = new BackgroundColorSwitcher();

$backgroundColorSwitcher->switchTo($newBackgroundColor);

$sshProcessMonitor = new SshProcessMonitor();

$sshProcessMonitor->waitForSshProcessToExit();

$backgroundColorSwitcher->switchTo($config->getDefaultBackgroundColor());

exit(0);
