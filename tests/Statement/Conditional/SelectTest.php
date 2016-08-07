<?php
use Juborm\Tests\Base;

use Bookstore\Model\Bookstore\Entities\Authors;
use Bookstore\Model\Bookstore\Entities\AuthorsAddresses;
use Bookstore\Model\Bookstore\Entities\Books;
use Bookstore\Model\Bookstore\Entities\BooksOpinions;
use Bookstore\Model\Bookstore\Entities\Dictionaries;

class SelectTest extends Base
{
    public function testIterator()
    {
        $this->initConnection();
        $select = Authors::select();

        $result = null;
        foreach ($select as $index => $author) {
            $result = $author->data();
            break;
        }

        $result = $this->inline($result);
        $expected = "array('author_id'=>'1','first_name'=>'Matthew','last_name'=>'Normani','birth_date'=>'1982-06-06',)";
        $this->assertEquals($expected, $result);

        $select = Authors::select()
            ->expr('1=2')
        ;

        $result = null;
        foreach ($select as $index => $author) {
            $result = $author->data();
            break;
        }

        $expected = null;
        $this->assertEquals($expected, $result);
    }

    public function testDelete()
    {
        $this->initConnection();
        $bookstore = $this->dbDiffer('bookstore');
        $bookstore->snapshot();

        $select = BooksOpinions::select();
        $select->delete();

        $bookstore->snapshot();

        $this->assertEquals($bookstore->numOfDeleted(), 5);
        $this->assertEquals($bookstore->numOfChanged(), 5);
    }

    // public function testTmp()
    // {
    //     //$this->initConnection();

    //     $select = Authors::select()
    //         ->equal('first_name', '123')
    //         ->in('author_id', array(2))
    //         ->in('author_id', array())
    //         ->like('last_name', 'Normani')
    //         ->endWith('last_name', 'i')
    //         ->contains('last_name', 'ma')
    //         ->expr('last_name = :lastName: OR first_name = :firstName:')
    //     ;

    //     $select->bind(array(
    //         'lastName' => '123',
    //         'firstName' => 'Normani',
    //     ));

    //     // todo : to delete
    //     die(print_r($select->sql(), true));
    //     // endtodo
    // }

    public function testFetchAll()
    {
        $this->initConnection();

        $select = Authors::select();
        $result = $select->fetchAll();
        $inline = "array(0=>array('author_id'=>'1','first_name'=>'Matthew','last_name'=>'Normani','birth_date'=>'1982-06-06',),1=>array('author_id'=>'2','first_name'=>'Roji','last_name'=>'Normani','birth_date'=>'1977-03-15',),2=>array('author_id'=>'3','first_name'=>'Anna','last_name'=>'Kowalska','birth_date'=>'1999-05-15',),)";
        $this->assertEquals($inline, $this->inline($result));

        $result = $select->fetchAll('bookstore_clone');
        $inline = "array(0=>array('author_id'=>'1','first_name'=>'Matthew','last_name'=>'Roma','birth_date'=>'1982-06-05',),1=>array('author_id'=>'2','first_name'=>'Roji','last_name'=>'Normani','birth_date'=>'1977-03-15',),2=>array('author_id'=>'3','first_name'=>'Anna','last_name'=>'Kowalska','birth_date'=>'1999-05-15',),)";
        $this->assertEquals($inline, $this->inline($result));

        $select->equal('first_name', '123');
        $result = $select->fetchAll();
        $this->assertEquals($result, array());

        $select = Authors::select(array(
            'firstName' => 'first_name',
        ));
        $result = $select->fetchAll();
        $inline = "array(0=>array('firstName'=>'Matthew','author_id'=>'1',),1=>array('firstName'=>'Roji','author_id'=>'2',),2=>array('firstName'=>'Anna','author_id'=>'3',),)";
        $this->assertEquals($inline, $this->inline($result));

        $select = Authors::select('last_name');
        $result = $select->fetchAll();
        $inline = "array(0=>array('last_name'=>'Normani','author_id'=>'1',),1=>array('last_name'=>'Normani','author_id'=>'2',),2=>array('last_name'=>'Kowalska','author_id'=>'3',),)";
        $this->assertEquals($inline, $this->inline($result));

    }

    public function testFetchRow()
    {
        $this->initConnection();

        $select = Books::select();
        $result = $select->fetchRow();
        $inline = "array('book_id'=>'1','name'=>'LearningPHP,MySQL&JavaScript:WithjQuery,CSS&HTML5','release_date'=>NULL,'format_id'=>NULL,)";
        $this->assertEquals($inline, $this->inline($result));

        $result = $select->fetchRow('bookstore_clone');
        $inline = "array('book_id'=>'1','name'=>'LearningPHP,MySQL&JavaScript:WithjQuery,CSS&HTML5','release_date'=>NULL,'format_id'=>NULL,)";
        $this->assertEquals($inline, $this->inline($result));

        $select->equal('name', '123');
        $result = $select->fetchRow();
        $this->assertEquals($result, null);

        $select = Books::select('name');
        $result = $select->fetchRow();
        $inline = "array('name'=>'LearningPHP,MySQL&JavaScript:WithjQuery,CSS&HTML5','book_id'=>'1',)";
        $this->assertEquals($inline, $this->inline($result));

    }

