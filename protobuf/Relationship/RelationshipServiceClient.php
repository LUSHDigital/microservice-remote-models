<?php
// GENERATED CODE -- DO NOT EDIT!

// Original file comments:
// Protocol buffer definition for a relationship service.
//
// A relationship service is intended to manage the links between data sets stored
// in separate microservices.
//
namespace Relationship {

  // The relationship service definition.
  class RelationshipServiceClient extends \Grpc\BaseStub {

    /**
     * @param string $hostname hostname
     * @param array $opts channel options
     * @param \Grpc\Channel $channel (optional) re-use channel object
     */
    public function __construct($hostname, $opts, $channel = null) {
      parent::__construct($hostname, $opts, $channel);
    }

    /**
     * Create a new relationship between two entity IDs.
     * @param \Relationship\Relationship $argument input argument
     * @param array $metadata metadata
     * @param array $options call options
     */
    public function CreateRelationship(\Relationship\Relationship $argument,
      $metadata = [], $options = []) {
      return $this->_simpleRequest('/relationship.RelationshipService/CreateRelationship',
      $argument,
      ['\Relationship\RelationshipResponse', 'decode'],
      $metadata, $options);
    }

    /**
     * Get all relations based on the dominant context entity ID.
     * @param \Relationship\RelationshipLeftEntity $argument input argument
     * @param array $metadata metadata
     * @param array $options call options
     */
    public function GetRelations(\Relationship\RelationshipLeftEntity $argument,
      $metadata = [], $options = []) {
      return $this->_serverStreamRequest('/relationship.RelationshipService/GetRelations',
      $argument,
      ['\Relationship\Relationship', 'decode'],
      $metadata, $options);
    }

    /**
     * Update an existing relationship.
     * @param \Relationship\UpdateRelationshipRequest $argument input argument
     * @param array $metadata metadata
     * @param array $options call options
     */
    public function UpdateRelationship(\Relationship\UpdateRelationshipRequest $argument,
      $metadata = [], $options = []) {
      return $this->_simpleRequest('/relationship.RelationshipService/UpdateRelationship',
      $argument,
      ['\Relationship\RelationshipResponse', 'decode'],
      $metadata, $options);
    }

    /**
     * Create a new relationship between two entity IDs.
     * @param \Relationship\Relationship $argument input argument
     * @param array $metadata metadata
     * @param array $options call options
     */
    public function DeleteRelationship(\Relationship\Relationship $argument,
      $metadata = [], $options = []) {
      return $this->_simpleRequest('/relationship.RelationshipService/DeleteRelationship',
      $argument,
      ['\Relationship\RelationshipResponse', 'decode'],
      $metadata, $options);
    }

  }

}
