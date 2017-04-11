<?php
/**
 * @file
 * Contains \LushDigital\MicroServiceRemoteModels\Events\RelationshipModified.
 */

namespace LushDigital\MicroServiceRemoteModels\Events;

use Illuminate\Queue\SerializesModels;
use LushDigital\MicroServiceRemoteModels\Model;

/**
 * Event class to represent a modified service model relationship.
 *
 * @package LushDigital\MicroServiceRemoteModels\Events
 */
class RelationshipModified
{
    use SerializesModels;

    /**
     * The left hand (dominant) entity from the relationship.
     *
     * @var Model
     */
    public $leftEntity;

    /**
     * The right hand entity from the relationship.
     *
     * @var Model
     */
    public $rightEntity;

    /**
     * The type of modification that occurred.
     *
     * @var string
     */
    public $op;

    /**
     * RelationshipModified constructor.
     *
     * @param Model $leftEntity
     * @param Model $rightEntity
     * @param string $op
     */
    public function __construct(Model $leftEntity, Model $rightEntity, $op = 'created')
    {
        $this->leftEntity = $leftEntity;
        $this->rightEntity = $rightEntity;
        $this->op = $op;
    }
}