    public function testFetchOne()
    {
        $this->initConnection();

        $select = Dictionaries::select();
        $result = $select->fetchOne();
        $this->assertEquals($result, 1);

        $select->equal('name', '123');
        $result = $select->fetchOne();
        $this->assertEquals($result, null);

        $select = Dictionaries::select('name');
        $result = $select->fetchOne();
        $this->assertEquals($result, 'Address types');
    }

    public function testEqual()
    {
        $this->initConnection();

        $select = Authors::select();
        $select->equal('first_name', 'Matthew');
        $select->equal('last_name', 'Normani');
        $result = $select->fetchRow();
        $inline = "array('author_id'=>'1','first_name'=>'Matthew','last_name'=>'Normani','birth_date'=>'1982-06-06',)";
        $this->assertEquals($inline, $this->inline($result));

        $select = Authors::select();
        $select->orOperator();
        $select->equal('first_name', 'Matthew');
        $select->equal('first_name', 'Roji');
        $result = $select->fetchAll();
        $inline = "array(0=>array('author_id'=>'1','first_name'=>'Matthew','last_name'=>'Normani','birth_date'=>'1982-06-06',),1=>array('author_id'=>'2','first_name'=>'Roji','last_name'=>'Normani','birth_date'=>'1977-03-15',),)";
        $this->assertEquals($inline, $this->inline($result));

        $select = Authors::select();
        $select->orOperator();
        $select->brackets(function($select){
            $select->equal('first_name', 'Matthew');
            $select->equal('last_name', 'Normani');
        });

        $select->equal('first_name', 'Anna');

        $result = $select->fetchAll();
        $inline = "array(0=>array('author_id'=>'1','first_name'=>'Matthew','last_name'=>'Normani','birth_date'=>'1982-06-06',),1=>array('author_id'=>'3','first_name'=>'Anna','last_name'=>'Kowalska','birth_date'=>'1999-05-15',),)";
        $this->assertEquals($inline, $this->inline($result));


    }

    public function testIn()
    {
        $this->initConnection();

        $select = Authors::select();
        $select->in('author_id', array(2));
        $result = $select->fetchRow();
        $inline = "array('author_id'=>'2','first_name'=>'Roji','last_name'=>'Normani','birth_date'=>'1977-03-15',)";
        $this->assertEquals($inline, $this->inline($result));

        $select = Authors::select();
        $select->in('author_id', array());
        $result = $select->fetchAll();
        $this->assertEquals(array(), $result);

        //with select bind
        $selectF = Authors::select('author_id');
        $selectF->in('author_id', array(2));

        $select = Authors::select();
        $select->in('author_id', $selectF);
        $result = $select->fetchRow();
        $inline = "array('author_id'=>'2','first_name'=>'Roji','last_name'=>'Normani','birth_date'=>'1977-03-15',)";
        $this->assertEquals($inline, $this->inline($result));

        // bind with brackets and select
        $selectF = Authors::select('author_id');
        $selectF->in('author_id', array(2));

        $selectFA = Authors::select('author_id');
        $selectFA->in('author_id', array(3));

        $select = Authors::select();
        $select->brackets(function($select) use ($selectF, $selectFA){
            $select->orOperator();
            $select->in('author_id', $selectF);
            $select->in('author_id', $selectFA);
        });
        $select->orOperator();
        $select->in('author_id', 1);

        $result = $select->fetchAll();
        $inline = "array(0=>array('author_id'=>'1','first_name'=>'Matthew','last_name'=>'Normani','birth_date'=>'1982-06-06',),1=>array('author_id'=>'2','first_name'=>'Roji','last_name'=>'Normani','birth_date'=>'1977-03-15',),2=>array('author_id'=>'3','first_name'=>'Anna','last_name'=>'Kowalska','birth_date'=>'1999-05-15',),)";
        $this->assertEquals($inline, $this->inline($result));
    }

    public function testLike()
    {
        $this->initConnection();

        $select = Authors::select();
        $select->like('last_name', 'Normani');
        $result = $select->fetchAll();
        $inline = "array(0=>array('author_id'=>'1','first_name'=>'Matthew','last_name'=>'Normani','birth_date'=>'1982-06-06',),1=>array('author_id'=>'2','first_name'=>'Roji','last_name'=>'Normani','birth_date'=>'1977-03-15',),)";
        $this->assertEquals($inline, $this->inline($result));
    }

