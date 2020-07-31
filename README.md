# Laravel DB extension
[![Latest Version on Packagist](https://img.shields.io/packagist/v/mehr-it/lara-db-ext.svg?style=flat-square)](https://packagist.org/packages/mehr-it/lara-db-ext)
[![Build Status](https://travis-ci.org/mehr-it/lara-db-ext.svg?branch=master)](https://travis-ci.org/mehr-it/lara-db-ext)

This package implements various extensions and improvements for Laravel's database abstraction.

Query builder:

* generateChunked
* insertOnDuplicateKey
* updateWithJoinedData

Eloquent builder:

* generateChunked
* insertModels
* insertModelsOnDuplicateKey
* updateWithJoinedModels



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




### Credits

Thanks to Jonas Staudenmeir and contributers of the staudenmeir/laravel-ct package, which the
common table expression support is based on.