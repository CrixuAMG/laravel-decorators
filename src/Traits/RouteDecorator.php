<?php

namespace CrixuAMG\Decorators\Traits;

use CrixuAMG\Decorators\Exceptions\RouteDecoratorMatchMissingException;
use Illuminate\Support\Facades\App;

trait RouteDecorator
{
    /**
     * @param bool          $silent
     * @param callable|null $errorCallback
     *
     * @return bool
     * @throws RouteDecoratorMatchMissingException
     */
    public function autoregisterRoute(bool $silent = false, callable $errorCallback = null): bool
    {
        if (App::runningInConsole()) {
            // Prevent issues from occurring when clearing cache for example
            return false;
        }

        // Get the source
        $source = $this->getRouteSource();

        if (\in_array($source, (array)config('decorators.ignored_routes'), true)) {
            return true;
        }

        // Get the matchables to check against
        $matchAbles = $this->getRouteMatchables();

        // First try to get it this way
        $match = $this->getDirectMatch($matchAbles, $source);
        $result = $this->checkMatch($match);
        if ($result) {
            $this->decorateMatch($match);

            return true;
        }

        // Parse the source
        $sourceParts = $this->parseRouteSource($source);

        $foundCompleteMatch = null;

        // Go through the parts and try to find a match
        foreach ($sourceParts as $sourcePart) {
            $match = $this->matchRouteData($sourcePart, $matchAbles);

            if ($this->checkMatch($match)) {
                $foundCompleteMatch = $match;
            }

            if (!empty($match)) {
                // A match has been found, but we need to go deeper into the data
                $matchAbles = $match;
            }
        }

        if ($foundCompleteMatch) {
            $this->decorateMatch($foundCompleteMatch);

            return true;
        }

        if (!$silent) {
            // No match could be found
            if ($errorCallback) {
                ($errorCallback)();
            } else {
                throw new RouteDecoratorMatchMissingException(
                    'No decorator match could be found for this route.',
                    500
                );
            }
        }

        return false;
    }

    /**
     * @return string
     */
    private function getRouteSource(): string
    {
        // Get the path and remove any trailing slashes
        return rtrim(ltrim(request()->getPathInfo(), '/'), '/');
    }

    /**
     * @return array
     */
    private function getRouteMatchables(): array
    {
        return (array)config('decorators.route_matchables');
    }

    /**
     * @param array  $matchAbles
     * @param string $source
     *
     * @return mixed
     */
    private function getDirectMatch(array $matchAbles, string $source)
    {
        $sourceParts = explode('/', $source);

        foreach ($sourceParts as $key => $sourcePart) {
            // In this loop, cast any dynamic values to * to support dynamic route matching
            if (is_numeric($sourcePart)) {
                $sourceParts[$key] = '*';
            }
        }

        return data_get($matchAbles, implode('.', $sourceParts));
    }

    /**
     * @param $match
     *
     * @return bool
     */
    private function checkMatch($match): bool
    {
        return \is_array($match) && !empty($match['__contract']) && !empty($match['__arguments']);
    }

    /**
     * @param $match
     *
     * @return void
     */
    private function decorateMatch($match): void
    {
        $this->decorate($match['__contract'], $match['__arguments']);
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
}
