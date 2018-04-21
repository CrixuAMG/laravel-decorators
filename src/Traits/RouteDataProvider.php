<?php

namespace CrixuAMG\Decorators\Traits;

use CrixuAMG\Decorators\Exceptions\MissingDataException;

trait RouteDataProvider
{
    /**
     * @return string
     */
    public function getRouteSource(): string
    {
        // Get the path and remove any trailing slashes
        return rtrim(ltrim(request()->getPathInfo(), '/'), '/');
    }

    /**
     * @param string $source
     *
     * @return array
     */
    public function parseRouteSource(string $source): array
    {
        // Get the parts of the uri
        return explode('/', $source);
    }

    /**
     * @param string $string
     * @param array  $possibleMatches
     *
     * @return array
     */
    public function matchRouteData(string $string, array $possibleMatches): array
    {
        return $possibleMatches[$string] ?? [];
    }

    /**
     * @param bool $silent
     *
     * @return bool
     * @throws MissingDataException
     */
    public function autoregisterRoute(bool $silent = false): bool
    {
        // Get the source
        $source = $this->getRouteSource();

        // Parse the source
        $sourceParts = $this->parseRouteSource($source);

        // Get the matchables to check against
        $matchAbles = $this->getRouteMatchables();

        $match = null;
        // Go through the parts and try to find a match
        foreach ($sourceParts as $sourcePart) {
            $match = $this->matchRouteData($sourcePart, $matchAbles);

            // Check if a match has been found
            if (!empty($match['__contract']) && !empty($match['__arguments'])) {
                // A match has been found, use it
                $this->decorate($match['__contract'], ...$match['__arguments']);

                return true;
            } elseif (!empty($match)) {
                // A match has been found, but we need to go deeper into the data
                $matchAbles = $match;
            }
        }

        if (!$silent) {
            // No match could be found
            throw new MissingDataException('No match could be found for this URI.', 500);
        }

        return false;
    }

    /**
     * @return array
     */
    public function getRouteMatchables(): array
    {
        return (array)config('decorators.route_matchables');
    }
}