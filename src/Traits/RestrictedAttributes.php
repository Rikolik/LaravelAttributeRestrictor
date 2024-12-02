<?php

namespace App\Traits;

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
     * is false, all attributes will be replaced by the text set on RESTRICTED_REPLACEMENT
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
        // return [
        //     'id' => false, // will always restrict
        //     'name' => fn() => rand() % 2 === 0,
        // ];
    }

    public function getAttribute($key)
    {
        // Check if global restriction's condition is false
        if ($this->isGloballyRestricted()) {
            return $this->getReplacedText();
        }
    
        // Check if specific attribute's condition is false (array)
        if ($this->isAttributeRestrictedByArray($key)) {
            return $this->getReplacedText();
        }

        // Check if specific attribute's condition is false
        if ($this->isAttributeRestricted($key)) {
            return $this->getReplacedText();
        }

        // Return the attribute, unaltered
        return parent::getAttribute($key);
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
        if (!$this->shouldBeRestricted($key))
            return false;

        $attributeRestrictor = $this->getAttributeRestrictionsArray();

        return is_callable($attributeRestrictor[$key])
            ? call_user_func($attributeRestrictor[$key]) === false
            : !$attributeRestrictor[$key];
    }

    private function isAttributeRestricted(string $key): bool
    {
        if (!$this->shouldBeRestricted($key))
            return false;
    
        $attributeRestrictor = $this->getAttributeRestrictions($key);
    
        return is_callable($attributeRestrictor)
            ? call_user_func($attributeRestrictor) === false
            : !$attributeRestrictor;
    }

    // Check if attribute is on getRestrictedAttributes
    private function shouldBeRestricted(string $key): bool
    {
        return in_array($key, $this->getRestrictedAttributes());
    }

    private function getReplacedText()
    {
        return 'Restricted';
    }
}