<?php

namespace Enriko\LaravelAttributeRestrictor\Traits;

trait RestrictedAttributes {
    /**
     * @return array An array of all the attribute names you want to restrict
     */
    abstract public static function getRestrictedAttributes() : array;
    /**
     * MUST return an condition depending on the attribute. Can be used with match, switch or anyway
     * you like.
     * @param string $attr The attribute being evaluated
     * @return callable|bool
     */
    abstract public static function getAttributeRestrictions(string $attr) : callable|bool;

    /**
     * Global restriction for the entire model. If the return (either bool or the callable's return)
     * is false, all attributes will be replaced by the text set on restrictedText
     * @return callable|bool The callable MUST return a bool, else the restriction might have unexpected
     * behavior due to typecasting.
     */
    public static function getGlobalRestriction() : callable|bool {
        return true; // doesn't restrict, by default
    }

    /**
     * MUST return a array with each restricted attribute and it's evaluator.
     * The evaluator MUST be a bool or a callable.
     * Takes precedence over getAttributeRestrictions, but is virtually the same
     * @return array Array of bools | callables. The key must be the attribute name
     */
    public static function getAttributeRestrictionsArray() : array
    {
        return [];
        // Examples
        // return [
        //     'id' => false, // will always restrict
        //     'name' => fn() => rand() % 2 === 0,
        // ];
    }

    /**
     * Use's the original attributesToArray, then check if the (already cast) attributes
     * need to be treated.
     * It get the attributes first, because the original method already uses the "hidden"
     * attribute, so it's overall less unecessary loops.
     */
    public function attributesToArray()
    {
        $attributes = parent::attributesToArray();

        foreach ($attributes as $key => &$value)
        {
            // If the attribute should be restricted, tries to restrict it
            if ($this->tryRestriction($key))
                $value = $this->getReplacedText();
        }

        return $attributes;
    }

    public function getAttributeValue($key)
    {
        if ($this->tryRestriction($key)) {
            return $this->getReplacedText();
        }

        // Return the attribute, unaltered
        return parent::getAttributeValue($key);
    }
 
    /**
     * Checks all types of restriction
     */
    private function tryRestriction($key): bool
    {
        // Check if it should be restricted first, to get it out of the way
        if (!$this->shouldBeRestricted($key)) {
            // Return the attribute, unaltered
            return false;
        }

        // Check if global restriction's condition is false
        if ($this->isGloballyRestricted()) {
            return true;
        }
    
        // Check if specific attribute's condition is false (array)
        if ($this->isAttributeRestrictedByArray($key)) {
            return true;
        }

        // Check if specific attribute's condition is false
        if ($this->isAttributeRestricted($key)) {
            return true;
        }

        return false;
    }

    private function isGloballyRestricted(): bool
    {
        $globalRestrictor = $this->getGlobalRestriction();
    
        return is_callable($globalRestrictor) 
            ? call_user_func($globalRestrictor) === false
            : !$globalRestrictor;
    }
    
    private function isAttributeRestrictedByArray(string $key): bool
    {
        $attributeRestrictor = $this->getAttributeRestrictionsArray();

        if (!array_key_exists($key, $attributeRestrictor))
            return false;
    
        return is_callable($attributeRestrictor[$key])
            ? call_user_func($attributeRestrictor[$key]) === false
            : !$attributeRestrictor[$key];
    }

    private function isAttributeRestricted(string $key): bool
    {    
        $attributeRestrictor = $this->getAttributeRestrictions($key);
    
        return is_callable($attributeRestrictor)
            ? call_user_func($attributeRestrictor) === false
            : !$attributeRestrictor;
    }

    /**
     * Check if attribute is on getRestrictedAttributes
     */ 
    private function shouldBeRestricted(string $key): bool
    {
        return in_array($key, $this->getRestrictedAttributes());
    }

    private function getReplacedText()
    {
        return config('restrictedText');
    }
}