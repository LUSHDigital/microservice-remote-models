<?php
/**
 * @file
 * Contains \LushDigital\MicroServiceRemoteModels\Traits\TimeoutTrait.
 */

namespace LushDigital\MicroServiceRemoteModels\Traits;

/**
 * Trait for generating gRPC timeout values.
 *
 * @package LushDigital\MicroServiceRemoteModels\Trait
 */
trait TimeoutTrait
{
    /**
     * Units of time.
     *
     * Format:
     *     unit => multiplier
     *
     * @var array
     */
    private $units = [
        'ms' => 1000,
        'µs' => (1000 * 1000),
        'ns' => (1000 * 1000 * 1000),
    ];

    /**
     * Get a timeout value in the specified unit.
     *
     * @param int $seconds
     *     The timeout value in seconds.
     * @param string $unit
     *     The unit we want the timeout in.
     *
     * @return int
     *     The timeout in the specified unit.
     */
    protected function getTimeoutVal($seconds = 1, $unit = 'µs')
    {
        if (!in_array($unit, array_keys($this->units))) {
            throw new \RuntimeException(sprintf('Invalid timeout unit specified: %s', $unit));
        }

        // Get the timeout in the specified unit.
        return $seconds * $this->units[$unit];
    }
}