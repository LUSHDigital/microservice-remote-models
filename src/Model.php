<?php
/**
 * @file
 * Contains \LushDigital\MicroServiceRemoteModels\Model.
 */

namespace LushDigital\MicroServiceRemoteModels;

use ArrayAccess;
use GuzzleHttp\Client;
use GuzzleHttp\ClientInterface;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\Promise\PromiseInterface;
use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Contracts\Support\Jsonable;
use Illuminate\Support\Str;
use JsonSerializable;
use LushDigital\MicroServiceModelUtils\Contracts\Cacheable;

/**
 * An abstract class that all service models need to implement.
 *
 * A 'service model' works like a normal data model but operates over web
 * services.
 *
 * @package LushDigital\MicroServiceRemoteModels
 */
abstract class Model implements ArrayAccess, Arrayable, Cacheable, Jsonable, JsonSerializable
{
    /**
     * The base URI for this model to make requests against.
     *
     * @var string
     */
    protected $baseUri;

    /**
     * List of endpoints. One for each HTTP method.
     *
     * If not defined then the plural model name will be used.
     *
     * @var array
     */
    protected $restEndpoints = [];

    /**
     * Should requests for this model be made using https.
     *
     * @var bool
     */
    protected $https = true;

    /**
     * The name of the attribute to use as the primary key.
     *
     * Defaults to 'id'.
     *
     * @var string
     */
    protected $primaryKeyAttribute = 'id';

    /**
     * The HTTP client to power all model operations.
     *
     * @var ClientInterface
     */
    private $httpClient;

    /**
     * The model's attributes.
     *
     * @var array
     */
    protected $attributes = [];

    /**
     * The plural model name.
     *
     * @var string
     */
    protected $pluralName;

    /**
     * The model's relations.
     *
     * @var Model[]
     */
    protected $relations = [];

    /**
     * A list of the model attributes that can be used as cache keys.
     *
     * @var array
     */
    protected $attributeCacheKeys = [];

    /**
     * Model constructor.
     *
     * @param array $attributes
     */
    public function __construct(array $attributes = [])
    {
        $this->fill($attributes);
    }

    /**
     * Fill the model with an array of attributes.
     *
     * @param array $attributes
     */
    public function fill(array $attributes)
    {
        foreach ($attributes as $key => $value) {
            $this->setAttribute($key, $value);
        }
    }

    /**
     * Specify the relations to get with this model.
     *
     * @param mixed $relations
     * @return Builder
     */
    public static function with($relations)
    {
        if (is_string($relations)) {
            $relations = func_get_args();
        }

        $instance = new static;
        return $instance->newQueryBuilder($relations);
    }

    /**
     * Get all of the models from the remote service.
     *
     * @return Collection
     *
     * @throws RequestException
     */
    public static function all()
    {
        $instance = new static;
        return $instance->newQueryBuilder()->fetchAll();
    }

    /**
     * Get a single model instance by it's primary key.
     *
     * @param mixed $primaryKey
     * @return Model
     *
     * @throws RequestException
     */
    public static function find($primaryKey)
    {
        $instance = new static;
        return $instance->newQueryBuilder()->fetch($primaryKey);
    }

    /**
     * Get a single model instance by a given key/value condition
     *
     * @param string $key
     * @param mixed $value
     *
     * @return Collection
     */
    public function where($key, $value)
    {
        $instance = new static;
        return $instance->newQueryBuilder()->where($key, $value);
    }

    /**
     * Save the model.
     *
     * @return bool
     */
    public function save()
    {
        $result = $this->newQueryBuilder()->save();
        if (!empty($result)) {
            return true;
        }

        return false;
    }

    /**
     * Delete a single model instance by it's primary key.
     *
     * @param $primaryKey
     * @return Model
     */
    public static function delete($primaryKey)
    {
        $instance = new static;
        return $instance->newQueryBuilder()->delete($primaryKey);
    }

    /**
     * Get the base URI used for this models REST communication.
     *
     * @return string
     */
    public function getBaseUri()
    {
        return $this->baseUri;
    }

    /**
     * Set the base URI used for this models REST communication.
     *
     * @param string $baseUri
     */
    public function setBaseUri($baseUri)
    {
        $this->baseUri = $baseUri;
    }

    /**
     * Get the list of REST endpoints for this model.
     *
     * @return array
     */
    public function getRestEndpoints()
    {
        return $this->restEndpoints;
    }

    /**
     * Should communication for this model be done over https?
     *
     * @return bool
     */
    public function isHttps()
    {
        return $this->https;
    }

    /**
     * Get the attribute used for this models primary key.
     *
     * @return string
     */
    public function getPrimaryKeyAttribute()
    {
        return $this->primaryKeyAttribute;
    }

    /**
     * Set the value of this models primary key.
     *
     * @param $primaryKeyValue
     * @return string
     */
    public function setPrimaryKeyValue($primaryKeyValue)
    {
        return $this->attributes[$this->primaryKeyAttribute] = $primaryKeyValue;
    }

    /**
     * Get the value of this models primary key.
     *
     * @return string
     */
    public function getPrimaryKeyValue()
    {
        if (empty($this->attributes[$this->primaryKeyAttribute])) {
            return null;
        }

        return $this->attributes[$this->primaryKeyAttribute];
    }

    /**
     * Get the endpoint for a given HTTP method.
     *
     * @param string $method
     * @return string
     */
    public function getMethodEndpoint($method)
    {
        // Check if an endpoint has been specified.
        if (!empty($this->getRestEndpoints()[$method])) {
            return $this->getRestEndpoints()[$method];
        }

        return $this->getPluralName();
    }

