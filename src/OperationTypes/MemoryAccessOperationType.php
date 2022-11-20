<?php


namespace TaHUoP\JackVM\OperationTypes;

enum MemoryAccessOperationType: string
{
    case PUSH = 'push';
    case POP = 'pop';
}
