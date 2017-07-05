<?php
/**
 * @file
 * Contains \MicroServiceRemoteModelsTimeoutTest.
 */

use LushDigital\MicroServiceRemoteModels\Traits\TimeoutTrait;

/**
 * Test the timeout trait.
 */
class MicroServiceRemoteModelsTimeoutTest extends PHPUnit_Framework_TestCase
{
    /**
     * Test the timeouts.
     */
    public function testTimeout()
    {
        $example = new ExampleTimeout;
        $this->assertEquals(2 * 1000, $example->exposeTimeout(2, 'ms'));
        $this->assertEquals(2 * 1000 * 1000, $example->exposeTimeout(2, 'µs'));
        $this->assertEquals(2 * 1000 * 1000  * 1000, $example->exposeTimeout(2, 'ns'));
    }
}

/**
 * An example class.
 */
class ExampleTimeout
{
    use TimeoutTrait;

    /**
     * Expose the timeout trait function.
     *
     * @param int $seconds
     *     The timeout value in seconds.
     * @param string $unit
     *     The unit we want the timeout in.
     *
     * @return int
     *     The timeout in the specified unit.
     */
    public function exposeTimeout($seconds = 1, $unit = 'µs')
    {
        return $this->getTimeoutVal($seconds, $unit);
    }
}