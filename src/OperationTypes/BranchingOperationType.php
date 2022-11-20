<?php


namespace TaHUoP\JackVM\OperationTypes;

enum BranchingOperationType: string
{
    case GOTO = 'goto';
    case IF_GOTO = 'if-goto';
    case LABEL = 'label';
}
