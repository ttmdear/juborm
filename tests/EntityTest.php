<?php
namespace Juborm\Tests;

use Juborm\Tests\Base;

use Bookstore\Model\Bookstore\Entities\Authors;
use Bookstore\Model\Bookstore\Entities\Warehouse;
use Bookstore\Model\Bookstore\Entities\AuthorsAddresses;
use Bookstore\Model\Bookstore\Entities\Books;
use Bookstore\Model\Bookstore\Entities\Dictionaries;
use Bookstore\Model\Bookstore\Entities\BooksOpinions;

use Exception;

class EntityTest extends Base
{
    public function testArrayAccess()
    {
        $this->initConnection();

        // select the opinion of 3
        $opinions = BooksOpinions::select()
            ->equal('opinion_id', 3)
            ->first()
        ;

        $this->assertEquals($opinions['opinion'], 'Everyone has their history and quirks.');
        $this->assertEquals($opinions['test'], null);
    }

    // insert
    public function testFetchNew()
    {
        $this->initConnection();
        $bookstore = $this->dbDiffer('bookstore');
        $bookstore->snapshot();

        $entity = Authors::fetchNew();
        $entity->set("first_name", "Kowalski");
        $id = $entity->save();

        $bookstore->snapshot();

        $this->assertEquals($id, 4);
        $this->assertEquals($bookstore->numOfCreated(), 1);
        $this->assertEquals($bookstore->numOfChanged(), 1);
    }

    public function testDelete()
    {
        $this->initConnection();

        $bookstore = $this->dbDiffer('bookstore');
        $bookstore->snapshot();

        // select the opinion of 3
        $opinions = BooksOpinions::select()
            ->equal('opinion_id', 3)
            ->first()
        ;

        $this->assertEquals($opinions->get('opinion'), 'Everyone has their history and quirks.');
        $this->assertEquals(($opinions instanceof BooksOpinions), true);

        $result = $opinions->delete();

        $bookstore->snapshot();
        $this->assertEquals(($result instanceof BooksOpinions), true);
        $this->assertEquals($bookstore->numOfChanged(), 1);
        $this->assertEquals($bookstore->numOfDeleted(), 1);

        // add three test row
        BooksOpinions::fetchNew()->set('book_id', 2)->set('opinion', 'test')->save();
        BooksOpinions::fetchNew()->set('book_id', 2)->set('opinion', 'test')->save();
        BooksOpinions::fetchNew()->set('book_id', 2)->set('opinion', 'test')->save();

        $bookstore->snapshot();

        $pk = $opinions->save();

        $bookstore->snapshot();

        $this->assertEquals($pk, 3);
        $this->assertEquals($bookstore->numOfCreated(), 1);
        $this->assertEquals($bookstore->numOfChanged(), 1);
    }

    public function testSave()
    {
        $this->initConnection();

        $bookstore = $this->dbDiffer('bookstore');
        $bookstore->snapshot();

        // select the opinion of 3
        $opinions = BooksOpinions::select()
            ->equal('opinion_id', 3)
            ->first()
        ;

        $opinions->set('opinion', 'test123');
        $opinions->save();

        $bookstore->snapshot();

        $this->assertEquals($bookstore->numOfChanged(), 1);
        $this->assertEquals($bookstore->numOfUpdated(), 1);
    }

    public  function testFields()
    {
        $this->initConnection();

        $result = Authors::fields();
        $result = $this->inline($result);
        $expected = "array(0=>'author_id',1=>'first_name',2=>'last_name',3=>'birth_date',)";

        $this->assertEquals($expected, $result);
    }

    public  function testTable()
    {
        $this->initConnection();

        $result = Authors::table();
        $expected = "authors";

        $this->assertEquals($expected, $result);
    }

    public function testPk()
    {
        $this->initConnection();

        $opinions = BooksOpinions::select()
            ->equal('opinion_id', 3)
            ->first()
        ;

        $result = $opinions->pk();
        $expected = "3";

        $this->assertEquals($expected, $result);
    }

    public function testPkException()
    {
        $this->initConnection();

        $addresses = AuthorsAddresses::select()
            ->equal('author_id', 1)
            ->equal('type', 2)
            ->first()
        ;

        $this->setExpectedException(Exception::class);

        $result = $addresses->pk();
    }

    public  function testHasComplexPk()
    {
        $this->assertEquals(AuthorsAddresses::hasComplexPk(), true);
        $this->assertEquals(Authors::hasComplexPk(), false);
    }

