<?php

namespace Wilr\SilverStripe\Algolia\Tests;

use SilverStripe\Core\Injector\Injector;
use SilverStripe\Dev\SapphireTest;
use SilverStripe\ORM\DataObjectSchema;
use Wilr\SilverStripe\Algolia\Service\AlgoliaIndexer;
use Wilr\SilverStripe\Algolia\Service\AlgoliaService;

class AlgoliaIndexerTest extends SapphireTest
{
    protected $usesDatabase = true;

    protected static $extra_dataobjects = [
        AlgoliaTestObject::class
    ];

    public static function setUpBeforeClass()
    {
        parent::setUpBeforeClass();

        // mock AlgoliaService
        Injector::inst()->get(DataObjectSchema::class)->reset();
        Injector::inst()->registerService(new TestAlgoliaService(), AlgoliaService::class);
    }

    public function testExportAttributesForObject()
    {
        $object = AlgoliaTestObject::create();
        $object->Title = 'Foobar';
        $object->write();
        $indexer = Injector::inst()->get(AlgoliaIndexer::class);
        $map = $indexer->exportAttributesFromObject($object)->toArray();

        $this->assertArrayHasKey('objectID', $map);
        $this->assertEquals($map['objectTitle'], 'Foobar');
    }

    public function testDeleteExistingItem()
    {
        $object = AlgoliaTestObject::create();
        $object->Title = 'Delete This';
        $object->assignAlgoliaUUID();
        $object->write();

        $indexer = Injector::inst()->get(AlgoliaIndexer::class);
        $deleted = $indexer->deleteItem($object->getClassName(), $object->AlgoliaUUID);

        return $this->assertTrue($deleted);
    }

    public function testDeleteNonExistentItem()
    {
        $indexer = Injector::inst()->get(AlgoliaIndexer::class);
        $deleted = $indexer->deleteItem(AlgoliaTestObject::class, 9999999);

        return $this->assertFalse($deleted);
    }
}
