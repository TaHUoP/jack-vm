<?php


namespace TaHUoP\Enums;

/**
 * @method static BranchingOperationType GOTO()
 * @method static BranchingOperationType IF_GOTO()
 * @method static BranchingOperationType LABEL()
 */
class BranchingOperationType extends AbstractEnum
{
    public const GOTO = 'goto';
    public const IF_GOTO = 'if-goto';
    public const LABEL = 'label';
}