    public function testPkArray()
    {
        $this->initConnection();

        $opinions = BooksOpinions::select()
            ->equal('opinion_id', 3)
            ->first()
        ;
        $result = $opinions->pkArray();
        $result = $this->inline($result);
        $expected = "array('opinion_id'=>'3',)";
        $this->assertEquals($expected, $result);

        $addresses = AuthorsAddresses::select()
            ->equal('author_id', 1)
            ->equal('type', 2)
            ->first()
        ;
        $result = $addresses->pkArray();
        $result = $this->inline($result);
        $expected = "array('author_id'=>'1','type'=>'2',)";
        $this->assertEquals($expected, $result);

    }

    public  function testPkDefinition()
    {
        $result = AuthorsAddresses::pkDefinition();
        $result = $this->inline($result);
        $expected = "array(0=>'author_id',1=>'type',)";
        $this->assertEquals($expected, $result);

        $result = BooksOpinions::pkDefinition();
        $result = $this->inline($result);
        $expected = "array(0=>'opinion_id',)";
        $this->assertEquals($expected, $result);
    }

    public function testGet()
    {
        $this->initConnection();

        $opinions = BooksOpinions::select()
            ->equal('opinion_id', 3)
            ->first()
        ;

        $this->assertEquals($opinions->get('opinion_id'), 3);
        $this->assertEquals($opinions->get('book_id'), 2);
        $this->assertEquals($opinions->get('opinion'), "Everyone has their history and quirks.");
        $this->assertEquals($opinions->get('author'), null);
        $this->assertEquals($opinions->get('noAttrubute'), null);

        // let's make some changes
        $opinions->set('opinion', 'test123');
        $opinions->set('author', 'janek');

        $this->assertEquals($opinions->get('opinion'), "test123");
        $this->assertEquals($opinions->get('author'), "janek");
    }

    public function testSet()
    {
        $this->initConnection();

        $bookstore = $this->dbDiffer('bookstore');
        $bookstore->snapshot();

        $opinions = BooksOpinions::select()
            ->equal('opinion_id', 3)
            ->first()
        ;

        $opinions->set('opinion', 'test123');
        $opinions->set('author', 'janek');
        $opinions->save();

        $bookstore->snapshot();

        $this->assertEquals($bookstore->numOfChanged(), 1);
        $this->assertEquals($opinions->get('opinion'), "test123");
        $this->assertEquals($opinions->get('author'), "janek");
    }

    public function testRelation()
    {
        $this->initConnection();

        $authors = Authors::select()
            ->equal('author_id', 1)
            ->first()
        ;

        $select = $authors->relation("books");
        $result = $select->fetchAll();
        $result = $this->inline($result);
        $expected = "array(0=>array('book_id'=>'1','name'=>'LearningPHP,MySQL&JavaScript:WithjQuery,CSS&HTML5','release_date'=>NULL,'format_id'=>NULL,),1=>array('book_id'=>'2','name'=>'We\'reAllDamaged','release_date'=>NULL,'format_id'=>NULL,),2=>array('book_id'=>'3','name'=>'JavaScriptandJQuery:InteractiveFront-EndWebDevelopment','release_date'=>'2016-06-06','format_id'=>NULL,),)";
        $this->assertEquals($expected, $result);

        $authors = Authors::select()
            ->equal('author_id', 1)
            ->first()
        ;

        $select = $authors->relation("warehouse");
        $result = $select->fetchAll();
        $result = $this->inline($result);
        $expected = "array(0=>array('book_id'=>'1','amount'=>'2',),1=>array('book_id'=>'2','amount'=>'10',),2=>array('book_id'=>'3','amount'=>'11',),)";
        $this->assertEquals($expected, $result);

        $authors = Authors::select()
            ->equal('author_id', 1)
            ->first()
        ;

        $select = $authors->relation("warehouse");
        $result = $select->fetchAll();
        $result = $this->inline($result);
        $expected = "array(0=>array('book_id'=>'1','amount'=>'2',),1=>array('book_id'=>'2','amount'=>'10',),2=>array('book_id'=>'3','amount'=>'11',),)";
        $this->assertEquals($expected, $result);

        $warehouse = Warehouse::select()
            ->equal('book_id', 1)
            ->first()
        ;

        $select = $authors->relation("warehouse");
        $result = $select->fetchAll();
        $result = $this->inline($result);
        $expected = "array(0=>array('book_id'=>'1','amount'=>'2',),1=>array('book_id'=>'2','amount'=>'10',),2=>array('book_id'=>'3','amount'=>'11',),)";
        $this->assertEquals($expected, $result);
    }

