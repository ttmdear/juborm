<?php
require_once __DIR__."/autoload.php";

use Juborm\ORM;
use Bookstore\Model\Bookstore\Entities\Authors;
use Bookstore\Model\Bookstore\Entities\AuthorsAddresses;
use Bookstore\Model\Bookstore\Entities\Books;
use Bookstore\Model\Bookstore\Entities\Dictionaries;

$config = ORM::service('config');
$config->load(__DIR__.'/../config/juborm.xml', 'production');

// simple select
$select = Authors::select();
$result = $select->fetchAll();

$select->column("CONCAT(authors.first_name, ' ', authors.last_name)", 'fullName');

// building queries
$select->equal('first_name', 'Matthew');

$select->like('last_name', 'Normani');
$select->startWith('last_name', 'N');
$select->endWith('last_name', 'i');
$select->contains('last_name', 'ma');

// author_id in(2)
$select->in('author_id', array(2));

// this will be automaticly replace to "1=2"
$select->in('author_id', array());

// use sub select as condition
$subSelect = Authors::select('author_id');
$subSelect->in('author_id', array(2));
$select->in('author_id', $subSelect);

// use expr to write complex condition
$select->expr('author_id = :idA: OR author_id = :idB:');
$select->bind('idA', 1);
$select->bind('idB', 2);

// brackets
$select = Authors::select();

$subSelect = Authors::select('author_id');
$subSelect->in('author_id', array(3));

$select->brackets(function($select) use ($subSelect){
    // set at brackets is OR operator
    $select->orOperator();
    $select->in('author_id', $subSelect);

    $select->brackets(function($select){
        $select
            ->equal('first_name', 'Roji')
            ->contains('first_name', 'a')
        ;
    });

    $select->brackets(function($select){
        $select->equal('last_name', 'Normani');
    });
});

$array = $select->fetchAll();
$array = $select->fetchOne();
$array = $select->fetchCol();
$array = $select->fetchRow();

$entity = $select->first();
$collection = $select->all();

// operations on entity
$author1 = Authors::select()
    ->equal('author_id', 1)
    ->first()
;

$author1->set('first_name', 'John');

// UPDATE
$author1->save();

// DELETE
// $author1->delete();

$authorNew = Authors::fetchNew();
$authorNew->set('first_name', 'John');
$authorNew->set('last_name', 'Matthew');
// INSERT
$authorNew->save();

// Collection
$authors = Authors::select()
    ->order('authors.author_id', 'DESC')
    ->all()
;

foreach ($authors as $author) {
    $authorNew->set('first_name', 'John');
    $authors->save();
}

// or short
$authors->set('first_name', 'John')->save();

// or delete all authors
// $authors->delete();

// joins

$select = Authors::select();
// you can only give column name to join
$select->innerJoin('books_authors', "author_id");

// automaticly add column during join
$select->leftJoin('books', "book_id", array('bookName' => 'books.name'));

// joins with table alias, plus condition as list of column
$select->rightJoin(array('o' => 'books_opinions'), array("book_id"), array(
    'o.opinion',
    'authorOfOpinion' => 'o.author',
));

// add column
$select->column('o.book_id', 'opinionBookId');

// set order
$select->order('authors.first_name');
$result = $select->fetchAll();


// relations joins
$select = Authors::select();
$select->relationJoin('books_opinions');


$author1 = Authors::select()
    ->equal('author_id', 1)
    ->first()
;

// implement this method
//$opinions = $author1->opinionsOfBook(1);

//$collection = $opinions->all();
//$firstOpinion = $opinions->first();

