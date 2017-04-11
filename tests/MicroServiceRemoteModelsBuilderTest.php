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
class MicroServiceRemoteModelsBuilderTest extends PHPUnit_Framework_TestCase
{
    /**
     * Test instantiating a builder from a model.
     */
    public function testModelBuilder()
    {
        $model = new ExampleModel;
        $builder = $model->newQueryBuilder();
        $this->assertEquals($model, $builder->getModel());
    }

    /**
     * Test instantiating a builder with related models.
     */
    public function testWith()
    {
        $builder = ExampleModel::with(RelatedModel::class);
        $this->assertEquals(ExampleModel::class, class_basename($builder->getModel()));
        $this->assertEquals([RelatedModel::class], $builder->getRelations());
    }
}

class ExampleModel extends Model
{
    /**
     * {@inheritdoc}
     */
    protected $baseUri = 'localhost';
}

class RelatedModel extends Model
{
    /**
     * {@inheritdoc}
     */
    protected $baseUri = 'localhost';
}