<?php
/**
 * @file
 * Contains \MicroServiceRemoteModelsTest.
 */

use LushDigital\MicroServiceRemoteModels\Model;

/**
 * Test the core microservice functionality.
 *
 * Base functionality includes the info endpoint, health endpoint and response
 * formatter.
 */
class MicroServiceRemoteModelsModelTest extends PHPUnit_Framework_TestCase
{
    /**
     * Test get, set and unset of an attribute.
     */
    public function testAttributeManipulation()
    {
        $model = new ExampleRemoteModel;

        $model->name = 'foo';
        $this->assertEquals('foo', $model->name);
        $this->assertTrue(isset($model->name));

        unset($model->name);
        $this->assertFalse(isset($model->name));
    }

    /**
     * Test the primary key attribute and value of a model.
     */
    public function testPrimaryKey()
    {
        $model = new ExampleRemoteModel;
        $this->assertEquals('id', $model->getPrimaryKeyAttribute());

        $model->id = 1;
        $this->assertTrue(isset($model->id));
        $this->assertEquals(1, $model->getPrimaryKeyValue());
        $this->assertEquals(1, $model->id);

        $model->setPrimaryKeyValue(2);
        $this->assertTrue(isset($model->id));
        $this->assertEquals(2, $model->getPrimaryKeyValue());
        $this->assertEquals(2, $model->id);

        $model = new ExampleNonDefaultRemoteModel;
        $this->assertEquals('name', $model->getPrimaryKeyAttribute());

        $model->name = 'test';
        $this->assertTrue(isset($model->name));
        $this->assertEquals('test', $model->getPrimaryKeyValue());
        $this->assertEquals('test', $model->name);

        $model->setPrimaryKeyValue('test_again');
        $this->assertTrue(isset($model->name));
        $this->assertEquals('test_again', $model->getPrimaryKeyValue());
        $this->assertEquals('test_again', $model->name);
    }

    /**
     * Test method endpoint retrieval.
     */
    public function testMethodEndpoints()
    {
        $model = new ExampleRemoteModel;
        $this->assertTrue(empty($model->getRestEndpoints()['GET']));
        $this->assertEquals('example-remote-models', $model->getMethodEndpoint('GET'));

        $model = new ExampleNonDefaultRemoteModel;
        $this->assertFalse(empty($model->getRestEndpoints()['GET']));
        $this->assertEquals('wibble', $model->getMethodEndpoint('GET'));
        $this->assertTrue(empty($model->getRestEndpoints()['POST']));
        $this->assertEquals('fakes', $model->getMethodEndpoint('POST'));
    }

    /**
     * Test serialization of model data.
     */
    public function testSerialization()
    {
        $data = [
            'name' => 'dan',
            'company' => 'lush',
        ];

        $model = new ExampleRemoteModel;
        $model->fill($data);
        $this->assertEquals($data, $model->toArray());
        $this->assertEquals(json_encode($data), $model->toJson());
    }

    /**
     * Test initialization of the HTTP client.
     */
    public function testClient()
    {
        $model = new ExampleRemoteModel;
        $client = $model->getClient();
        $config = $client->getConfig();
        $this->assertNotEmpty($config);

        $model = new ExampleNonDefaultRemoteModel;
        $this->expectException(RuntimeException::class);
        $model->getClient();
    }
}

class ExampleRemoteModel extends Model
{
    /**
     * {@inheritdoc}
     */
    protected $baseUri = 'localhost';
}

class ExampleNonDefaultRemoteModel extends Model
{
    /**
     * {@inheritdoc}
     */
    protected $primaryKeyAttribute = 'name';

    /**
     * {@inheritdoc}
     */
    protected $pluralName = 'fakes';

    /**
     * {@inheritdoc}
     */
    protected $restEndpoints = [
        'GET' => 'wibble'
    ];
}