    public function testState()
    {
        $this->initConnection();

        $warehouse = Warehouse::select()
            ->equal('book_id', 3)
            ->first()
        ;

        $this->assertEquals($warehouse->state(), Warehouse::STATE_FETCHED);

        $warehouse = Warehouse::select()
            ->equal('book_id', 3)
            ->first()
        ;

        $warehouse->delete();
        $this->assertEquals($warehouse->state(), Warehouse::STATE_DELETED);

        $warehouse = Warehouse::fetchNew();
        $this->assertEquals($warehouse->state(), Warehouse::STATE_NEW);


        $warehouse = Warehouse::select()
            ->equal('book_id', 2)
            ->first()
        ;

        $warehouse->state(Warehouse::STATE_NEW);
        $this->assertEquals($warehouse->state(), Warehouse::STATE_NEW);

    }

    public function testData()
    {
        $this->initConnection();

        $warehouse = Warehouse::select()
            ->equal('book_id', 2)
            ->first()
        ;

        $result = $warehouse->data();
        $result = $this->inline($result);
        $expected = "array('book_id'=>'2','amount'=>'10',)";
        $this->assertEquals($expected, $result);

        $warehouse = Warehouse::select()
            ->equal('book_id', 2)
            ->first()
        ;

        $warehouse->set('book_id', 10);
        $warehouse->set('new', "New field");

        $result = $warehouse->data();

        $result = $this->inline($result);
        $expected = "array('book_id'=>10,'amount'=>'10','new'=>'Newfield',)";
        $this->assertEquals($expected, $result);

        $warehouse = Warehouse::select()
            ->equal('book_id', 2)
            ->first()
        ;

        $warehouse->data(array('test' => '123'));
        $result = $warehouse->data();

        $result = $this->inline($result);
        $expected = "array('test'=>'123',)";
        $this->assertEquals($expected, $result);
    }

    public function testUndo()
    {
        $this->initConnection();

        $warehouse = Warehouse::select()
            ->equal('book_id', 2)
            ->first()
        ;

        $warehouse->set('book_id', 10);
        $warehouse->set('new', "New field");

        $warehouse->undo();
        $result = $warehouse->data();

        $result = $this->inline($result);
        $expected = "array('book_id'=>'2','amount'=>'10',)";
        $this->assertEquals($expected, $result);

        $warehouse = Warehouse::select()
            ->equal('book_id', 2)
            ->first()
        ;

        $warehouse->set('book_id', 10);
        $warehouse->set('new', "New field");

        $warehouse->undo(array('book_id'));
        $result = $warehouse->data();

        $result = $this->inline($result);
        $expected = "array('book_id'=>'2','amount'=>'10','new'=>'Newfield',)";
        $this->assertEquals($expected, $result);
    }

    public function testUpdate()
    {
        $this->initConnection();

        $warehouse = Warehouse::select()
            ->equal('book_id', 2)
            ->first()
        ;

        $warehouse->set('new', "New field");
        $warehouse->set('amount', 34);

        $result = $warehouse->data();
        $result = $this->inline($result);
        $expected = "array('book_id'=>'2','amount'=>34,'new'=>'Newfield',)";
        $this->assertEquals($expected, $result);

        $warehouse->update();
        $result = $warehouse->data();

        $result = $this->inline($result);
        $expected = "array('book_id'=>'2','amount'=>'10',)";
        $this->assertEquals($expected, $result);

        $warehouse = Warehouse::select()
            ->equal('book_id', 2)
            ->first()
        ;

        $warehouse->set('new', "New field");
        $warehouse->set('amount', 34);

        $warehouse->update(array('amount'));
        $result = $warehouse->data();

        $result = $this->inline($result);
        $expected = "array('book_id'=>'2','amount'=>'10','new'=>'Newfield',)";
        $this->assertEquals($expected, $result);
    }

    public function testIsSet()
    {
        $this->initConnection();

        $warehouse = Warehouse::select()
            ->equal('book_id', 2)
            ->first()
        ;

        $warehouse->set('new', "New field");
        $warehouse->set('amount', 34);

        $this->assertEquals($warehouse->has('new'), true);
        $this->assertEquals($warehouse->has('book_id'), true);
        $this->assertEquals($warehouse->has('amount'), true);
        $this->assertEquals($warehouse->has('title'), false);
    }

    public function testRemove()
    {
        $this->initConnection();

        $warehouse = Warehouse::select()
            ->equal('book_id', 2)
            ->first()
        ;

        $warehouse->set('new', "New field");
        $warehouse->set('amount', 34);

        $warehouse->remove('amount');
        $result = $warehouse->data();

        $result = $this->inline($result);
        $expected = "array('book_id'=>'2','new'=>'Newfield',)";
        $this->assertEquals($expected, $result);
    }
}
