<?php

namespace  app\Interfaces;

interface Command
{
    public static function set(array $arguments = []): bool;
    public static function getAccessLevel(): string;
    public static function description();
    public static function execute(array $arguments = []);
    public static function locale($phrase);
    public static function replyButton(array $data);
}
