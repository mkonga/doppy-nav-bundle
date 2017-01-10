<?php

namespace Doppy\NavBundle\Nav;

class Attributes
{
    /**
     * Attributes storage.
     *
     * @var array
     */
    protected $attributes = [];

    /**
     * Returns the attributes.
     *
     * @return array An array of attributes
     */
    public function all()
    {
        return $this->attributes;
    }

    /**
     * Returns the attribute keys.
     *
     * @return array An array of attribute keys
     */
    public function keys()
    {
        return array_keys($this->attributes);
    }

    /**
     * Adds attributes.
     *
     * @param array $attributes An array of attributes
     */
    public function add(array $attributes = array())
    {
        $this->attributes = array_replace($this->attributes, $attributes);
    }

    /**
     * Returns a attribute by name.
     *
     * @param string $key     The key
     * @param mixed  $default The default value if the attribute key does not exist
     *
     * @return mixed
     */
    public function get($key, $default = null)
    {
        return array_key_exists($key, $this->attributes) ? $this->attributes[$key] : $default;
    }

    /**
     * Sets a attribute by name.
     *
     * @param string $key   The key
     * @param mixed  $value The value
     */
    public function set($key, $value)
    {
        $this->attributes[$key] = $value;
    }

    /**
     * Returns true if the attribute is defined.
     *
     * @param string $key The key
     *
     * @return bool true if the attribute exists, false otherwise
     */
    public function has($key)
    {
        return array_key_exists($key, $this->attributes);
    }

    /**
     * Removes a attribute.
     *
     * @param string $key The key
     */
    public function remove($key)
    {
        unset($this->attributes[$key]);
    }

    /**
     * Returns the alphabetic characters of the attribute value.
     *
     * @param string $key     The attribute key
     * @param string $default The default value if the attribute key does not exist
     *
     * @return string The filtered value
     */
    public function getAlpha($key, $default = '')
    {
        return preg_replace('/[^[:alpha:]]/', '', $this->get($key, $default));
    }

    /**
     * Returns the alphabetic characters and digits of the attribute value.
     *
     * @param string $key     The attribute key
     * @param string $default The default value if the attribute key does not exist
     *
     * @return string The filtered value
     */
    public function getAlnum($key, $default = '')
    {
        return preg_replace('/[^[:alnum:]]/', '', $this->get($key, $default));
    }

    /**
     * Returns the digits of the attribute value.
     *
     * @param string $key     The attribute key
     * @param string $default The default value if the attribute key does not exist
     *
     * @return string The filtered value
     */
    public function getDigits($key, $default = '')
    {
        // we need to remove - and + because they're allowed in the filter
        return str_replace(array('-', '+'), '', $this->filter($key, $default, FILTER_SANITIZE_NUMBER_INT));
    }

    /**
     * Returns the attribute value converted to integer.
     *
     * @param string $key     The attribute key
     * @param int    $default The default value if the attribute key does not exist
     *
     * @return int The filtered value
     */
    public function getInt($key, $default = 0)
    {
        return (int) $this->get($key, $default);
    }

    /**
     * Returns the attribute value converted to boolean.
     *
     * @param string $key     The attribute key
     * @param mixed  $default The default value if the attribute key does not exist
     *
     * @return bool The filtered value
     */
    public function getBoolean($key, $default = false)
    {
        return $this->filter($key, $default, FILTER_VALIDATE_BOOLEAN);
    }
}