    public function testStartWith()
    {
        $this->initConnection();

        $select = Authors::select();
        $select->startWith('last_name', 'N');
        $result = $select->fetchAll();
        $inline = "array(0=>array('author_id'=>'1','first_name'=>'Matthew','last_name'=>'Normani','birth_date'=>'1982-06-06',),1=>array('author_id'=>'2','first_name'=>'Roji','last_name'=>'Normani','birth_date'=>'1977-03-15',),)";
        $this->assertEquals($inline, $this->inline($result));
    }

    public function testEndWith()
    {
        $this->initConnection();

        $select = Authors::select();
        $select->endWith('last_name', 'i');
        $result = $select->fetchAll();
        $inline = "array(0=>array('author_id'=>'1','first_name'=>'Matthew','last_name'=>'Normani','birth_date'=>'1982-06-06',),1=>array('author_id'=>'2','first_name'=>'Roji','last_name'=>'Normani','birth_date'=>'1977-03-15',),)";
        $this->assertEquals($inline, $this->inline($result));
    }

    public function testContains()
    {
        $this->initConnection();

        $select = Authors::select();
        $select->contains('last_name', 'ma');
        $result = $select->fetchAll();
        $inline = "array(0=>array('author_id'=>'1','first_name'=>'Matthew','last_name'=>'Normani','birth_date'=>'1982-06-06',),1=>array('author_id'=>'2','first_name'=>'Roji','last_name'=>'Normani','birth_date'=>'1977-03-15',),)";
        $this->assertEquals($inline, $this->inline($result));
    }

    public function testEntity()
    {
        $this->initConnection();

        $select = Authors::select();
        $result = $select->entity();
        $expected = "Bookstore\Model\Bookstore\Entities\Authors";
        $this->assertEquals($expected, $result);

        $select->entity(Authors::class);
        $result = $select->entity();
        $expected = Authors::class;
        $this->assertEquals($expected, $result);
    }

    public function testFrom()
    {
        $this->initConnection();

        $select = Authors::select();
        $result = $select->from();
        $result = $this->inline($result);
        $expected = "array('authors'=>'authors',)";
        $this->assertEquals($expected, $result);

        // setter
        $select = Authors::select();
        $select->from('test');
        $result = $select->from();
        $result = $this->inline($result);
        $expected = "array('test'=>'test',)";
        $this->assertEquals($expected, $result);

        $select = Authors::select();
        $select->from(array('a' => 'authors'));
        $result = $select->from();
        $result = $this->inline($result);
        $expected = "array('a'=>'authors',)";
        $this->assertEquals($expected, $result);
    }

    public function testAlias()
    {
        $this->initConnection();

        $select = Authors::select();
        $result = $select->alias();
        $expected = "authors";
        $this->assertEquals($expected, $result);

        $select = Authors::select();
        $select->from(array('a' => 'authors'));
        $result = $select->alias();
        $expected = "a";
        $this->assertEquals($expected, $result);
    }

    public function testTable()
    {
        $this->initConnection();

        $select = Authors::select();
        $result = $select->table();
        $expected = "authors";
        $this->assertEquals($expected, $result);

        $select = Authors::select();
        $select->from(array('a' => 'books'));
        $result = $select->table();
        $expected = "books";
        $this->assertEquals($expected, $result);
    }

