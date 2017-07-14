<?php

namespace X509\Feature;

use X501\ASN1\Attribute;
use X501\ASN1\AttributeType;

/**
 * Trait for objects containing X.501 attributes.
 *
 * Implements methods for Countable and IteratorAggregate interfaces.
 */
trait AttributeContainer
{
    /**
     * Array of attributes.
     *
     * @var Attribute[] $_attributes
     */
    protected $_attributes;
    
    /**
     * Find first attribute of given name or OID.
     *
     * @param string $name
     * @return Attribute|null
     */
    protected function _findFirst($name)
    {
        $oid = AttributeType::attrNameToOID($name);
        foreach ($this->_attributes as $attr) {
            if ($attr->oid() == $oid) {
                return $attr;
            }
        }
        return null;
    }
    
    /**
     * Check whether attribute is present.
     *
     * @param string $name OID or attribute name
     * @return boolean
     */
    public function has($name)
    {
        return null !== $this->_findFirst($name);
    }
    
    /**
     * Get first attribute by OID or attribute name.
     *
     * @param string $name OID or attribute name
     * @throws \OutOfBoundsException
     * @return Attribute
     */
    public function firstOf($name)
    {
        $attr = $this->_findFirst($name);
        if (!$attr) {
            throw new \UnexpectedValueException("No $name attribute.");
        }
        return $attr;
    }
    
    /**
     * Get all attributes of given name.
     *
     * @param string $name OID or attribute name
     * @return Attribute[]
     */
    public function allOf($name)
    {
        $oid = AttributeType::attrNameToOID($name);
        $attrs = array_filter($this->_attributes,
            function (Attribute $attr) use ($oid) {
                return $attr->oid() == $oid;
            });
        return array_values($attrs);
    }
    
    /**
     * Get all attributes.
     *
     * @return Attribute[]
     */
    public function all()
    {
        return $this->_attributes;
    }
    
    /**
     * Get self with additional attributes added.
     *
     * @param Attribute ...$attribs
     * @return self
     */
    public function withAdditional(Attribute ...$attribs)
    {
        $obj = clone $this;
        foreach ($attribs as $attr) {
            $obj->_attributes[] = $attr;
        }
        return $obj;
    }
    
    /**
     * Get self with single unique attribute added.
     *
     * All previous attributes of the same type are removed.
     *
     * @param Attribute $attr
     * @return self
     */
    public function withUnique(Attribute $attr)
    {
        $obj = clone $this;
        $obj->_attributes = array_filter($obj->_attributes,
            function (Attribute $a) use ($attr) {
                return $a->oid() != $attr->oid();
            });
        $obj->_attributes[] = $attr;
        return $obj;
    }
    
    /**
     * Get number of attributes.
     *
     * @see \Countable::count()
     * @return int
     */
    public function count()
    {
        return count($this->_attributes);
    }
    
    /**
     * Get iterator for attributes.
     *
     * @see \IteratorAggregate::getIterator()
     * @return \ArrayIterator
     */
    public function getIterator()
    {
        return new \ArrayIterator($this->_attributes);
    }
}
