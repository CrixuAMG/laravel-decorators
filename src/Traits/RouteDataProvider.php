<?php

namespace CrixuAMG\Decorators\Traits;

use CrixuAMG\Decorators\Exceptions\MissingDataException;
use Illuminate\Support\Facades\App;

trait RouteDataProvider
{
    /**
     * @return string
     */
    private function getRouteSource(): string
    {
        // Get the path and remove any trailing slashes
        return rtrim(ltrim(request()->getPathInfo(), '/'), '/');
    }

    /**
     * @param string $source
     *
     * @return array
     */
    private function parseRouteSource(string $source): array
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
    private function matchRouteData(string $string, array $possibleMatches): array
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
        if (App::runningInConsole()) {
            // Prevent issues from occurring when clearing cache for example
            return false;
        }

        // Get the source
        $source = $this->getRouteSource();

        // Get the matchables to check against
        $matchAbles = $this->getRouteMatchables();

        $match = null;

        // First try to get it this way
        $match = data_get($matchAbles, str_replace('/', '.', $source));
        $result = $this->checkMatch($match);
        if ($result) {
            $this->decorateMatch($match);

            return true;
        }

        // Parse the source
        $sourceParts = $this->parseRouteSource($source);

        // Go through the parts and try to find a match
        foreach ($sourceParts as $sourcePart) {
            $match = $this->matchRouteData($sourcePart, $matchAbles);

            $result = $this->checkMatch($match);
            if ($result) {
                $this->decorateMatch($match);

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
    private function getRouteMatchables(): array
    {
        return (array)config('decorators.route_matchables');
    }

    /**
     * @param $match
     *
     * @return bool
     */
    private function checkMatch($match): bool
    {
        return !empty($match['__contract']) && !empty($match['__arguments']);
    }

    /**
     * @param $match
     *
     * @return void
     */
    private function decorateMatch($match): void
    {
        $this->decorate($match['__contract'], ...$match['__arguments']);
    }
}