    public function testInnerJoin()
    {
        $this->initConnection();
        $bookstore = $this->dbDiffer('bookstore')->clean();

        $select = Authors::select();
        $select->innerJoin('books_authors', "books_authors.author_id = authors.author_id");
        $select->innerJoin('books', "books.book_id = books_authors.book_id", array('bookName' => 'books.name'));
        $result = $select->fetchAll();
        $result = $this->inline($result);
        $expected = "array(0=>array('author_id'=>'1','first_name'=>'Matthew','last_name'=>'Normani','birth_date'=>'1982-06-06','bookName'=>'LearningPHP,MySQL&JavaScript:WithjQuery,CSS&HTML5',),1=>array('author_id'=>'1','first_name'=>'Matthew','last_name'=>'Normani','birth_date'=>'1982-06-06','bookName'=>'We\'reAllDamaged',),2=>array('author_id'=>'2','first_name'=>'Roji','last_name'=>'Normani','birth_date'=>'1977-03-15','bookName'=>'We\'reAllDamaged',),3=>array('author_id'=>'1','first_name'=>'Matthew','last_name'=>'Normani','birth_date'=>'1982-06-06','bookName'=>'JavaScriptandJQuery:InteractiveFront-EndWebDevelopment',),4=>array('author_id'=>'3','first_name'=>'Anna','last_name'=>'Kowalska','birth_date'=>'1999-05-15','bookName'=>'JavaScriptandJQuery:InteractiveFront-EndWebDevelopment',),)";
        $this->assertEquals($expected, $result);

        $select = Authors::select();
        $select->innerJoin('books_authors', "books_authors.author_id = authors.author_id");
        $select->innerJoin('books', "books.book_id = books_authors.book_id", array('bookName' => 'books.name'));
        $select->innerJoin(array('o' => 'books_opinions'), "o.book_id = books.book_id", array(
            'o.opinion',
            'authorOfOpinion' => 'o.author',
        ));
        $select->column('o.book_id', 'opinionBookId');
        $select->order('authors.first_name');
        $select->order('o.opinion');
        $result = $select->fetchAll();
        $result = $this->inline($result);
        $expected = "array(0=>array('author_id'=>'1','first_name'=>'Matthew','last_name'=>'Normani','birth_date'=>'1982-06-06','bookName'=>'We\'reAllDamaged','opinion'=>'AtripbackhometotheMidwestisnotinhisplans','authorOfOpinion'=>'Milka','opinionBookId'=>'2',),1=>array('author_id'=>'1','first_name'=>'Matthew','last_name'=>'Normani','birth_date'=>'1982-06-06','bookName'=>'We\'reAllDamaged','opinion'=>'Andyisdivorcedandlivingaseeminglymeaninglesslifeinthebigcity','authorOfOpinion'=>'Jula','opinionBookId'=>'2',),2=>array('author_id'=>'1','first_name'=>'Matthew','last_name'=>'Normani','birth_date'=>'1982-06-06','bookName'=>'We\'reAllDamaged','opinion'=>'Everyonehastheirhistoryandquirks.','authorOfOpinion'=>NULL,'opinionBookId'=>'2',),3=>array('author_id'=>'1','first_name'=>'Matthew','last_name'=>'Normani','birth_date'=>'1982-06-06','bookName'=>'LearningPHP,MySQL&JavaScript:WithjQuery,CSS&HTML5','opinion'=>'Ithinkthisbookwouldserveabeginningcoderwell.','authorOfOpinion'=>'Mill','opinionBookId'=>'1',),4=>array('author_id'=>'1','first_name'=>'Matthew','last_name'=>'Normani','birth_date'=>'1982-06-06','bookName'=>'LearningPHP,MySQL&JavaScript:WithjQuery,CSS&HTML5','opinion'=>'Oneofthebest-writtenbooksI\'vecomeacrossinmanyyears.Itsamazinghow3majordevenvironmentscouldbecoveredsothoroughlyin1book','authorOfOpinion'=>'Mike','opinionBookId'=>'1',),5=>array('author_id'=>'2','first_name'=>'Roji','last_name'=>'Normani','birth_date'=>'1977-03-15','bookName'=>'We\'reAllDamaged','opinion'=>'AtripbackhometotheMidwestisnotinhisplans','authorOfOpinion'=>'Milka','opinionBookId'=>'2',),6=>array('author_id'=>'2','first_name'=>'Roji','last_name'=>'Normani','birth_date'=>'1977-03-15','bookName'=>'We\'reAllDamaged','opinion'=>'Andyisdivorcedandlivingaseeminglymeaninglesslifeinthebigcity','authorOfOpinion'=>'Jula','opinionBookId'=>'2',),7=>array('author_id'=>'2','first_name'=>'Roji','last_name'=>'Normani','birth_date'=>'1977-03-15','bookName'=>'We\'reAllDamaged','opinion'=>'Everyonehastheirhistoryandquirks.','authorOfOpinion'=>NULL,'opinionBookId'=>'2',),)";
        $this->assertEquals($expected, $result);

        $select = Authors::select();
        $select->innerJoin('books_authors', "author_id");
        $select->innerJoin('books', "book_id", array('bookName' => 'books.name'));
        $select->innerJoin(array('o' => 'books_opinions'), array("book_id"), array(
            'o.opinion',
            'authorOfOpinion' => 'o.author',
        ));
        $select->column('o.book_id', 'opinionBookId');
        $select->order('authors.first_name');
        $select->order('o.opinion');
        $result = $select->fetchAll();
        $result = $this->inline($result);
        $expected = "array(0=>array('author_id'=>'1','first_name'=>'Matthew','last_name'=>'Normani','birth_date'=>'1982-06-06','bookName'=>'We\'reAllDamaged','opinion'=>'AtripbackhometotheMidwestisnotinhisplans','authorOfOpinion'=>'Milka','opinionBookId'=>'2',),1=>array('author_id'=>'1','first_name'=>'Matthew','last_name'=>'Normani','birth_date'=>'1982-06-06','bookName'=>'We\'reAllDamaged','opinion'=>'Andyisdivorcedandlivingaseeminglymeaninglesslifeinthebigcity','authorOfOpinion'=>'Jula','opinionBookId'=>'2',),2=>array('author_id'=>'1','first_name'=>'Matthew','last_name'=>'Normani','birth_date'=>'1982-06-06','bookName'=>'We\'reAllDamaged','opinion'=>'Everyonehastheirhistoryandquirks.','authorOfOpinion'=>NULL,'opinionBookId'=>'2',),3=>array('author_id'=>'1','first_name'=>'Matthew','last_name'=>'Normani','birth_date'=>'1982-06-06','bookName'=>'LearningPHP,MySQL&JavaScript:WithjQuery,CSS&HTML5','opinion'=>'Ithinkthisbookwouldserveabeginningcoderwell.','authorOfOpinion'=>'Mill','opinionBookId'=>'1',),4=>array('author_id'=>'1','first_name'=>'Matthew','last_name'=>'Normani','birth_date'=>'1982-06-06','bookName'=>'LearningPHP,MySQL&JavaScript:WithjQuery,CSS&HTML5','opinion'=>'Oneofthebest-writtenbooksI\'vecomeacrossinmanyyears.Itsamazinghow3majordevenvironmentscouldbecoveredsothoroughlyin1book','authorOfOpinion'=>'Mike','opinionBookId'=>'1',),5=>array('author_id'=>'2','first_name'=>'Roji','last_name'=>'Normani','birth_date'=>'1977-03-15','bookName'=>'We\'reAllDamaged','opinion'=>'AtripbackhometotheMidwestisnotinhisplans','authorOfOpinion'=>'Milka','opinionBookId'=>'2',),6=>array('author_id'=>'2','first_name'=>'Roji','last_name'=>'Normani','birth_date'=>'1977-03-15','bookName'=>'We\'reAllDamaged','opinion'=>'Andyisdivorcedandlivingaseeminglymeaninglesslifeinthebigcity','authorOfOpinion'=>'Jula','opinionBookId'=>'2',),7=>array('author_id'=>'2','first_name'=>'Roji','last_name'=>'Normani','birth_date'=>'1977-03-15','bookName'=>'We\'reAllDamaged','opinion'=>'Everyonehastheirhistoryandquirks.','authorOfOpinion'=>NULL,'opinionBookId'=>'2',),)";
        $this->assertEquals($expected, $result);
    }

