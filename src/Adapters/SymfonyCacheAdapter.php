<?php

namespace OneFit\Events\Adapters;

use AvroSchema;
use Symfony\Component\Cache\Adapter\FilesystemAdapter;
use FlixTech\SchemaRegistryApi\Registry\Cache\CacheAdapter;

class SymfonyCacheAdapter implements CacheAdapter
{
    /** @var FilesystemAdapter */
    private $cache;

    public function __construct(FilesystemAdapter $cache)
    {
        $this->cache = $cache;
    }

    /**
     * Caches an AvroSchema with a given global schema id.
     *
     * @param AvroSchema $schema
     * @param int        $schemaId
     *
     * @return void
     */
    public function cacheSchemaWithId(AvroSchema $schema, int $schemaId): void
    {
        $item = $this->cache->getItem($this->makeKeyFromId($schemaId));
        $item->set((string)$schema);
        $this->cache->save($item);
    }

    /**s
     *
     * @param int $key
     *
     * @return string
     */
    private function makeKeyFromId(int $key): string
    {
        return sha1(sprintf('%s::%s::%d', self::class, 'id', $key));
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
    public function cacheSchemaWithSubjectAndVersion(AvroSchema $schema, string $subject, int $version): void
    {
        $item = $this->cache->getItem($this->makeKeyFromSubjectAndVersion($subject, $version));
        $item->set((string)$schema);
        $this->cache->save($item);
    }

    /**
     * @param string $subject
     * @param int    $version
     *
     * @return string
     */
    private function makeKeyFromSubjectAndVersion(string $subject, int $version): string
    {
        return sha1(sprintf('%s::%s::%s_%d', self::class, 'subject_version', $subject, $version));
    }

    /**
     * Caches a schema id by a hash (i.e. the hash of the Avro schema string representation).
     *
     * @param int    $schemaId
     * @param string $schemaHash
     *
     * @return void
     */
    public function cacheSchemaIdByHash(int $schemaId, string $schemaHash): void
    {
        $item = $this->cache->getItem($this->makeKeyFromHash($schemaHash));
        $item->set((int)$schemaId);
        $this->cache->save($item);
    }

    /**
     * @param string $schemaHash
     *
     * @return string
     */
    private function makeKeyFromHash(string $schemaHash): string
    {
        return sha1(sprintf('%s::%s::%s', self::class, 'hash', $schemaHash));
    }

    /**
     * Tries to fetch a cache with the global schema id.
     * Returns either the AvroSchema when found or `null` when not.
     *
     * @param int $schemaId
     *
     * @return AvroSchema|null
     * @throws \AvroSchemaParseException
     */
    public function getWithId(int $schemaId): ?AvroSchema
    {
        $key = $this->makeKeyFromId($schemaId);
        $item = $this->cache->getItem($key);

        if (!$item->isHit()) {
            return null;
        }

        return AvroSchema::parse($item->get());
    }

    /**
     * Tries to fetch a cached schema id with a given hash.
     * Either returns the schema id as int or `null` when none is found.
     *
     * @param string $hash
     *
     * @return int|null
     */
    public function getIdWithHash(string $hash): ?int
    {
        $key = $this->makeKeyFromHash($hash);

        if (!$this->cache->hasItem($key)) {
            return null;
        }

        return (int)$this->cache->getItem($key)->get();
    }

    /**
     * Tries to fetch a cache with a given subject and version.
     * Returns either the AvroSchema when found or `null` when not.
     *
     * @param string $subject
     * @param int    $version
     *
     * @return AvroSchema|null
     * @throws \AvroSchemaParseException
     */
    public function getWithSubjectAndVersion(string $subject, int $version): ?AvroSchema
    {
        $key = $this->makeKeyFromSubjectAndVersion($subject, $version);

        if (!$this->cache->hasItem($key)) {
            return null;
        }

        return AvroSchema::parse($this->cache->getItem($key)->get());
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
        return $this->cache->hasItem($this->makeKeyFromId($schemaId));
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
        return $this->cache->hasItem($this->makeKeyFromHash($schemaHash));
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
        return $this->cache->hasItem($this->makeKeyFromSubjectAndVersion($subject, $version));
    }
}
