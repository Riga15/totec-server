<?php

/**
 * Apcu対応のため\Phalcon\Cache\Backendの拡張
 * https://github.com/phalcon/cphalcon/blob/master/phalcon/cache/backend/apc.zep
 * 使い方はPhalcon\Cache\Backend\Apcと同じ
 * $cache = new Apc($frontCache, [
 * 'prefix' => 'app-data'
 * ]);
 * </code>.
 */
class Apcu extends \Phalcon\Cache\Backend implements \Phalcon\Cache\BackendInterface
{
    /**
     * Returns a cached content.
     *
     * @param string $keyName
     * @param int    $lifetime
     *
     * @return mixed|null
     */
    public function get($keyName, $lifetime = null)
    {
        $prefixedKey = '_PHCA' . $this->_prefix . $keyName;
        $this->_lastKey = $prefixedKey;
        $cachedContent = apcu_fetch($prefixedKey);
        return $cachedContent === false ? null : $this->_frontend->afterRetrieve($cachedContent);
    }

    /**
     * Stores cached content into the APC backend and stops the frontend.
     *
     * @param string $keyName
     * @param mixed  $content
     * @param int    $lifetime
     * @param bool   $stopBuffer
     *
     * @return bool
     *
     * @throws Exception
     */
    public function save($keyName = null, $content = null, $lifetime = null, $stopBuffer = true) : bool
    {
        $lastKey = ($keyName === null) ? $this->_lastKey : '_PHCA' . $this->_prefix . $keyName;
        if (empty($lastKey)) {
            throw new Exception('Cache must be started first');
        }

        $frontend = $this->_frontend;
        $cachedContent = ($content === null) ? $frontend->getContent() : $content;
        $preparedContent = ! is_numeric($content) ? $frontend->beforeStore($cachedContent) : $cachedContent;
        if ($lifetime === null) {
            $lifetime = $this->_lastLifetime;
            if ($lifetime === null) {
                $ttl = $frontend->getLifetime();
            } else {
                $ttl = $lifetime;
                $this->_lastKey = $lastKey;
            }
        } else {
            $ttl = $lifetime;
        }

        $success = apcu_store($lastKey, $preparedContent, $ttl);
        if ($success === false) {
            throw new Exception('Failed storing data in apcu');
        }

        $isBuffering = $frontend->isBuffering();
        if ($stopBuffer === true) {
            $frontend->stop();
        }

        if ($isBuffering === true) {
            echo $cachedContent;
        }

        $this->_started = false;

        return $success;
    }

    /**
     * Increment of a given key, by number $value.
     *
     * @param string $keyName
     * @param int    $value
     *
     * @return mixed
     */
    public function increment($keyName = null, $value = 1)
    {
        $prefixedKey = '_PHCA' . $this->_prefix . $keyName;
        $this->_lastKey = $prefixedKey;

        return apcu_inc($prefixedKey, $value);
    }

    /**
     * Decrement of a given key, by number $value.
     *
     * @param string $keyName
     * @param int    $value
     *
     * @return mixed
     */
    public function decrement($keyName = null, $value = 1)
    {
        $prefixedKey = '_PHCA' . $this->_prefix . $keyName;
        $this->_lastKey = $prefixedKey;

        return apcu_dec($prefixedKey, $value);
    }

    /**
     * Deletes a value from the cache by its key.
     *
     * @param string $keyName
     *
     * @return bool
     */
    public function delete($keyName) : bool
    {
        $prefixedKey = '_PHCA' . $this->_prefix . $keyName;

        return apcu_delete($prefixedKey);
    }

    /**
     * Query the existing cached keys.
     *
     * @param string $prefix
     *
     * @return array
     */
    public function queryKeys($prefix = null) : array
    {
        $prefixPattern = ($prefix === null) ? '/^_PHCA/' : '/^_PHCA' . prefix . '/';
        $keys = [];
        $it = new \APCUIterator('user', $prefixPattern);
        foreach ($it as $key => $_) {
            $keys[] = substr($key, 5);
        }

        return $keys;
    }

    /**
     * Checks if cache exists and it hasn't expired.
     *
     * @param string|int $keyName
     * @param int        $lifetime
     *
     * @return bool
     */
    public function exists($keyName = null, $lifetime = null) : bool
    {
        $lastKey = ($keyName === null) ? $this->_lastKey : '_PHCA' . $this->_prefix . $keyName;
        if ($lastKey) {
            if (apcu_exists($lastKey) !== false) {
                return true;
            }
        }

        return false;
    }

    /**
     * Immediately invalidates all existing items.
     *
     * @return bool
     */
    public function flush()
    {
        return apcu_clear_cache();
    }

    /**
     * Immediately invalidates all existing items.
     *
     * @return bool
     */
    public static function flushAll()
    {
        return apcu_clear_cache();
    }
}
