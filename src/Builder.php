<?php
/**
 * @file
 * Contains \LushDigital\MicroServiceRemoteModels\Builder.
 */

namespace LushDigital\MicroServiceRemoteModels;

use LushDigital\MicroServiceRemoteModels\Events\RelationshipModified;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\Promise;
use LushDigital\MicroServiceModelUtils\Traits\MicroServiceCacheTrait;
use Psr\Http\Message\ResponseInterface;
use Relationship\RelationshipLeftEntity;
use Relationship\RelationshipServiceClient;

/**
 * The query builder responsible for handling remote service communication.
 *
 * @package LushDigital\MicroServiceRemoteModels
 */
class Builder
{
    use MicroServiceCacheTrait;

    /**
     * The model being queried.
     *
     * @var Model
     */
    protected $model;

    /**
     * Array of related model classes to lazy load with this query.
     *
     * @var string[]
     */
    protected $relations = [];

    /**
     * Get the associated remote model.
     *
     * @return Model
     */
    public function getModel()
    {
        return $this->model;
    }

    /**
     * Set the associated remote model.
     *
     * @param Model $model
     */
    public function setModel($model)
    {
        $this->model = $model;
    }

    /**
     * Get a list of all specified remote model relations.
     *
     * @return \string[]
     */
    public function getRelations()
    {
        return $this->relations;
    }

    /**
     * Set the list of all specified remote model relations.
     *
     * @param \string[] $relations
     */
    public function setRelations($relations)
    {
        $this->relations = $relations;
    }

    /**
     * Fetch a single service model by it's primary key.
     *
     * @param $primaryKey
     * @return Model
     *
     * @throws RequestException
     */
    public function fetch($primaryKey)
    {
        $results = [];
        $queryBuilder = $this;

        // Set up the request to get the service results.
        $url = sprintf('%s/%s', $this->model->getMethodEndpoint('GET'), $primaryKey);
        $promise = $this->model->getClient()->getAsync($url);
        $promise->then(
            function (ResponseInterface $res) use ($queryBuilder, &$results) {
                $results = $queryBuilder->parseResponse($res);
            },
            // Re-throw the error.
            function (RequestException $e) {
                throw $e;
            }
        )->wait();

        return reset($results);
    }

    /**
     * Fetch a collection of service models.
     *
     * @return Collection
     *
     * @throws RequestException
     */
    public function fetchAll()
    {
        $results = [];
        $queryBuilder = $this;

        // Set up the request to get the service results.
        $promise = $this->model->getClient()->getAsync($this->model->getMethodEndpoint('GET'));
        $promise->then(
            function (ResponseInterface $res) use ($queryBuilder, &$results) {
                $results = $queryBuilder->parseResponse($res);
            },
            // Re-throw the error.
            function (RequestException $e) {
                throw $e;
            }
        )->wait();

        return new Collection($results);
    }

    /**
     * Get a single model instance by a given key/value condition
     *
     * @param $key
     * @param $value
     *
     * @return Collection
     */
    public function where($key, $value)
    {
        $results = [];
        $queryBuilder = $this;

        // Set up the request to get the service results.
        $url = sprintf('%s/%s/%s', $this->model->getMethodEndpoint('GET'), $key, $value);
        $promise = $this->model->getClient()->getAsync($url);
        $promise->then(
            function (ResponseInterface $res) use ($queryBuilder, &$results) {
                $results = $queryBuilder->parseResponse($res);
            },
            // Re-throw the error.
            function (RequestException $e) {
                throw $e;
            }
        )->wait();

        return new Collection($results);
    }

    /**
     * Save a new service model.
     *
     * @return Model
     */
    public function save()
    {
        $queryBuilder = $this;

        // Create the model.
        $promise = $this->model->getSavePromise();

        // If the model was successfully created we can go ahead and create the
        // related models too.
        $promise->then(
            function (ResponseInterface $res) use ($queryBuilder) {
                // Get the response from the service.
                $serviceResponse = json_decode($res->getBody(), true);
                if (empty($serviceResponse['data'][$this->model->getPluralName()])) {
                    return null;
                }

                // Populate the model from the response.
                $result = array_shift($serviceResponse['data'][$queryBuilder->getModel()->getPluralName()]);
                $queryBuilder->model->fill($result);

                // Save the relations.
                $this->saveRelations($queryBuilder->model);

                // Clear the cache.
                $this->cacheForget($queryBuilder->model);
            },
            // Re-throw the error.
            function (RequestException $e) {
                throw $e;
            }
        )->wait();

        return $this->model;
    }

