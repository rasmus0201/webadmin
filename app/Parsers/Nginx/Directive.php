<?php

namespace App\Parsers\Nginx;

class Directive implements \ArrayAccess
{
    /**
     * The directive name.
     *
     * @var string
     */
    public $name;

    /**
     * The directive parameters.
     *
     * @var string[]
     */
    public $parameters = [];

    /**
     * The list of directives.
     *
     * @var DirectiveCollection
     */
    public $directives = [];

    /**
     * Initializes a new instance of the class with the specified parameters.
     *
     * @param string $name The directive name.
     * @param string|string[] $parameters The list of parameters. Default: NULL.
     * @param DirectiveCollection $childs The collection of children. Default: NULL.
     */
    public function __construct($name, $parameters = null, $childs = null)
    {
        if (is_null($name) || (gettype($name) != 'string' && gettype($name) != 'integer') || (string)$name == '') {
            throw new \InvalidArgumentException('Name is required. The name must be a string. Value can not be null or empty.');
        }

        if (!is_null($childs) && gettype($childs) != 'object' && get_class($childs) != DirectiveCollection::class) {
            throw new \InvalidArgumentException("The parameter \$childs expected values of the DirectiveCollection::class type.");
        }

        $this->name = $name;
        $this->directives = ($childs != null ? $childs : new DirectiveCollection());

        if (!is_null($parameters)) {
            $this->parameters = [];
            if (is_array($parameters)) {
                $this->parameters = $parameters;
            } else {
                $this->parameters[] = $parameters;
            }
        }
    }

    /**
     * Returns a string of parameters separated by a space.
     *
     * @return null|string
     */
    public function parametersAsString()
    {
        if (isset($this->parameters) && count($this->parameters) > 0) {
            $result = '';
            foreach ($this->parameters as $parameter) {
                if ($parameter == '') {
                    continue;
                }
                if (preg_match('/[\s\{\}\#\;]+/', $parameter) === 1) {
                    $parameter = '"'.$parameter.'"';
                }
                if ($result != '') {
                    $result .= ' ';
                }
                $result .= $parameter;
            }

            return $result;
        } else {
            return null;
        }
    }

    /**
     * Adds new parameter to the parameter collection of the instance.
     *
     * @param object|array $value The value to add.
     *
     * @return void
     */
    public function addParameter($value)
    {
        if (!isset($this->parameters) || $this->parameters == null) {
            $this->parameters = [];
        }

        if (is_array($value)) {
            array_merge($this->parameters, $value);
        } else {
            $this->parameters[] = $value;
        }
    }

    /**
     * Adds a new directive to the directive collection of the instance.
     *
     * @param string|Directive|DirectiveGroup $nameOrInstance The directive name or an instance of the Directive or DirectiveGroup.
     * @param string|string[] $parameters The list of parameters. If it the $nameOrInstance specified instance, and this parameter is not NULL, it will use this value.
     * @param DirectiveCollection $childs The collection of children. If it the $nameOrInstance specified instance, and this parameter is not NULL, it will use this value.
     *
     * @return Directive|DirectiveGroup
     */
    public function addDirective($nameOrInstance, $parameters = null, $childs = null)
    {
        if (!is_null($childs) && gettype($childs) != 'object' && get_class($childs) != DirectiveCollection::class) {
            throw new \InvalidArgumentException("The parameter \$childs expected values of the DirectiveCollection::class type.");
        }

        $is_group = false;

        if (gettype($nameOrInstance) == 'object' && (($class = get_class($nameOrInstance)) == Directive::class || $class == DirectiveGroup::class)) {
            // Is directive instance
            if ($parameters == null) {
                $parameters = $nameOrInstance->parameters;
            }

            if ($childs == null) {
                $childs = $nameOrInstance->directives;
            }

            $is_group = $nameOrInstance->isGroup();

            $nameOrInstance = $nameOrInstance->name;
        }

        if ($this->containsChild($nameOrInstance)) {
            // Is not unique derictive name and not group, transform to group
            if (!$this->directives[$nameOrInstance]->isGroup()) {
                $group = new DirectiveGroup($nameOrInstance);
                $group->addDirective($this->directives[$nameOrInstance]);
                $group->addDirective(new Directive($nameOrInstance, $parameters, $childs));
                $this->directives[$nameOrInstance] = $group;
            } else {
                // Is group
                $this->directives[$nameOrInstance]->directives->add(new Directive($this->directives[$nameOrInstance]->childCount(), $parameters, $childs));
            }

            // Return group
            return $this->directives[$nameOrInstance];
        } else {
            // New directive
            if ($is_group) {
                $g = $this->directives->add(new DirectiveGroup($nameOrInstance));
                $g->directives = $childs;
            } else {
                $this->directives->add(new Directive($nameOrInstance, $parameters, $childs));
            }

            // Return added directive
            return $this->lastChild();
        }
    }

