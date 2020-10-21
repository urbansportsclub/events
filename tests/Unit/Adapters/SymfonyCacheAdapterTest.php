<?php

namespace OneFit\Events\Tests\Adapters;

use AvroSchema;
use DG\BypassFinals;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Cache\CacheItem;
use PHPUnit\Framework\MockObject\MockObject;
use OneFit\Events\Adapters\SymfonyCacheAdapter;
use Symfony\Component\Cache\Adapter\FilesystemAdapter;

class SymfonyCacheAdapterTest extends TestCase
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
     * @var SymfonyCacheAdapter
     */
    private $cacheAdapter;

    /** @var MockObject|FilesystemAdapter  */
    private $cacheMock;

    /**
     * @var MockObject|CacheItem
     */
    private $cacheItem;

    /**
     * @return void
     */
    public function setUp(): void
    {
        parent::setUp();
        BypassFinals::enable();

        $this->schemaMock = $this->createMock(AvroSchema::class);
        $this->schemaStub = file_get_contents(__DIR__.'/../stubs/avro-event-schema.json');
        $this->cacheMock = $this->getMockBuilder(FilesystemAdapter::class)
            ->addMethods(['set', 'isHit'])
            ->onlyMethods(['hasItem', 'getItem', 'save'])
            ->getMock();
        //$this->cacheMock = $this->createMock(FilesystemAdapter::class);
        $this->cacheAdapter = new SymfonyCacheAdapter($this->cacheMock);
        $this->cacheItem = $this->getMockBuilder(CacheItem::class)->getMock();
    }

    /** @test */
    public function can_cache_schema_with_id()
    {
        $schemaId = 123;

        $this->schemaMock
            ->expects($this->once())
            ->method('__toString')
            ->willReturn(json_encode($this->schemaStub));

        $this->cacheItem
            ->method('set')
            ->willReturnSelf();

        $this->cacheMock
            ->method('getItem')
            ->with($this->makeKeyFromId($schemaId))
            ->willReturn($this->cacheItem);

        $this->cacheAdapter->cacheSchemaWithId($this->schemaMock, $schemaId);
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

        $this->cacheItem
            ->method('set')
            ->willReturnSelf();

        $this->cacheMock
            ->method('getItem')
            ->with($this->makeKeyFromSubjectAndVersion($subject, $version))
            ->willReturn($this->cacheItem);

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

        $this->cacheItem
            ->method('set')
            ->willReturnSelf();

        $this->cacheMock
            ->method('getItem')
            ->with($this->makeKeyFromHash($hash))
            ->willReturn($this->cacheItem);

        $this->cacheAdapter->cacheSchemaIdByHash($schemaId, $hash);
    }

    /** @test */
    public function can_get_with_id()
    {
        $schemaId = 123;

        $this->cacheItem
            ->expects($this->once())
            ->method('isHit')
            ->willReturn(true);

        $this->cacheItem
            ->expects($this->once())
            ->method('get')
            ->willReturn($this->schemaStub);

        $this->cacheMock
            ->method('getItem')
            ->with($this->makeKeyFromId($schemaId))
            ->willReturn($this->cacheItem);

        $this->assertEquals(AvroSchema::parse($this->schemaStub), $this->cacheAdapter->getWithId($schemaId));
    }

    /** @test */
    public function get_with_id_will_return_null_when_no_record_found()
    {
        $schemaId = 123;

        $this->cacheItem
            ->method('isHit')
            ->willReturn(false);

        $this->cacheMock
            ->method('getItem')
            ->willReturn($this->cacheItem);

        $this->assertNull($this->cacheAdapter->getWithId($schemaId));
    }

    /** @test */
    public function can_get_id_with_hash()
    {
        $hash = 'schema-hash';
        $schemaId = '123';

        $this->cacheMock
            ->method('hasItem')
            ->with($this->makeKeyFromHash($hash))
            ->willReturn(true);

        $this->cacheItem
            ->method('get')
            ->willReturn((int) $schemaId);

        $this->cacheMock
            ->method('getItem')
            ->willReturn($this->cacheItem);

        $this->assertSame((int) $schemaId, $this->cacheAdapter->getIdWithHash($hash));
    }

    /** @test */
    public function get_id_with_hash_will_return_null_when_no_record_found()
    {
        $hash = 'schema-hash';

        $this->cacheMock
            ->method('hasItem')
            ->with($this->makeKeyFromHash($hash))
            ->willReturn(false);

        $this->assertNull($this->cacheAdapter->getIdWithHash($hash));
    }

    /** @test */
    public function can_get_with_subject_and_version()
    {
        $subject = 'my-topic';
        $version = 2;

        $this->cacheMock
            ->method('hasItem')
            ->with($this->makeKeyFromSubjectAndVersion($subject, $version))
            ->willReturn(true);

        $this->cacheItem
            ->method('get')
            ->willReturn($this->schemaStub);

        $this->cacheMock
            ->method('getItem')
            ->with($this->makeKeyFromSubjectAndVersion($subject, $version))
            ->willReturn($this->cacheItem);

        $this->assertEquals(AvroSchema::parse($this->schemaStub), $this->cacheAdapter->getWithSubjectAndVersion($subject, $version));
    }

    /** @test */
    public function get_with_subject_and_version_will_return_null_when_no_record_found()
    {
        $subject = 'my-topic';
        $version = 2;

        $this->cacheMock
            ->method('hasItem')
            ->with($this->makeKeyFromSubjectAndVersion($subject, $version))
            ->willReturn(false);

        $this->assertNull($this->cacheAdapter->getWithSubjectAndVersion($subject, $version));
    }

    /** @test */
    public function has_schema_for_id()
    {
        $schemaId = 123;

        $this->cacheMock
            ->method('hasItem')
            ->with($this->makeKeyFromId($schemaId))
            ->willReturn(false);

        $this->assertFalse($this->cacheAdapter->hasSchemaForId($schemaId));
    }

    /** @test */
    public function has_schema_id_for_hash()
    {
        $hash = 'schema-hash';

        $this->cacheMock
            ->method('hasItem')
            ->with($this->makeKeyFromHash($hash))
            ->willReturn(true);

        $this->assertTrue($this->cacheAdapter->hasSchemaIdForHash($hash));
    }

    /** @test */
    public function has_schema_for_subject_and_version()
    {
        $subject = 'my-topic';
        $version = 2;

        $this->cacheMock
            ->method('hasItem')
            ->with($this->makeKeyFromSubjectAndVersion($subject, $version))
            ->willReturn(true);

        $this->assertTrue($this->cacheAdapter->hasSchemaForSubjectAndVersion($subject, $version));
    }

    /**s
     * @param int $key
     * @return string
     */
    private function makeKeyFromId(int $key): string
    {
        return sha1(sprintf('%s::%s::%d', 'OneFit\Events\Adapters\SymfonyCacheAdapter', 'id', $key));
    }

    /**
     * @param  string $subject
     * @param  int    $version
     * @return string
     */
    private function makeKeyFromSubjectAndVersion(string $subject, int $version): string
    {
        return sha1(sprintf('%s::%s::%s_%d', 'OneFit\Events\Adapters\SymfonyCacheAdapter', 'subject_version', $subject, $version));
    }

    /**
     * @param  string $schemaHash
     * @return string
     */
    private function makeKeyFromHash(string $schemaHash): string
    {
        return sha1(sprintf('%s::%s::%s', 'OneFit\Events\Adapters\SymfonyCacheAdapter', 'hash', $schemaHash));
    }
}