    /**
     * Delete a single service model by it's primary key.
     *
     * @param $primaryKey
     *
     * @throws RequestException
     */
    public function delete($primaryKey)
    {
        $this->model->setPrimaryKeyValue($primaryKey);
        $queryBuilder = $this;

        // Set up the request to get the service results.
        $url = sprintf('%s/%s', $this->model->getMethodEndpoint('DELETE'), $primaryKey);
        $promise = $this->model->getClient()->deleteAsync($url);
        $promise->then(
            function () use ($queryBuilder) {
                $queryBuilder->deleteRelations($queryBuilder->model);

                // Clear the cache.
                $this->cacheForget($queryBuilder->model);
            },
            // Re-throw the error.
            function (RequestException $e) {
                throw $e;
            }
        )->wait();
    }

    /**
     * Parse a service response into an array of service models.
     *
     * @param ResponseInterface $res
     *
     * @return Model[]|null
     */
    public function parseResponse(ResponseInterface $res)
    {
        // Decode the service response.
        $serviceResponse = json_decode($res->getBody(), true);
        if (empty($serviceResponse['data'][$this->model->getPluralName()])) {
            return null;
        }

        // Populate the service model instances.
        $resultInstances = [];
        foreach ($serviceResponse['data'][$this->model->getPluralName()] as $result) {
            $instance = clone $this->model;
            $instance->fill($result);

            // If there are relations specified, load all the related models
            // for this result instance.
            if (!empty($this->relations)) {
                $this->getRelatedModels($instance, $this->relations);
            }

            $resultInstances[] = $instance;
        }

        return $resultInstances;
    }

    /**
     * Get a new gRPC relationship middleware client.
     *
     * The client will always attempt to connect to the service on the URL in
     * the following pattern: left_entity_plural-right_entity_plural:port. For
     * example shops-addresses:50051.
     *
     * @param Model $leftEntity
     * @param Model $rightEntity
     *
     * @return RelationshipServiceClient
     */
    public function getRelationshipClient(Model $leftEntity, Model $rightEntity)
    {
        // Build the name of the environment variable to check.
        $envVar = strtoupper(sprintf('REMOTE_MODEL_%s_%s_DNS', $leftEntity->getPluralName(), $rightEntity->getPluralName()));

        // Build the URL.
        $url = sprintf('%s-%s', $leftEntity->getPluralName(), $rightEntity->getPluralName());

        // Override the URL if we have an environment variable available.
        if (!empty(getenv($envVar))) {
            $url = getenv($envVar);
        }

        return new RelationshipServiceClient(sprintf('%s:%d', $url, $this->getGrpcPort()), [
            // TODO: TLS.
            'credentials' => \Grpc\ChannelCredentials::createInsecure(),
        ]);
    }

    /**
     * Get the models related to a given model instance.
     *
     * @param Model $instance
     *     The model to get relations for.
     * @param array $relations
     *     Array of fully qualified class names of the model representing the
     *     type of.
     */
    protected function getRelatedModels(Model &$instance, array $relations = [])
    {
        // Ensure the related models are of the right type.
        array_walk($relations, function($relation) {
            if (!is_subclass_of($relation, Model::class)) {
                throw new \RuntimeException(sprintf(
                    'Cannot get related data. Relation model (%s) is not a sub class of %s.',
                    $relation,
                    Model::class
                ));
            }
        });

        // Create the left entity, to get relationships from.
        $leftEntity = new RelationshipLeftEntity();
        $leftEntity->setLeftId($instance->getPrimaryKeyValue());

        // Generate an array of promises for each possible relation.
        $promises = [];
        foreach ($relations as $relation) {
            $relationInstance = new $relation;

            // Create a new relationship client.
            $relationshipClient = $this->getRelationshipClient($instance, $relationInstance);

            // Get all relation IDs.
            $call = $relationshipClient->GetRelations($leftEntity);
            $relationships = $call->responses();

            // Generate a promise for each relationship.
            $newRelations = [];
            foreach ($relationships as $relationship) {
                $promise = $relationInstance->getClient()->getAsync($relationInstance->getMethodEndpoint('GET') . '/' . $relationship->getRightId());
                $promise->then(
                    function (ResponseInterface $res) use (&$newRelations) {
                        // Get the JSON response or exit if none.
                        $serviceResponse = json_decode($res->getBody(), true);
                        if (empty($serviceResponse['data'])) {
                            return null;
                        }

                        // Append this result to the relations array.
                        $type = key($serviceResponse['data']);
                        if (empty($newRelations[$type])) {
                            $newRelations[$type] = [];
                        }

                        $newRelations[$type] = array_merge($newRelations[$type], $serviceResponse['data'][$type]);
                    },
                    function (RequestException $e) use (&$newRelations) {
                        $serviceResponse = json_decode($e->getResponse()->getBody());

                        if (!empty($serviceResponse->data->errors)) {
                            $newRelations['errors'] = array_merge($newRelations['errors'], $serviceResponse->data->errors);
                        }
                    }
                );

                $promises[] = $promise;
            }
        }

        // Wait for the requests to complete.
        Promise\settle($promises)->wait();

        // Attach the relations to the model instance.
        foreach ($newRelations as $type => $value) {
            $instance->setAttribute($type, $value);
        }
    }

