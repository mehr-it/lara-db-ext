# Laravel DB extension
[![Latest Version on Packagist](https://img.shields.io/packagist/v/mehr-it/lara-db-ext.svg?style=flat-square)](https://packagist.org/packages/mehr-it/lara-db-ext)
[![Build Status](https://travis-ci.org/mehr-it/lara-db-ext.svg?branch=master)](https://travis-ci.org/mehr-it/lara-db-ext)

This package implements various extensions and improvements for Laravel's database abstraction.

Query builder:

* generateChunked
* insertOnDuplicateKey
* selectPrefixed
* updateWithJoinedData
* whereMultiColumns
* whereMultiIn
* whereNotNested
* automatic whereIn detection
* support for common table expressions (CTE)

Eloquent builder:
* generateChunked
* insertModels
* insertModelsOnDuplicateKey
* updateWithJoinedModels
* withJoined (query relations using joins instead of eager loading)

Eloquent models:
* allow to specify temporary custom date serialization format
* static access to identifiers, such as table name
* helper functions to create SQL queries using model fields

## Installation

To install via composer run:
    
    composer require mehr-it/lara-db-ext

## Usage

Before the eloquent extensions can be used, the `DbExtensions` trait must be added to all models:

    class User extends Model {
        use DbExtensions;
    }

## ToDo

The readme yet does not cover all functionality.


## updateWithJoinedData()

The `updateWithJoinedData()` method executes an update of multiple database records within a 
single query. This is achieved by creating a virtual table with the update data and joining it
with the target table. See following example:

    DB::table('test_table')
        ->updateWithJoinedData([
            [
                'id'   => 1,
                'name' => 'name a updated',
                'x'    => 12,
            ],
            [
                'id'   => 2,
                'name' => 'name b updated',
                'x'    => 22,
            ],
        ]);
        
This will execute the following SQL statement (with the given data bound to parameters of the join table):

    update 
        "test_table" 
    inner join (
        (select (?) as id, (?) as "name", (?) as "x") 
        union all 
        (select (?) as id, (?) as "name", (?) as "x")
    ) as "data"
    on 
        "test_table"."id" = "data"."id" 
    set 
        "test_table"."name" = "data"."name"',
        "test_table"."x" = "data"."x"'

The other parameters allow to customize the join conditions, the list of fields to be updated
and the alias of the data table. See method documentation for details.


## insertModels()
The `insertModels()` method implements mass inserts for eloquent models. It automatically sets
the timestamps and applies any mutations:

    TestModel::insertModels([
        new TestModel([
            'id'   => 1,
            'name' => 'name a updated',
            'x'    => 12,
        ])
        new TestModel([
            'id'   => 2,
            'name' => 'name b updated',
            'x'    => 22,
        ]),
    ]);

By default, the fields to insert are determined by examining the attributes of the first given 
model. If you only want to insert specific attributes a custom field list can be specified.
See method documentation for details.

Neither are the passed model instances updated nor are any model events triggered. 



## updateWithJoinedModels()

The `updateWithJoinedModels()` method implements the behaviour of `updateWithJoinedData()` for
eloquent models. Instead of a data array, an array of models is expected as first parameter.

    TestModel::updateWithJoinedModels([
        new TestModel([
            'id'   => 1,
            'name' => 'name a updated',
            'x'    => 12,
        ])
        new TestModel([
            'id'   => 2,
            'name' => 'name b updated',
            'x'    => 22,
        ]),
    ]);
    
Timestamps are updated automatically by this method, but neither are the passed model instances
updated nor are any model events triggered. 

Using this method ensures that any mutators and casts are applied to the model data before 
passing it to the database. 

If the model does not have a primary key or you want to join based on other than primary key
field, custom join conditions can be passed as second parameter.

By default, the fields to update are determined by examining the attributes of the first given 
model. If you only want to update specific attributes only or if the first model does not 
contain all attributes you wish to update, a custom update field list can be specified as 
third parameter.

See method documentation for further details and parameters.


## generateChunked() (query builder and eloquent)

When processing a large amount of data, laravel's `chunked()` method is widely used. But it does not
offer a simple way to pass the data on to another consumer. The `generateChunked()` method can 
help here and is available for both, the eloquent and the query builder:

    $generator = User::query()
        ->where('active', true)
        ->generateChunked();

It will return a generator object, which queries data in chunks from the DB and outputs it one record 
after another.

By default, data is queried in chunks of 500 records. This can be changed with the first parameter.
The second parameter accepts a callable, which is invoked for each chunk of data, right
before outputting it:

    $generator = User::query()
            ->where('active', true)
            ->generateChunked(100, function($records) {
            
                return $records->pluck('name');
                                    
            });
            
As shown above, the callback can transform the chunk data in any desired way and it's return value
will be output by the generator. **The only restriction is that it must return an iterable**. It 
can even filter out some records, which should not be outputted.


### selectPrefixed

Sometimes you want certain column names to be prepended with a given prefix. Following
example prefixes all returned column names with the `"user_"` prefix:

	DB:table('users')->selectPrefixed(['id', 'name'], 'user_')->get();
	
	// => [['user_id' => 1, 'user_name' => 'John']]
	
This becomes really useful when you're using joins on tables with conflicting column names:

	DB:table('users')
		->join('children', 'users.id', '=', 'children.user_id')
		->addSelectPrefixed('users.*', 'user_')
		->addSelectPrefixed('children.*', 'child_')
		->get();
	
	// => [[ 'user_id' => 1, 'user_name' => 'John', 'child_id' => 12, 'child_name' => 'Maria']]
	
Without prefixing, the `children` fields would silently overwrite the `users` fields.
As you see it also works using wildcard column selectors. And the best: it does
not produce an extra query to obtain the table structure. In fact it does not depend on
table columns at all:
 
It can also be used with expressions:

	DB:table('users')->selectPrefixed(new Expression('count(*) as myCount'), 'user_')->get();
	
	// => [['user_myCount' => 123]]
	
	
You can also set multiple prefixes in one call if you omit the prefix parameter and pass an
associative array as first parameter

	DB:table('users')
		->join('children', 'users.id', '=', 'children.user_id')
		->selectPrefixed([
			'user_'  => 'users.*',
			'child_' => ['id', 'name'],
		])
		->get();
	
	
### whereNotNested

If you want to negate a nested where clause, the new `whereNotNested` function comes in:

	DB:table('users')
		->whereNotNested(function($query) {
			$query->where('name', 'John');
			$query->where('age', '>', 49);
		})
		->get();
    		
This would produce following query:

	SELECT * FROM users WHERE NOT (first_name = 'John' AND age > 49) 
	
	
### whereMultiIn

Some SQL dialects allow to compare multiple columns using the `IN` operator. You may use
it using the `whereMultiIn` function:

	DB:table('users')
		->whereMultiIn(['name', 'age'], [
			['John', 38],
			['Ida', 49],
		])
		->get();
		
This would produce following query:

	SELECT * FROM users WHERE (name, age) IN ( ('John', 38), ('Ida', 49) )
	
You may even pass a sub select instead of a values array:

	DB:table('users')
		->whereMultiIn(['name', 'age'], function ($query) {
			return $query->select(['parent_name', 'parent_age'])
				->from('children')
				->where('age', '<', 3);
		})
		->get();


### whereMultiColumns

The `whereMultiColumns` accepts multiple columns to be compared:

	DB:table('users')
		->whereMultiColumns(['name', 'age'], ['n', 'a'])
		->get();

This would produce following query:

	SELECT * FROM users WHERE (name = n AND age = a)
	
Operators are applied to the combination of columns. That's why only `=`, `!=`, `<>` are
supported.

	DB:table('users')
		->whereMultiColumns(['name', 'age'], '!=', ['n', 'a'])
		->get();
	
This would produce following query:

	SELECT * FROM users WHERE NOT (name = n AND age = a)
	
	
### Automatic whereIn detection

Another improvement is that the `where` functions now can be used with an array as
values parameter, so it get's automatically converted to `whereIn`. Of course this
also works with multiple columns:

	DB:table('users')
		->where('name', ['John', 'Ida'])
		->get();
		
	DB:table('users')
		->where(['name', 'age'], [
			['John', 38],
			['Ida', 49],
		])
		->get();
		
This works also for the `whereColumn` functions:

	DB:table('users')
		->whereColumn(['name', 'age'], ['n', 'a'])
		->get();
		
		
## Timezone handling

Time zone handling in database can be complicated. Laravel passes dates without timezone
information to the database. This behavior is correct as the data is usually stored without
any timezone information. When reading dates, Laravel (Eloquent) interprets dates using the
default application timezone.

But Laravel does not ensure that DateTime parameters are using the application timezone when
passing them to the database. So if you pass a date with a different timezone to database, it
will be interpreted using another timezone on reading.

To ensure all DateTime parameters are converted to application timezone before sending them to
database, this package adds the "adapt_timezone" configuration option for database connections.
If set to true, any DateTime values will be converted to the application timezone before passing
them to the database.

The `AdaptsAttributeTimezone` trait implements the timezone adaption
for Eloquent model attributes.

### Database session timezone
Some databases, such as MySQL use the database session timezone when converting dates to
timestamps (see [MySQL Documentation for details](https://dev.mysql.com/doc/refman/8.0/en/datetime.html)).
For MySQL this does only affect storing dates to TIMESTAMP columns (not for DATETIME columns) and
NOW() and CURTIME() functions. Therefore you should always configure the "timezone" parameter
for connections to the same value as the application timezone!



### Credits

Thanks to Jonas Staudenmeir and contributers of the staudenmeir/laravel-ct package, which the
common table expression support is based on.