    /**
     * Get the list of related models.
     *
     * @return Model[]
     */
    public function getRelations()
    {
        return $this->relations;
    }

    /**
     * Set the list of related models.
     *
     * @param Model[] $relations
     */
    public function setRelations($relations)
    {
        $this->relations = $relations;
    }

    /**
     * Add a single related model.
     *
     * @param Model $relation
     */
    public function addRelation(Model $relation)
    {
        $this->relations[] = $relation;
    }

    /**
     * Get the attributes of this model that can be used as cache keys.
     *
     * @return array
     */
    public function getAttributeCacheKeys()
    {
        return $this->attributeCacheKeys;
    }

    /**
     * Set the attributes of this model that can be used as cache keys.
     *
     * @param array $attributeCacheKeys
     * @return $this
     */
    public function setAttributeCacheKeys(array $attributeCacheKeys)
    {
        $this->attributeCacheKeys = $attributeCacheKeys;

        return $this;
    }

    /**
     * Get The 'table' name associated with the model.
     *
     * In reality this is the DNS name of the remote service, but we use the
     * 'table' nomenclature for consistency with Lumen.
     *
     * @return string
     */
    public function getTable()
    {
        return $this->getPluralName();
    }

    /**
     * Determine if the given attribute exists.
     *
     * @param  mixed  $offset
     * @return bool
     */
    public function offsetExists($offset)
    {
        return isset($this->$offset);
    }

    /**
     * Get the value for a given offset.
     *
     * @param  mixed  $offset
     * @return mixed
     */
    public function offsetGet($offset)
    {
        return $this->$offset;
    }

    /**
     * Set the value for a given offset.
     *
     * @param  mixed  $offset
     * @param  mixed  $value
     * @return void
     */
    public function offsetSet($offset, $value)
    {
        $this->$offset = $value;
    }

    /**
     * Unset the value for a given offset.
     *
     * @param  mixed  $offset
     * @return void
     */
    public function offsetUnset($offset)
    {
        unset($this->$offset);
    }

    /**
     * Returns the formatted object as an array.
     *
     * @return mixed
     */
    public function toArray()
    {
        return $this->attributes;
    }

    /**
     * Convert the object into something JSON serializable.
     *
     * @return array
     */
    public function jsonSerialize()
    {
        return $this->toArray();
    }

    /**
     * Convert the model instance to JSON.
     *
     * @param  int  $options
     * @return string
     */
    public function toJson($options = 0)
    {
        return json_encode($this->jsonSerialize(), $options);
    }

    /**
     * Get an attribute from the model.
     *
     * @param  string  $key
     * @return mixed
     */
    public function getAttribute($key)
    {
        if (!$key) {
            return null;
        }

        return array_key_exists($key, $this->attributes) ? $this->attributes[$key] : null;
    }

    /**
     * Set a given attribute on the model.
     *
     * @param  string  $key
     * @param  mixed  $value
     */
    public function setAttribute($key, $value)
    {
        $this->attributes[$key] = $value;
    }

    /**
     * Get a HTTP client.
     *
     * @return Client|ClientInterface
     */
    public function getClient()
    {
        // Ensure we have a base URI.
        if (empty($this->baseUri)) {
            throw new \RuntimeException('Cannot get HTTP client. No base URI specified.');
        }

        // Create a new HTTP client if we do not have one.
        if (empty($this->httpClient)) {
            $uriParts = parse_url($this->baseUri);
            $this->httpClient = new Client(['base_uri' => sprintf('%s://%s', ($this->isHttps()) ? 'https' : 'http', $uriParts['path'])]);
        }

        return $this->httpClient;
    }

    /**
     * Get the plural version of the model name.
     *
     * @return mixed
     */
    public function getPluralName()
    {
        // Use the string library to get a plural name if one is not specified.
        if (empty($this->pluralName)) {
            return str_replace('\\', '', Str::snake(Str::plural(class_basename($this)), '-'));
        }

        return $this->pluralName;
    }

    /**
     * Generate a new query builder for this model.
     *
     * @param array $relations
     * @return Builder
     */
    public function newQueryBuilder(array $relations = [])
    {
        $builder = new Builder();
        $builder->setModel($this);
        $builder->setRelations($relations);

        return $builder;
    }

    /**
     * Dynamically retrieve attributes on the model.
     *
     * @param string $key
     * @return mixed
     */
    public function __get($key)
    {
        return $this->getAttribute($key);
    }

    /**
     * Dynamically set attributes on the model.
     *
     * @param string $key
     * @param mixed $value
     */
    public function __set($key, $value)
    {
        $this->setAttribute($key, $value);
    }

    /**
     * Determine if an attribute or relation exists on the model.
     *
     * @param string $key
     * @return bool
     */
    public function __isset($key)
    {
        return !is_null($this->getAttribute($key));
    }

    /**
     * Unset an attribute on the model.
     *
     * @param $key
     */
    public function __unset($key)
    {
        unset($this->attributes[$key], $this->relations[$key]);
    }

    /**
     * Get a HTTP promise for saving this model.
     *
     * Can either be a POST or PUT depending on if the model exists or not.
     *
     * @return PromiseInterface
     */
    public function getSavePromise()
    {
        // Check if the model has a populated primary key.
        if (!empty($this->getPrimaryKeyValue())) {
            $url = sprintf('%s/%s', $this->getMethodEndpoint('PUT'), $this->getPrimaryKeyValue());
            return $this->getClient()->putAsync($url, [
                'json' => $this->toArray()
            ]);
        }
        else {
            return $this->getClient()->postAsync($this->getMethodEndpoint('POST'), [
                'json' => $this->toArray()
            ]);
        }
    }
}