    public function testLeftJoin()
    {
        $this->initConnection();

        $select = Authors::select();
        $select->leftJoin('books_authors', "books_authors.author_id = authors.author_id");
        $select->leftJoin('books', "books.book_id = books_authors.book_id", array('bookName' => 'books.name'));
        $select->order('books.name');
        $select->order('authors.first_name');
        $result = $select->fetchAll();
        $result = $this->inline($result);
        $expected = "array(0=>array('author_id'=>'3','first_name'=>'Anna','last_name'=>'Kowalska','birth_date'=>'1999-05-15','bookName'=>'JavaScriptandJQuery:InteractiveFront-EndWebDevelopment',),1=>array('author_id'=>'1','first_name'=>'Matthew','last_name'=>'Normani','birth_date'=>'1982-06-06','bookName'=>'JavaScriptandJQuery:InteractiveFront-EndWebDevelopment',),2=>array('author_id'=>'1','first_name'=>'Matthew','last_name'=>'Normani','birth_date'=>'1982-06-06','bookName'=>'LearningPHP,MySQL&JavaScript:WithjQuery,CSS&HTML5',),3=>array('author_id'=>'1','first_name'=>'Matthew','last_name'=>'Normani','birth_date'=>'1982-06-06','bookName'=>'We\'reAllDamaged',),4=>array('author_id'=>'2','first_name'=>'Roji','last_name'=>'Normani','birth_date'=>'1977-03-15','bookName'=>'We\'reAllDamaged',),)";
        $this->assertEquals($expected, $result);

        $select = Authors::select();
        $select->leftJoin('books_authors', "books_authors.author_id = authors.author_id");
        $select->leftJoin('books', "book_id", array('bookName' => 'name'));
        $select->leftJoin('books_opinions', "book_id", array('opinion'));
        $result = $select->fetchAll();
        $result = $this->inline($result);
        $expected = "array(0=>array('author_id'=>'1','first_name'=>'Matthew','last_name'=>'Normani','birth_date'=>'1982-06-06','bookName'=>'LearningPHP,MySQL&JavaScript:WithjQuery,CSS&HTML5','opinion'=>'Ithinkthisbookwouldserveabeginningcoderwell.',),1=>array('author_id'=>'1','first_name'=>'Matthew','last_name'=>'Normani','birth_date'=>'1982-06-06','bookName'=>'LearningPHP,MySQL&JavaScript:WithjQuery,CSS&HTML5','opinion'=>'Oneofthebest-writtenbooksI\'vecomeacrossinmanyyears.Itsamazinghow3majordevenvironmentscouldbecoveredsothoroughlyin1book',),2=>array('author_id'=>'1','first_name'=>'Matthew','last_name'=>'Normani','birth_date'=>'1982-06-06','bookName'=>'We\'reAllDamaged','opinion'=>'Everyonehastheirhistoryandquirks.',),3=>array('author_id'=>'2','first_name'=>'Roji','last_name'=>'Normani','birth_date'=>'1977-03-15','bookName'=>'We\'reAllDamaged','opinion'=>'Everyonehastheirhistoryandquirks.',),4=>array('author_id'=>'1','first_name'=>'Matthew','last_name'=>'Normani','birth_date'=>'1982-06-06','bookName'=>'We\'reAllDamaged','opinion'=>'Andyisdivorcedandlivingaseeminglymeaninglesslifeinthebigcity',),5=>array('author_id'=>'2','first_name'=>'Roji','last_name'=>'Normani','birth_date'=>'1977-03-15','bookName'=>'We\'reAllDamaged','opinion'=>'Andyisdivorcedandlivingaseeminglymeaninglesslifeinthebigcity',),6=>array('author_id'=>'1','first_name'=>'Matthew','last_name'=>'Normani','birth_date'=>'1982-06-06','bookName'=>'We\'reAllDamaged','opinion'=>'AtripbackhometotheMidwestisnotinhisplans',),7=>array('author_id'=>'2','first_name'=>'Roji','last_name'=>'Normani','birth_date'=>'1977-03-15','bookName'=>'We\'reAllDamaged','opinion'=>'AtripbackhometotheMidwestisnotinhisplans',),8=>array('author_id'=>'1','first_name'=>'Matthew','last_name'=>'Normani','birth_date'=>'1982-06-06','bookName'=>'JavaScriptandJQuery:InteractiveFront-EndWebDevelopment','opinion'=>NULL,),9=>array('author_id'=>'3','first_name'=>'Anna','last_name'=>'Kowalska','birth_date'=>'1999-05-15','bookName'=>'JavaScriptandJQuery:InteractiveFront-EndWebDevelopment','opinion'=>NULL,),)";
        $this->assertEquals($expected, $result);

        $select = Authors::select();
        $select->leftJoin(array('aa' => 'authors_addresses'), array('author_id'), array('street'));
        $select->leftJoin(array('diType' => 'dictionary_values'), array("id" => "type"), array('type' => 'value'))->pointer();
        $select->leftJoin(array('diStreet' => 'dictionary_values'), array("id" => "street_prefix"), array('street_prefix' => 'value'));

        $result = $select->fetchAll();
        $result = $this->inline($result);
        $expected = "array(0=>array('author_id'=>'1','first_name'=>'Matthew','last_name'=>'Normani','birth_date'=>'1982-06-06','street'=>'1ChapelHill','type'=>'correspondence','street_prefix'=>'Road',),1=>array('author_id'=>'2','first_name'=>'Roji','last_name'=>'Normani','birth_date'=>'1977-03-15','street'=>'56/45ApelMill','type'=>'correspondence','street_prefix'=>'Road',),2=>array('author_id'=>'3','first_name'=>'Anna','last_name'=>'Kowalska','birth_date'=>'1999-05-15','street'=>'45/23Apply','type'=>'business','street_prefix'=>'Road',),)";
        $this->assertEquals($expected, $result);

        // with alias generator
        $select = Authors::select();
        $select->leftJoin('authors_addresses', array('author_id'), array('street'));
        $select->leftJoin('dictionary_values', array("id" => "type"), array('type' => 'value'))->pointer();
        $select->leftJoin('dictionary_values', array("id" => "street_prefix"), array('street_prefix' => 'value'))->pointer();
        $select->leftJoin('dictionary_values', array("id" => "country"), array('country' => 'value'));

        $result = $select->fetchAll();
        $result = $this->inline($result);
        $expected = "array(0=>array('author_id'=>'1','first_name'=>'Matthew','last_name'=>'Normani','birth_date'=>'1982-06-06','street'=>'1ChapelHill','type'=>'correspondence','street_prefix'=>'Road','country'=>'Poland',),1=>array('author_id'=>'2','first_name'=>'Roji','last_name'=>'Normani','birth_date'=>'1977-03-15','street'=>'56/45ApelMill','type'=>'correspondence','street_prefix'=>'Road','country'=>'Poland',),2=>array('author_id'=>'3','first_name'=>'Anna','last_name'=>'Kowalska','birth_date'=>'1999-05-15','street'=>'45/23Apply','type'=>'business','street_prefix'=>'Road','country'=>'USA',),)";
        $this->assertEquals($expected, $result);

        $select = Authors::select();
        $select->leftJoin('authors_addresses', array('author_id'), array('street'));
        $select->leftJoin('authors_addresses_changes', array('author_id', 'type' => 'type'), array('dateOfChange' => 'date'));
        $select->pointer('authors_addresses')->leftJoin('dictionary_values', array("id" => "type"), array('type' => 'value'))->pointer();
        $select->pointer('authors_addresses')->leftJoin('dictionary_values', array("id" => "street_prefix"), array('street_prefix' => 'value'))->pointer();
        $select->pointer('authors_addresses')->leftJoin('dictionary_values', array("id" => "country"), array('country' => 'value'));
        $select->pointer('author')->leftJoin('books_authors', 'author_id');
        $select->leftJoin('books', 'book_id', array('bookName' => 'name', 'release_date'));

        $result = $select->fetchAll();
        $result = $this->inline($result);
        $expected = "array(0=>array('author_id'=>'1','first_name'=>'Matthew','last_name'=>'Normani','birth_date'=>'1982-06-06','street'=>'1ChapelHill','dateOfChange'=>'2016-06-2114:43:41','type'=>'correspondence','street_prefix'=>'Road','country'=>'Poland','bookName'=>'LearningPHP,MySQL&JavaScript:WithjQuery,CSS&HTML5','release_date'=>NULL,),1=>array('author_id'=>'1','first_name'=>'Matthew','last_name'=>'Normani','birth_date'=>'1982-06-06','street'=>'1ChapelHill','dateOfChange'=>'2016-06-2114:43:41','type'=>'correspondence','street_prefix'=>'Road','country'=>'Poland','bookName'=>'We\'reAllDamaged','release_date'=>NULL,),2=>array('author_id'=>'1','first_name'=>'Matthew','last_name'=>'Normani','birth_date'=>'1982-06-06','street'=>'1ChapelHill','dateOfChange'=>'2016-06-2114:43:41','type'=>'correspondence','street_prefix'=>'Road','country'=>'Poland','bookName'=>'JavaScriptandJQuery:InteractiveFront-EndWebDevelopment','release_date'=>'2016-06-06',),3=>array('author_id'=>'1','first_name'=>'Matthew','last_name'=>'Normani','birth_date'=>'1982-06-06','street'=>'1ChapelHill','dateOfChange'=>'2016-06-2114:43:56','type'=>'correspondence','street_prefix'=>'Road','country'=>'Poland','bookName'=>'LearningPHP,MySQL&JavaScript:WithjQuery,CSS&HTML5','release_date'=>NULL,),4=>array('author_id'=>'1','first_name'=>'Matthew','last_name'=>'Normani','birth_date'=>'1982-06-06','street'=>'1ChapelHill','dateOfChange'=>'2016-06-2114:43:56','type'=>'correspondence','street_prefix'=>'Road','country'=>'Poland','bookName'=>'We\'reAllDamaged','release_date'=>NULL,),5=>array('author_id'=>'1','first_name'=>'Matthew','last_name'=>'Normani','birth_date'=>'1982-06-06','street'=>'1ChapelHill','dateOfChange'=>'2016-06-2114:43:56','type'=>'correspondence','street_prefix'=>'Road','country'=>'Poland','bookName'=>'JavaScriptandJQuery:InteractiveFront-EndWebDevelopment','release_date'=>'2016-06-06',),6=>array('author_id'=>'1','first_name'=>'Matthew','last_name'=>'Normani','birth_date'=>'1982-06-06','street'=>'1ChapelHill','dateOfChange'=>'2016-06-2114:44:04','type'=>'correspondence','street_prefix'=>'Road','country'=>'Poland','bookName'=>'LearningPHP,MySQL&JavaScript:WithjQuery,CSS&HTML5','release_date'=>NULL,),7=>array('author_id'=>'1','first_name'=>'Matthew','last_name'=>'Normani','birth_date'=>'1982-06-06','street'=>'1ChapelHill','dateOfChange'=>'2016-06-2114:44:04','type'=>'correspondence','street_prefix'=>'Road','country'=>'Poland','bookName'=>'We\'reAllDamaged','release_date'=>NULL,),8=>array('author_id'=>'1','first_name'=>'Matthew','last_name'=>'Normani','birth_date'=>'1982-06-06','street'=>'1ChapelHill','dateOfChange'=>'2016-06-2114:44:04','type'=>'correspondence','street_prefix'=>'Road','country'=>'Poland','bookName'=>'JavaScriptandJQuery:InteractiveFront-EndWebDevelopment','release_date'=>'2016-06-06',),9=>array('author_id'=>'2','first_name'=>'Roji','last_name'=>'Normani','birth_date'=>'1977-03-15','street'=>'56/45ApelMill','dateOfChange'=>NULL,'type'=>'correspondence','street_prefix'=>'Road','country'=>'Poland','bookName'=>'We\'reAllDamaged','release_date'=>NULL,),10=>array('author_id'=>'3','first_name'=>'Anna','last_name'=>'Kowalska','birth_date'=>'1999-05-15','street'=>'45/23Apply','dateOfChange'=>NULL,'type'=>'business','street_prefix'=>'Road','country'=>'USA','bookName'=>'JavaScriptandJQuery:InteractiveFront-EndWebDevelopment','release_date'=>'2016-06-06',),)";
        $this->assertEquals($expected, $result);
    }

