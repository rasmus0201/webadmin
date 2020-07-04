<?php

namespace App\Parsers\Nginx;

class DirectiveGroup extends Directive
{
    /**
     * Initializes a new instance of the class with the specified parameters.
     *
     * @param string $groupName Name of the group.
     */
    public function __construct($groupName)
    {
        parent::__construct($groupName, null);
    }

    /**
     * Adds a new directive with specified parameters to the group.
     *
     * @param string|string[]|Directive $value List of parameters or directive instance.
     *
     * @return Directive|DirectiveGroup
     */
    public function addDirective($value, $parameters = null, $childs = null)
    {
        if (is_null($value) || !is_object($value)) {
            return $this->directives->add(new Directive($this->directives->count(), $value));
        } elseif (get_class($value) == Directive::class) {
            $d = new Directive($this->directives->count(), $value->parameters, $value->directives);
            return $this->directives->add($d);
        } else {
            throw new \ErrorException('The value of the specified type is not supported. Expected: string, string array or Directive instance.');
        }
    }

    /**
     * [NOT SUPPORED FOR GROUPS!] Adds new parameter to the parameter collection of the instance.
     *
     * @param object|array $value The value to add.
     *
     * @return void
     */
    public function addParameter($value)
    {
        throw new \ErrorException('Parameters not suppered for groups. It is only for single directives.');
    }
}
