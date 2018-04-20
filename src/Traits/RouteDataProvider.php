<?php

namespace CrixuAMG\Decorators\Traits;

use CrixuAMG\Decorators\Exceptions\MissingDataException;

trait RouteDataProvider
{
    /**
     * @return string
     */
    public function getSource(): string
    {
        // Get the path and remove any trailing slashes
        return rtrim(ltrim(request()->getPathInfo(), '/'), '/');
    }

    /**
     * @param string $source
     *
     * @return array
     */
    public function parseSource(string $source): array
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
    public function matchData(string $string, array $possibleMatches): array
    {
        $match = [];

        // Go through the parts and try to find a match
        if (isset($possibleMatches[$string])) {
            $match = $possibleMatches[$string];
        } elseif (isset($match[$string])) {
            $match = $match[$string];
        }

        return $match;
    }

    /**
     * @param bool $silent
     *
     * @throws MissingDataException
     */
    public function run(bool $silent = false)
    {
        $source = $this->getSource();

        $sourceParts = $this->parseSource($source);

        $matchAbles = $this->getMatchables();

        $match = null;
        foreach ($sourceParts as $sourcePart) {
            $match = $this->matchData($sourcePart, $matchAbles);
        }

        if (!empty($match['__contract']) && !empty($match['__arguments'])) {
            // A match has been found, use it
            $this->decorate($match['__contract'], ...$match['__arguments']);
        }

        if (!$silent) {
            // No match could be found
            throw new MissingDataException('No match could be found for this URI.', 500);
        }
    }

    /**
     * @return array
     */
    public function getMatchables(): array
    {
        return (array)config('decorators.route_matchables');
    }
}