<?php

namespace OneFit\Events\Adapters;

use AvroSchema;
use Illuminate\Support\Facades\Cache;

class CacheAdapter implements \FlixTech\SchemaRegistryApi\Registry\CacheAdapter
{
    /**
     * Caches an AvroSchema with a given global schema id.
     *
     * @param AvroSchema $schema
     * @param int        $schemaId
     *
     * @return void
     */
    public function cacheSchemaWithId(AvroSchema $schema, int $schemaId)
    {
        Cache::add($this->makeKeyFromId($schemaId), (string) $schema, null);
    }

    /**
     * Caches an AvroSchema with a given subject and version.
     *
     * @param AvroSchema $schema
     * @param string     $subject
     * @param int        $version
     *
     * @return void
     */
    public function cacheSchemaWithSubjectAndVersion(AvroSchema $schema, string $subject, int $version)
    {
        Cache::add($this->makeKeyFromSubjectAndVersion($subject, $version), (string) $schema, null);
    }

    /**
     * Caches a schema id by a hash (i.e. the hash of the Avro schema string representation).
     *
     * @param int    $schemaId
     * @param string $schemaHash
     *
     * @return void
     */
    public function cacheSchemaIdByHash(int $schemaId, string $schemaHash)
    {
        Cache::add($this->makeKeyFromHash($schemaHash), $schemaId, null);
    }

    /**
     * Tries to fetch a cache with the global schema id.
     * Returns either the AvroSchema when found or `null` when not.
     *
     * @param int $schemaId
     *
     * @return AvroSchema|null
     */
    public function getWithId(int $schemaId)
    {
        $key = $this->makeKeyFromId($schemaId);

        return Cache::has($key) ? Cache::get($key) : null;
    }

    /**
     * Tries to fetch a cached schema id with a given hash.
     * Either returns the schema id as int or `null` when none is found.
     *
     * @param string $hash
     *
     * @return int|null
     */
    public function getIdWithHash(string $hash)
    {
        $key = $this->makeKeyFromHash($hash);

        return Cache::has($key) ? Cache::get($key) : null;
    }

    /**
     * Tries to fetch a cache with a given subject and version.
     * Returns either the AvroSchema when found or `null` when not.
     *
     * @param string $subject
     * @param int    $version
     *
     * @return AvroSchema|null
     */
    public function getWithSubjectAndVersion(string $subject, int $version)
    {
        $key = $this->makeKeyFromSubjectAndVersion($subject, $version);

        return Cache::has($key) ? Cache::get($key) : null;
    }

    /**
     * Checks if the cache engine has a cached schema for a given global schema id.
     *
     * @param int $schemaId
     *
     * @return bool
     */
    public function hasSchemaForId(int $schemaId): bool
    {
        return Cache::has($this->makeKeyFromId($schemaId));
    }

    /**
     * Checks if a schema id exists for the given hash.
     *
     * @param string $schemaHash
     *
     * @return bool
     */
    public function hasSchemaIdForHash(string $schemaHash): bool
    {
        return Cache::has($this->makeKeyFromHash($schemaHash));
    }

    /**
     * Checks if the cache engine has a cached schema for a given subject and version.
     *
     * @param string $subject
     * @param int    $version
     *
     * @return bool
     */
    public function hasSchemaForSubjectAndVersion(string $subject, int $version): bool
    {
        return Cache::has($this->makeKeyFromSubjectAndVersion($subject, $version));
    }

    /**s
     * @param int $key
     * @return string
     */
    private function makeKeyFromId(int $key): string
    {
        return sha1(sprintf('%s::%s::%d', self::class, 'id', $key));
    }

    /**
     * @param  string $subject
     * @param  int    $version
     * @return string
     */
    private function makeKeyFromSubjectAndVersion(string $subject, int $version): string
    {
        return sha1(sprintf('%s::%s::%s_%d', self::class, 'subject_version', $subject, $version));
    }

    /**
     * @param  string $schemaHash
     * @return string
     */
    private function makeKeyFromHash(string $schemaHash): string
    {
        return sha1(sprintf('%s::%s::%s', self::class, 'hash', $schemaHash));
    }
}