    /**
     * Asynchronously save all relations for a model.
     *
     * @param Model $model
     */
    protected function saveRelations(Model &$model)
    {
        // Generate an array of promises for each relation.
        $promises = [];
        foreach ($model->getRelations() as $relation) {
            $promise = $relation->getSavePromise();
            $op = !empty($relation->getPrimaryKeyValue()) ? 'updated' : 'created';

            $promise->then(
                function (ResponseInterface $res) use (&$model, $relation, $op) {
                    $serviceResponse = json_decode($res->getBody(), true);
                    $type = key($serviceResponse['data']);

                    // Populate the new relation model.
                    $result = array_shift($serviceResponse['data'][$type]);
                    $relationInstance = new $relation;
                    $relationInstance->fill($result);

                    // Initialise the relations if none exist.
                    if (!isset($model->{$type})) {
                        $model->{$type} = [];
                    }

                    // Add the relation to the left hand model.
                    $relations = array_merge($model->{$type}, $result);
                    $model->{$type} = $relations;

                    // Clear the cache.
                    $this->cacheForget($relationInstance);

                    // Dispatch an event to show the relationship was modified.
                    event(new RelationshipModified($model, $relationInstance, $op));
                },
                function (RequestException $e) use (&$model) {
                    $serviceResponse = json_decode($e->getResponse()->getBody());

                    if (!empty($serviceResponse->data->errors)) {
                        $model['errors'] = array_merge($model['errors'], $serviceResponse->data->errors);
                    }
                }
            );

            $promises[] = $promise;
        }

        // Wait for the requests to complete.
        Promise\settle($promises)->wait();
    }

    /**
     * Delete all relations for a given model.
     *
     * @param Model $model
     * @return bool
     */
    protected function deleteRelations(Model $model)
    {
        // Bail out if there are no relations.
        if (empty($this->relations)) {
            return false;
        }

        // Create the left entity, to get relationships from.
        $leftEntity = new RelationshipLeftEntity();
        $leftEntity->setLeftId($model->getPrimaryKeyValue());

        $promises = [];
        foreach ($this->relations as $relation) {
            $relationInstance = new $relation;

            // Get all related IDs for this model.
            $call = $this->getRelationshipClient($model, $relationInstance)->GetRelations($leftEntity);
            $relationships = $call->responses();

            // Create the promises.
            foreach ($relationships as $index => $relationship) {
                $relatedId = $relationship->getRightId();
                $relationInstance->setPrimaryKeyValue($relatedId);

                $url = sprintf('%s/%s', $relationInstance->getMethodEndpoint('DELETE'), $relatedId);
                $promise = $relationInstance->getClient()->deleteAsync($url);
                $promise->then(
                    function () use ($model, $relationInstance) {
                        // Clear the cache.
                        $this->cacheForget($relationInstance);

                        // Dispatch an event to indicate the relationship was deleted.
                        event(new RelationshipModified($model, $relationInstance, 'deleted'));
                    },
                    // Re-throw the error.
                    function (RequestException $e) {
                        throw $e;
                    }
                );

                $promises[] = $promise;
            }
        }

        // Delete the relations.
        Promise\settle($promises)->wait();
    }

    /**
     * Get the port number to use for gRPC.
     *
     * @return int
     */
    protected function getGrpcPort()
    {
        return !empty(getenv('REMOTE_MODEL_GRPC_PORT')) ? getenv('REMOTE_MODEL_GRPC_PORT') : 50051;
    }
}