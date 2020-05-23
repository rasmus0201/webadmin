<?php

namespace App\Websites\Nginx\ConfigParser;

class DirectiveCollection implements \ArrayAccess
{
    /**
     * The list of directives.
     *
     * @var Directive[]|DirectiveGroup[]
     */
    protected $items = [];

    /**
     * Constructor.
     */
    public function __construct()
    {
        $this->items = [];
    }

    /**
     * Determines whether the DirectiveCollection contains the specified directive name.
     *
     * @param string $name The key to locate in the DirectiveCollection.
     *
     * @return bool
     */
    public function containsDirective($name)
    {
        if (is_null($name) || (gettype($name) != 'string' && gettype($name) != 'integer') || (string)$name == '') {
            throw new \InvalidArgumentException('Name is required. The name must be a string. Value can not be null or empty.');
        }

        return array_key_exists($name, $this->items);
    }

    /**
     * Adds a new directive or group to the collection.
     *
     * @param Directive|DirectiveGroup[] $item The directive or group to add.
     *
     * @return Directive|DirectiveGroup
     */
    public function add($item)
    {
        if ($this->containsDirective($item->name)) {
            throw new \ErrorException(sprintf('Directive `%s` already exists.', $item->name));
        }

        $this->items[$item->name] = $item;

        return end($this->items);
    }

    /**
     * Returns elements count.
     *
     * @return int
     */
    public function count()
    {
        return $this->items != null ? count($this->items) : 0;
    }

    /**
     * Returns the first directive of the collection.
     *
     * @return Directive|DirectiveGroup
     */
    public function first()
    {
        reset($this->items);
        return current($this->items);
    }

    /**
     * Returns the last directive of the collection.
     *
     * @return Directive|DirectiveGroup
     */
    public function last()
    {
        return end($this->items);
    }

    /**
     * Whether or not an offset exists.
     *
     * @param string|int $offset An offset to check for
     *
     * @return bool
     */
    public function offsetExists($offset)
    {
        return isset($this->items[$offset]);
    }

    /**
     * Returns the value at specified offset.
     *
     * @param mixed $offset The offset to retrieve.
     *
     * @return Directive|DirectiveGroup
     */
    public function offsetGet($offset)
    {
        return isset($this->items[$offset]) ? $this->items[$offset] : null;
    }

    /**
     * Assigns a value to the specified offset.
     *
     * @param mixed $offset The offset to assign the value to.
     * @param mixed $value The value to set.
     *
     * @return void
     */
    public function offsetSet($offset, $value)
    {
        $this->items[$offset] = $value;
    }

    /**
     * Unsets an offset.
     *
     * @param mixed $offset The offset to unset.
     * @return void
     */
    public function offsetUnset($offset)
    {
        unset($this->items[$offset]);
    }
}