    public function testRelationJoin()
    {
        $this->initConnection();

        $select = Authors::select();
        $select->relationJoin('warehouse', array(), array(
            'warehouse.amount',
            'bookName' => 'books.name'
        ));

        $result = $select->fetchAll();
        $result = $this->inline($result);
        $expected = "array(0=>array('author_id'=>'1','first_name'=>'Matthew','last_name'=>'Normani','birth_date'=>'1982-06-06','amount'=>'2','bookName'=>'LearningPHP,MySQL&JavaScript:WithjQuery,CSS&HTML5',),1=>array('author_id'=>'1','first_name'=>'Matthew','last_name'=>'Normani','birth_date'=>'1982-06-06','amount'=>'10','bookName'=>'We\'reAllDamaged',),2=>array('author_id'=>'1','first_name'=>'Matthew','last_name'=>'Normani','birth_date'=>'1982-06-06','amount'=>'11','bookName'=>'JavaScriptandJQuery:InteractiveFront-EndWebDevelopment',),3=>array('author_id'=>'2','first_name'=>'Roji','last_name'=>'Normani','birth_date'=>'1977-03-15','amount'=>'10','bookName'=>'We\'reAllDamaged',),4=>array('author_id'=>'3','first_name'=>'Anna','last_name'=>'Kowalska','birth_date'=>'1999-05-15','amount'=>'11','bookName'=>'JavaScriptandJQuery:InteractiveFront-EndWebDevelopment',),)";
        $this->assertEquals($expected, $result);

        // not clear relation
        $select = AuthorsAddresses::select();
        $select->relationJoin('$authors_addresses_type:dictionary_values',
            array('dictionary_values' => 'dtype'),
            array('type' => 'dtype.value')
        );
        $select->relationJoin('$authors_addresses_country:dictionary_values',
            array('dictionary_values' => 'dcountry'),
            array('country' => 'dcountry.value')
        );
        $select->relationJoin('$authors_addresses_street_prefix:dictionary_values',
            array('dictionary_values' => 'dprefix'),
            array('street_prefix' => 'dprefix.value')
        );

        $result = $select->fetchAll();
        $result = $this->inline($result);
        $expected = "array(0=>array('author_id'=>'1','type'=>'correspondence','street_prefix'=>'Road','street'=>'1ChapelHill','country'=>'Poland',),1=>array('author_id'=>'2','type'=>'correspondence','street_prefix'=>'Road','street'=>'56/45ApelMill','country'=>'Poland',),2=>array('author_id'=>'3','type'=>'business','street_prefix'=>'Road','street'=>'45/23Apply','country'=>'USA',),)";
        $this->assertEquals($expected, $result);
    }

