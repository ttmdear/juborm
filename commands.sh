# odpalaj z katalogu tests
# generowanie pokrycia kodu
phpunit --coverage-html ../doc/codecover/


# odpalasz z katalogu /tests/bookstore/src/public
php ../../../../src/Assistant/compile.php; juborm generateModel -m bookstore --config ../config/juborm.xml -e production
