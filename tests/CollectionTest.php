<?php
namespace Juborm\Tests;

use Juborm\Tests\Base;

use Bookstore\Model\Bookstore\Entities\Authors;
use Bookstore\Model\Bookstore\Entities\Warehouse;
use Bookstore\Model\Bookstore\Entities\AuthorsAddresses;
use Bookstore\Model\Bookstore\Entities\Books;
use Bookstore\Model\Bookstore\Entities\BooksCategories;
use Bookstore\Model\Bookstore\Entities\Dictionaries;
use Bookstore\Model\Bookstore\Entities\BooksOpinions;

class CollectionTest extends Base
{
    public function testCount()
    {
        $this->initConnection();

        $all = Warehouse::select()
            ->all()
        ;

        $this->assertEquals($all->count(), 3);

        $all = Warehouse::select()
            ->expr("1=2")
            ->all()
        ;

        $this->assertEquals($all->count(), 0);
    }

    public function testData()
    {
        $this->initConnection();

        $all = Warehouse::select()
            ->all()
        ;

        $result = $all->data();
        $expected = "array(0=>array('book_id'=>'1','amount'=>'2',),1=>array('book_id'=>'2','amount'=>'10',),2=>array('book_id'=>'3','amount'=>'11',),)";
        $this->assertEquals($expected, $this->inline($result));

        $all->data(array(
            array(
                'id' => '12',
                'imie' => 'test',
            ),
            array(
                'id' => '13',
                'imie' => 'test',
            ),
            array(
                'id' => '14',
                'imie' => 'test',
            ),
        ));

        $result = $all->data();
        $expected = "array(0=>array('id'=>'12','imie'=>'test',),1=>array('id'=>'13','imie'=>'test',),2=>array('id'=>'14','imie'=>'test',),)";
        $this->assertEquals($expected, $this->inline($result));
    }

    public function testSource()
    {
        $this->initConnection();

        $all = Warehouse::select()
            ->all()
        ;

        $result = $all->source();
        $expected = "bookstore";
        $this->assertEquals($expected, $result);
    }

    public function testIndex()
    {
        $this->initConnection();
        $this->loadBooks();

        $books = Books::select()
            ->all()
        ;

        $result = $books->findByPk(34)->data();

        $expected = "array('book_id'=>'34','name'=>'book30','release_date'=>'2016-06-24','format_id'=>NULL,)";

        $this->assertEquals($expected, $this->inline($result));

        $books = Books::select();
        $books->relationJoin(
            'dictionary_books_categories',
            array('dictionary_books_categories' => 'd'),
            array('bookCategory' => 'd.name')
        );

        $all = $books->all(null, array(
            'GROUPS' => function($row){
                return $row['bookCategory'];
            }
        ));

        $result = $all->findByGroup('Life')->data();
        $expected = "array(0=>array('book_id'=>'2','name'=>'We\'reAllDamaged','release_date'=>NULL,'format_id'=>NULL,'bookCategory'=>'Life',),)";
        $this->assertEquals($expected, $this->inline($result));
    }

    public function testEntity()
    {
        $this->initConnection();

        $books = Books::select()
            ->all()
        ;

        $this->assertEquals($books->entity(), Books::class);

        $books->entity(BooksCategories::class);

        $this->assertEquals($books->entity(), BooksCategories::class);
    }

    public function testFindByPk()
    {
        $this->initConnection();
        $all = Books::select()
            ->all()
        ;

        $book = $all->findByPk(2);

        $result = $book->data();

        $expected = "array('book_id'=>'2','name'=>'We\'reAllDamaged','release_date'=>NULL,'format_id'=>NULL,)";
        $this->assertEquals($expected, $this->inline($result));
    }

    public function testGet()
    {
        $this->initConnection();

        $all = Books::select()
            ->all()
        ;

        $count = $all->count();
        $result = array();

        for($i = 0; $i < $count; $i++){
            $result[] = $all->get($i)->data();
        }

        $expected = "array(0=>array('book_id'=>'1','name'=>'LearningPHP,MySQL&JavaScript:WithjQuery,CSS&HTML5','release_date'=>NULL,'format_id'=>NULL,),1=>array('book_id'=>'2','name'=>'We\'reAllDamaged','release_date'=>NULL,'format_id'=>NULL,),2=>array('book_id'=>'3','name'=>'JavaScriptandJQuery:InteractiveFront-EndWebDevelopment','release_date'=>'2016-06-06','format_id'=>NULL,),)";
        $this->assertEquals($expected, $this->inline($result));
    }

    public function testFirst()
    {
        $this->initConnection();

        $all = Books::select()
            ->all()
        ;

        $count = $all->count();
        $result = $all->first()->data();

        $expected = "array('book_id'=>'1','name'=>'LearningPHP,MySQL&JavaScript:WithjQuery,CSS&HTML5','release_date'=>NULL,'format_id'=>NULL,)";
        $this->assertEquals($expected, $this->inline($result));
    }

    private function loadBooks()
    {
        $categoryId = 1;

        // load testing books
        for ($i=0; $i < 50; $i++) {
            $book = Books::fetchNew();
            $bookId = $book
                ->set('name', 'book'.$i)
                ->set('release_date', '2016-06-24')
                ->save()
            ;

            $booksCategories = BooksCategories::fetchNew();
            $booksCategories
                ->set('book_id', $bookId)
                ->set('category_id', $categoryId++)
            ;

            $booksCategories->save();

            if ($categoryId == 4) {
                $categoryId = 1;
            }
        }
    }

}
