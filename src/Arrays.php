<?php

declare(strict_types=1);

namespace Atomastic\Arrays;

use function array_merge;
use function array_reverse;
use function array_shift;
use function arsort;
use function asort;
use function count;
use function explode;
use function function_exists;
use function is_array;
use function is_null;
use function mb_strtolower;
use function natsort;
use function print_r;
use function strpos;
use function strtolower;
use function strval;

use const SORT_NATURAL;
use const SORT_REGULAR;

class Arrays
{
    /**
     * The underlying array items.
     *
     * @var array
     */
    protected $items = [];

    /**
     * Create a new arrayable object from the given elements.
     *
     * Initializes a Arrays object and assigns $items the supplied values.
     *
     * @param mixed $items Elements
     */
    public function __construct(array $items = [])
    {
        $this->items = (array) $items;
    }

    /**
     * Create a new arrayable object from the given elements.
     *
     * Initializes a Arrays object and assigns $items the supplied values.
     *
     * @param mixed $items Elements
     */
    public static function create(array $items = []): Arrays
    {
        return new Arrays($items);
    }

    /**
     * Set an array item to a given value using "dot" notation.
     *
     * If no key is given to the method, the entire array will be replaced.
     *
     * @param  string $key   Key
     * @param  mixed  $value Value
     */
    public function set(string $key, $value): self
    {
        $array = &$this->items;

        if (is_null($key)) {
            return $array = $value;
        }

        $segments = explode('.', $key);

        foreach ($segments as $i => $segment) {
            if (count($segments) === 1) {
                break;
            }

            unset($segments[$i]);

            if (! isset($array[$segment]) || ! is_array($array[$segment])) {
                $array[$segment] = [];
            }

            $array = &$array[$segment];
        }

        $array[array_shift($segments)] = $value;

        return $this;
    }

    /**
     * Checks if the given dot-notated key exists in the array.
     *
     * @param  string|array $keys Keys
     */
    public function has($keys): bool
    {
        $array = $this->items;

        $keys = (array) $keys;

        if (! $array || $keys === []) {
            return false;
        }

        foreach ($keys as $key) {
            $subKeyArray = $array;

            if (isset($array[$key])) {
                continue;
            }

            foreach (explode('.', $key) as $segment) {
                if (! is_array($subKeyArray) || ! isset($subKeyArray[$segment])) {
                    return false;
                }

                $subKeyArray = $subKeyArray[$segment];
            }
        }

        return true;
    }

    /**
     * Get an item from an array using "dot" notation.
     *
     * @param  string|int|null $key     Key
     * @param  mixed           $default Default
     */
    public function get($key, $default = null)
    {
        $array = $this->items;

        if (! is_array($array)) {
            return $default;
        }

        if (is_null($key)) {
            return $array;
        }

        if (isset($array[$key])) {
            return $array[$key];
        }

        if (strpos($key, '.') === false) {
            return $array[$key] ?? $default;
        }

        foreach (explode('.', $key) as $segment) {
            if (! is_array($array) || ! isset($array[$segment])) {
                return $default;
            }

            $array = $array[$segment];
        }

        return $array;
    }

    /**
     * Deletes an array value using "dot notation".
     *
     * @param  array|string $keys Keys
     */
    public function delete($keys): self
    {
        $array = $this->items;

        $original = &$array;

        $keys = (array) $keys;

        if (count($keys) === 0) {
            return self;
        }

        foreach ($keys as $key) {
            if (isset($array[$key])) {
                unset($array[$key]);
                continue;
            }

            $segements = explode('.', $key);

            $array = &$original;

            while (count($segements) > 1) {
                $segement = array_shift($segements);

                if (! isset($array[$segement]) || ! is_array($array[$segement])) {
                    continue 2;
                }

                $array = &$array[$segement];
            }

            unset($array[array_shift($segements)]);
        }

        $this->items = $array;

        return $this;
    }

    /**
     * Expands a dot notation array into a full multi-dimensional array.
     */
    public function undot(): self
    {
        $array = $this->items;

        $this->items = [];

        foreach ($array as $key => $value) {
            $this->set($key, $value);
        }

        return $this;
    }

    /**
     * Flatten a multi-dimensional associative array with dots.
     *
     * @param  string $prepend Prepend string
     */
    public function dot(string $prepend = ''): self
    {
        $_dot = static function ($array, $prepend) use (&$_dot) {
            $results = [];

            foreach ($array as $key => $value) {
                if (is_array($value) && ! empty($value)) {
                    $results = array_merge($results, $_dot($value, $prepend . $key . '.'));
                } else {
                    $results[$prepend . $key] = $value;
                }
            }

            return $results;
        };

        $this->items = $_dot($this->items, $prepend);

        return $this;
    }

    /**
     * Flush all values from the array.
     */
    public function flush(): void
    {
        $this->items = [];
    }

    /**
     * Sorts a multi-dimensional associative array by a certain field.
     *
     * @param  string $field      The name of the field path
     * @param  string $direction  Order type DESC (descending) or ASC (ascending)
     * @param  const  $sort_flags A PHP sort method flags.
     */
    public function sortAssoc(string $field, string $direction = 'ASC', $sort_flags = SORT_REGULAR): self
    {
        $array = $this->items;

        if (count($array) <= 0) {
            return self;
        }

        foreach ($array as $key => $row) {
            $helper[$key] = function_exists('mb_strtolower') ? mb_strtolower(strval(static::create($row)->get($field))) : strtolower(strval(static::create($row)->get($field)));
        }

        if ($sort_flags === SORT_NATURAL) {
            natsort($helper);
            ($direction === 'DESC') and $helper = array_reverse($helper);
        } elseif ($direction === 'DESC') {
            arsort($helper, $sort_flags);
        } else {
            asort($helper, $sort_flags);
        }

        foreach ($helper as $key => $val) {
            $result[$key] = $array[$key];
        }

        $this->items = $result;

        return $this;
    }

    /**
     * Return the number of items in a given key.
     *
     * @param  int|string|null $key
     */
    public function count($key = null): int
    {
        return count($this->get($key));
    }

    /**
     *  Get all items from stored array.
     */
    public function all(): array
    {
        return $this->items;
    }
}