    public function testIsNull()
    {
        $this->initConnection();

        $select = Books::select()
            ->isNull('release_date')
        ;

        $result = $select->fetchAll();
        $result = $this->inline($result);
        $expected = "array(0=>array('book_id'=>'1','name'=>'LearningPHP,MySQL&JavaScript:WithjQuery,CSS&HTML5','release_date'=>NULL,'format_id'=>NULL,),1=>array('book_id'=>'2','name'=>'We\'reAllDamaged','release_date'=>NULL,'format_id'=>NULL,),)";
        $this->assertEquals($expected, $result);

        $select = Books::select()
            ->isNotNull('release_date')
        ;

        $result = $select->fetchAll();
        $result = $this->inline($result);
        $expected = "array(0=>array('book_id'=>'3','name'=>'JavaScriptandJQuery:InteractiveFront-EndWebDevelopment','release_date'=>'2016-06-06','format_id'=>NULL,),)";
        $this->assertEquals($expected, $result);
    }

    public function testColumn()
    {
        $this->initConnection();
    }

    public function testColumns()
    {
        $this->initConnection();
    }

    public function testLimit()
    {
        $this->initConnection();
    }

    public function testOrder()
    {
        $this->initConnection();
    }

    public function testOrders()
    {
        $this->initConnection();
    }

    public function all()
    {
        $this->initConnection();
    }

    public function testFirst()
    {
        $this->initConnection();
    }

    public function testCount()
    {
        $this->initConnection();
    }

    public function testFetchCol()
    {
        $this->initConnection();
    }

}
