<?php


namespace TaHUoP\Operations;


interface OperationInterface
{
    public function getAsmInstructions(): string;

    public static function getRegexp(): string;
}