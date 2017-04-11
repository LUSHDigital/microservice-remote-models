<?php
/**
 * @file
 * Contains \LushDigital\MicroServiceRemoteModels\Listeners\RelationshipModifiedListener.
 */

namespace LushDigital\MicroServiceRemoteModels\Listeners;

use LushDigital\MicroServiceRemoteModels\Events\RelationshipModified;
use LushDigital\MicroServiceRemoteModels\Builder;
use LushDigital\MicroServiceRemoteModels\Model;
use Relationship\Relationship;

/**
 * Event listener to act upon a service model relationship modification.
 *
 * @package LushDigital\MicroServiceRemoteModels\Listeners
 */
class RelationshipModifiedListener
{
    /**
     * Handle the event.
     *
     * @param RelationshipModified $event
     * @return void
     */
    public function handle(RelationshipModified $event)
    {
        switch ($event->op) {
            case 'created':
                $this->createRelationship($event->leftEntity, $event->rightEntity);
                break;

            case 'deleted':
                $this->deleteRelationship($event->leftEntity, $event->rightEntity);
                break;
        }
    }

    /**
     * Create a new relationship in the middleware.
     *
     * @param Model $leftEntity
     *     The left-hand (dominant) entity of the relationship.
     * @param Model $rightEntity
     *     The right-hand entity of the relationship.
     */
    private function createRelationship(Model $leftEntity, Model $rightEntity)
    {
        // Create a new gRPC client.
        $queryBuilder = new Builder();
        $client = $queryBuilder->getRelationshipClient($leftEntity, $rightEntity);

        // Build the relationship.
        $relationship = new Relationship();
        $relationship->setLeftId($leftEntity->getPrimaryKeyValue());
        $relationship->setRightId($rightEntity->getPrimaryKeyValue());

        // Create the relationship.
        $client->CreateRelationship($relationship)->wait();
    }

    /**
     * Delete a relationship in the middleware.
     *
     * @param Model $leftEntity
     *     The left-hand (dominant) entity of the relationship.
     * @param Model $rightEntity
     *     The right-hand entity of the relationship.
     */
    private function deleteRelationship(Model $leftEntity, Model $rightEntity)
    {
        // Create a new gRPC client.
        $queryBuilder = new Builder();
        $client = $queryBuilder->getRelationshipClient($leftEntity, $rightEntity);

        // Build the relationship.
        $relationship = new Relationship();
        $relationship->setLeftId($leftEntity->getPrimaryKeyValue());
        $relationship->setRightId($rightEntity->getPrimaryKeyValue());

        // Delete the relationship.
        $client->DeleteRelationship($relationship)->wait();
    }
}