<?php


namespace TaHUoP\OperationTypes;

enum MemoryAccessOperationType: string
{
    case PUSH = 'push';
    case POP = 'pop';
}