    /**
     * Determines the directive is group or single.
     *
     * @return bool
     */
    public function isGroup()
    {
        return get_class($this) == DirectiveGroup::class;
    }

    /**
     * Determines the directive is block or not.
     *
     * Block directives contain many nested directives.
     *
     * @return bool
     */
    public function isBlock()
    {
        return !$this->isSimple();
    }

    /**
     * Determines the directive is simple or not.
     *
     * Simple directives contain only parameters.
     *
     * @return bool|null
     */
    public function isSimple()
    {
        if ($this->isGroup()) {
            if ($this->hasChild()) {
                return $this->firstChild()->isSimple();
            } else {
                return null;
            }
        } else {
            return !$this->hasChild();
        }
    }

    /**
     * Returns true, if the directive has parameters. Otherwise, it returns false.
     *
     * @return bool
     */
    public function hasParameters()
    {
        return isset($this->parameters) && count($this->parameters) > 0;
    }

    /**
     * Returns parameters count.
     *
     * @return int
     */
    public function parametersCount()
    {
        return isset($this->parameters) ? count($this->parameters) : 0;
    }

    /**
     * Returns true, if the directive has child elements. Otherwise, it returns false.
     *
     * @return bool
     */
    public function hasChild()
    {
        return $this->directives->count() > 0;
    }

    /**
     * Returns child directives count.
     *
     * @return int
     */
    public function childCount()
    {
        return $this->directives->count();
    }

    /**
     * Returns the first child directive of the current instance.
     *
     * @return Directive|DirectiveGroup
     */
    public function firstChild()
    {
        return $this->directives->first();
    }

    /**
     * Returns the last child directive of the current instance.
     *
     * @return Directive|DirectiveGroup
     */
    public function lastChild()
    {
        return $this->directives->last();
    }

    /**
     * Determines whether the child collection contains the specified directive name.
     *
     * @param string $name The key to locate in the DirectiveCollection.
     *
     * @return bool
     */
    public function containsChild($name)
    {
        return $this->directives->containsDirective($name);
    }

    /**
     * Returns an array of directives.
     *
     * @return Directive[]
     */
    public function toArray()
    {
        if ($this->isGroup()) {
            return $this->directives->items;
        } else {
            return [$this];
        }
    }

    /**
     * Adds the current instance to separated instance of Directive.
     *
     * @param Directive|DirectiveGroup $parent The Directive instance in which you want to add the current instance.
     *
     * @return Directive|DirectiveGroup|null
     */
    public function addTo($parent)
    {
        if (is_null($parent)) {
            throw new \InvalidArgumentException('Parent can not be null.');
        }

        return $parent->addDirective($this);
    }

    /**
     * Whether or not an offset exists.
     *
     * @param string|int $offset An offset to check for.
     *
     * @return bool
     */
    public function offsetExists($offset)
    {
        if (is_int($offset)) {
            return isset($this->parameters[$offset]);
        } else {
            return isset($this->directives[$offset]);
        }
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
        if (is_int($offset)) {
            if ($this->isGroup()) {
                return isset($this->directives[$offset]) ? $this->directives[$offset] : null;
            } else {
                return isset($this->parameters[$offset]) ? $this->parameters[$offset] : null;
            }
        } else {
            return isset($this->directives[$offset]) ? $this->directives[$offset] : null;
        }
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
        if (is_null($offset)) {
            throw new \ErrorException('Unable to determine the type of value. Use an explicit assignment of values through the properties.');
        } elseif (is_int($offset)) {
            $this->parameters[$offset] = $value;
        } else {
            $this->directives[$offset] = $value;
        }
    }

    /**
     * Unsets an offset.
     *
     * @param mixed $offset The offset to unset.
     *
     * @return void
     */
    public function offsetUnset($offset)
    {
        unset($this->directives[$offset]);
    }
}
