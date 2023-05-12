<?php

namespace  app\Interfaces;

interface Command {
    public static function description();
    public static function execute(array $arguments = []);
    public static function locale(string $phrase);
}