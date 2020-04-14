<?php

namespace OneFit\Events\Tests\Adapters;

use AvroSchema;
use PHPUnit\Framework\TestCase;
use Illuminate\Support\Facades\Cache;
use OneFit\Events\Adapters\CacheAdapter;
use PHPUnit\Framework\MockObject\MockObject;

class CacheAdapterTest extends TestCase
{
    /**
     * @var AvroSchema|MockObject
     */
    private $schemaMock;

    /**
     * @var string
     */
    private $schemaStub;

    /**
     * @var CacheAdapter
     */
    private $cacheAdapter;

    /**
     * @return void
     */
    public function setUp(): void
    {
        parent::setUp();

        $this->schemaMock = $this->createMock(AvroSchema::class);
        $this->schemaStub = file_get_contents(__DIR__.'/../stubs/checkin.json');
        $this->cacheAdapter = new CacheAdapter();
    }

    /** @test */
    public function can_cache_schema_with_id()
    {
        $schemaId = 123;

        $this->schemaMock
            ->expects($this->once())
            ->method('__toString')
            ->willReturn(json_encode($this->schemaStub));

        Cache::shouldReceive('add')
            ->once()
            ->with($this->makeKeyFromId($schemaId), json_encode($this->schemaStub), null);

        $this->cacheAdapter->cacheSchemaWithId($this->schemaMock, 123);
    }

    /** @test */
    public function can_cache_schema_with_subject_and_version()
    {
        $subject = 'my-topic';
        $version = 2;

        $this->schemaMock
            ->expects($this->once())
            ->method('__toString')
            ->willReturn(json_encode($this->schemaStub));

        Cache::shouldReceive('add')
            ->once()
            ->with($this->makeKeyFromSubjectAndVersion($subject, $version), json_encode($this->schemaStub), null);

        $this->cacheAdapter->cacheSchemaWithSubjectAndVersion($this->schemaMock, $subject, $version);
    }

    /** @test */
    public function can_cache_schema_id_by_hash()
    {
        $schemaId = 123;
        $hash = 'schema-hash';

        $this->schemaMock
            ->expects($this->never())
            ->method('__toString');

        Cache::shouldReceive('add')
            ->once()
            ->with($this->makeKeyFromHash($hash), $schemaId, null);

        $this->cacheAdapter->cacheSchemaIdByHash($schemaId, $hash);
    }

    /** @test */
    public function can_get_with_id()
    {
        $schemaId = 123;

        Cache::shouldReceive('has')
            ->once()
            ->with($this->makeKeyFromId($schemaId))
            ->andReturn(true);

        Cache::shouldReceive('get')
            ->once()
            ->with($this->makeKeyFromId($schemaId))
            ->andReturn($this->schemaMock);

        $this->assertSame($this->schemaMock, $this->cacheAdapter->getWithId($schemaId));
    }

    /** @test */
    public function get_with_id_will_return_null_when_no_record_found()
    {
        $schemaId = 123;

        Cache::shouldReceive('has')
            ->once()
            ->with($this->makeKeyFromId($schemaId))
            ->andReturn(false);

        Cache::shouldReceive('get')
            ->never();

        $this->assertNull($this->cacheAdapter->getWithId($schemaId));
    }

    /** @test */
    public function can_get_id_with_hash()
    {
        $hash = 'schema-hash';
        $schemaId = 123;

        Cache::shouldReceive('has')
            ->once()
            ->with($this->makeKeyFromHash($hash))
            ->andReturn(true);

        Cache::shouldReceive('get')
            ->once()
            ->with($this->makeKeyFromHash($hash))
            ->andReturn($schemaId);

        $this->assertSame($schemaId, $this->cacheAdapter->getIdWithHash($hash));
    }

    /** @test */
    public function get_id_with_hash_will_return_null_when_no_record_found()
    {
        $hash = 'schema-hash';

        Cache::shouldReceive('has')
            ->once()
            ->with($this->makeKeyFromHash($hash))
            ->andReturn(false);

        Cache::shouldReceive('get')
            ->never();

        $this->assertNull($this->cacheAdapter->getIdWithHash($hash));
    }

    /** @test */
    public function can_get_with_subject_and_version()
    {
        $subject = 'my-topic';
        $version = 2;

        Cache::shouldReceive('has')
            ->once()
            ->with($this->makeKeyFromSubjectAndVersion($subject, $version))
            ->andReturn(true);

        Cache::shouldReceive('get')
            ->once()
            ->with($this->makeKeyFromSubjectAndVersion($subject, $version))
            ->andReturn($this->schemaMock);

        $this->assertSame($this->schemaMock, $this->cacheAdapter->getWithSubjectAndVersion($subject, $version));
    }

    /** @test */
    public function get_with_subject_and_version_will_return_null_when_no_record_found()
    {
        $subject = 'my-topic';
        $version = 2;

        Cache::shouldReceive('has')
            ->once()
            ->with($this->makeKeyFromSubjectAndVersion($subject, $version))
            ->andReturn(false);

        Cache::shouldReceive('get')
            ->never();

        $this->assertNull($this->cacheAdapter->getWithSubjectAndVersion($subject, $version));
    }

    /** @test */
    public function has_schema_for_id()
    {
        $schemaId = 123;

        Cache::shouldReceive('has')
            ->once()
            ->with($this->makeKeyFromId($schemaId))
            ->andReturn(false);

        $this->assertFalse($this->cacheAdapter->hasSchemaForId($schemaId));
    }

    /** @test */
    public function has_schema_id_for_hash()
    {
        $hash = 'schema-hash';

        Cache::shouldReceive('has')
            ->once()
            ->with($this->makeKeyFromHash($hash))
            ->andReturn(true);

        $this->assertTrue($this->cacheAdapter->hasSchemaIdForHash($hash));
    }

    /** @test */
    public function has_schema_for_subject_and_version()
    {
        $subject = 'my-topic';
        $version = 2;

        Cache::shouldReceive('has')
            ->once()
            ->with($this->makeKeyFromSubjectAndVersion($subject, $version))
            ->andReturn(true);

        $this->assertTrue($this->cacheAdapter->hasSchemaForSubjectAndVersion($subject, $version));
    }

    /**s
     * @param int $key
     * @return string
     */
    private function makeKeyFromId(int $key): string
    {
        return sha1(sprintf('%s::%s::%d', 'OneFit\Events\Adapters\CacheAdapter', 'id', $key));
    }

    /**
     * @param  string $subject
     * @param  int    $version
     * @return string
     */
    private function makeKeyFromSubjectAndVersion(string $subject, int $version): string
    {
        return sha1(sprintf('%s::%s::%s_%d', 'OneFit\Events\Adapters\CacheAdapter', 'subject_version', $subject, $version));
    }

    /**
     * @param  string $schemaHash
     * @return string
     */
    private function makeKeyFromHash(string $schemaHash): string
    {
        return sha1(sprintf('%s::%s::%s', 'OneFit\Events\Adapters\CacheAdapter', 'hash', $schemaHash));
    }
}
